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
	// V�rifications compl�mentaires au cas o� ce fichier serait appel� directement
	verif_auth();

	if(!isset($_SESSION["candidat_id"]))
	{
		header("Location:index.php");
		exit;
	}

	// Nettoyage
	unset($_SESSION["current_choix"]);

	// Onglet suppl�mentaire si le dossier le demande (CF. Constructeur de Dossiers dans la partie Gestion)
	$result_elems=db_query($dbr, "SELECT $_DBC_dossiers_elems_id, $_DBC_dossiers_elems_type, $_DBC_dossiers_elems_intitule,
													 $_DBC_dossiers_ef_propspec_id, $_DBC_dossiers_elems_para, $_DBC_dossiers_elems_vap,
													 $_DBC_dossiers_elems_unique
											FROM $_DB_dossiers_elems, $_DB_dossiers_ef, $_DB_cand, $_DB_propspec
										WHERE $_DBC_dossiers_elems_id=$_DBC_dossiers_ef_elem_id
										AND $_DBC_dossiers_ef_propspec_id=$_DBC_propspec_id
										AND $_DBC_propspec_id=$_DBC_cand_propspec_id
										AND $_DBC_cand_propspec_id=$_DBC_dossiers_ef_propspec_id
										AND $_DBC_cand_candidat_id='$_SESSION[candidat_id]'
										AND $_DBC_cand_periode='$__PERIODE'
										AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
											GROUP BY $_DBC_dossiers_elems_id, $_DBC_dossiers_elems_type, $_DBC_dossiers_elems_intitule,
														$_DBC_dossiers_ef_propspec_id,$_DBC_dossiers_elems_para, $_DBC_dossiers_elems_vap,
														$_DBC_dossiers_elems_unique, $_DBC_dossiers_ef_ordre
											ORDER BY $_DBC_dossiers_ef_ordre");

	$rows_elems=db_num_rows($result_elems);

	print("<div class='centered_box'>
				<font class='Texte_16'><strong>$_SESSION[onglet] - Autres Renseignements Obligatoires</strong></font>
			</div>");

	// Tableau utilis� pour ne pas afficher plusieurs fois les demandes uniques
	// demande dont la r�ponse est commune � plusieurs formations

	$elements_dossier_array=array();

	if($rows_elems)
	{
		print("<table align='center' width='90%'>
				 <tr>
					<td>\n");

		for($i=0; $i<$rows_elems; $i++)
		{
			list($elem_id, $elem_type, $elem_intitule, $elem_propspec_id, $elem_para, $elem_vap, $demande_unique)=db_fetch_row($result_elems, $i);

			// Si l'�l�ment est une demande unique et s'il a d�j� �t� affich�, on passe au suivant
			if($demande_unique=="f" || !in_array($elem_id, $elements_dossier_array))
			{
				$elem_para=nl2br($elem_para);
	/*
				if($demande_unique=="t")
					$elem_propspec_id=0;
	*/

				if($demande_unique=="t")
				{
					$elem_propspec_id=0;
					$cond_propspec="";
				}
				else
					$cond_propspec="AND $_DBC_propspec_id='$elem_propspec_id'";

				// on liste la ou les formations concern�es
				$res_formations=db_query($dbr, "SELECT $_DBC_annees_annee, $_DBC_specs_nom, $_DBC_propspec_finalite
															FROM $_DB_propspec, $_DB_annees, $_DB_specs, $_DB_dossiers_ef, $_DB_cand
														WHERE $_DBC_annees_id=$_DBC_propspec_annee
														AND $_DBC_specs_id=$_DBC_propspec_id_spec
														AND $_DBC_dossiers_ef_propspec_id=$_DBC_propspec_id
														AND $_DBC_dossiers_ef_elem_id='$elem_id'
														AND $_DBC_cand_propspec_id=$_DBC_propspec_id
														AND $_DBC_cand_candidat_id='$_SESSION[candidat_id]'
														AND $_DBC_cand_periode='$__PERIODE'
														AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
														$cond_propspec
															ORDER BY $_DBC_annees_ordre, $_DBC_specs_nom, $_DBC_propspec_finalite");

				$num_formations=db_num_rows($res_formations);

				if($num_formations)
				{
					$elem_formation=$num_formations==1 ? "<i><u>Cette question concerne la formation suivante :</u>" : "<i><u>Cette question concerne les $num_formations formations suivantes :</u>";

					for($j=0; $j<$num_formations; $j++)
					{
						list($f_annee, $f_spec, $f_finalite)=db_fetch_row($res_formations, $j);

						$nom_formation=$f_annee=="" ? "$f_spec" : "$f_annee $f_spec";
						$nom_formation.=$tab_finalite[$f_finalite]=="" ? "" : " $tab_finalite[$f_finalite]";

						$elem_formation.="<br>- $nom_formation";
					}
					$elem_formation.="<br></i>";
				}
				else
					$elem_formation="";

				db_free_result($res_formations);

				// On regarde si le candidat a rempli quelque chose :
				$result=db_query($dbr, "SELECT $_DBC_dossiers_elems_contenu_para, $_DBC_dossiers_elems_contenu_propspec_id
													FROM $_DB_dossiers_elems_contenu
												WHERE $_DBC_dossiers_elems_contenu_candidat_id='$_SESSION[candidat_id]'
												AND $_DBC_dossiers_elems_contenu_elem_id='$elem_id'
												AND $_DBC_dossiers_elems_contenu_comp_id='$_SESSION[comp_id]'
												AND $_DBC_dossiers_elems_contenu_periode='$__PERIODE'
												AND $_DBC_dossiers_elems_contenu_propspec_id='$elem_propspec_id'");
	
				if(db_num_rows($result))
					list($contenu, $propspec_id)=db_fetch_row($result, 0);
				else
					$contenu="";
	
				db_free_result($result);

				unset($aucun_contenu);
				$contenu_txt="";

				switch($elem_type)
				{
					case $__ELEM_TYPE_FORM : // Formulaire texte simple
							if($contenu!="")
								$contenu_txt=nl2br($contenu);
							else
								$aucun_contenu="1";

							break;

					case $__ELEM_TYPE_UN_CHOIX : // normalement une seule r�ponse : id du choix
							$res_choix=db_query($dbr, "SELECT $_DBC_dossiers_elems_choix_id,$_DBC_dossiers_elems_choix_texte
																	FROM $_DB_dossiers_elems_choix
																WHERE $_DBC_dossiers_elems_choix_elem_id='$elem_id'
																ORDER BY $_DBC_dossiers_elems_choix_ordre");
							$nb_choix=db_num_rows($res_choix);

							if($nb_choix)
							{
								for($c=0; $c<$nb_choix; $c++)
								{
									list($choix_id, $choix_texte)=db_fetch_row($res_choix, $c);

									$elem_para.="<br>- $choix_texte\n";

									if($contenu!="" && ctype_digit($contenu) && $choix_id==$contenu)
										$contenu_txt=$choix_texte;
								}
							}

							if($contenu_txt=="")
								$aucun_contenu="1";

							db_free_result($res_choix);

							break;

					case $__ELEM_TYPE_MULTI_CHOIX : // plusieurs r�ponses possibles s�par�es par "|" (id du ou des choix)

							$choix_array=explode("|",$contenu);

							// Affichage des choix
							$res_choix=db_query($dbr, "SELECT $_DBC_dossiers_elems_choix_id, $_DBC_dossiers_elems_choix_texte
																	FROM $_DB_dossiers_elems_choix
																WHERE $_DBC_dossiers_elems_choix_elem_id='$elem_id'
																ORDER BY $_DBC_dossiers_elems_choix_ordre");

							$nb_choix=db_num_rows($res_choix);

							if($nb_choix)
							{
								for($c=0; $c<$nb_choix; $c++)
								{
									list($choix_id, $choix_texte)=db_fetch_row($res_choix, $c);

									$elem_para.="<br>- $choix_texte\n";

									if(is_array($choix_array) && in_array($choix_id, $choix_array))
										$contenu_txt.="- $choix_texte<br>\n";
								}
							}

							if($contenu_txt=="")
								$aucun_contenu="1";

							db_free_result($res_choix);

							break;
				}

				if(isset($aucun_contenu) && $aucun_contenu==1)
					$contenu_txt="Champ non compl�t� par " . $_SESSION["tab_candidat"]["etudiant_particule"] . ".";

				// Si la fiche est verrouill�e, on autorise la modification et la suppression
				if(in_array($_SESSION["niveau"], array("$__LVL_SCOL_MOINS","$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")) && ($_SESSION["tab_candidat"]["lock"]==1 || $_SESSION["tab_candidat"]["manuelle"]==1))
				{
					$colspan="colspan='2'";
					$td_class="td-droite fond_menu";
				}
				else
				{
					$colspan="";
					$td_class="td-gauche fond_menu";
				}
	
				print("<table style='width:100%; padding-bottom:15px;'>
						 <tr>
							<td $colspan class='td-gauche fond_menu2' style='padding:8px 20px 12px 20px; white-space:normal;'>
								<font class='Texte_menu2'>
									$elem_formation
									<b>&#8226;&nbsp;&nbsp;$elem_intitule</b>\n");
	
				if(!empty($elem_para))
					print("<br><i>$elem_para</i>");
	
				print("			</font>
								</td>
							</tr>
							<tr>\n");
	
				if(in_array($_SESSION["niveau"], array("$__LVL_SCOL_MOINS","$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")) && ($_SESSION["tab_candidat"]["lock"]==1 || $_SESSION["tab_candidat"]["manuelle"]==1))
				{
					$crypt_params=$demande_unique=="f" ? crypt_params("elem_id=$elem_id&elem_propspec=$elem_propspec_id") : crypt_params("elem_id=$elem_id&elem_propspec=0");
	
					print("<td class='td-gauche fond_menu' align='center' style='width:40px; padding:4px 10px 4px 10px;'>
								<a href='renseignements.php?p=$crypt_params' target='_self'><img src='$__ICON_DIR/edit_32x32_menu.png' border='0' alt=''></a>
								<br>
								<a href='renseignements.php?p=$crypt_params' target='_self' class='lien_menu_gauche'>Compl�ter<br>cette<br>demande</a>
							</td>\n");
				}

				print("<td class='$td_class' style='text-align:justify; white-space:normal; padding:8px 20px 8px 20px;'>
						 	<font class='Texte_menu'>$contenu_txt</font>
						</td>
					</tr>
					</table>\n");
	
				$elements_dossier_array["$i"]=$elem_id;
			} // fin du if(demande_unique || ... )
		} // fin de la boucle for

		print("	</td>
				</tr>
				</table>\n");
	}
	else
		message("<center>
						Ce dossier ne comporte aucun �l�ment compl�mentaire.
						<br>Pour en ajouter, veuillez passer par le menu \"Administration => Constructeur de dossiers\" (r�serv� aux Gestionnaires).
					</center>\n", $__INFO);

	db_free_result($result_elems);
?>
