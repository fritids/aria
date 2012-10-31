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

	include "../../configuration/aria_config.php";
	include "$__INCLUDE_DIR_ABS/vars.php";
	include "$__INCLUDE_DIR_ABS/fonctions.php";
	include "$__INCLUDE_DIR_ABS/db.php";

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	if(!isset($_SESSION["authentifie"]))
	{
		session_write_close();
		header("Location:../../index.php");
		exit();
	}

	if(!isset($_SESSION["comp_id"]) || (isset($_SESSION["comp_id"]) && $_SESSION["comp_id"]==""))
	{
		session_write_close();
		header("Location:../composantes.php");
		exit();
	}

	// Flag de lecture
	if(isset($_GET["q"]) && $_GET["q"]==1)
		unset($_SESSION["q_messages_non_lus"]);

	$dbr=db_connect();

	// Nettoyage
	unset($_SESSION['msg_sujet']);
	unset($_SESSION['msg_exp_id']);
	unset($_SESSION['msg_exp']);
	unset($_SESSION['msg_message']);
	unset($_SESSION['msg_message_txt']);

	if(isset($_GET["dossier"]) && ctype_digit($_GET["dossier"]) && array_key_exists($_GET["dossier"], $__MSG_DOSSIERS))
		$current_dossier=$_SESSION["current_dossier"]=$_GET["dossier"];

	// Offset
	if(isset($_GET["offset"]) && ctype_digit($_GET["offset"]))
		$offset=$_GET["offset"];
	elseif(isset($_SESSION["msg_offset"]))
		$offset=$_SESSION["msg_offset"];

	// S�lection / d�s�lection des messages
	if(isset($_GET["sa"]) && $_GET["sa"]==1)
		$checked="checked";
	elseif((isset($_GET["sa"]) && $_GET["sa"]==0) || !isset($_GET["sa"]))
		$checked="";

	// Suppression ou d�placement des messages s�lectionn�s

	if((isset($_POST["move"]) || isset($_POST["move_x"])) && isset($_SESSION["current_dossier"]) && isset($_POST["newfolder"]) && isset($_POST["selection"]))
	{
		$nouveau_dossier=$_POST["newfolder"];

		if($nouveau_dossier!="" && $nouveau_dossier!=$_SESSION["current_dossier"])
		{
			foreach($_POST["selection"] as $filename)
			{
            if(!is_dir("$__CAND_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[authentifie]/$nouveau_dossier"))
				{
					if(FALSE==mkdir("$__CAND_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[authentifie]/$nouveau_dossier", 0770, TRUE))
					{
						mail($__EMAIL_ADMIN, "[Pr�candidatures] - Erreur de cr�ation de r�pertoire", "R�pertoire : $__CAND_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[authentifie]/$nouveau_dossier");
						die("Erreur syst�me lors de la cr�ation du dossier destination. Un message a �t� envoy� � l'administrateur.");
					}
				}

				if((is_file("$__CAND_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[authentifie]/$_SESSION[current_dossier]/$filename") || is_dir("$__CAND_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[authentifie]/$_SESSION[current_dossier]/$filename"))
				    && is_dir("$__CAND_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[authentifie]/$nouveau_dossier"))
					rename("$__CAND_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[authentifie]/$_SESSION[current_dossier]/$filename", "$__CAND_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[authentifie]/$nouveau_dossier/$filename");
				else
					mail($__EMAIL_ADMIN, "[Pr�candidatures] - Erreur de d�placement de message", "Source : $__CAND_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[authentifie]/$_SESSION[current_dossier]/$filename\nDestination : $__CAND_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[authentifie]/$nouveau_dossier/$filename");
			}
		}
	}

	if((isset($_POST["suppr"]) || isset($_POST["suppr_x"])) && isset($_SESSION["current_dossier"]) && isset($_POST["selection"]))
	{
		foreach($_POST["selection"] as $filename)
		{
			if(is_file("$__CAND_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[authentifie]/$_SESSION[current_dossier]/$filename") || is_dir("$__CAND_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[authentifie]/$_SESSION[current_dossier]/$filename"))
			{
				if($_SESSION["current_dossier"]==$__MSG_TRASH) // si dossier actuel = corbeille : suppression = effacement physique
				{
					if(is_file("$__CAND_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[authentifie]/$_SESSION[current_dossier]/$filename"))
						unlink("$__CAND_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[authentifie]/$_SESSION[current_dossier]/$filename");
					elseif(is_dir("$__CAND_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[authentifie]/$_SESSION[current_dossier]/$filename"))
						deltree("$__CAND_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[authentifie]/$_SESSION[current_dossier]/$filename");
				}
				else // Vers la corbeille
					rename("$__CAND_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[authentifie]/$_SESSION[current_dossier]/$filename", "$__CAND_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[authentifie]/$__MSG_TRASH/$filename");
			}
		}
	}

	if(isset($_GET["trash"]) && $_GET["trash"]==1)
	{
		if($del=scandir("$__CAND_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[authentifie]/$__MSG_TRASH/"))
		{
			$points=array(".", "..");

			$del_files=array_diff($del, $points); // suppression des r�pertoires . et .. de la liste des fichiers � supprimer
			
			foreach($del_files as $file)
				@unlink("$__CAND_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[authentifie]/$__MSG_TRASH/$file");
		}
	}

	en_tete_candidat();
	menu_sup_candidat($__MENU_MSG);

