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

   if(!isset($_SESSION["current_dossier"]))
   {
      header("Location:index.php");
      exit();
   }
   else
      $current_dossier=$_SESSION["current_dossier"];

   unset($_SESSION["current_corps"]);

   $dbr=db_connect();

   if(isset($_GET["p"]) && -1!=($params=get_params($_GET['p']))) // identifiant du message en param�tre crypt�
   {
      if(isset($params["dir"]) && $params["dir"]==1)
         $flag_pj=1;

      if(isset($params["msg"]))
      {
         $_SESSION["current_message_filename"]=$fichier=$params["msg"];

         // On v�rifie que le message existe et qu'il appartient bien � l'utilisateur
         // $_SESSION["msg_fichier"]="$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$_SESSION[current_dossier]/$_SESSION[msg]";

         // Test d'ouverture du fichier
         if(($array_file=@file("$fichier"))==FALSE)
         {
            // On tente en modifiant la fin du nom du fichier (flag read)
            if(substr($fichier, -1)=="0")
               $fichier=preg_replace("/\.0$/", ".1", $fichier);
            else
               $fichier=preg_replace("/\.1$/", ".0", $fichier);

            // $_SESSION["msg_fichier"]="$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$_SESSION[current_dossier]/$new_file";

            if(($array_file=@file("$fichier"))==FALSE)
               $location="index.php";
/*
            else
               $_SESSION["msg"]=$new_file;
*/
         }

         if(!isset($location))
         {
            // Nom du fichier sans le r�pertoire
            $complete_path=explode("/", $fichier);
            $rang_fichier=count($complete_path)-1;
   
            // Nom du fichier (sans le r�pertoire)
            $_SESSION["msg"]=$complete_path[$rang_fichier];
   
            // R�pertoire
            unset($complete_path[$rang_fichier]);
            $_SESSION["msg_dir"]=implode("/", $complete_path);

            if(strlen($_SESSION["msg"])==18) // Ann�e sur un caract�re (16 pour l'identifiant + ".0" ou ".1" pour le flag "lu / non lu")
            {
               $date_offset=0;
               $annee_len=1;
               $leading_zero="0";
               $_SESSION["msg_id"]=$msg_id=substr($_SESSION["msg"], 0, 16);
               $_SESSION["msg_read"]=substr($_SESSION["msg"], 17, 1);
            }
            else // Ann�e sur 2 caract�res (chaine : 19 caract�res)
            {
               $date_offset=1;
               $annee_len=2;
               $leading_zero="";
               $_SESSION["msg_id"]=$msg_id=substr($_SESSION["msg"], 0, 17);
               $_SESSION["msg_read"]=substr($_SESSION["msg"], 18, 1);
            }

            $_SESSION['msg_exp_id']=trim($array_file["0"]);
            $_SESSION['msg_exp']=trim($array_file["1"]);
            $_SESSION['msg_to_id']=trim($array_file["2"]);
            $_SESSION['msg_to']=trim($array_file["3"]);
            $_SESSION['msg_sujet']=stripslashes(trim($array_file["4"]));

            $_SESSION['msg_message']=array_slice($array_file, 5);
            $_SESSION['msg_message_txt']=stripslashes(implode($_SESSION['msg_message']));
         }
      }
      else
         $location="index.php";
   }
   elseif(!isset($_SESSION["msg"]) || !isset($_SESSION["msg_id"]) || !isset($_SESSION['msg_sujet']) || !isset($_SESSION['msg_exp_id'])
            || !isset($_SESSION['msg_exp']) || !isset($_SESSION['msg_message']) || !isset($_SESSION["msg_message_txt"]))
      $location="index.php";

   // Transfert d'un message � un autre utilisateur
   if((isset($_POST["transfert"]) || isset($_POST["transfert_x"])) && isset($_POST["dest_transfert"]) && $_POST["dest_transfert"]!=""
       && isset($_SESSION['msg_exp_id']))
   {
      $destinataire_id=$_POST["dest_transfert"];

      $res_from=db_query($dbr,"SELECT $_DBC_candidat_nom, $_DBC_candidat_prenom
                               FROM $_DB_candidat
                               WHERE $_DBC_candidat_id='$_SESSION[msg_exp_id]'");

      if(db_num_rows($res_from))
      {
         $array_from=array("id"    => $_SESSION['msg_exp_id']);

         list($array_from["nom"], $array_from["prenom"])=db_fetch_row($res_from, 0);

         $array_dest=array("0" => array("id"    => $destinataire_id));

         // /!\ Fonction diff�rente de write_msg
         // copy_msg($dbr, $array_from, $array_dest, $sujet, $corps, "", $__FLAG_MSG_NO_NOTIFICATION);
         $retour_copie=copy_msg("", $_SESSION["current_dossier"], $_SESSION["msg"], $destinataire_id);
      }

      db_free_result($res_from);
   }

   // Code traitant les messages syst�mes (assistance aux utilisateurs)
   include "messages_systeme.php";

   if(isset($location)) // param�tre manquant : retour � l'index
   {
      db_close($dbr);
      header("Location:$location");
      exit();
   }

   // EN-TETE
   en_tete_gestion();

   // MENU SUPERIEUR
   menu_sup_gestion();
?>

<div class='main'>
   <div class='menu_gauche'>
      <ul class='menu_gauche'>
         <?php
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
         titre_page_icone("Messagerie interne : message de $_SESSION[msg_exp]", "email_32x32_fond.png", 15, "L");
   
         if(isset($retour_copie) && $retour_copie==1)
            message("Message transf�r� avec succ�s !", $__SUCCES);

         if(isset($erreur_candidat_id))
            message("Erreur : vous devez s�lectionner un candidat avant de valider.", $__ERREUR);

         if(isset($erreur_new_email))
            message("Erreur : nouvelle adresse �lectronique invalide.", $__ERREUR);

         if(isset($erreur_envoi_mail))
            message("Erreur lors de l'envoi des identifiants par courriel.", $__ERREUR);

         $date_today=date("ymd") . "00000000000"; // on s'aligne sur le format des identifiants

         // Identifiant du message = date
         // Format : AA(1 ou 2) MM JJ HH Mn SS �S(5)

         if(strlen($_SESSION["msg_id"])==16) // Ann�e sur un caract�re
         {
            $date_offset=0;
            $annee_len=1;
            $leading_zero="0";
         }
         else
         {
            $date_offset=1;
            $annee_len=2;
            $leading_zero="";
         }

         // Flag lu/non lu
         if(!$_SESSION["msg_read"])
         {
            $_SESSION["msg"]=$_SESSION["msg_id"] . ".1";

            // Attention : il faut bien tenir compte du r�pertoire
            rename("$fichier", "$_SESSION[msg_dir]/$_SESSION[msg]");

            $_SESSION["current_message_filename"]=$fichier="$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$_SESSION[current_dossier]/$_SESSION[msg]";
            $_SESSION["msg_read"]=1;
         }

         // On convertit la date en temps Unix : plus simple ensuite pour l'affichage et les conversions
         $unix_date=mktime(substr($_SESSION["msg_id"], 5+$date_offset, 2), substr($_SESSION["msg_id"], 7+$date_offset, 2), substr($_SESSION["msg_id"], 9+$date_offset, 2),
                                          substr($_SESSION["msg_id"], 1+$date_offset, 2), substr($_SESSION["msg_id"], 3+$date_offset, 2), $leading_zero . substr($_SESSION["msg_id"], 0, $annee_len));

         $date_txt=ucfirst(date_fr("l d F Y - H\hi", $unix_date));


         $crypt_params_suppr=crypt_params("msg=$_SESSION[msg]");
   
         // Les liens changent en fonction du dossier
         if($_SESSION["current_dossier"]==$__MSG_SENT)
         {
            if(db_num_rows(db_query($dbr,"SELECT * FROM $_DB_candidat WHERE $_DBC_candidat_id='$_SESSION[msg_to_id]'")))
            {
               $crypt_params_to=crypt_params("to=$_SESSION[msg_to_id]&r=1");
               $lien_repondre="<a href='compose.php?p=$crypt_params_to' class='lien_bleu_12'>Envoyer un message � $_SESSION[msg_to]</a>";
               $lien_fiche="<a href='$__GESTION_DIR/edit_candidature.php?fiche=$_SESSION[msg_to_id]&dco=$_SESSION[comp_id]' class='lien_bleu_12'>Acc�s direct � cette fiche</a>";
            }
            else
               $lien_repondre=$crypt_params_to=$lien_fiche="";
         }
         else
         {
            if(db_num_rows(db_query($dbr,"SELECT * FROM $_DB_candidat WHERE $_DBC_candidat_id='$_SESSION[msg_exp_id]'")))
            {
               $crypt_params_to=crypt_params("to=$_SESSION[msg_exp_id]&r=1");
               $lien_repondre="<a href='compose.php?p=$crypt_params_to' class='lien_bleu_12'>R�pondre</a>";
               $lien_fiche="<a href='$__GESTION_DIR/edit_candidature.php?fiche=$_SESSION[msg_exp_id]&dco=$_SESSION[comp_id]' class='lien_bleu_12'>Acc�s direct � cette fiche</a>";
            }
            else
               $lien_repondre=$crypt_params_to=$lien_fiche="";
         }

         // Liste pour un transfert potentiel du message
         $res_transfert=db_query($dbr,"SELECT $_DBC_acces_id, $_DBC_acces_nom, $_DBC_acces_prenom, $_DBC_acces_niveau, $_DBC_composantes_id, $_DBC_composantes_nom
                                          FROM $_DB_acces, $_DB_composantes
                                       WHERE $_DBC_acces_composante_id=$_DBC_composantes_id
                                       AND $_DBC_acces_niveau!='$GLOBALS[__LVL_DESACTIVE]'
                                       AND $_DBC_acces_id!='0'
                                       AND ($_DBC_acces_composante_id='$_SESSION[comp_id]'
                                            OR $_DBC_acces_id IN (SELECT distinct($_DBC_acces_id) FROM $_DB_acces 
                                                                     WHERE $_DBC_acces_composante_id IN (SELECT distinct($_DBC_acces_comp_composante_id) FROM $_DB_acces_comp 
                                                                                                         WHERE $_DBC_acces_comp_acces_id='$_SESSION[auth_id]')))                                                          
                                          ORDER BY $_DBC_composantes_id, $_DBC_acces_niveau DESC, $_DBC_acces_nom, $_DBC_acces_prenom");

         $rows_transfert=db_num_rows($res_transfert);

         $old_niveau=$old_composante="--";

         if($rows_transfert)
         {
            $liste_transfert="<select name='dest_transfert'>\n<option value=''></option>\n";

            for($i=0; $i<$rows_transfert; $i++)
            {
               list($acces_id, $acces_nom, $acces_prenom, $acces_niveau, $composante_id, $composante_nom)=db_fetch_row($res_transfert, $i);

               if($old_composante!=$composante_id)
               {
                  if($i)
                     $liste_transfert.="<option value=''></option>\n</optgroup>";

                  $liste_transfert.="<optgroup label='".htmlspecialchars(stripslashes($composante_nom), ENT_QUOTES)."'>\n";

                  $old_composante=$composante_id;

                  $old_niveau="--";
               }

               if($old_niveau!=$acces_niveau)
               {
                  if($i)
                     $liste_transfert.="</optgroup>\n<option value='' disabled></option>\n";

                  $niveau_txt=htmlspecialchars($tab_niveau[$acces_niveau], ENT_QUOTES);

                  $liste_transfert.="<optgroup label='- $niveau_txt'>\n";

                  $old_niveau=$acces_niveau;
               }

               $liste_transfert.="<option label='$acces_nom $acces_prenom' value='$acces_id'>$acces_nom $acces_prenom</option>\n";
            }

            $liste_transfert.="</optgroup>\n</select>\n";

            $menu_transfert="$liste_transfert&nbsp;&nbsp;<input type='submit' name='transfert' value='Transf�rer'>";
         }
         else
            $menu_transfert="";

         db_free_result($res_transfert);

         if($lien_fiche!="" && $lien_repondre!="")
            $liens="$lien_repondre&nbsp;|&nbsp;$lien_fiche";
         elseif($lien_fiche!="")
            $liens=$lien_fiche;
         else
            $liens=$lien_repondre;

         print("<form action='$php_self' method='POST' name='form1'>
                  <table class='encadre_messagerie' width='95%' align='center' style='margin-bottom:20px;'>
                  <tr>
                     <td colspan='2' class='td-msg-titre fond_menu2' style='padding:4px 2px 4px 2px;'>
                        <a href='index.php' class='lien_menu_gauche' style='font-size:14px;'><b>Dossier : $__MSG_DOSSIERS[$current_dossier]</b>
                     </td>
                  </tr>
                  <tr>
                     <td class='td-msg-tools fond_gris_B' style='text-align:left; vertical-align:top;'>
                        <font class='Texte_menu'>$liens</font>
                     </td>
                     <td class='td-msg-tools fond_gris_B' style='text-align:right; vertical-align:top; padding-right:20px;'>
                        <font class='Texte_menu'>
                           <a href='suppr_msg.php?p=$crypt_params_suppr' class='lien_bleu_12'>Supprimer</a>
                        </font>
                     </td>
                  </tr>
                  <tr>
                     <td colspan='2' class='td-msg-menu fond_menu' style='white-space:normal; padding:4px 2px 4px 2px;'>
                        <font class='Texte_menu'>
                           <b>Date</b> : $date_txt</b>
                        </font>
                     </td>
                  </tr>
                  <tr>
                     <td colspan='2' class='td-msg-menu fond_menu' style='white-space:normal; padding:4px 2px 4px 2px;'>
                        <font class='Texte_menu'>
                           <b>Sujet :</b> $_SESSION[msg_sujet]
                        </font>
                     </td>
                  </tr>
                  <tr>
                     <td colspan='2' class='td-msg-menu fond_menu' style='vertical-align:top; white-space:normal; padding:4px 2px 4px 2px;'>
                        <font class='Texte_menu'>
                           <b>Transf�rer ce message � : </b> $menu_transfert
                        </font>
                     </td>
                  </tr>
                  <tr>
                     <td class='td-msg-titre fond_page' style='border-right:0px; border-left:0px; height:10px;' colspan='2'></td>
                  </tr>
                  <tr>
                     <td colspan='2' class='td-msg fond_blanc' style='white-space:normal; padding-bottom:20px;vertical-align:top;'>
                        <font class='Texte'><br>");

         // Pi�ces jointes ?
         if(isset($flag_pj) && $flag_pj==1 && is_dir("$_SESSION[msg_dir]/files"))
         {
            $array_pj=scandir("$_SESSION[msg_dir]/files");
            // 4 �l�ments � ne pas inclure dans la recherche : ".", "..", le message et "index.php"

            if(FALSE!==($key=array_search("$_SESSION[msg]", $array_pj)))
               unset($array_pj[$key]);

            if(FALSE!==($key=array_search(".", $array_pj)))
               unset($array_pj[$key]);

            if(FALSE!==($key=array_search("..", $array_pj)))
               unset($array_pj[$key]);

            if(FALSE!==($key=array_search("index.php", $array_pj)))
               unset($array_pj[$key]);
            // **************** //

            if(count($array_pj))
               print("Pi�ce(s) jointe(s) : <br>\n");

            foreach($array_pj as $pj_name)
            {
               $crypt_params_pj=crypt_params("pj=$pj_name");
               print("- <a href='view.php?p=$crypt_params_pj' class='lien_bleu_12' target='_blank'>$pj_name</a><br>\n");
            }
         }

         // Corps du message, �ventuellement apr�s les pi�ces jointes
         echo nl2br(parse_macros($_SESSION["msg_message_txt"]));

         print("         </font>
                     </td>
                  </tr>
                  </table>
               </form>\n");

         db_close($dbr);
      ?>
   </div>
</div>
<?php
   pied_de_page();
?>
</body></html>
