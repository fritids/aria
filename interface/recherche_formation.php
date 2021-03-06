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
	session_name("preinsc");
	session_start();

	include "../configuration/aria_config.php";
	include "$__INCLUDE_DIR_ABS/vars.php";
	include "$__INCLUDE_DIR_ABS/fonctions.php";
	include "$__INCLUDE_DIR_ABS/db.php";

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	if(!isset($_SESSION["authentifie"]))
	{
		session_write_close();
		header("Location:../index.php");
		exit();
	}

	if(!isset($_SESSION["comp_id"]))
	{
		session_write_close();
		header("Location:composantes.php");
		exit();
	}

	$dbr=db_connect();

	if(isset($_POST["recherche"]) || isset($_POST["recherche_x"]))
	{
		$formation=mb_strtolower(trim($_POST["formation"]));

		$mention=mb_strtolower(trim($_POST["mention"]));

		if($formation=="" && $mention=="")
			$champs_vides=1;
		elseif(preg_match("/([a-z\'\ ]+)/i", $formation) || preg_match("/([a-z\'\ ]+)/i", $mention))
		{
			$formation=clean_str_requete($formation);
			$mention=clean_str_requete($mention);

			// Second test apr�s nettoyage des chaines
			if($formation=="" && $mention=="")
				$champs_vides=1;
			else
			{
				if($formation=="")
					$critere_recherche="AND lower($_DBC_mentions_nom) SIMILAR TO '%$mention%' ";
				elseif($mention=="")
					$critere_recherche="AND lower($_DBC_specs_nom) SIMILAR TO '%$formation%' ";
				else
					$critere_recherche="AND (lower($_DBC_mentions_nom) SIMILAR TO '%$mention%' AND lower($_DBC_specs_nom) SIMILAR TO '%$formation%') ";

				$result=db_query($dbr,"SELECT $_DBC_propspec_id, $_DBC_annees_annee, $_DBC_mentions_nom, $_DBC_specs_nom,
														$_DBC_propspec_finalite, $_DBC_universites_nom, $_DBC_composantes_id, $_DBC_composantes_nom
												FROM $_DB_propspec, $_DB_annees, $_DB_specs, $_DB_universites, $_DB_composantes,
													  $_DB_mentions
											WHERE $_DBC_propspec_annee=$_DBC_annees_id
											AND $_DBC_specs_mention_id=$_DBC_mentions_id
											AND $_DBC_propspec_id_spec=$_DBC_specs_id
											AND $_DBC_propspec_comp_id=$_DBC_composantes_id
											AND $_DBC_composantes_univ_id=$_DBC_universites_id
											AND $_DBC_propspec_active='1'
											AND $_DBC_propspec_manuelle='0'
											$critere_recherche
												ORDER BY $_DBC_universites_nom, $_DBC_composantes_nom, $_DBC_mentions_nom,
															$_DBC_annees_annee, $_DBC_specs_nom, $_DBC_propspec_finalite");

				$rows=db_num_rows($result);
				$nb_trouves=$rows;
			}
		}
		else
			$format=1;
	}
	
	en_tete_candidat();
	menu_sup_candidat($__MENU_RECH);
			
?>
<div class='main'>
	<?php
		titre_page_icone("Rechercher une formation", "xmag_32x32_fond.png", 15, "L");

		if(isset($$champs_vides))
			message("Le formulaire ne doit pas �tre vide", $__ERREUR);

		if(isset($format))
			message("Le format du texte recherch� est incorrect", $__ERREUR);

		if(!isset($nb_trouves))
		{
			message("<center>
							Si les deux champs sont compl�t�s, la recherche portera sur les formations
							<br>appartenant explicitement � la mention indiqu�e.
						</center>", $__INFO);

			print("<form action='$php_self' method='POST' name='form1'>\n");
	?>

	<table align='center'>
	<tr>
		<td class='td-complet fond_menu2' colspan='2'>
			<font class='Texte_menu2'><b>Recherche ... </b></font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu'>
			<font class='Texte_menu'><b>Intitul� ou partie de l'intitul� de la formation : </b><br>(N'entrez PAS l'ann�e L2, M1 ..)</font>
		</td>
		<td class='td-milieu fond_menu'>
			<input type='text' name='formation' value='' maxlength='60' size='30'>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu'>
			<font class='Texte_menu'><b>Mention ou partie de la mention : </font>
		</td>
		<td class='td-milieu fond_menu'>
			<input type='text' name='mention' value='' maxlength='60' size='30'>
		</td>
	</tr>
	</table>	

	<div class='centered_icons_box'>
		<input type="image" src="<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>" alt="Rechercher" name="recherche" value="Rechercher">
		</form>
	</div>

	<script language="javascript">
		document.form1.formation.focus()
	</script>
		<?php
		}
		else // r�sultat de la recherche	
		{
			if(isset($nb_trouves) && $nb_trouves!=0)
			{
				if($nb_trouves>1)			
					print("<div class='centered_box'>
								<font class='Texte'><i>$nb_trouves formations trouv�es :</i></font>
							</div>\n");
				else
					print("<div class='centered_box'>
								<font class='Texte'><i>$nb_trouves formation trouv�e :</i></font>
							</div>\n");

				print("<table align='center'>\n");

				$old_univ=$old_comp=$old_mention="--";
			
				for($i=0; $i<$rows;$i++)
				{
					list($propspec_id, $annee_nom, $mention_nom, $spec_nom, $finalite, $univ_nom, $comp_id, $comp_nom)=db_fetch_row($result,$i);

					$formation=$annee_nom=="" ? "$spec_nom" : "$annee_nom - $spec_nom";

					if($univ_nom!=$old_univ)
					{
						if($i)
							print("<tr>
										<td class='td-separation' height='15' colspan='3'></td>
									 </tr>\n");

						print("<tr>
									<td class='td-gauche fond_menu2' colspan='3'>
										<font class='Texte_menu3'><strong>$univ_nom</strong></font>
									</td>
								</tr>
								<tr>
									<td class='td-gauche fond_menu2'>
										<font class='Texte_menu2'><strong>Composante / Formation</strong></font>
									</td>
									<td class='td-milieu fond_menu2'>
										<font class='Texte_menu2'><strong>Finalit�</strong></font>
									</td>
									<td class='td-droite fond_menu2'>
										<font class='Texte_menu2'><strong>Session</strong></font>
									</td>
								</tr>\n");

						$old_univ=$univ_nom;
						$old_comp=$old_mention="--";
					}

					// Dates
					$res_session=db_query($dbr,"SELECT $_DBC_session_ouverture, $_DBC_session_fermeture 
																FROM $_DB_session
															WHERE $_DBC_session_propspec_id='$propspec_id'
															AND $_DBC_session_periode='$__PERIODE'
														GROUP BY $_DBC_session_ouverture, $_DBC_session_fermeture
														ORDER BY $_DBC_session_ouverture, $_DBC_session_fermeture");

					$nb_sessions=db_num_rows($res_session);			

					if($nb_sessions)
					{
						// Une seule date ? on affiche
						if($nb_sessions==1)
						{
							list($ouv,$ferm)=db_fetch_row($res_session, 0);

							if($ouv!="" && $ferm!="" )
							{
								if($ouv<time() && $ferm>time())
									$dates_txt="<font class='Textevert_menu'>du " . date_fr("j F Y", $ouv) . " au " . date_fr("j F Y", $ferm) . "</font>";
								else
									$dates_txt="<font class='Texte_important_menu'>du " . date_fr("j F Y", $ouv) . " au " . date_fr("j F Y", $ferm) . "</font>";
							}
							else
								$dates_txt="<font class='Texte_menu'>Dates non d�termin�es pour les candidatures $__PERIODE-".($__PERIODE+1)."</font>";
						}
						else // plusieurs dates : si une session est ouverte, on l'affiche, sinon on indique la plus proche
						{
							for($j=0; $j<$nb_sessions; $j++)
							{
								list($ouv,$ferm)=db_fetch_row($res_session, $j);

								if($ouv<time() && $ferm>time())
								{
									$dates_txt="<font class='Textevert_menu'>du " . date_fr("j F Y", $ouv) . " au " . date_fr("j F Y", $ferm) . "</font>";
									$j=$nb_sessions;
								}
								elseif($ouv>time()) // la plus proche dans le futur
								{
									$dates_txt="<font class='Texteorange'>du " . date_fr("j F Y", $ouv) . " au " . date_fr("j F Y", $ferm) . "</font>";
									$j=$nb_sessions;
								}
								else // La derni�re session (d�j� ferm�e)
									$dates_txt="<font class='Texte_important'>du " . date_fr("j F Y", $ouv) . " au " . date_fr("j F Y", $ferm) . "</font>";
							}
						}
					}
					else
						$dates_txt="<font class='Texte_menu'>Dates non d�termin�es pour les candidatures $__PERIODE-".($__PERIODE+1)."</font>";

					db_free_result($res_session);

					if($comp_nom!=$old_comp)
					{
						if($i && $univ_nom==$old_univ)
							print("<tr>
										<td class='td-separation' height='15' colspan='3'></td>
										</tr>\n");

						$crypt_params=crypt_params("co=$comp_id");

						print("<tr>
									<td class='td-gauche fond_menu' colspan='3'>
										<a href='composantes.php?p=$crypt_params' class='lien_menu_gauche'><b>$comp_nom</b></a>
									</td>
								</tr>\n");

						$old_comp=$comp_nom;
						$old_mention="--";
					}

					if($mention_nom!=$old_mention)
					{
						print("<tr>
									<td class='td-gauche fond_menu' colspan='3'>
										<font class='Texte_menu'><strong>Mention : $mention_nom</strong></font>
									</td>
								 </tr>\n");

						$old_mention=$mention_nom;
					}

					print("<tr>
								<td class='td-gauche fond_page'>
									<font class='Texte'>$formation</font>
								</td>
								<td class='td-milieu fond_page'>
									<font class='Texte'>$tab_finalite[$finalite]</font>
								</td>
								<td class='td-droite fond_page'>$dates_txt</td>
							</tr>\n");
				}

				print("</table>\n");
			}
			else
				message("Aucune formation ne correspond � votre recherche", $__WARNING);
			
			print("<div class='centered_icons_box'>
						<a href='$php_self' target='_self' class='lien2'><img src='$__ICON_DIR/xmag_32x32_fond.png' alt='Retour' border='0'></a>
					</div>\n");
			
			db_free_result($result);
		}

		db_close($dbr);
	?>
</div>
<?php
	pied_de_page_candidat();
?>
</body></html>