?>
<div class='main'>
	<div class='menu_gauche'>
		<ul class='menu_gauche'>
			<?php
				if(!isset($_SESSION["current_dossier"]))
					$current_dossier=$_SESSION["current_dossier"]=$__MSG_INBOX;
				else
					$current_dossier=$_SESSION["current_dossier"];

				dossiers_messagerie();
			?>
		</ul>
	</div>
	<div class='corps'>
		<?php
			titre_page_icone("Messagerie interne", "email_32x32_fond.png", 15, "L");

			if(isset($_GET["sent"]) && $_GET["sent"]==1)
				message("Votre message a �t� envoy�.", $__SUCCES);

			if(!is_dir("$__CAND_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[authentifie]/"))
			{
				if(FALSE==mkdir("$__CAND_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[authentifie]/$current_dossier", 0770, TRUE))
				{
					mail($__EMAIL_ADMIN, "[Pr�candidatures] - Erreur de cr�ation de r�pertoire", "R�pertoire : $__CAND_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[authentifie]/$current_dossier\n\nUtilisateur : $_SESSION[nom] $_SESSION[prenom]");

					die("Erreur syst�me lors de la cr�ation de votre r�pertoire personnel. Un message a �t� envoy� � l'administrateur.");
				}
			}

			$_SESSION["contenu_repertoire"] = scandir("$__CAND_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[authentifie]/$current_dossier", 1);

			// 3 �l�ments � ne pas inclure dans la recherche : ".", "..", et "index.php"
			if(FALSE!==($key=array_search(".", $_SESSION["contenu_repertoire"])))
				unset($_SESSION["contenu_repertoire"][$key]);

			if(FALSE!==($key=array_search("..", $_SESSION["contenu_repertoire"])))
				unset($_SESSION["contenu_repertoire"][$key]);

			if(FALSE!==($key=array_search("index.php", $_SESSION["contenu_repertoire"])))
				unset($_SESSION["contenu_repertoire"][$key]);
			// **************** //

			rsort($_SESSION["contenu_repertoire"]);
			$nb_msg=$nb_fichiers=count($_SESSION["contenu_repertoire"]);

			if($nb_msg==1)
				$nb_msg_texte="1 message";
			elseif($nb_msg==0)
				$nb_msg_texte="Aucun message";
			else
				$nb_msg_texte="$nb_msg messages";
				
			if(!isset($offset) || !ctype_digit($offset) || $offset>$nb_msg)
				$offset=0;

			$_SESSION["msg_offset"]=$offset;

			// Calcul des num�ros de messages et de la pr�sence/absence de fl�ches pour aller � la page suivante/pr�c�dente
			if($_SESSION["msg_offset"]>0)	 // lien vers la page pr�c�dente
			{
				$prev_offset=$_SESSION["msg_offset"]-20;

				$prev_offset=$prev_offset < 0 ? 0 : $prev_offset;

				$prev="<a href='$php_self?offset=$prev_offset'><img src='$__ICON_DIR/back_16x16_menu2.png' border='0'></a>";
				$prev_txt="[$prev_offset - $_SESSION[msg_offset]] ";

				$limite_inf_msg=$_SESSION["msg_offset"];
			}
			else
			{
				$prev=$prev_txt="";
				$limite_inf_msg=0;
			}

			if(($_SESSION["msg_offset"]+20)<$nb_msg) // encore des messages
			{
				// texte affich�
				if(($_SESSION["msg_offset"]+40)<$nb_msg)
				 	$limite_texte=$_SESSION["msg_offset"]+40;
				else
					$limite_texte=$nb_msg;

				// Offset suivant et limite de la boucle for() suivante
				$limite_sup_msg=$next_offset=$_SESSION["msg_offset"]+20;

				$next="<a href='$php_self?offset=$next_offset' style='vertical-align:bottom;'><img src='$__ICON_DIR/forward_16x16_menu2.png' border='0'></a>";
				$next_txt="[$next_offset - $limite_texte]";
			}
			else
			{
				$next=$next_txt="";

				// Pour la limite de la boucle for() suivante
				$limite_sup_msg=$nb_msg;
			}

			if($limite_sup_msg) // Si on a des messages, on propose des liens suppl�mentaires
			{
				// Lien "S�lection / d�s�lection"
				// if(isset($select_all) && $select_all==1)
				if(!empty($checked))
					$lien_select_deselect="<a href='$php_self?sa=0' class='lien_menu_gauche'>Tout d�s�lectionner</a>";
				else
					$lien_select_deselect="<a href='$php_self?sa=1' class='lien_menu_gauche'>Tout s�lectionner</a>";

				// Lien "Vider la corbeille"
				if($current_dossier==$__MSG_TRASH)
					$lien_corbeille="&nbsp;|&nbsp;<a href='$php_self?trash=1' class='lien_menu_gauche'>Vider la corbeille</a>";
				else
					$lien_corbeille="";
			}
			else
				$lien_select_deselect=$lien_corbeille="";

			// changement de nom pour la colonne "Exp�diteur" si le dossier est "Envoy�s"
			$col_name=$current_dossier==$__MSG_SENT ? "Destinataire" : "Exp�diteur";

			print("<form action='$php_self' method='POST' name='form1'>
						<table class='encadre_messagerie' width='95%' align='center'>
						<tr>
							<td class='td-msg-titre fond_menu2' style='border-right:0px; padding:1px 2px 1px 2px;' colspan='2'>
								<font class='Texte_menu2'>
									<a href='compose.php' class='lien_menu_gauche'>Nouveau message</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='$php_self' class='lien_menu_gauche'>Rafra�chir</a>$lien_corbeille
								</font>
							</td>
							<td class='td-msg-titre fond_menu2' style='vertical-align:middle; text-align:right; border-left:0px; padding:1px 2px 1px 2px;' colspan='2'>
								<font class='Texte_menu2'>
<!--
								<table cellpadding='0' cellspacing='0' align='right'>
								<tr>
									<td style='padding-right:10px;'>
										<font class='Texte_menu'>
-->
											$lien_select_deselect
											<select name='newfolder'>
												<option value=''></option>
												<option value='$__MSG_INBOX'>$__MSG_DOSSIERS[$__MSG_INBOX]</option>
												<option value='$__MSG_SENT'>$__MSG_DOSSIERS[$__MSG_SENT]</option>
												<option value='$__MSG_TRAITES'>$__MSG_DOSSIERS[$__MSG_TRAITES]</option>
												<option value='$__MSG_TRASH'>$__MSG_DOSSIERS[$__MSG_TRASH]</option>
											</select>
											&nbsp;
											<input type='submit' name='move' value='D�placer'>&nbsp;&nbsp;&nbsp;<input type='submit' name='suppr' value='Supprimer'>
<!--
										</td>

										<td style='padding-right:4px;'>
											<font class='Texte_menu'>$prev_txt</font>
										</td>
										<td style='padding-right:4px;'>$prev</td>
										<td style='padding-left:4px;'>$next</td>
										<td style='padding-left:4px;'>
											<font class='Texte_menu'>$next_txt</font>
										</td>
									</tr>
									</table>
-->
									$prev_txt
									$prev
									$next
									$next_txt</font>
								</font>
							</td>
						</tr>
						<tr>
							<td class='td-msg-titre fond_page' style='border-right:0px; border-left:0px; height:10px;' colspan='4'></td>
						</tr>
						<tr>
							<td class='td-msg-titre fond_menu' style='padding:4px 2px 4px 2px;' colspan='4'>
								<font class='Texte_menu'>
									<b>$__MSG_DOSSIERS[$current_dossier]</b> ($nb_msg_texte)
								</font>
							</td>
						</tr>
						<tr>
							<td class='td-msg-menu fond_menu' style='padding:4px 2px 4px 2px;' width='10%'><font class='Texte_menu'><b>Date</b></font></td>
							<td class='td-msg-menu fond_menu' style='padding:4px 2px 4px 2px;'><font class='Texte_menu'><b>$col_name</b></font></td>
							<td class='td-msg-menu fond_menu' style='padding:4px 2px 4px 2px;'><font class='Texte_menu'><b>Sujet</b></font></td>
							<td class='td-msg-menu fond_menu' style='padding:4px 2px 4px 2px;' width='5%'><font class='Texte_menu'></font></td>
						</tr>\n");

			for($i=$limite_inf_msg; $i<$limite_sup_msg; $i++)
			{
				// TODO : ajouter tests de retour des fonctions
				// $fichier=$_SESSION["repertoire"] . "/" . $_SESSION["contenu_repertoire"][$i];

				$fichier="$__CAND_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[authentifie]/$current_dossier/" . $_SESSION["contenu_repertoire"][$i];

				$file_or_dir_name=$nom_fichier=$_SESSION["contenu_repertoire"][$i];

				if(is_dir($fichier)) // R�pertoire : message avec pi�ce(s) jointe(s)
				{
					// On regarde le contenu du r�pertoire. Normalement, le message a le m�me nom que ce dernier, termin� par .0 ou .1
					if(is_file("$fichier/$nom_fichier.0"))
					{
						$fichier.="/$nom_fichier.0";
						$nom_fichier="$nom_fichier.0";
					}
					elseif(is_file("$fichier/$nom_fichier.1"))
					{
						$fichier.="/$nom_fichier.1";
						$nom_fichier="$nom_fichier.1";
					}

					$crypt_params=crypt_params("dir=1&msg=$fichier");
				}
				else
					$crypt_params=crypt_params("msg=$fichier");

				if(strlen($nom_fichier)==18) // Ann�e sur un caract�re (16 pour l'identifiant + ".0" ou ".1" pour le flag "read")
				{
					$date_offset=0;
					$annee_len=1;
					$leading_zero="0";
					$msg_id=substr($nom_fichier, 0, 16);
					$msg_read=substr($nom_fichier, 17, 1);
				}
				else // Ann�e sur 2 caract�res (chaine : 19 caract�res)
				{
					$date_offset=1;
					$annee_len=2;
					$leading_zero="";
					$msg_id=substr($nom_fichier, 0, 17);
					$msg_read=substr($nom_fichier, 18, 1);
				}

				if(($array_file=file("$fichier"))==FALSE)
				{
					mail($__EMAIL_ADMIN, "[Pr�candidatures] - Erreur d'ouverture de mail", "Fichier : $fichier\n\nUtilisateur : $_SESSION[nom] $_SESSION[prenom]");

					die("Erreur d'ouverture du fichier. Un message a �t� envoy� � l'administrateur.");
				}

				$msg_exp_id=$array_file["0"];
				$msg_exp=$array_file["1"];
				$msg_to_id=$array_file["2"];
				$msg_to=$array_file["3"];
				$msg_sujet=stripslashes($array_file["4"]);

				$date_today=date("ymd") . "00000000000"; // on s'aligne sur le format des identifiants

				// On convertit la date en temps Unix : plus simple ensuite pour l'affichage et les conversions
				$unix_date=mktime(substr($msg_id, 5+$date_offset, 2), substr($msg_id, 7+$date_offset, 2), substr($msg_id, 9+$date_offset, 2),
										substr($msg_id, 1+$date_offset, 2), substr($msg_id, 3+$date_offset, 2), $leading_zero . substr($msg_id, 0, $annee_len));

				if($msg_id<$date_today) // le message n'est pas du jour : on affiche la date enti�re (date + heure)
					$date_txt=date_fr("d/m/y - H\hi", $unix_date);
				else // message du jour : on n'affiche que l'heure
					$date_txt=date_fr("H\hi", $unix_date);

				if(!$msg_read)
				{
					$style_bold="style='font-weight:bold;'";
					$style_bg="background-color:#FFECBD;";
				}
				else
				{
					$style_bold="";
					$style_bg="";
				}

				$col_value=$current_dossier==$__MSG_SENT ? $msg_to : $msg_exp;

				print("<tr>
							<td class='td-msg' style='text-align:left; $style_bg' width='10%'><font class='Texte' $style_bold>$date_txt</font></td>
							<td class='td-msg' style='text-align:left; $style_bg'><font class='Texte' $style_bold>
								<a href='message.php?p=$crypt_params' class='lien_bleu_12'>$col_value</a>
							</td>
							<td class='td-msg' style='text-align:left; white-space:normal; $style_bg'>
								<a href='message.php?p=$crypt_params' class='lien_bleu_12' $style_bold>$msg_sujet</a>
							</td>
							<td class='td-msg' width='5%' style='text-align:center; $style_bg'>\n");

				// Impossible de s�lectionner (et donc supprimer) un message de l'utilisateur Syst�me
				
				if($msg_exp_id!=$__USER_SYSTEME_ID)
					print("<font class='Texte_menu'><input type='checkbox' name='selection[]' value='$file_or_dir_name' $checked>\n");

				print("	</td>
						</tr>\n");
		}

			print("</table>\n");
							
			// db_free_result($result);
			db_close($dbr);
		?>
	</div>
</div>
<?php
	pied_de_page_candidat();
?>
</body></html>
