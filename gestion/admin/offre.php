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

	include "../../configuration/aria_config.php";
	include "$__INCLUDE_DIR_ABS/vars.php";
	include "$__INCLUDE_DIR_ABS/fonctions.php";
	include "$__INCLUDE_DIR_ABS/db.php";


	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	verif_auth("$__GESTION_DIR/login.php");

	// Param�tre permettant de masquer/afficher les formations d�sactiv�es
	if(isset($_GET["m"]) && ($_GET["m"]==1 || $_GET["m"]==0))
		$masquees=$_GET["m"];
	elseif(isset($_SESSION["affichage_masquees"]))
		$masquees=$_SESSION["affichage_masquees"];
	else // par d�faut : masqu�es
		$masquees=0;

	$dbr=db_connect();

	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>

<div class='main'>
	<?php
		titre_page_icone("Liste des formations", "contents_32x32_fond.png", 10, "L");

		if(isset($_GET["succes"]) && $_GET["succes"]==1)
			message("La formation a �t� modifi�e avec succ�s.", $__SUCCES);
			
		if(isset($_GET["info_succes"]) && $_GET["info_succes"]==1)
			message("Les informations sur la formation ont �t� enregistr�es avec succ�s.", $__SUCCES);

		if(isset($masquees) && $masquees==1)
		{
			$condition_masquees="";
			$lien_masquees="<a href='$php_self?m=0' class='lien_bleu_12'><strong>Masquer les formations d�sactiv�es</strong></a>";

			message("<center>
							Les formations sur fond gris sont d�sactiv�es.
							<br>Elles n'apparaissent pas sur la plupart des listes et les candidats ne pourront pas les s�lectionner
							<br><br>$lien_masquees
						</center>", $__INFO);


		}
		elseif(db_num_rows(db_query($dbr,"SELECT * FROM $_DB_propspec WHERE $_DBC_propspec_active='0'
													 AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'")))
		{
			$condition_masquees="AND $_DBC_propspec_active='1'";
			$lien_masquees="<a href='$php_self?m=1' class='lien_bleu_12'><strong>Afficher les formations d�sactiv�es</strong></a>";

			message("$lien_masquees", $__INFO);
		}
		else
			$condition_masquees=$lien_masquees="";

		$result=db_query($dbr,"SELECT $_DBC_propspec_id, $_DBC_annees_annee, $_DBC_specs_nom, $_DBC_propspec_finalite, 
												$_DBC_specs_mention_id, $_DBC_mentions_nom, $_DBC_propspec_selective, $_DBC_propspec_resp, 
												$_DBC_propspec_entretiens, $_DBC_propspec_manuelle, $_DBC_propspec_active, $_DBC_propspec_info
											FROM $_DB_propspec, $_DB_annees, $_DB_specs, $_DB_mentions
										WHERE $_DBC_propspec_annee=$_DBC_annees_id
										AND $_DBC_propspec_id_spec=$_DBC_specs_id
										AND $_DBC_specs_mention_id=$_DBC_mentions_id
										AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
										$condition_masquees
											ORDER BY $_DBC_annees_ordre, $_DBC_mentions_nom, $_DBC_specs_nom_court, $_DBC_propspec_finalite");

		$rows=db_num_rows($result);

		$old_annee="===="; // on initialise � n'importe quoi (sauf vide)
		$old_mention="===="; // idem
		$j=0;

		if($rows)
		{
			print("<table border='0' align='center' cellpadding='4'>\n");

			for($i=0; $i<$rows; $i++)
			{
				list($propspec_id, $annee, $spec_nom, $finalite, $mention, $mention_nom, $selective, $resp, 
						$entretiens, $manuelle, $active, $info_formation)=db_fetch_row($result, $i);

				$nom_finalite=$tab_finalite[$finalite];

				if($annee=="")
					$annee="Ann�es particuli�res";

				if($annee!=$old_annee)
				{
					if($i)
						print("</table>
									</td>\n");

					if(!$j)
					{
						if($i)
							print("</tr>\n");

						print("<tr>
									<td align='center' valign='top' style='padding-top:10px;'>\n");

						$j=1;
					}
					else
					{
						print("<td align='center' valign='top' style='padding-top:10px;'>\n");

						$j=0;
					}

					$old_annee=$annee;

					print("<table align='center'>
								<tr>
									<td class='fond_menu2' colspan='2' style='padding:4px 20px 4px 20px;'>
										<font class='Texte_menu2'><b>$annee</b></font>
									</td>
<!--
									<td class='fond_menu2' style='padding:4px 20px 4px 20px;'>
										<font class='Texte_menu2'><b>S�lective</b></font>
									</td>
-->
									<td class='fond_menu2' style='padding:4px 20px 4px 20px;'>
										<font class='Texte_menu2'><b>Entretiens</b></font>
									</td>
								</tr>\n");

					$old_mention="====";
					$nb=0;
				}

				if($old_mention!=$mention)
				{
					$old_mention=$mention;

					print("<tr>
								<td class='td-gauche fond_page' height='20' align='center' colspan='3'>
									<font class='Texte'><b>$mention_nom</b></font>
								</td>
							</tr>\n");
				}

				$selective_text=$selective ? "Oui" : "Non";
				$entretiens_txt=$entretiens ? "Oui" : "Non";

				if($resp=="Le Responsable")
					$resp="responsable non renseign�";

				$manuelle_txt=$manuelle ? "- Gestion manuelle" : "";

				$fond=$active ? "fond_menu" : "fond_gris_C";

				// Lien direct vers la modification si les droits sont corrects
				if(in_array($_SESSION["niveau"], array("$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
				{
					$crypt_params=crypt_params("propspec=$propspec_id");
					$spec_nom="<a href='formations.php?p=$crypt_params' class='lien_bleu_12'>$spec_nom</a>";
				}

				print("<tr>
							<td class='td-gauche $fond'>
								<font class='Texte_menu'>&#8226;&nbsp;$spec_nom</font>
							</td>
							<td class='td-milieu $fond'>
								<font class='Texte_menu'>$nom_finalite</font>
							</td>
<!--
							<td class='td-milieu $fond' style='text-align:center;'>
								<font class='Texte_menu'>$selective_text</font>
							</td>
-->
							<td class='td-droite $fond' style='text-align:center;'>
								<font class='Texte_menu'>$entretiens_txt</font>
							</td>
						</tr>
						<tr>
							<td class='td-gauche $fond' style='padding-left:30px; white-space:normal;' colspan='3'>
								<font class='Texte_menu_10'><i>Responsable : $resp $manuelle_txt</i>\n");				

				if($info_formation!="")
				{
					if(strlen($info_formation)>70) // 70 caract�res : bon compromis pour afficher un aper�u du texte ?
						$info_formation=substr($info_formation, 0, 70) . " [...]";
					
					// suppression des retours de ligne superflus
					$info_formation=preg_replace("/[\n\r]+/", "\n\r", $info_formation);
					
					// Lien direct vers la modification si les droits sont corrects
					if(in_array($_SESSION["niveau"], array("$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
					{
						$crypt_params=crypt_params("propspec=$propspec_id");
						$info_texte="<a href='info_formations.php?p=$crypt_params' class='lien_bleu_10'><i><u>Info formation :</u> ".nl2br($info_formation)."</i></a>";
					}
					else
						$info_texte="<i><u>Info formation :</u> $info_formation</i>";
				
					print("<br>$info_texte");
				}
							
				print("</font>
						</td>
						</tr>\n");

				$nb++;
			}

			print("</table>\n");

			if($j)
				print("<td></td>\n");

			print("</tr>
					 </table>\n");
		}

		db_free_result($result);
		db_close($dbr);
	?>

	<div class='centered_box'>
		<a href='index.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/back_32x32_fond.png"; ?>' alt='Retour' border='0'></a>
	</div>
</div>
<?php
	pied_de_page();
?>
</body></html>
