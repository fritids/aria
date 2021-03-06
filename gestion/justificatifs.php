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
	include "$__INCLUDE_DIR_ABS/access_functions.php";
	
	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	verif_auth();

	$candidat_id=$_SESSION["candidat_id"];

	$dbr=db_connect();

	// Utilisation de la librairie fpdf (libre)
	require("$__FPDF_DIR_ABS/fpdf.php");

	// Pour chaque candidature, on g�n�re un fichier contenant la liste des justificatifs (pi�ces jointes non comprises)

	// S�lection d'une candidature via la page "edit_candidature" : on tient compte de l'identifiant
	if(isset($_GET["cand_id"]) && ctype_digit($_GET["cand_id"]))
	{
		$cand_id=$_GET["cand_id"];
		$condition_candidature="AND $_DBC_cand_id='$cand_id'";
	}
	else
		$condition_candidature="";

	$result2=db_query($dbr, "SELECT $_DBC_cand_id, $_DBC_cand_propspec_id, $_DBC_annees_annee, $_DBC_specs_nom,
											  $_DBC_propspec_finalite, $_DBC_propspec_frais, $_DBC_composantes_nom, $_DBC_composantes_scolarite,
											  $_DBC_universites_nom
										FROM $_DB_cand, $_DB_propspec, $_DB_annees, $_DB_specs, $_DB_composantes, $_DB_universites
									WHERE $_DBC_cand_propspec_id=$_DBC_propspec_id
									AND $_DBC_propspec_annee=$_DBC_annees_id
									AND $_DBC_propspec_id_spec=$_DBC_specs_id
									AND $_DBC_composantes_id=$_DBC_propspec_comp_id
									AND $_DBC_composantes_univ_id=$_DBC_universites_id
									AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
									AND $_DBC_cand_candidat_id='$candidat_id'
									$condition_candidature");

	$rows2=db_num_rows($result2);

	// Boucle sur les candidatures
	if($rows2)
	{
		// Ici, on cr�� UN SEUL FICHIER avec UNE PAGE PAR FORMATION

		$justificatifs=new FPDF("P","mm","A4");

		$justificatifs->SetCreator("Application ARIA : Gestion des Candidatures");
		$justificatifs->SetAuthor("Christophe BOCCHECIAMPE - UFR de Math�matique et d'Informatique - Universit� de Strasbourg");
		$justificatifs->SetSubject("Justificatifs");
		$justificatifs->SetTitle("Justificatifs");

		// saut de page automatique, � 15mm du bas
		$justificatifs->SetAutoPageBreak(1,11);
		// $justificatifs->SetMargins(11,11,11);

		for($k=0; $k<$rows2; $k++)
		{
			list($candidature_id, $propspec_id, $annee, $spec, $finalite, $frais, $comp_nom, $adr_scol, $univ_nom)=db_fetch_row($result2, $k);

			$nom_finalite=$tab_finalite[$finalite];

			// Cr�ation du PDF

			$justificatifs->AddPage();

			$justificatifs->SetXY(13, 24);
			// TODO : ATTENTION : NE PAS OUBLIER DE GENERER LA FONTE ARIBLK.TTF LORS D'UN CHANGEMENT DE MACHINE

			$justificatifs->SetFont('arial','',10);
			$justificatifs->SetTextColor(0, 0, 0);

			if(empty($annee))
				$formation="$spec $nom_finalite $__PERIODE - " . ($__PERIODE+1);
			else
				$formation="$annee $spec $nom_finalite $__PERIODE - " . ($__PERIODE+1);

			$justificatifs->SetXY(20, 15);
			$justificatifs->SetFont('arial',"B",12);

			$justificatifs->MultiCell(0, 5, "$formation\nJustificatifs � fournir", 0, "C");

			$justificatifs->SetXY(20, 30);

			$result=db_query($dbr, "SELECT $_DBC_justifs_id, $_DBC_justifs_titre, $_DBC_justifs_texte, $_DBC_justifs_jf_nationalite
												FROM $_DB_justifs, $_DB_justifs_jf
											WHERE $_DBC_justifs_jf_propspec_id='$propspec_id'
											AND $_DBC_justifs_jf_justif_id=$_DBC_justifs_id
												ORDER BY $_DBC_justifs_jf_ordre");

			$rows=db_num_rows($result);

			// Boucle sur les justificatifs
			for($j=0; $j<$rows; $j++)
			{
				list($justif_id, $justif_titre, $justif_texte, $justif_nationalite)=db_fetch_row($result, $j);

				switch($justif_nationalite)
				{
						case $__COND_NAT_TOUS :		if($justif_texte=="")
															{
																$justificatifs->SetFont('arial',"",10);
																$justificatifs->SetX(20);
															}
															else
															{
																$justificatifs->SetFont('arial',"B",10);
																$justificatifs->SetX(20);
															}

															$justificatifs->MultiCell(0, 5, $justif_titre, 0, "J");

															if($justif_texte!="")
															{
																$justificatifs->SetFont('arial',"",10);
																$justificatifs->SetX(20);
																$justificatifs->MultiCell(0, 5, $justif_texte, 0, "J");
															}

															$justificatifs->Ln(5);

															break;

						// Uniquement les candidats non fran�ais
						case $__COND_NAT_FR :	if(!strncasecmp($_SESSION['tab_candidat']["nationalite"], "fran�ais", 8) || !strncasecmp($_SESSION['tab_candidat']["nationalite"], "francais", 8))
														{
															if($justif_texte=="")
															{
																$justificatifs->SetFont('arial',"",10);
																$justificatifs->SetX(20);
															}
															else
															{
																$justificatifs->SetFont('arial',"B",10);
																$justificatifs->SetX(20);
															}

															$justificatifs->MultiCell(0, 5, $justif_titre, 0, "J");

															if($justif_texte!="")
															{
																$justificatifs->SetFont('arial',"",10);
																$justificatifs->SetX(20);
																$justificatifs->MultiCell(0, 5, $justif_texte, 0, "J");
															}

															$justificatifs->Ln(5);
														}

														break;

						// Uniquement les candidats NON fran�ais
						case $__COND_NAT_NON_FR :	if(strncasecmp($_SESSION['tab_candidat']["nationalite"], "fran�ais", 8) && strncasecmp($_SESSION['tab_candidat']["nationalite"], "francais", 8))
															{
																if($justif_texte=="")
																{
																	$justificatifs->SetFont('arial',"",10);
																	$justificatifs->SetX(20);
																}
																else
																{
																	$justificatifs->SetFont('arial',"B",10);
																	$justificatifs->SetX(20);
																}

																$justificatifs->MultiCell(0, 5, $justif_titre, 0, "J");

																if($justif_texte!="")
																{
																	$justificatifs->SetFont('arial',"",10);
																	$justificatifs->SetX(20);
																	$justificatifs->MultiCell(0, 5, $justif_texte, 0, "J");
																}

																$justificatifs->Ln(5);
															}

															break;

						// Uniquement les candidats HORS UE
						case $__COND_NAT_HORS_UE :		// On balaye la liste des nationalit�s UE
																$dans_ue=0;

																foreach($__PAYS_UE as $nationalite_ue)
																{
																	if(!strcasecmp($_SESSION['tab_candidat']["nationalite"], "$nationalite_ue"))
																		$dans_ue=1;		// Le candidat est dans l'UE : on n'imprime pas
																}

																// Non trouv� dans la liste : le candidat est hors UE (France comprise) : on imprime
																if($dans_ue==0 && strncasecmp($_SESSION['tab_candidat']["nationalite"], "fran�ais", 8) && strncasecmp($_SESSION['tab_candidat']["nationalite"], "francais", 8))
																{
																	if($justif_texte=="")
																	{
																		$justificatifs->SetFont('arial',"",10);
																		$justificatifs->SetX(20);
																	}
																	else
																	{
																		$justificatifs->SetFont('arial',"B",10);
																		$justificatifs->SetX(20);
																	}

																	$justificatifs->MultiCell(0, 5, $justif_titre, 0, "J");

																	if($justif_texte!="")
																	{
																		$justificatifs->SetFont('arial',"",10);
																		$justificatifs->SetX(20);
																		$justificatifs->MultiCell(0, 5, $justif_texte, 0, "J");
																	}

																	$justificatifs->Ln(5);
																}

																break;

						// Uniquement les candidats DANS l'UE (les Fran�ais ne sont pas inclus dans cette liste)
						case $__COND_NAT_UE :	$dans_ue=0;

														foreach($__PAYS_UE as $nationalite_ue)
														{
															if(!strcasecmp($_SESSION['tab_candidat']["nationalite"], "$nationalite_ue"))
																$dans_ue=1;		// Le candidat est dans l'UE : on imprime
														}

														// Trouv� dans la liste : le candidat est dans l'UE : on imprime
														if($dans_ue==1)
														{
															if($justif_texte=="")
															{
																$justificatifs->SetFont('arial',"",10);
																$justificatifs->SetX(20);
															}
															else
															{
																$justificatifs->SetFont('arial',"B",10);
																$justificatifs->SetX(20);
															}

															$justificatifs->MultiCell(0, 5, $justif_titre, 0, "J");

															if($justif_texte!="")
															{
																$justificatifs->SetFont('arial',"",10);
																$justificatifs->SetX(20);
																$justificatifs->MultiCell(0, 5, $justif_texte, 0, "J");
															}

															$justificatifs->Ln(5);
														}

														break;
				} // fin du switch

			} // Fin de la boucle for() sur les justificatifs

			db_free_result($result);
		}

		$nom_fichier=clean_str($_SESSION["auth_user"] . "_" . time() . "_Justificatifs.pdf");
		// $justificatifs->Output("$nom_fichier", "I");

		$justificatifs->Output("$__GESTION_COMP_STOCKAGE_DIR_ABS/$_SESSION[comp_id]/$nom_fichier");

		write_evt($dbr, $__EVT_ID_G_DOC, "G�n�ration des justificatifs", $candidat_id, $candidature_id);

		// Attention : chemin relatif � www-root (document_root du serveur Apache)
		echo "<HTML><SCRIPT>document.location='$__GESTION_COMP_STOCKAGE_DIR/$_SESSION[comp_id]/$nom_fichier';</SCRIPT></HTML>";
	}

	db_close($dbr);
?>
