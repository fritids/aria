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

	$dbr=db_connect();

	// D�verrouillage, au cas o�
	if(isset($_SESSION["candidat_id"]))
		cand_unlock($dbr, $_SESSION["candidat_id"]);

	// Nettoyage
	unset($_SESSION['msg_sujet']);
	unset($_SESSION['msg_exp_id']);
	unset($_SESSION['msg_exp']);
	unset($_SESSION['msg_to']);
	unset($_SESSION["msg_dest_civilite"]);
	unset($_SESSION["msg_dest_nom"]);
	unset($_SESSION["msg_dest_prenom"]);
	unset($_SESSION["msg_dest_email"]);
	unset($_SESSION['msg_message']);
	unset($_SESSION['msg_message_txt']);
	unset($_SESSION["msg_fichier"]);
	unset($_SESSION["msg_read"]);
	unset($_SESSION["ajout"]);
	unset($_SESSION["current_corps"]);

	// Suppression des fichiers joints temporaires �ventuels
	unset($_SESSION["tmp_message_fichiers"]);
	
	if(isset($_SESSION["auth_id"]) && $_SESSION["auth_id"]!="" && is_dir("$__ROOT_DIR/$__MOD_DIR/tmp/$_SESSION[auth_id]"))
	   deltree("$__ROOT_DIR/$__MOD_DIR/tmp/$_SESSION[auth_id]");

	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();

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

   // Ouverture de l'aper�u d'un message en cliquant sur l'exp�diteur ou le sujet
   $apercu_message="";   
   
   if(isset($_GET["p"]) && -1!=($params=get_params($_GET['p'])))
   {
      if(isset($params["msg"]))
      {
         // Nom du fichier avec les r�pertoires
         $fichier_apercu=$params["msg"];
         
         if(array_key_exists("dir", $params)) // sauvegarde du param�tre pour l'ouverture
            $dir=$params["dir"];
                  
         // ouverture
         if(($array_file=@file("$fichier_apercu"))!==FALSE)
         {
            // On n'ouvre que le corps du message (� partir de la 6�me ligne)
            $corps_apercu_message=array_slice($array_file, 5);
            
            // Suppression des lignes vides
            $apercu_array=array();
            $i=0;
            foreach($corps_apercu_message as $ligne_apercu)
            {               
               if(trim($ligne_apercu)!="") // on ne garde que les 5 premi�res lignes vides, le reste est ignor�
               {
                  if($i<5)
                     $apercu_array[$i]=$ligne_apercu;
                  elseif($i==5)
                     $apercu_array[$i]="[...]";
                     
                  $i++; 
               }
            }
            $crypt_params=isset($dir) ? crypt_params("dir=$dir&msg=$fichier_apercu") : crypt_params("msg=$fichier_apercu");
            $apercu_message=stripslashes(implode($apercu_array)) . "\n<a href='message.php?p=$crypt_params' class='lien_bleu_12'><strong>[Ouvrir le message]</strong></a>";
         }
      }
   }
     
	// Suppression ou d�placement des messages s�lectionn�s

	if((isset($_POST["move"]) || isset($_POST["move_x"])) && isset($_SESSION["current_dossier"]) && isset($_POST["newfolder"]) && isset($_POST["selection"]))
	{
		$nouveau_dossier=$_POST["newfolder"];

		if($nouveau_dossier!="" && $nouveau_dossier!=$_SESSION["current_dossier"])
		{
			foreach($_POST["selection"] as $filename)
			{
				if(!is_dir("$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$nouveau_dossier"))
				{
					if(FALSE==mkdir("$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$nouveau_dossier", 0770, TRUE))
					{
						mail($__EMAIL_ADMIN, "[Pr�candidatures] - Erreur de cr�ation de r�pertoire", "R�pertoire : $__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$nouveau_dossier\n\nUtilisateur : $_SESSION[auth_prenom] $_SESSION[auth_nom]");
						die("Erreur syst�me lors de la cr�ation du dossier destination. Un message a �t� envoy� � l'administrateur.");
					}
				}

				if((is_file("$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$_SESSION[current_dossier]/$filename") || is_dir("$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$_SESSION[current_dossier]/$filename"))
				    && is_dir("$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$nouveau_dossier/"))
					rename("$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$_SESSION[current_dossier]/$filename", "$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$nouveau_dossier/$filename");
				else
					mail($__EMAIL_ADMIN, "[Pr�candidatures] - Erreur de d�placement de message", "Source : $__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$_SESSION[current_dossier]/$filename\nDestination : $__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$nouveau_dossier/\n\nUtilisateur : $_SESSION[auth_prenom] $_SESSION[auth_nom]");
			}
		}
	}

	if((isset($_POST["suppr"]) || isset($_POST["suppr_x"])) && isset($_SESSION["current_dossier"]) && isset($_POST["selection"]))
	{
		foreach($_POST["selection"] as $filename)
		{
			if(is_file("$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$_SESSION[current_dossier]/$filename") || is_dir("$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$_SESSION[current_dossier]/$filename"))
			{
				if($_SESSION["current_dossier"]==$__MSG_TRASH) // dossier actuel = corbeille : suppression = effacement physique
				{
				   // Attention : fonctions diff�rentes pour un r�pertoire et un fichier
				   if(is_file("$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$_SESSION[current_dossier]/$filename"))
						unlink("$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$_SESSION[current_dossier]/$filename");
					elseif(is_dir("$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$_SESSION[current_dossier]/$filename"))
						deltree("$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$_SESSION[current_dossier]/$filename");
				}
				else // Vers la corbeille
					rename("$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$_SESSION[current_dossier]/$filename", "$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$__MSG_TRASH/$filename");
			}
		}
	}

	// Vidage de la corbeille
	if(isset($_GET["trash"]) && $_GET["trash"]==1)
	{
		if($del=scandir("$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$__MSG_TRASH/"))
		{
			$points=array(".", "..");

			$del_files=array_diff($del, $points); // suppression des r�pertoires . et .. de la liste des fichiers � supprimer
			
			foreach($del_files as $file)
				@unlink("$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$__MSG_TRASH/$file");
		}
	}
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
			<li class='menu_gauche' style='margin-top:30px;'><a href='modeles/modele.php?a=1' class='lien_menu_gauche' target='_self'>Cr�er un mod�le</a></li>
			<li class='menu_gauche'><a href='modeles/modele.php' class='lien_menu_gauche' target='_self'>Modifier un mod�le</a></li>
			<li class='menu_gauche'><a href='modeles/suppr_modele.php' class='lien_menu_gauche' target='_self'>Supprimer un mod�le</a></li>
			<li class='menu_gauche' style='margin-top:30px;'><a href='signature.php' class='lien_menu_gauche' target='_self'>Modifier votre signature</a></li>
			<li class='menu_gauche' style='margin-top:30px;'><a href='absence.php' class='lien_menu_gauche' target='_self'>Absence : r�pondeur</a></li>
		</ul>
	</div>
	<div class='corps'>
		<?php
			titre_page_icone("Messagerie interne", "email_32x32_fond.png", 10, "L");
			
			if(isset($_GET["sent"]) && $_GET["sent"]==1)
				message("Message envoy�", $__SUCCES);

			if(isset($_GET["form_adresse_succes"]) && $_GET["form_adresse_succes"]==1)
				message("Adresse �lectronique mise � jour et identifiants envoy�s avec succ�s.", $__SUCCES);
				
			if(isset($_GET["form_adresse_candidat_inconnu"]) && $_GET["form_adresse_candidat_inconnu"]==1)
				message("Candidat(e) inconnu(e) : proc�dure d'enregistrement envoy�e par courriel.", $__SUCCES);

         if(isset($_GET["form_dev_succes"]) && $_GET["form_dev_succes"]==1)
            message("Formulaire de d�verrouillage valid� - Message envoy�.", $__SUCCES);

			if(!is_dir("$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/"))
			{
				if(FALSE==mkdir("$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$current_dossier", 0770, TRUE))
				{
					mail($__EMAIL_ADMIN, "[Pr�candidatures] - Erreur de cr�ation de r�pertoire", "R�pertoire : $__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$current_dossier\n\nUtilisateur : $_SESSION[auth_prenom] $_SESSION[auth_nom]");

					die("Erreur syst�me lors de la cr�ation de votre r�pertoire personnel. Un message a �t� envoy� � l'administrateur.");
				}
			}

			$contenu_repertoire=scandir("$GLOBALS[__GESTION_MSG_STOCKAGE_DIR_ABS]/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$current_dossier", 1);

         if($contenu_repertoire===FALSE)
         {
            mail($__EMAIL_ADMIN, "$GLOBALS[__ERREUR_SUJET] - Erreur de lecture d'un r�pertoire", "R�pertoire : $GLOBALS[__GESTION_MSG_STOCKAGE_DIR_ABS]/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$current_dossier\n\nUtilisateur : $_SESSION[auth_prenom] $_SESSION[auth_nom]");
            die("Erreur de lecture d'un r�pertoire. Un message a �t� envoy� � l'administrateur.");
         }

			if(FALSE!==($key=array_search(".", $contenu_repertoire)))
				unset($contenu_repertoire[$key]);

			if(FALSE!==($key=array_search("..", $contenu_repertoire)))
				unset($contenu_repertoire[$key]);

			if(FALSE!==($key=array_search("index.php", $contenu_repertoire)))
				unset($contenu_repertoire[$key]);

         rsort($contenu_repertoire);

			// TRI DE LA LISTE DES FICHIERS
         // TODO : � �crire

