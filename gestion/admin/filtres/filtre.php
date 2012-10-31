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
	// Gestion des filtres entre les formations
	// Exemple : si un candidat a s�lectionn� la formation X, alors il ne peut pas s�lectionner la formation Y

	session_name("preinsc_gestion");
	session_start();

	include "../../../configuration/aria_config.php";
	include "$__INCLUDE_DIR_ABS/vars.php";
	include "$__INCLUDE_DIR_ABS/fonctions.php";
	include "$__INCLUDE_DIR_ABS/db.php";

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	verif_auth("$__GESTION_DIR/login.php");

	if(!in_array($_SESSION['niveau'], array("$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
	{
		header("Location:$__GESTION_DIR/noaccess.php");
		exit();
	}

	// param�tre chiffr� : identifiant du filtre en cas de modification
	if(isset($_GET["p"]) && -1!=($params=get_params($_GET['p'])))
	{
		if(isset($params["fid"]) && ctype_digit($params["fid"]))
		{
			$_SESSION["fid"]=$params["fid"];
			$_SESSION["modification"]=1;
			$_SESSION["etape"]=1;
		}
	}
	elseif(isset($_SESSION["fid"]) && ctype_digit($_SESSION["fid"]))
		$_SESSION["modification"]=1;
	else // pas de param�tre : ajout d'une �tape au cursus
		$_SESSION["ajout"]=1;

	// TRAITEMENT DES FORMULAIRES
	// En fonction de l'�tape, on ne stocke pas les valeurs dans les m�mes variables
	if(isset($_POST["suivant"]) || isset($_POST["suivant_x"]))
	{
		if($_SESSION["etape"]==1)
		{
			$_SESSION["filtre_formations_condition_propspec"]=isset($_POST["propspec_id"]) ? $_POST["propspec_id"] : "-1";
			$_SESSION["filtre_formations_condition_annee"]=isset($_POST["annee_id"]) ? $_POST["annee_id"] : "-1";
			$_SESSION["filtre_formations_condition_mention"]=isset($_POST["mention_id"]) ? $_POST["mention_id"] : "-1";
			$_SESSION["filtre_formations_condition_specialite"]=isset($_POST["spec_id"]) ? $_POST["spec_id"] : "-1";
			$_SESSION["filtre_formations_condition_finalite"]=isset($_POST["finalite"]) ? $_POST["finalite"] : "-1";

			$_SESSION["etape"]=2;
		}
		elseif($_SESSION["etape"]==2 || $_SESSION["etape"]==3)
		{
			$_SESSION["filtre_formations_cible_propspec"]=isset($_POST["propspec_id"]) ? $_POST["propspec_id"] : "-1";
			$_SESSION["filtre_formations_cible_annee"]=isset($_POST["annee_id"]) ? $_POST["annee_id"] : "-1";
			$_SESSION["filtre_formations_cible_mention"]=isset($_POST["mention_id"]) ? $_POST["mention_id"] : "-1";
			$_SESSION["filtre_formations_cible_specialite"]=isset($_POST["spec_id"]) ? $_POST["spec_id"] : "-1";
			$_SESSION["filtre_formations_cible_finalite"]=isset($_POST["finalite"]) ? $_POST["finalite"] : "-1";

			$_SESSION["etape"]=3;

			// R�capitulatif
			// Formation compl�te : champ prioritaire
			if($_SESSION["filtre_formations_condition_propspec"]!="-1")
			{
				$_SESSION["filtre_condition"]=$_SESSION["tab_formations"][$_SESSION["filtre_formations_condition_propspec"]];
				$_SESSION["filtre_condition_txt"]="";
			}
			else // construction ann�e / mention / sp�cialit� / finalite
			{
				$_SESSION["filtre_condition"]="";
				$_SESSION["filtre_condition_txt"]="";
				$cnt=0;

				if($_SESSION["filtre_formations_condition_annee"]!="-1")
					$_SESSION["filtre_condition"].="<strong>Ann�e</strong> : " . $_SESSION["tab_annees"][$_SESSION["filtre_formations_condition_annee"]];
				else
				{
					$_SESSION["filtre_condition_txt"].="l'ann�e";
					$cnt++;
				}

				if($_SESSION["filtre_formations_condition_mention"]!="-1")
				{
					$_SESSION["filtre_condition"]=$_SESSION["filtre_condition"]=="" ? "" : ", ";
					$_SESSION["filtre_condition"].="<strong>Mention</strong> : " . $_SESSION["tab_mentions"][$_SESSION["filtre_formations_condition_mention"]];
				}
				else
				{
					$_SESSION["filtre_condition_txt"].=$_SESSION["filtre_condition_txt"]=="" ? "la mention" : ", la mention";
					$cnt++;
				}

				if($_SESSION["filtre_formations_condition_specialite"]!="-1")
				{
					$_SESSION["filtre_condition"]=$_SESSION["filtre_condition"]=="" ? "" : ", ";
					$_SESSION["filtre_condition"].="<strong>Sp�cialit�</strong> : " . $_SESSION["tab_specs"][$_SESSION["filtre_formations_condition_specialite"]];
				}
				else
				{
					$_SESSION["filtre_condition_txt"].=$_SESSION["filtre_condition_txt"]=="" ? "la sp�cialit�" : ", la sp�cialit�";
					$cnt++;
				}

				if($_SESSION["filtre_formations_condition_finalite"]!="-1")
				{
					$_SESSION["filtre_condition"]=$_SESSION["filtre_condition"]=="" ? "" : ", ";
					$_SESSION["filtre_condition"].=$tab_finalite[$_SESSION["filtre_formations_condition_finalite"]]=="" ? "" : "</strong>Finalit�</strong> : " . $tab_finalite_complete[$_SESSION["filtre_formations_condition_finalite"]];
				}
				else
				{
					$_SESSION["filtre_condition_txt"].=$_SESSION["filtre_condition_txt"]=="" ? "la finalit�" : ", la finalit�";
					$cnt++;
				}

				if($cnt)
				{
					if($cnt>1)
						$_SESSION["filtre_condition_txt"]="<br>(Quelles que soient $_SESSION[filtre_condition_txt])";
					else
						$_SESSION["filtre_condition_txt"]="<br>(Quelle que soit $_SESSION[filtre_condition_txt])";
				}
			}

			// M�me chose pour la cible

			// Formation compl�te : champ prioritaire
			if($_SESSION["filtre_formations_cible_propspec"]!="-1")
			{
				$filtre_cible=$_SESSION["tab_formations"][$_SESSION["filtre_formations_cible_propspec"]];
				$filtre_cible_txt="";
			}
			else // construction ann�e / mention / sp�cialit� / finalite
			{
				$filtre_cible="";
				$filtre_cible_txt="";
				$cnt=0;

				if($_SESSION["filtre_formations_cible_annee"]!="-1")
					$filtre_cible.="<strong>Ann�e</strong> : " . $_SESSION["tab_annees"][$_SESSION["filtre_formations_cible_annee"]];
				else
				{
					$filtre_cible_txt.="l'ann�e";
					$cnt++;
				}

				if($_SESSION["filtre_formations_cible_mention"]!="-1")
				{
					$filtre_cible=$filtre_cible=="" ? "" : ", ";
					$filtre_cible.="<strong>Mention</strong> : " . $_SESSION["tab_mentions"][$_SESSION["filtre_formations_cible_mention"]];
				}
				else
				{
					$filtre_cible_txt.=$filtre_cible_txt=="" ? "la mention" : ", la mention";
					$cnt++;
				}

				if($_SESSION["filtre_formations_cible_specialite"]!="-1")
				{
					$filtre_cible=$filtre_cible=="" ? "" : ", ";
					$filtre_cible.="<strong>Sp�cialit�</strong> : " . $_SESSION["tab_specs"][$_SESSION["filtre_formations_cible_specialite"]];
				}
				else
				{
					$filtre_cible_txt.=$filtre_cible_txt=="" ? "la sp�cialit�" : ", la sp�cialit�";
					$cnt++;
				}

				if($_SESSION["filtre_formations_cible_finalite"]!="-1")
				{
					$filtre_cible=$filtre_cible=="" ? "" : ", ";
					$filtre_cible.=$tab_finalite[$_SESSION["filtre_formations_cible_finalite"]]=="" ? "" : "</strong>Finalit�</strong> : " . $tab_finalite_complete[$_SESSION["filtre_formations_cible_finalite"]];
				}
				else
				{
					$filtre_cible_txt.=$filtre_cible_txt=="" ? "la finalit�" : ", la finalit�";
					$cnt++;
				}

				if($cnt)
				{
					if($cnt>1)
						$filtre_cible_txt="<br>(Quelles que soient $filtre_cible_txt)";
					else
						$filtre_cible_txt="<br>(Quelle que soit $filtre_cible_txt)";
				}
			}
		}
	}

	$dbr=db_connect();

	// Validation : cr�ation / modification du filtre
	if(isset($_POST["valider"]) || isset($_POST["valider_x"]))
	{
		$_SESSION["filtre_formations_nom"]=$filtre_nom=$_POST["nom_filtre"];

		if(isset($_SESSION["filtre_formations_condition_propspec"]) && isset($_SESSION["filtre_formations_condition_annee"])
			&& isset($_SESSION["filtre_formations_condition_mention"]) && isset($_SESSION["filtre_formations_condition_specialite"])
			&& isset($_SESSION["filtre_formations_condition_finalite"]) && isset($_SESSION["filtre_formations_condition_propspec"])
			&& isset($_SESSION["filtre_formations_condition_annee"]) && isset($_SESSION["filtre_formations_condition_mention"])
			&& isset($_SESSION["filtre_formations_condition_specialite"]) && isset($_SESSION["filtre_formations_condition_finalite"]))
		{
			if(isset($_SESSION["modification"]) && $_SESSION["modification"]==1)
			{
				// Unicit�
				if(db_num_rows(db_query($dbr, "SELECT * FROM $_DB_filtres
															WHERE $_DBC_filtres_cond_propspec_id='$_SESSION[filtre_formations_condition_propspec]'
															AND $_DBC_filtres_cond_annee_id='$_SESSION[filtre_formations_condition_annee]'
															AND $_DBC_filtres_cond_mention_id='$_SESSION[filtre_formations_condition_mention]'
															AND $_DBC_filtres_cond_spec_id='$_SESSION[filtre_formations_condition_specialite]'
															AND $_DBC_filtres_cond_finalite='$_SESSION[filtre_formations_condition_finalite]'
															AND $_DBC_filtres_cible_propspec_id='$_SESSION[filtre_formations_cible_propspec]'
															AND $_DBC_filtres_cible_annee_id='$_SESSION[filtre_formations_cible_annee]'
															AND $_DBC_filtres_cible_mention_id='$_SESSION[filtre_formations_cible_mention]'
															AND $_DBC_filtres_cible_spec_id='$_SESSION[filtre_formations_cible_specialite]'
															AND $_DBC_filtres_cible_finalite='$_SESSION[filtre_formations_cible_finalite]'
															AND $_DBC_filtres_comp_id='$_SESSION[comp_id]'
															AND $_DBC_filtres_id!='$_SESSION[fid]'")))
					$filtre_existe=1;
				else
				{
					db_query($dbr, "UPDATE $_DB_filtres SET	$_DBU_filtres_nom='$filtre_nom',
																			$_DBU_filtres_cond_propspec_id='$_SESSION[filtre_formations_condition_propspec]',
																			$_DBU_filtres_cond_annee_id='$_SESSION[filtre_formations_condition_annee]',
																			$_DBU_filtres_cond_mention_id='$_SESSION[filtre_formations_condition_mention]',
																			$_DBU_filtres_cond_spec_id='$_SESSION[filtre_formations_condition_specialite]',
																			$_DBU_filtres_cond_finalite='$_SESSION[filtre_formations_condition_finalite]',
																			$_DBU_filtres_cible_propspec_id='$_SESSION[filtre_formations_cible_propspec]',
																			$_DBU_filtres_cible_annee_id='$_SESSION[filtre_formations_cible_annee]',
																			$_DBU_filtres_cible_mention_id='$_SESSION[filtre_formations_cible_mention]',
																			$_DBU_filtres_cible_spec_id='$_SESSION[filtre_formations_cible_specialite]',
																			$_DBU_filtres_cible_finalite='$_SESSION[filtre_formations_cible_finalite]'
										 	WHERE $_DBU_filtres_comp_id='$_SESSION[comp_id]'
											AND $_DBU_filtres_id='$_SESSION[fid]'");

					write_evt($dbr, $__EVT_ID_G_FILTRES, "Modification filtre $_SESSION[fid]", "", $_SESSION["fid"]);

					$succes="succes_m=1";
				}
			}
			elseif(isset($_SESSION["ajout"]) && $_SESSION["ajout"]==1)
			{
				// Unicit�
				if(db_num_rows(db_query($dbr, "SELECT * FROM $_DB_filtres
															WHERE $_DBC_filtres_cond_propspec_id='$_SESSION[filtre_formations_condition_propspec]'
															AND $_DBC_filtres_cond_annee_id='$_SESSION[filtre_formations_condition_annee]'
															AND $_DBC_filtres_cond_mention_id='$_SESSION[filtre_formations_condition_mention]'
															AND $_DBC_filtres_cond_spec_id='$_SESSION[filtre_formations_condition_specialite]'
															AND $_DBC_filtres_cond_finalite='$_SESSION[filtre_formations_condition_finalite]'
															AND $_DBC_filtres_cible_propspec_id='$_SESSION[filtre_formations_cible_propspec]'
															AND $_DBC_filtres_cible_annee_id='$_SESSION[filtre_formations_cible_annee]'
															AND $_DBC_filtres_cible_mention_id='$_SESSION[filtre_formations_cible_mention]'
															AND $_DBC_filtres_cible_spec_id='$_SESSION[filtre_formations_cible_specialite]'
															AND $_DBC_filtres_cible_finalite='$_SESSION[filtre_formations_cible_finalite]'
															AND $_DBC_filtres_comp_id='$_SESSION[comp_id]'")))
					$filtre_existe=1;
				else
				{
					// Cr�ation du filtre d�fini
					$new_fid=db_locked_query($dbr, $_DB_filtres, "INSERT INTO $_DB_filtres VALUES (
																					'##NEW_ID##',
																					'$filtre_nom',
																					'$_SESSION[comp_id]',
																					'$_SESSION[filtre_formations_condition_propspec]',
																					'$_SESSION[filtre_formations_condition_annee]',
																					'$_SESSION[filtre_formations_condition_mention]',
																					'$_SESSION[filtre_formations_condition_specialite]',
																					'$_SESSION[filtre_formations_condition_finalite]',
																					'$_SESSION[filtre_formations_cible_propspec]',
																					'$_SESSION[filtre_formations_cible_annee]',
																					'$_SESSION[filtre_formations_cible_mention]',
																					'$_SESSION[filtre_formations_cible_specialite]',
																					'$_SESSION[filtre_formations_cible_finalite]')");

					write_evt($dbr, $__EVT_ID_G_FILTRES, "Ajout filtre $new_fid", "", $new_fid);

					// Cr�ation du filtre r�ciproque ? (s'il n'existe pas d�j�)
					if(isset($_POST["reciproque"]) && $_POST["reciproque"]==1)
					{
						if(!db_num_rows(db_query($dbr, "SELECT * FROM $_DB_filtres
															WHERE $_DBC_filtres_cond_propspec_id='$_SESSION[filtre_formations_cible_propspec]'
															AND $_DBC_filtres_cond_annee_id='$_SESSION[filtre_formations_cible_annee]'
															AND $_DBC_filtres_cond_mention_id='$_SESSION[filtre_formations_cible_mention]'
															AND $_DBC_filtres_cond_spec_id='$_SESSION[filtre_formations_cible_specialite]'
															AND $_DBC_filtres_cond_finalite='$_SESSION[filtre_formations_cible_finalite]'
															AND $_DBC_filtres_cible_propspec_id='$_SESSION[filtre_formations_condition_propspec]'
															AND $_DBC_filtres_cible_annee_id='$_SESSION[filtre_formations_condition_annee]'
															AND $_DBC_filtres_cible_mention_id='$_SESSION[filtre_formations_condition_mention]'
															AND $_DBC_filtres_cible_spec_id='$_SESSION[filtre_formations_condition_specialite]'
															AND $_DBC_filtres_cible_finalite='$_SESSION[filtre_formations_condition_finalite]'
															AND $_DBC_filtres_comp_id='$_SESSION[comp_id]'")))
						{
							$new_fid=db_locked_query($dbr, $_DB_filtres, "INSERT INTO $_DB_filtres VALUES (
																						'##NEW_ID##',
																						'$filtre_nom (r�ciproque)',
																						'$_SESSION[comp_id]',
																						'$_SESSION[filtre_formations_cible_propspec]',
																						'$_SESSION[filtre_formations_cible_annee]',
																						'$_SESSION[filtre_formations_cible_mention]',
																						'$_SESSION[filtre_formations_cible_specialite]',
																						'$_SESSION[filtre_formations_cible_finalite]',
																						'$_SESSION[filtre_formations_condition_propspec]',
																						'$_SESSION[filtre_formations_condition_annee]',
																						'$_SESSION[filtre_formations_condition_mention]',
																						'$_SESSION[filtre_formations_condition_specialite]',
																						'$_SESSION[filtre_formations_condition_finalite]')");

							write_evt($dbr, $__EVT_ID_G_FILTRES, "Ajout filtre r�ciproque $new_fid", "", $new_fid);
						}
					}

					$succes="succes_a=1";
				}
			}

			if(!isset($filtre_existe))
			{
				header("Location:index.php?$succes");
				db_close($dbr);
				exit();
			}
		}
	}

	// Changement d'�tape
	if(isset($_GET["e"]) && ctype_digit($_GET["e"]) && ($_GET["e"]==1 || $_GET["e"]==2 || $_GET["e"]==3))
		$_SESSION["etape"]=$_GET["e"];
	elseif(!isset($_SESSION["etape"]))	// Etape par d�faut
		$_SESSION["etape"]=1;

	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>

<div class='main'>
	<?php
		if(isset($_SESSION["ajout"]))
			titre_page_icone("Ajouter un filtre : �tape $_SESSION[etape]", "applications-science_32x32_fond.png", 15, "L");
		elseif(isset($_SESSION["modification"]))
			titre_page_icone("Modifier un filtre : �tape $_SESSION[etape]", "applications-science_32x32_fond.png", 15, "L");

		print("<form action='$php_self' method='POST' name='form1'>\n");

		if(isset($filtre_existe))
			message("Erreur : un filtre avec des param�tres identiques existe d�j�.", $__ERREUR);

		switch($_SESSION["etape"])
		{
			case 1	:	message("<center>
											<strong>Etape 1</strong> : s�lection de la condition (<strong>voeu choisi par le candidat</strong>)
											<br>(le caract�re * signifie \"n'importe quel �l�m�nt\")
										</center>", $__INFO);

							// conservation en m�moire des champs select en cas de retour ou de modification
							if(isset($_SESSION["filtre_formations_condition_propspec"]) && $_SESSION["filtre_formations_condition_propspec"]!="-1")
								$cur_val_propspec=$_SESSION["filtre_formations_condition_propspec"];
							elseif(isset($_SESSION["fid"]) && array_key_exists($_SESSION["fid"], $_SESSION["tab_filtres"]) && $_SESSION["tab_filtres"][$_SESSION["fid"]]["cond_propspec_id"]!="-1")
								$cur_val_propspec=$_SESSION["tab_filtres"][$_SESSION["fid"]]["cond_propspec_id"];
							else
							{
								if(isset($_SESSION["filtre_formations_condition_annee"]))
									$cur_val_annee=$_SESSION["filtre_formations_condition_annee"];
								elseif(isset($_SESSION["fid"]) && array_key_exists($_SESSION["fid"], $_SESSION["tab_filtres"]))
									$cur_val_annee=$_SESSION["tab_filtres"][$_SESSION["fid"]]["cond_annee_id"];

								if(isset($_SESSION["filtre_formations_condition_mention"]))
									$cur_val_mention=$_SESSION["filtre_formations_condition_mention"];
								elseif(isset($_SESSION["fid"]) && array_key_exists($_SESSION["fid"], $_SESSION["tab_filtres"]))
									$cur_val_mention=$_SESSION["tab_filtres"][$_SESSION["fid"]]["cond_mention_id"];

								if(isset($_SESSION["filtre_formations_condition_specialite"]))
									$cur_val_spec=$_SESSION["filtre_formations_condition_specialite"];
								elseif(isset($_SESSION["fid"]) && array_key_exists($_SESSION["fid"], $_SESSION["tab_filtres"]))
									$cur_val_spec=$_SESSION["tab_filtres"][$_SESSION["fid"]]["cond_spec_id"];

								if(isset($_SESSION["filtre_formations_condition_finalite"]))
									$cur_val_finalite=$_SESSION["filtre_formations_condition_finalite"];
								elseif(isset($_SESSION["fid"]) && array_key_exists($_SESSION["fid"], $_SESSION["tab_filtres"]))
									$cur_val_finalite=$_SESSION["tab_filtres"][$_SESSION["fid"]]["cond_finalite_id"];
							}

							break;

			case 2	:	message("<center>
											<strong>Etape 2</strong> : s�lection de la cons�quence (<strong>ce que ne peut plus choisir le candidat</strong>)
											<br>(le caract�re * signifie \"n'importe quel �l�m�nt\")
										</center>", $__INFO);

							// m�moire des champs select en cas de retour ou de modification
							if(isset($_SESSION["filtre_formations_cible_propspec"]) && $_SESSION["filtre_formations_cible_propspec"]!="-1")
								$cur_val_propspec=$_SESSION["filtre_formations_cible_propspec"];
							elseif(isset($_SESSION["fid"]) && array_key_exists($_SESSION["fid"], $_SESSION["tab_filtres"]) && $_SESSION["tab_filtres"][$_SESSION["fid"]]["cible_propspec_id"]!="-1")
								$cur_val_propspec=$_SESSION["tab_filtres"][$_SESSION["fid"]]["cible_propspec_id"];
							else
							{
								if(isset($_SESSION["filtre_formations_cible_annee"]))
									$cur_val_annee=$_SESSION["filtre_formations_cible_annee"];
								elseif(isset($_SESSION["fid"]) && array_key_exists($_SESSION["fid"], $_SESSION["tab_filtres"]))
									$cur_val_annee=$_SESSION["tab_filtres"][$_SESSION["fid"]]["cible_annee_id"];

								if(isset($_SESSION["filtre_formations_cible_mention"]))
									$cur_val_mention=$_SESSION["filtre_formations_cible_mention"];
								elseif(isset($_SESSION["fid"]) && array_key_exists($_SESSION["fid"], $_SESSION["tab_filtres"]))
									$cur_val_mention=$_SESSION["tab_filtres"][$_SESSION["fid"]]["cible_mention_id"];

								if(isset($_SESSION["filtre_formations_cible_specialite"]))
									$cur_val_spec=$_SESSION["filtre_formations_cible_specialite"];
								elseif(isset($_SESSION["fid"]) && array_key_exists($_SESSION["fid"], $_SESSION["tab_filtres"]))
									$cur_val_spec=$_SESSION["tab_filtres"][$_SESSION["fid"]]["cible_spec_id"];

								if(isset($_SESSION["filtre_formations_cible_finalite"]))
									$cur_val_finalite=$_SESSION["filtre_formations_cible_finalite"];
								elseif(isset($_SESSION["fid"]) && array_key_exists($_SESSION["fid"], $_SESSION["tab_filtres"]))
									$cur_val_finalite=$_SESSION["tab_filtres"][$_SESSION["fid"]]["cible_finalite_id"];
							}

							break;

			case 3	: 	message("<strong>Derni�re �tape</strong> : r�capitulatif, options et confirmation", $__INFO);

							if(isset($_SESSION["filtre_formations_nom"]))
								$cur_val_nom=$_SESSION["filtre_formations_nom"];
							elseif(isset($_SESSION["fid"]) && array_key_exists($_SESSION["fid"], $_SESSION["tab_filtres"]))
								$cur_val_nom=$_SESSION["tab_filtres"][$_SESSION["fid"]]["nom"];
							else
								$cur_val_nom="";

							break;
		}

		if($_SESSION["etape"]!="3")
		{
			// S�lection d'une formation compl�te
			$result=db_query($dbr, "SELECT $_DBC_propspec_id, $_DBC_propspec_annee, $_DBC_annees_annee, $_DBC_propspec_id_spec,
													 $_DBC_specs_nom, $_DBC_specs_mention_id, $_DBC_propspec_finalite, $_DBC_mentions_nom,
													 $_DBC_propspec_manuelle, $_DBC_propspec_active
												FROM $_DB_annees, $_DB_propspec, $_DB_specs, $_DB_mentions
											WHERE $_DBC_propspec_annee=$_DBC_annees_id
											AND $_DBC_propspec_id_spec=$_DBC_specs_id
											AND $_DBC_specs_mention_id=$_DBC_mentions_id
											AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
											AND $_DBC_propspec_active='1'
												ORDER BY $_DBC_annees_ordre, $_DBC_specs_mention_id, $_DBC_specs_nom, $_DBC_propspec_finalite");

			$rows=db_num_rows($result);

			print("<table cellpadding='4' align='center'>
					<tr>
						<td class='fond_menu2' colspan='2'>
							<font class='Texte_menu2'><strong>S�lectionnez une formation ... </strong></font>
						</td>
					</tr>
					<tr>
						<td class='fond_menu2' align='right'>
							<font class='Texte_menu2' style='font-weight:bold;'>Formation : </font>
						</td>
						<td class='fond_menu'>\n");

			if($rows)
			{
				print("<select name='propspec_id' size='1'>
							<option value='-1'></option>\n");

				$old_annee=$old_mention="-1";

				$_SESSION["tab_formations"]=array();

				for($i=0; $i<$rows; $i++)
				{
					list($form_propspec_id, $form_annee_id, $form_annee_nom, $form_spec_id, $form_spec_nom, $form_mention_id,
							$form_finalite, $form_mention_nom, $form_manuelle, $form_active)=db_fetch_row($result, $i);

					if($form_annee_id!=$old_annee)
					{
						if($i!=0)
							print("</optgroup>
										<option value='-1' label='' disabled></option>\n");

						$annee_nom=$form_annee_nom=="" ? "Ann�es particuli�res" : $form_annee_nom;

						print("<optgroup label='$annee_nom'>\n");

						$new_sep_annee=1;

						$old_annee=$form_annee_id;
						$old_mention="-1";
					}
					else
						$new_sep_annee=0;

					if($form_mention_id!=$old_mention)
					{
						if(!$new_sep_annee)
							print("</optgroup>
									 <option value='-1' label='' disabled></option>\n");

						$val=htmlspecialchars($form_mention_nom, ENT_QUOTES);

						print("<optgroup label='- $val'>\n");

						$old_mention=$form_mention_id;
					}

					$manuelle_txt=$form_manuelle ? "(M) " : "";

					$selected=isset($cur_val_propspec) && $cur_val_propspec==$form_propspec_id ? "selected" : "";
					
					if($form_annee_nom=="")
					{
						print("<option value='$form_propspec_id' label=\"$manuelle_txt$form_spec_nom $tab_finalite[$form_finalite]\" $selected>$manuelle_txt$form_spec_nom  $tab_finalite[$form_finalite]</option>\n");
						$_SESSION["tab_formations"][$form_propspec_id]=$form_spec_nom . " " . $tab_finalite[$form_finalite] . " (mention : $form_mention_nom)";
					}
					else
					{
						print("<option value='$form_propspec_id' label=\"$manuelle_txt$form_annee_nom - $form_spec_nom  $tab_finalite[$form_finalite]\" $selected>$manuelle_txt$form_annee_nom - $form_spec_nom  $tab_finalite[$form_finalite]</option>\n");
						$_SESSION["tab_formations"][$form_propspec_id]="$form_annee_nom - $form_spec_nom " . $tab_finalite[$form_finalite] . " (mention : $form_mention_nom)";
					}
				}

				print("</optgroup>
						</select>
						<br>
						<font class='Texte_important_menu'><strong>S'il est utilis�, ce champ sera prioritaire sur les suivants.</strong></font>\n");
			}
			else
				print("<font class='Texte_menu'><i>Aucune formation enregistr�e</i></font>\n");

			print("</td>
					</tr>\n");

			// S�lection d'une ann�e
			$result=db_query($dbr, "SELECT $_DBC_annees_id, $_DBC_annees_annee FROM $_DB_annees ORDER BY $_DBC_annees_ordre");
			$rows=db_num_rows($result);

			print("<tr>
						<td class='fond_page' colspan='2' height='15px'></td>
					 </tr>
					 <tr>
						<td class='fond_menu2' colspan='2'>
							<font class='Texte_menu2'>... <strong>ou</strong> une combinaison des �lements suivants :</font>
						</td>
					</tr>
					<tr>
						<td class='fond_menu2' align='right'>
							<font class='Texte_menu2' style='font-weight:bold;'>Ann�e : </font>
						</td>
						<td class='fond_menu'>\n");

			if($rows)
			{
				$_SESSION["tab_annees"]=array();

				print("<select name='annee_id' size='1'>
							<option value='-1'>*</option>\n");

				for($i=0; $i<$rows; $i++)
				{
					list($form_annee_id, $form_annee_nom)=db_fetch_row($result, $i);

					$form_annee_nom=$form_annee_nom=="" ? "Ann�es particuli�res" : $form_annee_nom;

					$_SESSION["tab_annees"][$form_annee_id]=$form_annee_nom;

					$selected=isset($cur_val_annee) && $cur_val_annee==$form_annee_id ? "selected" : "";

					print("<option value='$form_annee_id' $selected>$form_annee_nom</option>\n");
				}

				print("</optgroup>
						</select>\n");
			}
			else
				print("<font class='Texte_menu'><i>Aucune ann�e enregistr�e</i></font>\n");

			print("</td>
					</tr>\n");

			// S�lection d'une mention
			$result=db_query($dbr, "SELECT $_DBC_mentions_id, $_DBC_mentions_nom FROM $_DB_mentions
												WHERE $_DBC_mentions_comp_id='$_SESSION[comp_id]'
											ORDER BY $_DBC_mentions_nom");
			$rows=db_num_rows($result);

			print("<tr>
						<td class='fond_menu2' align='right'>
							<font class='Texte_menu2' style='font-weight:bold;'>Mention : </font>
						</td>
						<td class='fond_menu'>\n");

			if($rows)
			{
				$_SESSION["tab_mentions"]=array();

				print("<select name='mention_id' size='1'>
							<option value='-1'>*</option>\n");

				for($i=0; $i<$rows; $i++)
				{
					list($form_mention_id, $form_mention_nom)=db_fetch_row($result, $i);

					$_SESSION["tab_mentions"][$form_mention_id]=$form_mention_nom;

					$selected=isset($cur_val_mention) && $cur_val_mention==$form_mention_id ? "selected" : "";

					print("<option value='$form_mention_id' $selected>$form_mention_nom</option>\n");
				}

				print("</optgroup>
						</select>\n");

			}
			else
				print("<font class='Texte_menu'><i>Aucune mention enregistr�e</i></font>\n");

			print("</td>
					</tr>\n");

			// S�lection d'une sp�cialit�
			$result=db_query($dbr, "SELECT $_DBC_specs_id, $_DBC_specs_nom, $_DBC_mentions_nom FROM $_DB_specs, $_DB_mentions
												WHERE $_DBC_mentions_id=$_DBC_specs_mention_id
												AND $_DBC_specs_comp_id='$_SESSION[comp_id]'
											ORDER BY $_DBC_mentions_nom, $_DBC_specs_nom");
			$rows=db_num_rows($result);

			print("<tr>
						<td class='fond_menu2' align='right'>
							<font class='Texte_menu2' style='font-weight:bold;'>Sp�cialit� : </font>
						</td>
						<td class='fond_menu'>\n");

			if($rows)
			{
				$_SESSION["tab_specs"]=array();

				print("<select name='spec_id' size='1'>
							<option value='-1'>*</option>\n");

				$old_mention="--";

				for($i=0; $i<$rows; $i++)
				{
					list($form_spec_id, $form_spec_nom, $mention_nom)=db_fetch_row($result, $i);

					$_SESSION["tab_specs"][$form_spec_id]=$form_spec_nom;

					if($mention_nom!=$old_mention)
					{
						$val=htmlspecialchars($mention_nom, ENT_QUOTES);

						print("</optgroup>
								 <optgroup label=\"- $mention_nom\">\n");

						$old_mention=$mention_nom;
					}

					$selected=isset($cur_val_spec) && $cur_val_spec==$form_spec_id ? "selected" : "";

					print("<option value='$form_spec_id' label=\"$form_spec_nom\" $selected>$form_spec_nom</option>\n");
				}

				print("</optgroup>
						</select>\n");

			}
			else
				print("<font class='Texte_menu'><i>Aucune sp�cialit� enregistr�e</i></font>\n");

			if(isset($cur_val_finalite))
			{
				switch($cur_val_finalite)
				{
					case	'-1'	:	$select_all="selected";
										$select_sans=$select_rech=$select_pro="";
										break;

					case	'0'	:	$select_sans="selected";
										$select_all=$select_rech=$select_pro="";
										break;

					case	'1'	:	$select_rech="selected";
										$select_all=$select_sans=$select_pro="";
										break;

					case	'2'	:	$select_pro="selected";
										$select_all=$select_sans=$select_rech="";
										break;
				}
			}
			else
				$select_all=$select_sans=$select_rech=$select_pro="";

			print("</td>
					</tr>
					<tr>
						<td class='fond_menu2' align='right'>
							<font class='Texte_menu2' style='font-weight:bold;'>Finalit� : </font>
						</td>
						<td class='fond_menu'>
							<select name='finalite' size='1'>
								<option value='-1' $select_all>*</option>
								<option value='0' $select_sans>Formations sans finalit�</option>
								<option value='1' $select_rech>Recherche</option>
								<option value='2' $select_pro>Professionnelle</option>
							</select>
						</td>
					</tr>
					</table>

					<div class='centered_icons_box'>\n");

			if($_SESSION["etape"]==2)
				print("<a href='$php_self?e=1' target='_self' class='lien_bleu_12'><img class='icone' src='$__ICON_DIR/back_32x32_fond.png' alt='Retour' border='0'  title='Retour'></a>\n");

			print("<a href='index.php' target='_self' class='lien_bleu_12'><img class='icone' src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Retour' border='0'></a>
					<input type='image' class='icone' style='vertical-align:bottom;' src='$__ICON_DIR/forward_32x32_fond.png' alt='Suivant' name='suivant' value='Suivant' title='[Etape suivante]'>
					</form>
				</div>\n");

			db_free_result($result);
		}
		elseif($_SESSION["etape"]==3) // Etape de confirmation + nommage du filtre
		{
			print("<table cellpadding='4' align='center'>
					<tr>
						<td class='fond_menu2' colspan='2'>
							<font class='Texte_menu2'><strong>Si un candidat choisit :</strong></font>
						</td>
					</tr>
					<tr>
						<td class='fond_menu' colspan='2'>
							<font class='Texte'>$_SESSION[filtre_condition]$_SESSION[filtre_condition_txt]</font>
						</td>
					</tr>
					<tr>
						<td class='fond_menu2' colspan='2'>
							<font class='Texte_menu2'><strong>Alors il ne peut plus choisir :</strong></font>
						</td>
					</tr>
					<tr>
						<td class='fond_menu' colspan='2'>
							<font class='Texte'>$filtre_cible$filtre_cible_txt</font>
						</td>
					</tr>
					<tr>
						<td class='fond_page' colspan='2' height='15px'></td>
					 </tr>
					 <tr>
						<td class='fond_menu2' colspan='2'>
							<font class='Texte_menu2'><strong>Option</strong></font>
						</td>
					</tr>
					<tr>
						<td class='fond_menu2' align='right'>
							<font class='Texte_menu2' style='font-weight:bold;'>Nom du filtre : </font>
						</td>
						<td class='fond_menu'>
							<input type='text' name='nom_filtre' value='$cur_val_nom' size='40' maxlength='40'>
							<font class='Texte_menu'><i>(40 caract�res maximum)</i></font>
						</td>
					</tr>\n");

				if(isset($_SESSION["ajout"]) && $_SESSION["ajout"]==1)
				{
					print("<tr>
								<td class='fond_page' colspan='2' height='15px'></td>
							</tr>
							<tr>
								<td class='fond_menu2' align='right'>
									<font class='Texte_menu2' style='font-weight:bold;'>Cr�er automatiquement le filtre r�ciproque ?</font>
								</td>
								<td class='fond_menu'>
									<input style='vertical-align:middle; padding-right:5px;' type='radio' name='reciproque' value='1'><font class='Texte_menu'>Oui</font>
									<input style='vertical-align:middle; padding-left:10px; padding-right:5px;' type='radio' name='reciproque' value='0' checked><font class='Texte_menu'>Non</font>
								</td>
							</tr>\n");
				}

				print("</table>

				<div class='centered_icons_box'>
					<a href='$php_self?e=2' target='_self' class='lien_bleu_12'><img class='icone' src='$__ICON_DIR/back_32x32_fond.png' alt='Retour' border='0'  title='Retour'></a>
					<a href='index.php' target='_self' class='lien_bleu_12'><img class='icone' src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Annuler' border='0' title='Annuler'></a>
					<input type='image' class='icone' style='vertical-align:bottom;' src='$__ICON_DIR/button_ok_32x32_fond.png' alt='Valider' name='valider' value='Valider' title='[Valider le filtre]'>
					</form>
				</div>\n");
		}
	?>
</div>
<?php
	pied_de_page();
?>
</body></html>
