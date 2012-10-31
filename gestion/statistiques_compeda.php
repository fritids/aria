<?php
/*
=======================================================================================================
APPLICATION ARIA - UNIVERSITE DE STRASBOURG

LICENCE : CECILL-B
Copyright Universit� de Strasbourg
Contributeur : Christophe Boccheciampe - Janvier 2006
Adresse : cb@dpt-info.u-strasbg.fr

L'application utilise des �l�ments �crits par des tiers, plac�s sous les licences suivantes :

Ic�nes :
- CrystalSVG (http://www.everaldo.com), sous licence LGPL (http://www.gnu.org/licenses/lgpl.html).
- Oxygen (http://oxygen-icons.org) sous licence LGPL-V3
- KDE (http://www.kde.org) sous licence LGPL-V2

Librairie FPDF : http://fpdf.org (licence permissive sans restriction d'usage)

=======================================================================================================
[CECILL-B]

Ce logiciel est un programme informatique permettant � des candidats de d�poser un ou plusieurs
dossiers de candidatures dans une universit�, et aux gestionnaires de cette derni�re de traiter ces
demandes.

Ce logiciel est r�gi par la licence CeCILL-B soumise au droit fran�ais et respectant les principes de
diffusion des logiciels libres. Vous pouvez utiliser, modifier et/ou redistribuer ce programme sous les
conditions de la licence CeCILL-B telle que diffus�e par le CEA, le CNRS et l'INRIA sur le site
"http://www.cecill.info".

En contrepartie de l'accessibilit� au code source et des droits de copie, de modification et de
redistribution accord�s par cette licence, il n'est offert aux utilisateurs qu'une garantie limit�e.
Pour les m�mes raisons, seule une responsabilit� restreinte p�se sur l'auteur du programme, le titulaire
des droits patrimoniaux et les conc�dants successifs.

A cet �gard l'attention de l'utilisateur est attir�e sur les risques associ�s au chargement, �
l'utilisation, � la modification et/ou au d�veloppement et � la reproduction du logiciel par l'utilisateur
�tant donn� sa sp�cificit� de logiciel libre, qui peut le rendre complexe � manipuler et qui le r�serve
donc � des d�veloppeurs et des professionnels avertis poss�dant  des  connaissances informatiques
approfondies. Les utilisateurs sont donc invit�s � charger et tester l'ad�quation du logiciel � leurs
besoins dans des conditions permettant d'assurer la s�curit� de leurs syst�mes et ou de leurs donn�es et,
plus g�n�ralement, � l'utiliser et l'exploiter dans les m�mes conditions de s�curit�.

Le fait que vous puissiez acc�der � cet en-t�te signifie que vous avez pris connaissance de la licence
CeCILL-B, et que vous en avez accept� les termes.

=======================================================================================================
*/
?>
<?php
	session_name("preinsc_gestion");
	session_start();

	include "../configuration/aria_config.php";
	include "$__INCLUDE_DIR_ABS/vars.php";
	include "$__INCLUDE_DIR_ABS/fonctions.php";
	include "$__INCLUDE_DIR_ABS/db.php";	

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	verif_auth();

	$periode_annee_courante=substr($__PERIODE, 2, 2) . "010100020000000";

	if(isset($_POST["go"]) || isset($_POST["go_x"])) // validation du formulaire
		$current_periode=$_POST["periode"];
	else // par d�faut : ann�e en cours
 		$current_periode=$__PERIODE;

	$periode_txt="$current_periode - " . ($current_periode+1);

	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>
