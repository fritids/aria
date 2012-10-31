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
   // Messages syst�mes : traitement des actions possibles via le formulaire inclus dans le message
   // TODO : mettre �a ailleurs ?

   // Mise � jour du mail et renvoi des identifiants

   if((isset($_POST["Valider_form_adresse"]) || isset($_POST["Valider_form_adresse_x"])) && isset($_SESSION["niveau"]) && $_SESSION["niveau"]=="$__LVL_ADMIN")
   {
      if(array_key_exists("sel_candidat", $_POST) && ctype_digit($_POST["sel_candidat"]))
         $sel_candidat_id=$_POST["sel_candidat"];
      else
         $erreur_candidat_id=1;
         
      // Candidat inconnu : message automatique
      if(array_key_exists("mail_inconnu", $_POST))
         $email_candidat_pour_enregistrement=trim($_POST["mail_inconnu"]);

      // V�rification du format de l'adresse email (TODO : regexp � contr�ler/compl�ter et g�n�raliser
      if(array_key_exists("new_mail", $_POST) && $_POST["new_mail"]!="" && preg_match("/[[:alnum:]\.\-\_]@[[:alnum:]\-\_]+[[:alnum:]\-\_\.]*\.[[:alnum:]]+\$/", $_POST["new_mail"]))
         $new_email=strtolower($_POST["new_mail"]);
      else
         $erreur_new_email=1;

      if(isset($sel_candidat_id) && isset($new_email) && !isset($erreur_candidat_id) && !isset($erreur_new_email))
      {
         db_query($dbr,"UPDATE $_DB_candidat SET $_DBU_candidat_email='$new_email' WHERE $_DBU_candidat_id='$sel_candidat_id'");

         // Mise � jour des informations de session si le candidat trait� est le candidat actuel
         if(array_key_exists("tab_candidat", $_SESSION) && isset($_SESSION['candidat_id']) && $sel_candidat_id==$_SESSION['candidat_id'])
            $_SESSION['tab_candidat']['email']=$new_email;

         write_evt($dbr, $__EVT_ID_G_MAN, "[Assistance] - Nouveau courriel : $new_email", $sel_candidat_id, $sel_candidat_id);

         // Renvoi des identifiants
         // TODO : cr�er une fonction et des variables pour les messages ...

         $res_candidat=db_query($dbr,"SELECT $_DBC_candidat_civilite, $_DBC_candidat_nom, $_DBC_candidat_identifiant, $_DBC_candidat_code_acces
                                          FROM $_DB_candidat
                                         WHERE $_DBC_candidat_id='$sel_candidat_id'");

         $rows_candidat=db_num_rows($res_candidat);

         if($rows_candidat==1)
         {
            list($cand_civ, $cand_nom, $cand_identifiant, $cand_pass)=db_fetch_row($res_candidat,0);

            switch($cand_civ)
            {
               case "M" :      $civ_texte="M.";
                               break;

               case   "Mlle" : $civ_texte="Mlle";
                               break;

               case   "Mme"  : $civ_texte="Mme";
                               break;

               default       : $civ_texte="M.";
            }

            $headers = "MIME-Version: 1.0\r\nFrom: $__EMAIL_ADMIN\r\nReply-To: $_SESSION[auth_email]\r\nContent-Type: text/plain; charset=ISO-8859-15\r\nContent-transfer-encoding: 8bit\r\n\r\n";

            $corps_message="============================================================\nCeci est un message automatique, merci de ne pas y r�pondre.\n============================================================\n
Bonjour $civ_texte $cand_nom,\n

Suite � votre requ�te, votre adresse �lectronique a �t� modifi�e sur l'interface ARIA.

Les informations vous permettant d'acc�der � l'interface de pr�candidatures sont les suivantes:
- Adresse : $__URL_CANDIDAT
- Identifiant : ". stripslashes($cand_identifiant) . "
- Code Personnel : $cand_pass\n
Ces informations sont strictement confidentielles.\n
Conservez-les, car elles vous seront utiles pour suivre l'�volution de vos dossiers.\n\n
Cordialement,\n\n
--
$_SESSION[universite]";

            $ret=mail($new_email,"[$_SESSION[universite] - Pr�candidatures en ligne] - Vos identifiants", $corps_message, $headers);

            if($ret!=true)
            {
               mail($__EMAIL_ADMIN,"[$_SESSION[universite] - ERREUR Pr�candidatures] - Erreur d'envoi de courriel", "Erreur lors du renvoi manuel des identifiants\n\nFiche : " . $_SESSION['tab_candidat']['nom'] . " " . $_SESSION['tab_candidat']['prenom'] . "\nID :  $candidat_id\nEmail : " . $_SESSION['tab_candidat']['email']);

               $erreur_envoi_mail=-1;
            }
            else // Succ�s de la proc�dure : on met le message dans le dossier "Trait�s"
            {
               if(!is_dir("$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$__MSG_TRAITES"))
               {
                  if(FALSE==mkdir("$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$__MSG_TRAITES", 0770, TRUE))
                  {
                     mail($__EMAIL_ADMIN, "[Pr�candidatures] - Erreur de cr�ation de r�pertoire", "R�pertoire : $__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$__MSG_TRAITES\n\nUtilisateur : $_SESSION[auth_prenom] $_SESSION[auth_nom]");
                     die("Erreur syst�me lors de la cr�ation du dossier destination. Un message a �t� envoy� � l'administrateur.");
                  }
               }

               if(is_file($_SESSION["current_message_filename"]) && is_dir("$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$__MSG_TRAITES/"))
               {
                  $new_name=basename($_SESSION["current_message_filename"]);
                  rename($_SESSION["current_message_filename"], "$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$__MSG_TRAITES/$new_name");

                  db_free_result($res_candidat);
                  db_close($dbr);
                  header("Location:index.php?form_adresse_succes=1");
                  exit();
               }
               else
                  mail($__EMAIL_ADMIN, "[Pr�candidatures] - Erreur de d�placement de message", "Source : $_SESSION[current_message_filename]\nDestination : $__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$__MSG_TRAITES/$new_name\n\nUtilisateur : $_SESSION[auth_prenom] $_SESSION[auth_nom]");
            }
         }

         db_free_result($res_candidat);

         write_evt($dbr, $__EVT_ID_G_MAN, "[Assistance] - Envoi manuel des identifiants", $sel_candidat_id, $sel_candidat_id);
      }
      elseif(isset($email_candidat_pour_enregistrement) && !empty($email_candidat_pour_enregistrement))
      {
         
         $headers = "MIME-Version: 1.0\r\nFrom: $__EMAIL_ADMIN\r\nReply-To: $_SESSION[auth_email]\r\nContent-Type: text/plain; charset=ISO-8859-15\r\nContent-transfer-encoding: 8bit\r\n\r\n";

         $corps_message="============================================================\nCeci est un message automatique, merci de ne pas y r�pondre.\n============================================================\n
Bonjour,\n

Nous ne pouvons malheureusement pas valider votre demande de changement d'adresse �lectronique. En effet, nous n'avons pu vous identifier dans notre base de donn�es � partir des renseignements que vous nous avez fournis.

Nous vous conseillons donc de compl�ter le formulaire d'enregistrement accessible depuis la page d'accueil � cette adresse (apr�s avoir valid� les conditions d'utilisation) : 

$__URL_CANDIDAT 

Vous obtiendrez alors de nouveaux identifiants.

Cordialement,\n\n
--
$_SESSION[universite]";

         $ret=mail($email_candidat_pour_enregistrement, "[$_SESSION[universite] - Pr�candidatures en ligne] - Votre demande de changement d'adresse", $corps_message, $headers);

         if($ret!=true)
         {
            mail($__EMAIL_ADMIN,"[$_SESSION[universite] - ERREUR Pr�candidatures] - Erreur d'envoi de courriel", "Erreur lors du renvoi manuel des identifiants ($email_candidat_pour_enregistrement)");

            $erreur_envoi_mail=-1;
         }
         else // Succ�s de la proc�dure : on met le message dans le dossier "Trait�s"
         {
            if(!is_dir("$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$__MSG_TRAITES"))
            {
               if(FALSE==mkdir("$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$__MSG_TRAITES", 0770, TRUE))
               {
                  mail($__EMAIL_ADMIN, "[Pr�candidatures] - Erreur de cr�ation de r�pertoire", "R�pertoire : $__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$__MSG_TRAITES\n\nUtilisateur : $_SESSION[auth_prenom] $_SESSION[auth_nom]");
                  die("Erreur syst�me lors de la cr�ation du dossier destination. Un message a �t� envoy� � l'administrateur.");
               }
            }

            if(is_file($_SESSION["current_message_filename"]) && is_dir("$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$__MSG_TRAITES/"))
            {
               $new_name=basename($_SESSION["current_message_filename"]);
               rename($_SESSION["current_message_filename"], "$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$__MSG_TRAITES/$new_name");

               db_free_result($res_candidat);
               db_close($dbr);
               header("Location:index.php?form_adresse_candidat_inconnu=1");
               exit();
            }
            else
               mail($__EMAIL_ADMIN, "[Pr�candidatures] - Erreur de d�placement de message", "Source : $_SESSION[current_message_filename]\nDestination : $__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$__MSG_TRAITES/$new_name\n\nUtilisateur : $_SESSION[auth_prenom] $_SESSION[auth_nom]");
         }
      }
   }

   // D�verrouillage

   if((isset($_POST["Valider_form_deverrouillage"]) || isset($_POST["Valider_form_deverrouillage_x"])) && isset($_POST["candidat_id"]) && isset($_SESSION["niveau"]) && $_SESSION["niveau"]=="$__LVL_ADMIN")
   {
      $form_candidat_id=$_POST["candidat_id"];

      // Message eventuel de l'admin
      if(array_key_exists("message", $_POST) && trim($_POST["message"])!="")
         $message=trim($_POST["message"]);
      else
         $message="";

      // Tableau contenant les candidatures � d�verrouiller
      if(isset($_POST["rien"]) && $_POST["rien"]==1)
      {
         $intro="Suite � votre requ�te, aucune formation n'a �t� d�verrouill�e.\n";
      }
      elseif(array_key_exists("cand_id", $_POST) && count($_POST["cand_id"]))
      {
         $dev_cand_array=$_POST["cand_id"];

         // Pour chaque candidature s�lectionn�e, on r�cup�re le nom (pour le message) et la nouvelle date de verrouillage.
/*
         foreach($dev_cand_array as $candidature_id)
         {
            if(array_key_exists("spec_nom", $_POST) && array_key_exists("$candidature_id", $_POST["spec_nom"]))
               print("DBG : $candidature_id : ".stripslashes($_POST["spec_nom"]["$candidature_id"])."<br>\n");

            if(array_key_exists("jour_verr", $_POST) && array_key_exists("$candidature_id", $_POST["jour_verr"]))
               print("DBG : jour : ".$_POST["jour_verr"]["$candidature_id"]."<br>\n");

            if(array_key_exists("mois_verr", $_POST) && array_key_exists("$candidature_id", $_POST["mois_verr"]))
               print("DBG : mois : ".$_POST["mois_verr"]["$candidature_id"]."<br>\n");

            if(array_key_exists("annee_verr", $_POST) && array_key_exists("$candidature_id", $_POST["annee_verr"]))
               print("DBG : ann�e : ".$_POST["annee_verr"]["$candidature_id"]."<br>\n");

         }
*/
         $formations_deverrouillees="";
         $formations_traitees=array();

         foreach($dev_cand_array as $candidature_id)
         {
            if(array_key_exists("spec_nom", $_POST) && array_key_exists("$candidature_id", $_POST["spec_nom"])
               && array_key_exists("jour_verr", $_POST) && array_key_exists("$candidature_id", $_POST["jour_verr"])
               && array_key_exists("mois_verr", $_POST) && array_key_exists("$candidature_id", $_POST["mois_verr"])
               && array_key_exists("annee_verr", $_POST) && array_key_exists("$candidature_id", $_POST["annee_verr"]))
            {
               // Nouvelle date de verrouillage
               $new_lockdate=MakeTime("2", "0", "0", $_POST["mois_verr"]["$candidature_id"], $_POST["jour_verr"]["$candidature_id"], $_POST["annee_verr"]["$candidature_id"]);

               // Si la "nouvelle" date est d�j� pass�e ...
               if($new_lockdate<time())
                  $new_lockdate=MakeTime("2", "0", "0", date("m"), date("d")+1, date("Y"));

               // Cas des candidatures � choix multiples : on d�verrouille tout le groupe

               // A cause des candidatures � choix multiples, on risque de d�verrouiller plusieurs fois la m�me formation
               // => �vit� � l'aide du tableau $formations_traitees

               $formations_groupes="";

               if(!array_key_exists($candidature_id, $formations_traitees))
               {
                  $res_candidature=db_query($dbr, "SELECT $_DBC_cand_id,$_DBC_annees_annee,$_DBC_specs_nom,$_DBC_propspec_finalite
                                                      FROM $_DB_cand, $_DB_propspec, $_DB_specs, $_DB_annees
                                                   WHERE $_DBC_cand_propspec_id=$_DBC_propspec_id
                                                   AND $_DBC_propspec_annee=$_DBC_annees_id
                                                   AND $_DBC_propspec_id_spec=$_DBC_specs_id
                                                   AND $_DBC_cand_candidat_id='$form_candidat_id'
                                                   AND $_DBC_cand_groupe_spec IN (SELECT $_DBC_cand_groupe_spec FROM $_DB_cand WHERE $_DBC_cand_id='$candidature_id')
                                                   AND $_DBC_cand_groupe_spec!='-1'
                                                   AND $_DBC_cand_periode='$__PERIODE'
                                                   AND $_DBC_cand_id!='$candidature_id'
                                                   AND $_DBC_propspec_comp_id IN (SELECT $_DBC_propspec_comp_id FROM $_DB_cand,$_DB_propspec
                                                                                    WHERE $_DBC_cand_propspec_id=$_DBC_propspec_id
                                                                                    AND $_DBC_cand_id='$candidature_id')");

                  if($nb_cand_multiples=db_num_rows($res_candidature))
                  {
                     for($n=0; $n<$nb_cand_multiples; $n++)
                     {
                        list($mult_cand_id, $nom_annee, $nom_spec, $nom_finalite)=db_fetch_row($res_candidature, $n);

                        $formations_traitees["$mult_cand_id"]=1;

                        db_query($dbr,"UPDATE $_DB_cand SET $_DBU_cand_lock='0',
                                                            $_DBU_cand_lockdate='$new_lockdate'
                                    WHERE $_DBC_cand_id='$mult_cand_id'
                                    AND $_DBC_cand_candidat_id='$form_candidat_id'");

                        $nom_formation=$nom_annee=="" ? "$nom_spec" : "$nom_annee $nom_spec";
                        $nom_formation.=$tab_finalite["$nom_finalite"]=="" ? "" : " $tab_finalite[$nom_finalite]";

                        write_evt($dbr, $__EVT_ID_G_MAN, "[Assistance] - D�verrouillage manuel", $form_candidat_id, $mult_cand_id);

                        $formations_groupes.="<tr>
                                                <td class='td-gauche fond_menu'><font class='Texte'>$nom_formation</font></td>
                                                <td class='td-droite fond_menu'><font class='Texte'>(identique)</font></td>
                                              </tr>\n";
                     }
                  }
                  else
                     db_query($dbr,"UPDATE $_DB_cand SET $_DBU_cand_lock='0',
                                                         $_DBU_cand_lockdate='$new_lockdate'
                                    WHERE $_DBC_cand_id='$candidature_id'
                                    AND $_DBC_cand_candidat_id='$form_candidat_id'");

                  db_free_result($res_candidature);
               }

               $formations_deverrouillees.="<tr>
                                             <td class='td-gauche fond_menu'><font class='Texte'>".$_POST["spec_nom"]["$candidature_id"]."</font></td>
                                             <td class='td-droite fond_menu'><font class='Texte'>".date_fr("j F Y", $new_lockdate)."</font></td>
                                            </tr>
                                            $formations_groupes\n";
            }
         }
      }
/*
      else
         $erreur_dev_cand=1;
*/
      if(isset($_POST["candidat_id"]) && ((isset($formations_deverrouillees) && $formations_deverrouillees!="") || (isset($_POST["rien"]) && $_POST["rien"]==1)) && !isset($erreur_dev_cand))
      {
         if(isset($formations_deverrouillees) && $formations_deverrouillees!="")
         {
            $intro="Suite � votre requ�te, la ou les formations suivantes ont �t� d�verrouill�es :\n";

            $formations_deverrouillees="<table>
                                       <tr>
                                          <td class='td-gauche fond_menu2'><font class='Texte'><strong>Formation</strong></font></td>
                                          <td class='td-droite fond_menu2'><font class='Texte'><strong>Nouvelle date de verrouillage</strong></font></td>
                                       </tr>".$formations_deverrouillees."</table>";

            // Nettoyage pour un bon affichage
            $formations_deverrouillees=preg_replace("/[ ]+/", " ", preg_replace("/[\r]*[\n]+/","", $formations_deverrouillees));
         }
         else
            $formations_deverrouillees="";

         $res_candidat=db_query($dbr,"SELECT $_DBC_candidat_civilite, $_DBC_candidat_nom, $_DBC_candidat_prenom, $_DBC_candidat_email
                                          FROM $_DB_candidat
                                         WHERE $_DBC_candidat_id='$form_candidat_id'");

         $rows_candidat=db_num_rows($res_candidat);

         if($rows_candidat==1)
         {
            list($cand_civ, $cand_nom, $cand_prenom, $cand_email)=db_fetch_row($res_candidat,0);

            switch($cand_civ)
            {
               case "M" :      $civ_texte="M.";
                               break;

               case   "Mlle" : $civ_texte="Mlle";
                               break;

               case   "Mme" :  $civ_texte="Mme";
                               break;

               default      :  $civ_texte="M.";
            }

            $headers = "MIME-Version: 1.0\r\nFrom: $__EMAIL_ADMIN\r\nReply-To: $_SESSION[auth_email]\r\nContent-Type: text/plain; charset=ISO-8859-15\r\nContent-transfer-encoding: 8bit\r\n\r\n";

            $corps_message="Bonjour $civ_texte $cand_nom,\n
$intro
$formations_deverrouillees
$message";

            $array_dest=array("0" => array("id"    => $form_candidat_id,
                                           "civ"   => $civ_texte,
                                           "nom"    => $cand_nom,
                                           "prenom"=> $cand_prenom,
                                           "email"   => $cand_email,
                                           "dest_type" => "candidat"));

            $sujet="[Assistance] - Demande de d�verrouillage";
            $msg_dest="$cand_prenom $cand_nom";

            write_msg_2($dbr, array("id" => "0", "nom" => "Syst�me", "prenom" => "", "src_type" => "gestion", "composante" => "", "universite" => "$__SIGNATURE_COURRIELS"),
                        $array_dest, $sujet, $corps_message);

            // write_msg($dbr, array("id" => $_SESSION["auth_id"], "nom" => $_SESSION["auth_nom"], "prenom" => $_SESSION["auth_prenom"]), $array_dest, $sujet, $corps_message, $msg_dest);

            // Fin de la proc�dure : on met le message dans le dossier "Trait�s"

            if(!is_dir("$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$__MSG_TRAITES"))
            {
               if(FALSE==mkdir("$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$__MSG_TRAITES", 0770, TRUE))
               {
                  mail($__EMAIL_ADMIN, "[Pr�candidatures] - Erreur de cr�ation de r�pertoire", "R�pertoire : $__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$__MSG_TRAITES\n\nUtilisateur : $_SESSION[auth_prenom] $_SESSION[auth_nom]");
                  die("Erreur syst�me lors de la cr�ation du dossier destination. Un message a �t� envoy� � l'administrateur.");
               }
            }

            if(is_file($_SESSION["current_message_filename"]) && is_dir("$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$__MSG_TRAITES/"))
            {
               $new_name=basename($_SESSION["current_message_filename"]);
               rename($_SESSION["current_message_filename"], "$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$__MSG_TRAITES/$new_name");

               db_free_result($res_candidat);
               db_close($dbr);
               header("Location:index.php?form_dev_succes=1");
               exit();
            }
            else
               mail($__EMAIL_ADMIN, "[Pr�candidatures] - Erreur de d�placement de message", "Source : $_SESSION[current_message_filename]\nDestination : $__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$__MSG_TRAITES/$new_name\n\nUtilisateur : $_SESSION[auth_prenom] $_SESSION[auth_nom]");
         }

         db_free_result($res_candidat);
      }
   }
?>