/*			
			switch($methode_tri)
			{
				case	"date_asc"		:	// Tri de la liste des fichiers par date croissante (revient � trier par nom cr.)
												sort($contenu_repertoire);
												break;

				case	"date_desc"		:	// Tri de la liste des fichiers par date d�croissante (revient � trier par nom d�cr.)
												rsort($contenu_repertoire);
												break;

				case	"exp_asc"		:	// Tri par exp�diteur croissant
												break;

				case	"exp_desc"		:	// Tri par exp�diteur d�croissant
												break;

				case	"sujet_asc"		:	// Tri par sujet croissant
												break;

				case	"sujet_desc"	:	// Tri par sujet d�croissant
												break;
			}
*/

			$nb_msg=$nb_fichiers=count($contenu_repertoire);

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
				if(!empty($checked))
					$lien_select_deselect="<a href='$php_self?sa=0' class='lien_menu_gauche'>Tout d�s�lectionner</a>";
				else
					$lien_select_deselect="<a href='$php_self?sa=1' class='lien_menu_gauche'>Tout s�lectionner</a>";

				// Lien "Vider la corbeille"
				$lien_corbeille=($current_dossier==$__MSG_TRASH) ? "&nbsp;|&nbsp;<a href='$php_self?trash=1' class='lien_menu_gauche'>Vider la corbeille</a>" : "";
			}
			else
				$lien_select_deselect=$lien_corbeille="";

			// changement de nom pour la colonne "Exp�diteur" si le dossier est "Envoy�s"
			$col_name=($current_dossier==$__MSG_SENT) ? "Destinataire" : "Exp�diteur";


			// *****************************************************************
			// Options / fl�ches de tri
			$icon_date_down="1downarrow_green_16x16_menu2.png";
			$icon_date_up="1uparrow_green_16x16_menu2.png";
			$icon_exp_down="1downarrow_green_16x16_menu2.png";
			$icon_exp_up="1uparrow_green_16x16_menu2.png";
			$icon_sujet_down="1downarrow_green_16x16_menu2.png";
			$icon_sujet_up="1uparrow_green_16x16_menu2.png";

         // TODO : � terminer
			if(isset($_GET["tri"]) && ctype_digit($_GET["tri"]) && ($_GET["tri"]>=1 || $_GET["tri"]<=6))
			{
				switch($_GET["tri"])
				{
					case	1	:	$tri_messages="$_DBC_hist_date DESC";
									$icon_date_down="2downarrow_green_16x16_menu2.png";
									break;

					case	2	:	$tri_messages="$_DBC_hist_date ASC";
									$icon_date_up="2uparrow_green_16x16_menu2.png";
									break;

					case	3	:	$tri_messages="$_DBC_hist_type_evt DESC, $_DBC_hist_date DESC";
									$icon_exp_down="2downarrow_green_16x16_menu2.png";
									break;

					case	4	:	$tri_messages="$_DBC_hist_type_evt ASC, $_DBC_hist_date DESC";
									$icon_exp_up="2uparrow_green_16x16_menu2.png";
									break;

					case	5	:	$tri_messages="$_DBC_hist_type_evt ASC, $_DBC_hist_date DESC";
									$icon_sujet_up="2uparrow_green_16x16_menu2.png";
									break;

					case	6	:	$tri_messages="$_DBC_hist_type_evt ASC, $_DBC_hist_date DESC";
									$icon_sujet_up="2uparrow_green_16x16_menu2.png";
									break;
				}
			}
			else
			{
				$tri_messages="$_DBC_hist_date DESC";
				$icon_date_down="2downarrow_green_16x16_menu2.png";
			}
			// *****************************************************************

			print("<form action='$php_self' method='POST' name='form1'>
						<table class='encadre_messagerie' width='95%' align='center''>
						<tr>
							<td class='td-msg-titre fond_menu2' style='border-right:0px; padding:1px 2px 1px 2px;' colspan='2'>
								<font class='Texte_menu2'>
									<a href='$php_self' class='lien_menu_gauche'>Rafra�chir</a>$lien_corbeille
								</font>
							</td>
							<td class='td-msg-titre fond_menu2' style='vertical-align:middle; text-align:right; border-left:0px; padding:1px 2px 1px 2px;' colspan='3'>
								<font class='Texte_menu2'>
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

									$prev_txt
									$prev
									$next
									$next_txt
								</font>
							</td>
						</tr>
						<tr>
							<td class='td-msg-titre fond_page' style='border-right:0px; border-left:0px; height:10px;' colspan='5'></td>
						<tr>
							<td class='td-msg-titre fond_menu' style='padding:4px 2px 4px 2px;' colspan='5'>
								<font class='Texte_menu'>
									<b>$__MSG_DOSSIERS[$current_dossier]</b> ($nb_msg_texte)
								</font>
							</td>
						</tr>
						<tr>
							<td class='td-msg-menu fond_menu' style='padding:4px 2px 4px 2px;' width='10%'>
								<font class='Texte_menu'><b>Date</b></font>
							</td>
							<td class='td-msg-menu fond_menu' style='padding:4px 2px 4px 2px;' width='30%'>
								<font class='Texte_menu'><b>$col_name</b></font>
							</td>
							<td class='td-msg-menu fond_menu' style='padding:4px 2px 4px 2px;' width='55%' colspan='2'>
								<font class='Texte_menu'><b>Sujet</b></font>
							</td>
							<td class='td-msg-menu fond_menu' style='padding:4px 2px 4px 2px;' width='5%'></td>
						</tr>\n");
/*
			for($i=0; $i<$rows; $i++)
			{
				list($msg_id, $msg_sujet, $msg_exp_id, $msg_exp_nom, $msg_exp_prenom, $msg_read)=db_fetch_row($result, $i);
*/

			for($i=$limite_inf_msg; $i<$limite_sup_msg; $i++)
			{
				// TODO : ajouter tests de retour des fonctions
				// $fichier=$_SESSION["repertoire"] . "/" . $contenu_repertoire[$i];

				$fichier="$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$current_dossier/" . $contenu_repertoire[$i];
				$file_or_dir_name=$nom_fichier=$contenu_repertoire[$i];	

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

				// Identifiant du message = date
				// Format : AA(1 ou 2) MM JJ HH Mn SS �S(5)

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
					mail($__EMAIL_ADMIN, "[Pr�candidatures] - Erreur d'ouverture de mail", "Fichier : $fichier\n\nUtilisateur : $_SESSION[auth_prenom] $_SESSION[auth_nom]");

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
							<td class='td-msg' style='text-align:left; $style_bg' width='10%'>
								<font class='Texte' $style_bold>$date_txt</font>
							</td>
							<td class='td-msg' style='text-align:left; $style_bg' width='30%'>
								<font class='Texte' $style_bold>
									<a href='message.php?p=$crypt_params' class='lien_bleu_12'>$col_value</a>
									<!-- <a href='$php_self?p=$crypt_params' class='lien_bleu_12'>$col_value</a> -->
								</font>
							</td>\n");
							
				// Aper�u du message ?
				if($fichier_apercu==$fichier && isset($apercu_message) && trim($apercu_message)!="")
				{
               print("<td class='td-msg' width='13' style='text-align:center; $style_bg; border-width:1px 0px 1px 1px;'>
								<a href='$php_self' target='_self'><img src='$__ICON_DIR/moins_11x11.png' width='11' border='0' title='Apercu' desc='Apercu'></a>
							</td>
							<td class='td-msg' style='text-align:left; $style_bg; border-width:1px 1px 1px 0px;' width='55%' $style_bold>
								<a href='message.php?p=$crypt_params' class='lien_bleu_12' $style_bold>$msg_sujet</a>
								<!-- <a href='$php_self?p=$crypt_params' class='lien_bleu_12' $style_bold>$msg_sujet</a> -->
							</td>
							<td class='td-msg' width='5%' style='text-align:center; $style_bg'>
								<font class='Texte_menu'><input type='checkbox' name='selection[]' value='$file_or_dir_name' $checked>
							</td>
						</tr>
						<tr>
							<td class='td-msg' style='padding:4px 0px 4px 10px; white-space:normal; border-width:1px 0px 1px 1px; text-align:justify; background-color:#FFFFFF;' colspan='4'>
				           <font class='Texte_10'></i>".nl2br(parse_macros($apercu_message))."</i></font>
				         </td>
				         <td class='td-msg' style='border-width:1px 1px 1px 0px; background-color:#FFFFFF;'></td>
				      </tr>\n");
				}
				else
				{
				   print("<td class='td-msg' width='13' style='text-align:center; $style_bg; border-width:1px 0px 1px 1px;'>
								<a href='$php_self?p=$crypt_params' target='_self'><img src='$__ICON_DIR/plus_11x11.png' width='11' border='0' title='Apercu' desc='Apercu'></a>
							</td>
				         <td class='td-msg' style='text-align:left; $style_bg; border-width:1px 1px 1px 0px;' width='55%' $style_bold>
								<a href='message.php?p=$crypt_params' class='lien_bleu_12' $style_bold>$msg_sujet</a>
								<!-- <a href='$php_self?p=$crypt_params' class='lien_bleu_12' $style_bold>$msg_sujet</a> -->
							</td>
							<td class='td-msg' width='5%' style='text-align:center; $style_bg'>
								<font class='Texte_menu'><input type='checkbox' name='selection[]' value='$file_or_dir_name' $checked>
							</td>
						</tr>\n");
				}
			}

			print("</table>\n");
							
			// db_free_result($result);
			db_close($dbr);
		?>
	</div>
</div>

<?php
	pied_de_page();
?>
</body></html>
