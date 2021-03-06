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

	$date_creation=date_fr("j F Y", id_to_date($_SESSION["candidat_id"]));

	print("<div class='centered_box'>
				<font class='Texte_16'><strong>$_SESSION[onglet] - Historique : candidatures et �v�nements</strong></font>
				<br>
				<font class='Texte'>Fiche candidat cr��e le $date_creation</font>
			</div>");

	$result=db_query($dbr,"SELECT $_DBC_cand_id, $_DBC_annees_annee, $_DBC_specs_nom, 
											$_DBC_cand_motivation_decision, $_DBC_cand_statut, $_DBC_cand_ordre_spec, $_DBC_cand_groupe_spec,
											$_DBC_cand_ordre, $_DBC_decisions_id, $_DBC_decisions_texte, $_DBC_cand_liste_attente,
											$_DBC_cand_transmission_dossier, $_DBC_cand_recours, $_DBC_cand_vap_flag, $_DBC_cand_talon_reponse,
											$_DBC_propspec_id, $_DBC_propspec_finalite, $_DBC_composantes_id,
											$_DBC_composantes_nom, $_DBC_cand_periode
										FROM $_DB_cand, $_DB_specs, $_DB_annees, $_DB_decisions, $_DB_propspec, $_DB_composantes
									WHERE $_DBC_cand_candidat_id='$candidat_id'
									AND $_DBC_propspec_annee=$_DBC_annees_id
									AND $_DBC_propspec_id_spec=$_DBC_specs_id
									AND $_DBC_cand_decision=$_DBC_decisions_id
									AND $_DBC_cand_propspec_id=$_DBC_propspec_id
									AND $_DBC_propspec_comp_id=$_DBC_composantes_id
										ORDER BY $_DBC_cand_periode DESC, $_DBC_propspec_comp_id,$_DBC_cand_ordre, $_DBC_cand_ordre_spec ASC");

	$rows=db_num_rows($result);

	$old_comp="--";
	$old_periode="--";

	if($rows)
	{
		print("<table border='0' cellpadding='0' cellspacing='0' align='center'>
				 <tr>
					<td>\n");

		for($i=0; $i<$rows; $i++)
		{
			list($cand_id, $nom_annee, $nom_specialite, $motivation_decision,$statut,$ordre_spec, $groupe_spec, $ordre,
					$decision_id, $decision_texte, $rang_liste_attente, $transmission_dossier, $recours,$vap, $talon_reponse, $propspec_id,
					$finalite, $comp_id, $comp_nom, $periode)=db_fetch_row($result,$i);

			if(!$vap)
				$vap_flag="";
			else
				$vap_flag="<b>VAP/VAE</b> ";

			$nom_finalite=$tab_finalite[$finalite];

			if($old_periode!=$periode)
			{
				if($i)
					print("</table>
							</td>
						</tr>
						<tr>
							<td height='15' style='border:none;'></td>
						 </tr>\n");

				printf("<tr>
							<td height='15'>
								<font class='Texte'><b>P�riode $periode - %d</b>
							</td>
						 </tr>
						 <tr>
							<td style='border:none;'>\n", $periode+1);

				
				print("<table style='border:none;' align='center' width='100%'>
						 <tr>\n");

				// R�initialisation de la composante
				$old_comp="--";

				if($periode==$__PERIODE)
				{
					$fond_menu="fond_menu2";
					$class_texte="Texte_menu2";
					$fond="fond_menu";
				}
				else // anciennes p�riodes : en gris
				{
					$fond_menu="fond_gris_B";
					$class_texte="Texte_menu";
					$fond="fond_gris_D";
				}
			}

			if($comp_nom!=$old_comp)
			{
				print("<tr>
							<td class='td-gauche $fond_menu'>\n");

				// Admin ou acc�s dans une autre composante : on peut changer directement de composante avec un lien
				if(($_SESSION["niveau"]==$__LVL_ADMIN || $_SESSION["niveau"]==$__LVL_SUPPORT)
					|| db_num_rows(db_query($dbr, "SELECT $_DBC_acces_comp_composante_id FROM $_DB_acces_comp
															 WHERE $_DBC_acces_comp_acces_id='$_SESSION[auth_id]'
															 AND $_DBC_acces_comp_composante_id='$comp_id'")))
					print("<a href='$php_self?co=$comp_id' target='_self' class='lien_menu_gauche'><b>$comp_nom</b></a>\n");
				else
					print("<font class='Texte_menu'><b>$comp_nom</b></font>\n");

				print("</td>
							<td class='td-milieu $fond_menu'>
								<font class='$class_texte'><b>Recevabilit�</b></font>
							</td>
							<td class='td-droite $fond_menu'>
								<font class='$class_texte'><b>Commission P�dagogique</b></font>
							</td>
						</tr>\n");

				$old_comp=$comp_nom;
			}

			if($nom_annee=="")
				$formation_txt="$nom_specialite $nom_finalite $vap_flag";
			else
				$formation_txt="$nom_annee $nom_specialite $nom_finalite $vap_flag";

			switch($statut)
			{
				case $__PREC_NON_TRAITEE	:	// pr�candidature non trait�e
														$statut_txt="Non trait�e";
														break;

				case $__PREC_PLEIN_DROIT	:	// entr�e de plein droit
														$statut_txt="Plein droit";
														break;

				case $__PREC_RECEVABLE	:		// pr�candidature recevable
														$statut_txt="Recevable";
														break;

				case $__PREC_EN_ATTENTE	:		// pr�candidature en attente
														$statut_txt="En attente";
														break;

				case $__PREC_NON_RECEVABLE	:	// pr�candidature non recevable
														$statut_txt="Non recevable";
														break;

				case $__PREC_ANNULEE	:			// pr�candidature annul�e
														$statut_txt="Annul�e";
														break;

				default	:	// par d�faut : pr�candidature non trait�e
									$statut_txt="Non trait�e";
									break;
			}

			print("<tr>
						<td class='td-gauche $fond'>
							<font class='Texte_menu'>- $formation_txt</font>
						</td>
						<td class='td-milieu $fond'>
							<font class='Texte_menu'>$statut_txt</font>
						</td>
						<td class='td-droite $fond'>
							<font class='Texte_menu'>$decision_texte</font>
						</td>
					</tr>\n");

			$old_periode=$periode;
		}

		print("</td>
			  </tr>
			  </table>
			</td>
		</tr>
		</table>\n");

	}
	else
		message("Aucune candidature les ann�es pass�es", $__INFO);

	db_free_result($result);

	// Ev�nements

	if(isset($_SESSION["niveau"]) && in_array($_SESSION["niveau"], array("$__LVL_SUPPORT", "$__LVL_SAISIE","$__LVL_SCOL_MOINS","$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
	{
		// Tri de l'historique :
		// 1 = tri par date (DESC)
		// 2 = tri par date (ASC)
		// 3 = tri par �v�nement (DESC) (puis par date DESC)
		// 4 = tri par �v�nement (ASC) (puis par date DESC)

		$icon_date_down="1downarrow_green_16x16_menu2.png";
		$icon_date_up="1uparrow_green_16x16_menu2.png";
		$icon_evt_down="1downarrow_green_16x16_menu2.png";
		$icon_evt_up="1uparrow_green_16x16_menu2.png";

		if(isset($_GET["th"]) && ctype_digit($_GET["th"]) && ($_GET["th"]>=1 || $_GET["th"]<=4))
		{
			if($_GET["th"]==1)
			{
				$tri_historique="$_DBC_hist_date DESC";
				$icon_date_down="2downarrow_green_16x16_menu2.png";
			}
			elseif($_GET["th"]==2)
			{
				$tri_historique="$_DBC_hist_date ASC";
				$icon_date_up="2uparrow_green_16x16_menu2.png";
			}
			elseif($_GET["th"]==3)
			{
				$tri_historique="$_DBC_hist_type_evt DESC, $_DBC_hist_date DESC";
				$icon_evt_down="2downarrow_green_16x16_menu2.png";
			}
			elseif($_GET["th"]==4)
			{
				$tri_historique="$_DBC_hist_type_evt ASC, $_DBC_hist_date DESC";
				$icon_evt_up="2uparrow_green_16x16_menu2.png";
			}
		}
		else
		{
			$tri_historique="$_DBC_hist_date DESC";
			$icon_date_down="2downarrow_green_16x16_menu2.png";
		}

		if($_SESSION["niveau"]==$__LVL_ADMIN || $_SESSION["niveau"]==$__LVL_SUPPORT) // Admin : toutes composantes confondues
		{
			$res_evt=db_query($dbr, "SELECT $_DBC_hist_date, $_DBC_hist_ip, $_DBC_hist_host, $_DBC_hist_g_nom, $_DBC_hist_g_prenom,
													  $_DBC_hist_g_email,
													  CASE WHEN $_DBC_hist_comp_id IN (SELECT $_DBC_composantes_id FROM $_DB_composantes)
															THEN (SELECT $_DBC_composantes_nom FROM $_DB_composantes
																	WHERE $_DBC_composantes_id=$_DBC_hist_comp_id)
															ELSE CAST($_DBC_hist_comp_id AS text)
														END, 
													  $_DBC_hist_niveau, $_DBC_hist_type_evt, $_DBC_hist_evt
												FROM $_DB_hist
											WHERE $_DBC_hist_c_id='$candidat_id'
											ORDER BY $tri_historique");
		}
		else // Restriction � la composante courante
			$res_evt=db_query($dbr, "SELECT $_DBC_hist_date, $_DBC_hist_ip, $_DBC_hist_host, $_DBC_hist_g_nom, $_DBC_hist_g_prenom,
													  $_DBC_hist_g_email,
													  CASE WHEN $_DBC_hist_comp_id IN (SELECT $_DBC_composantes_id FROM $_DB_composantes)
															THEN (SELECT $_DBC_composantes_nom FROM $_DB_composantes
																	WHERE $_DBC_composantes_id=$_DBC_hist_comp_id)
															ELSE CAST($_DBC_hist_comp_id AS text)
														END, 
													$_DBC_hist_niveau, $_DBC_hist_type_evt, $_DBC_hist_evt
												FROM $_DB_hist
											WHERE $_DBC_hist_c_id='$candidat_id'
											AND $_DBC_hist_comp_id IN ('0', '-1', '$_SESSION[comp_id]')
											ORDER BY $tri_historique");

		$rows_evt=db_num_rows($res_evt);

		if($rows_evt)
		{
			print("<center>
						<br>
						<font class='Texte_16'><b>Ev�nements li�s au candidat</b></font>
						<br><br>
						<table align='center' border='0'>
						<tr>
							<td class='fond_page' width='16' style='border-color:black; border:solid; border-width:1px;'>&nbsp;</td>
							<td class='fond_page' style='white-space:nowrap; padding-left:5px; padding-right:20px;'>
								<font class='Texte'>Ev�nements Candidat</font>
							</td>
							<td class='fond_evenements_gestion' width='16' style='border-color:black; border:solid; border-width:1px;'>&nbsp;</td>
							<td class='fond_page' style='white-space:nowrap; padding-left:5px; padding-right:20px;'>
								<font class='Texte'>Ev�nements Gestion</font>
							</td>
							<td class='fond_gris_D' width='16' style='border-color:black; border:solid; border-width:1px;'>&nbsp;</td>
							<td class='fond_page' style='white-space:nowrap; padding-left:5px;'>
								<font class='Texte'>Autres</font>
							</td>
						</tr>
						</table>
						<br clear='all'>
					</center>
					<table border='0' cellpadding='0' cellspacing='0' align='center'>\n");

			if($_SESSION["niveau"]==$__LVL_ADMIN || $_SESSION["niveau"]==$__LVL_SUPPORT)
				print("<tr>
							<td class='fond_menu2' style='padding-left:20px;' width='16'>
								<a href='$php_self?th=1'><img src='$__ICON_DIR/$icon_date_down' width='16' border='0' title='Tri dates d�croissantes'></a>
							</td>
							<td class='fond_menu2' width='16'>
								<a href='$php_self?th=2'><img src='$__ICON_DIR/$icon_date_up' width='16' border='0' title='Tri dates croissantes'></a>
							</td>
							<td class='td-milieu fond_menu2'>
								<font class='Texte_menu2'><b>Date</b></font>
							</td>
							<td class='td-milieu fond_menu2'>
								<font class='Texte_menu2'><b>IP/Host</b></font>
							</td>
							<td class='td-milieu fond_menu2'>
								<font class='Texte_menu2'><b>Composante</b></font>
							</td>
							<td class='td-milieu fond_menu2'>
								<font class='Texte_menu2'><b>Gestionnaire</b></font>
							</td>
							<td class='td-droite fond_menu2' style='margin-right:0px; padding-right:0px; width:16px;'>
								<a href='$php_self?th=3'><img src='$__ICON_DIR/$icon_evt_down' width='16' height='16' border='0' title='Types �v�nements d�croissants'></a>
							</td>
							<td class='fond_menu2' style='margin-left:0px; padding-left:0px; width:16px;'>
								<a href='$php_self?th=4'><img src='$__ICON_DIR/$icon_evt_up' width='16' height='16' border='0' title='Types �v�nements croissants'></a>
							</td>
							<td class='td-droite fond_menu2'>
								<font class='Texte_menu2'><b>Ev�nement</b></font>
							</td>
						</tr>\n");
			else
				print("<tr>
							<td class='fond_menu2' style='padding-left:20px;' width='16'>
								<a href='$php_self?th=1'><img src='$__ICON_DIR/$icon_date_down' width='16' height='16' border='0' title='Tri dates d�croissantes'></a>
							</td>
							<td class='fond_menu2' width='16'>
								<a href='$php_self?th=2'><img src='$__ICON_DIR/$icon_date_up' width='16' height='16' border='0' title='Tri dates croissantes'></a>
							</td>
							<td class='td-milieu fond_menu2'>
								<font class='Texte_menu2'><b>Date</b></font>
							</td>
							<td class='fond_menu2' width='16'>
								<a href='$php_self?th=3'><img src='$__ICON_DIR/$icon_evt_down' width='16' height='16' border='0' title='Types �v�nements d�croissants'></a>
							</td>
							<td class='fond_menu2' width='16'>
								<a href='$php_self?th=4'><img src='$__ICON_DIR/$icon_evt_up' width='16' height='16' border='0' title='Types �v�nements croissants'></a>
							</td>
							<td class='td-droite fond_menu2'>
								<font class='Texte_menu2'><b>Ev�nement</b></font>
							</td>
						</tr>\n");

			for($i=0; $i<$rows_evt; $i++)
			{
				list($hist_date, $hist_ip, $hist_host, $hist_g_nom, $hist_g_prenom, $hist_g_email, $hist_comp, $hist_niveau,
					$hist_type_evt, $hist_evt)=db_fetch_row($res_evt, $i);

				$date_txt=date_fr("j F Y H:i", id_to_date($hist_date));
				$hist_comp=($hist_comp=="-1" || $hist_comp=="0") ? "n/a" : $hist_comp;
				$gestionnaire=$hist_g_nom=="" ? "n/a" : "$hist_g_prenom $hist_g_nom"; 

				if($hist_type_evt >= 3 && $hist_type_evt <=11) // Couleur diff�rente en fonction du type d'�v�nement
					$fond="fond_page"; // Candidat : couleur normale
				elseif($hist_type_evt >= 104 && $hist_type_evt <=116)
					$fond="fond_evenements_gestion";	// Gestion
				else
					$fond="fond_gris_D";	// Neutres : gris


				if($_SESSION["niveau"]==$__LVL_ADMIN || $_SESSION["niveau"]==$__LVL_SUPPORT)
				{
					print("<tr>
								<td class='td-gauche $fond' colspan='3'>
									<font class='Texte_10'>$date_txt</font>
								</td>
								<td class='td-milieu $fond'>
									<font class='Texte_10'>$hist_ip<br>$hist_host</font>
								</td>
								<td class='td-milieu $fond' style='white-space:normal;'>
									<font class='Texte_10'>$hist_comp</font>
								</td>
								<td class='td-milieu $fond'>
									<font class='Texte_10'>$gestionnaire</font>
								</td>
								<td class='td-droite $fond'  style='white-space:normal;' colspan='3'>
									<font class='Texte_10'>$hist_evt</font>
								</td>
							</tr>\n");
				}
				else
				{
					print("<tr>
								<td class='td-gauche $fond' colspan='3'>
									<font class='Texte'>$date_txt</font>
								</td>
								<td class='td-droite $fond' colspan='3'>
									<font class='Texte'>$hist_evt</font>
								</td>
							</tr>\n");
				}
			}

			print("</table>
					 <br>\n");
		}

		db_free_result($res_evt);
	}
?>