<div class='main'>
	<?php
		titre_page_icone("Candidatures et r�sultats en chiffres", "kpercentage_32x32_fond.png", 15, "L");

		// Selection de la p�riode : on prend les bornes min et max des identifiants de candidatures (timestamps)

		$dbr=db_connect();

		$result=db_query($dbr,"SELECT min($_DBC_cand_periode), max($_DBC_cand_periode) FROM $_DB_cand
										WHERE $_DBC_cand_propspec_id IN (SELECT $_DBC_propspec_id FROM $_DB_propspec
																					WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]')");
		$rows=db_num_rows($result);

		if($rows)
		{
			list($minY,$maxY)=db_fetch_row($result,0);

			print("<form action='$php_self' method='POST' name='form1'>

						<table cellspacing='0' cellpadding='4' border='0' align='center'>
						<tr>
							<td class='td-gauche fond_menu'>
								<font class='Texte_menu'><b>Statistiques de Commission P�dagogique sur la p�riode</b></font>
							</td>
							<td class='td-milieu fond_menu' valign='middle'>
								<select name='periode' size='1'>\n");

			$minY=$minY=="" ? $__PERIODE : $minY;
			$maxY=$maxY=="" ? $__PERIODE : $maxY;

			for($i=$maxY; $i>=$minY; $i--)
			{
				if($i==$current_periode)
					$selected="selected='1'";
				else
					$selected="";

				print("<option value='$i' $selected>$i</option>");
			}

			print("</select>
					</td>
					<td class='td-droite fond_menu' valign='middle' width='40'>
						<input type='image' src='$__ICON_DIR/forward_32x32_menu.png' alt='Valider' name='go' value='Valider'>
					</td>
				</tr>
				</table>
				</form>
				<br>\n");
		}

		db_free_result($result);
	?>

	<center>
		<font class='Texte_important'>
			Seules les pr�candidatures <b>recevables</b> sont prises en compte dans ces listes.
		</font>
		<br><br>
		<a href='tabs_stats.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/back_32x32.png" ; ?>' alt='Retour au menu pr�c�dent' border='0'></a>
	</center>
	<br>

	<table width='70%' cellpadding='4' cellspacing='0' border='0' align='center'>
	<tr>
		<td align='left' valign='top' nowrap='true'>
			<font class='Texte'><b>Statistiques globales</b></font>
		</td>
		<td align='left' valign='top' nowrap='true'>
			<font class='Texte'><b>Statistiques pour la p�riode <?php echo $periode_txt; ?></b></font>
		</td>
	</tr>
	<tr>
		<td align='left' valign='top' nowrap='true'>
			<font class='Texte'>
			Nombre total de candidats <b>recevables</b> :
				<?php
					// nombre total
					$result=db_query($dbr,"SELECT count(*) FROM $_DB_candidat
													WHERE $_DBC_candidat_id IN (SELECT distinct($_DBC_cand_candidat_id)
																							FROM $_DB_cand, $_DB_specs, $_DB_propspec
																						WHERE $_DBC_propspec_id_spec=$_DBC_specs_id
																						AND $_DBC_cand_propspec_id=$_DBC_propspec_id
																						AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
																						AND $_DBC_cand_statut='$__PREC_RECEVABLE')");

					list($nombre_total)=db_fetch_row($result,0);
					db_free_result($result);

/*
					// candidats ajout�s depuis le 1er janvier de l'ann�e courante
					$result=db_query($dbr,"SELECT count(*) FROM $_DB_candidat
													WHERE $_DBC_candidat_id>'$periode_annee_courante'
													AND $_DBC_candidat_id IN (SELECT distinct($_DBC_cand_candidat_id)
																						FROM $_DB_cand, $_DB_specs, $_DB_propspec
																					WHERE $_DBC_propspec_id_spec=$_DBC_specs_id
																					AND $_DBC_cand_propspec_id=$_DBC_propspec_id
																					AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
																					AND $_DBC_cand_periode='$__PERIODE'
																					AND $_DBC_cand_statut='$__PREC_RECEVABLE')");

					list($nb_nouveaux)=db_fetch_row($result,0);
					db_free_result($result);
*/
					print("$nombre_total\n");
//								<br>&nbsp;&nbsp;- dont $nb_nouveaux candidats pour la p�riode $periode_txt
//								<br><br>

					print("<br>Nombre total de dossiers en Commission P�dagogique : ");

					// nombre total
					$result=db_query($dbr,"SELECT count(*) FROM $_DB_cand, $_DB_specs, $_DB_propspec
														WHERE $_DBC_propspec_id_spec=$_DBC_specs_id
													AND $_DBC_cand_propspec_id=$_DBC_propspec_id
													AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
													AND $_DBC_cand_statut='$__PREC_RECEVABLE'");
					list($nombre_total)=db_fetch_row($result,0);

					db_free_result($result);
		/*
					// dossiers ajout�s depuis le 1er janvier de l'ann�e courante
					$result=db_query($dbr,"SELECT count(*) FROM $_DB_cand, $_DB_specs, $_DB_propspec
														WHERE $_DBC_propspec_id_spec=$_DBC_specs_id
													AND $_DBC_cand_propspec_id=$_DBC_propspec_id
													AND $_DBC_propspec_comp_id=$_SESSION[comp_id]
													AND $_DBC_cand_periode='$__PERIODE'
													AND $_DBC_cand_statut='$__PREC_RECEVABLE'");

					list($nb_nouveaux)=db_fetch_row($result,0);
					db_free_result($result);
*/
					print("$nombre_total\n");

//							 <br>&nbsp;&nbsp;- dont $nb_nouveaux pour la p�riode $periode_txt");
				?>
			</font>
		</td>
		<td align='left' valign='top' nowrap='true'>
			<font class='Texte'>
				<?php
					// �tudiants ajout�s pendant l'ann�e s�lectionn�e
					$result=db_query($dbr,"SELECT count(*) FROM $_DB_candidat
													WHERE $_DBC_candidat_id IN (SELECT distinct($_DBC_cand_candidat_id)
																							FROM $_DB_cand, $_DB_specs, $_DB_propspec
																						WHERE $_DBC_propspec_id_spec=$_DBC_specs_id
																						AND $_DBC_cand_propspec_id=$_DBC_propspec_id
																						AND $_DBC_propspec_comp_id=$_SESSION[comp_id]
																						AND $_DBC_cand_periode='$current_periode'
																						AND $_DBC_cand_statut='$__PREC_RECEVABLE')");

					list($nb_etudiants_Y)=db_fetch_row($result,0);
					db_free_result($result);

					// dossiers ajout�s pendant l'ann�e s�lectionn�e
					$result=db_query($dbr,"SELECT count(*) FROM $_DB_cand, $_DB_specs, $_DB_propspec
													WHERE $_DBC_propspec_id_spec=$_DBC_specs_id
													AND $_DBC_cand_propspec_id=$_DBC_propspec_id
													AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
													AND $_DBC_cand_periode='$current_periode'
													AND $_DBC_cand_statut='$__PREC_RECEVABLE'");

					list($nb_dossiers_Y)=db_fetch_row($result,0);
					db_free_result($result);

					print("Candidats <b>recevables</b> : $nb_etudiants_Y
								<br>Dossiers en Commission P�dagogique : $nb_dossiers_Y");
				?>
		</font>
		</td>
	</tr>
	</table>

	<br><br>

	<table width='70%' cellpadding='4' cellspacing='0' border='0' align='center'>
	<tr>
		<td align='left' valign='top' nowrap='true'>
			<?php
				// R�cup�ration et stockage des d�cisions pour les tableaux
				$result=db_query($dbr,"SELECT $_DBC_decisions_id, $_DBC_decisions_texte FROM $_DB_decisions
												WHERE $_DBC_decisions_id IN (SELECT distinct($_DBC_decisions_comp_dec_id) FROM $_DB_decisions_comp
																						WHERE $_DBC_decisions_comp_comp_id='$_SESSION[comp_id]')
												ORDER BY $_DBC_decisions_texte");

				$rows=db_num_rows($result);

				$decisions_array=array();

				for($i=0; $i<$rows;$i++)
				{
					list($dec_id,$dec_texte)=db_fetch_row($result,$i);
					$decisions_array[$dec_id]=array();
					$decisions_array[$dec_id]["texte"]=$dec_texte;

					// Choix de la couleur pour les d�cisions

					if($dec_id<0) // pour les dossiers n�cessitant encore un traitement
						$decisions_array[$dec_id]["couleur"]="#FF8800";
					elseif($dec_id>0) // dossiers trait�s
						$decisions_array[$dec_id]["couleur"]="#00BB00";
					else
						$decisions_array[$dec_id]["couleur"]="#CC0000";
				}
				db_free_result($result);

				// tri du tableau
				ksort($decisions_array,SORT_NUMERIC);

				$nb_decisions=count($decisions_array);
				$span=$nb_decisions+1;

				// boucle sur les ann�es
				$result=db_query($dbr,"SELECT $_DBC_propspec_annee, $_DBC_annees_annee, $_DBC_annees_ordre, count(*)
												FROM $_DB_cand, $_DB_annees, $_DB_specs, $_DB_propspec
											WHERE $_DBC_propspec_annee=$_DBC_annees_id
											AND $_DBC_propspec_id_spec=$_DBC_specs_id
											AND $_DBC_cand_propspec_id=$_DBC_propspec_id
											AND $_DBC_propspec_comp_id=$_SESSION[comp_id]
											AND $_DBC_cand_statut='$__PREC_RECEVABLE'
											AND $_DBC_cand_periode='$current_periode'
												GROUP BY $_DBC_propspec_annee, $_DBC_annees_annee, $_DBC_annees_ordre
												ORDER BY $_DBC_annees_ordre ASC");
				$rows=db_num_rows($result);

				for($i=0; $i<$rows; $i++)
				{
					list($id_annee, $annee, $annee_ordre, $nombre_inscr_annee)=db_fetch_row($result,$i);

					// REQUETE POUR LES DECISIONS : select annee,specialite,decision,count(*) from inscriptions group by annee,specialite,decision order by annee,specialite,decision;

					$nom_annee=$annee=="" ? "Ann�es particuli�res" : $annee;

					print("<table width='100%' cellpadding='2' cellspacing='0' align='center' border='0'>
								<tr>
									<td class='td-gauche fond_menu2' style='text-align:center' colspan='$span'>
										<font class='Texte_menu2'><b>$nom_annee ($nombre_inscr_annee dossiers)</b></font>
									</td>
								</tr>
								<tr>
									<td class='td-gauche fond_menu'>
										<font class='Texte_menu'><b>Formation (trait�s / total)</b></font>
									</td>\n");

					// ligne donnant l'intitul� des d�cisions
					foreach($decisions_array as $sub_dec_array)
					{
						$dec_intitule=$sub_dec_array["texte"];
						print("<td class='td-milieu fond_menu' style='text-align:center'>
									<font class='Texte_menu'><b>$dec_intitule</b></font>
								</td>\n");
					}

					print("</tr>");

					// plutot que de s�lectionner toutes les specs d'un coup avec les diff�rentes d�cisions (qui n�cessiterait ensuite
					// un traitement php assez lourd), on boucle sur les diff�rentes formations de cette ann�e :
					$result2=db_query($dbr,"SELECT $_DBC_propspec_id_spec, $_DBC_specs_nom_court, count(*)
														FROM $_DB_cand, $_DB_specs, $_DB_propspec
													WHERE $_DBC_propspec_id_spec=$_DBC_specs_id
													AND $_DBC_propspec_annee='$id_annee'
													AND $_DBC_cand_propspec_id=$_DBC_propspec_id
													AND $_DBC_propspec_comp_id=$_SESSION[comp_id]
													AND $_DBC_cand_periode='$current_periode'
													AND $_DBC_cand_statut='$__PREC_RECEVABLE'
														GROUP BY $_DBC_propspec_id_spec, $_DBC_specs_nom_court
														ORDER BY $_DBC_specs_nom_court");
					$rows2=db_num_rows($result2);

					$fond1='fond_blanc';
					$fond2="fond_gris_E";

					for($j=0; $j<$rows2; $j++)
					{
						list($spec_id, $spec_nom,$nombre_inscr)=db_fetch_row($result2,$j);

						// Nombre de dossiers trait�s dans cette formation
						$result3=db_query($dbr,"SELECT count(*) FROM $_DB_cand, $_DB_propspec
															WHERE $_DBC_propspec_annee='$id_annee'
														AND $_DBC_propspec_id_spec='$spec_id'
														AND $_DBC_cand_propspec_id=$_DBC_propspec_id
														AND $_DBC_cand_statut='$__PREC_RECEVABLE'
														AND $_DBC_cand_periode='$current_periode'
														AND $_DBC_cand_decision!='$__DOSSIER_NON_TRAITE'");
						list($nb_traites)=db_fetch_row($result3, 0);

						if($nb_traites=="")
							$nb_traites=0;

						print("<tr>
									<td class='td-gauche $fond1'>
										<font class='Texte'>$spec_nom ($nb_traites / $nombre_inscr)</font></td>\n");

						// s�lection des d�cisions pour ce couple ann�e/formation
						$result3=db_query($dbr,"SELECT $_DBC_cand_decision, count(*) FROM $_DB_cand, $_DB_propspec
														WHERE $_DBC_propspec_annee='$id_annee'
															AND $_DBC_cand_propspec_id=$_DBC_propspec_id
															AND $_DBC_propspec_id_spec=$spec_id
															AND $_DBC_cand_statut='$__PREC_RECEVABLE'
															AND $_DBC_cand_periode='$current_periode'
														GROUP BY $_DBC_cand_decision
														ORDER BY $_DBC_cand_decision");

						$rows3=db_num_rows($result3);

						// on met le compteur des d�cisions � 0
						foreach($decisions_array as $key => $sub_dec_array)
							$decisions_array[$key]["nombre"]="";

						for($k=0; $k<$rows3;$k++)
						{
							list($decision_id,$nombre)=db_fetch_row($result3,$k);
							$decisions_array[$decision_id]["nombre"]=$nombre;
						}
						db_free_result($result3);

						// les donn�es sont pr�tes, on affiche le tableau
						foreach($decisions_array as $sub_dec_array)
						{
							$nombre=$sub_dec_array["nombre"];
							$couleur=$sub_dec_array["couleur"];

							print("<td class='td-milieu $fond1' style='text-align:center'>
										<font class='Texte' style='color:$couleur'>$nombre</font>
									</td>\n");
						}

						switch_vals($fond1, $fond2);

						print("</tr>\n");
					}
					print("</table><br><br>\n");

					db_free_result($result2);
				}
				db_free_result($result);
			?>
			</font>
		</td>
	</tr>
	</table>
	</form>
</div>
<?php
	db_close($dbr);
	pied_de_page();
?>

</body>
</html>
