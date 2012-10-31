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
   // $_SESSION['CURRENT_FILE']=$php_self;

   $dbr=db_connect();

   if(isset($_POST["Valider"]) || isset($_POST["Valider_x"])) // validation du formulaire
   {
      // v�rification des valeurs entr�es dans le formulaire
      // TODO : v�rifications pouss�es

      $civilite=$_POST["civilite"];

      $nom=mb_strtoupper(trim($_POST["nom"]));
      $prenom=ucwords(strtolower((trim($_POST["prenom"]))));
      $prenom2=ucwords(strtolower((trim($_POST["prenom2"]))));

      $jour=trim($_POST["jour"]);
      $mois=trim($_POST["mois"]);
      $annee=trim($_POST["annee"]);

      $lieu_naissance=ucwords(strtolower(trim($_POST["lieu_naissance"])));
      $pays_naissance_code=$_POST["pays_naissance"];

      $old_email=mb_strtolower(trim($_POST["old_email"]));

      $email=mb_strtolower(trim($_POST["email"]));
      $emailconf=mb_strtolower(trim($_POST["emailconf"]));

      if(strcmp($email, $emailconf)) // si les 2 adresses sont diff�rentes ...
         $email_inegaux=1;

      $nationalite_code=$_POST["nationalite"];

      $num_ine=str_replace(" ", "", $_POST["num_ine"]);

      if($num_ine!="" && check_ine_bea($num_ine))
         $erreur_ine_bea=1;

      $autres_infos=trim($_POST["autres"]);

      $champs_obligatoires=array($nom,$prenom,$jour,$mois,$annee,$lieu_naissance,$pays_naissance_code,$email,$emailconf,$nationalite_code);
      $cnt_obl=count($champs_obligatoires);

      for($i=0; $i<$cnt_obl; $i++) // v�rification des champs obligatoires
      {
         if($champs_obligatoires[$i]=="")
         {
            $champ_vide=1;
            $i=$cnt_obl;
         }
      }

      if(!ctype_digit($jour) || !ctype_digit($mois) || !ctype_digit($annee) || $annee>=date('Y'))
         $erreur_date_naissance=1;

      // Le nouveau mail et l'ancien ne doivent pas �tre identiques
      /*
      if($email == $old_email)
         $ancien_nouveau_identiques=1;
      */
      
      if(db_num_rows(db_query($dbr, "SELECT * FROM $_DB_candidat WHERE $_DBC_candidat_email ILIKE '$email'")))
         $new_email_existe=1;

      if(!isset($champ_vide) && !isset($email_inegaux) && !isset($new_email_existe) && !isset($erreur_date_naissance) && !isset($erreur_ine_bea))
      {
         // Construction du corps du message en fonction des donn�es du formulaire
         // Le corps contient un tableau HTML : il est affich� dans la messagerie, on peut donc utiliser le format d�sir�

         $identite=$prenom2!="" ? "$civilite. $nom $prenom ($prenom2)" : "$civilite. $nom $prenom";

         $lien_old_email=trim($old_email)!="" ? "[mail=".$old_email."]".$old_email."[/mail]" : "";

         $corps_message="<table cellpadding='4' border='0' valign='top'>
                         <tr>
                            <td class='td-complet fond_menu2' colspan='2'>
                               <font class='Texte_menu2'><strong>D�tails de la requ�te :</strong></font>
                            </td>
                         </tr>
                         <tr>
                            <td class='td-gauche'><font class='Texte'><strong>Candidat(e) :</strong></font></td>
                            <td class='td-droite'>
                               <font class='Texte'>$identite, n�(e) le $jour/$mois/$annee � $lieu_naissance (".$_SESSION["liste_pays_nat_iso"]["$pays_naissance_code"]["pays"].")</font>
                            </td>
                         </tr>
                         <tr>
                            <td class='td-gauche'><font class='Texte'><strong>Nationalit� :</strong></font></td>
                            <td class='td-droite'><font class='Texte'>".$_SESSION["liste_pays_nat_iso"]["$nationalite_code"]["nationalite"]."</font></td>
                         </tr>
                         <tr>
                            <td class='td-gauche'><font class='Texte'><strong>Num�ro INE :</strong></font></td>
                            <td class='td-droite'><font class='Texte'>$num_ine</font></td>
                         </tr>
                         <tr>
                            <td class='td-gauche'><font class='Texte'><strong>Ancienne adresse @ :</strong></font></td>
                            <td class='td-droite'><font class='Texte'>$lien_old_email</font></td>
                         </tr>
                         <tr>
                            <td class='td-gauche'><font class='Texte'><strong>Nouvelle adresse @ :</strong></font></td>
                            <td class='td-droite'><font class='Texte'>[mail=".$email."]".$email."[/mail]</font></td>
                         </tr>
                         <tr>
                            <td class='td-gauche'><font class='Texte'><strong>Autres :</strong></font></td>
                            <td class='td-droite' style='white-space:normal;'><font class='Texte'>$autres_infos</font></td>
                          </tr>
                          </table><br>\n";

         // On cherche le candidat dans la base de donn�es � partir des informations, puis on �crit le r�sultat
          // de la recherche dans le message.

         // Nettoyage de la chaine de caract�res pour la requ�te � la base de donn�es
         // caract�res � traiter : � � � � � �  �  � � � �  � � � �  �  � � � � � �  � � � �  � � *
         $rech_nom=preg_replace("/[ ]+/", " ", clean_str_requete($nom));
         $rech_prenom=preg_replace("/[ ]+/", " ", clean_str_requete($prenom));

         // Crit�res de recherche : nom, pr�nom, ancien et nouvel email, num�ro INE, ...

         // Conditions variables pour les champs non obligatoires
         $condition_old_email=$old_email!="" ? "OR $_DBC_candidat_email ILIKE '$old_email'" : "";
         $condition_ine=$num_ine!="" ? "OR $_DBC_candidat_numero_ine ILIKE '$num_ine'" : "";
         
         $res_recherche=db_query($dbr,"SELECT $_DBC_candidat_id, $_DBC_candidat_civilite, $_DBC_candidat_nom, $_DBC_candidat_prenom,
                                              $_DBC_candidat_date_naissance, $_DBC_candidat_email, $_DBC_candidat_numero_ine,
                                              $_DBC_candidat_lieu_naissance, $_DBC_candidat_pays_naissance,
                                              $_DBC_candidat_nationalite, $_DBC_candidat_manuelle
                                          FROM $_DB_candidat
                                       WHERE ($_DBC_candidat_nom SIMILAR TO '$rech_nom%'
                                              AND $_DBC_candidat_prenom SIMILAR TO '$rech_prenom%')
                                       $condition_ine
                                       $condition_old_email
                                       OR $_DBC_candidat_email ILIKE '$email'
                                          ORDER BY $_DBC_candidat_nom, $_DBC_candidat_prenom, $_DBC_candidat_date_naissance");

         $rows_recherche=db_num_rows($res_recherche);
         
         if($rows_recherche)
         {
            if($rows_recherche==1)
               $corps_message.="<font class='Texte'>
                                    Le candidat suivant semble correspondre aux crit�res.
                                    <br>- S'il correspond, s�lectionnez-le et validez : son adresse �lectronique sera mise � jour et ses identifiants seront renvoy�s.
                                    <br>- Dans le cas contraire, utilisez le <a href='$__GESTION_DIR/recherche.php' class='lien_bleu_12' style='vertical-align:top;'>menu Recherche</a> de l'application.
                                    <br>- Apr�s validation, ce message sera automatiquement plac� dans le dossier \"Trait�s\".
                                 </font>\n";
            else
               $corps_message.="<font class='Texte'>
                                 Les candidats suivants semblent correspondre aux crit�res.
                                 <br>- Si l'un d'eux correspond, s�lectionnez-le et validez : son adresse �lectronique sera mise � jour et ses identifiants seront renvoy�s.
                                 <br>- Dans le cas contraire, utilisez le <a href='$__GESTION_DIR/recherche.php' class='lien_bleu_12' style='vertical-align:top;'>menu Recherche</a> de l'application.
                                 <br>- Apr�s validation, ce message sera automatiquement plac� dans le dossier \"Trait�s\".
                              </font>\n";

            $corps_message.="<br><br>

                              <input type='hidden' name='new_mail' value='$email'>

                              <table cellpadding='4' border='0' style='padding-bottom:20px;'>
                              <tr>
                                 <td class='td-gauche fond_menu2' colspan='2'><font class='Texte_menu2'><strong>Candidat(e)</strong></font></td>
                                 <td class='td-milieu fond_menu2' colspan='2'><font class='Texte_menu2'><strong>Naissance</strong></font></td>
                                 <td class='td-milieu fond_menu2'><font class='Texte_menu2'><strong>Nationalit�</strong></font></td>
                                 <td class='td-milieu fond_menu2'><font class='Texte_menu2'><strong>Num�ro INE</strong></font></td>
                                 <td class='td-milieu fond_menu2'><font class='Texte_menu2'><strong>Adresse @</strong></font></td>
                              </tr>";

            // Les actions sont possibles directement depuis le message (uniquement pour les admins, pour le moment : prudence.)
            // Elles sont trait�es dans le fichiers gestion/messagerie/message.php (� d�placer ?)

            for($rech_i=0; $rech_i<$rows_recherche; $rech_i++)
            {
               list($r_cand_id, $r_cand_civ, $r_cand_nom, $r_cand_prenom, $r_cand_date_naissance, $r_cand_email, $r_cand_ine,
                    $r_cand_lieu_naissance,$r_cand_pays_naissance,$r_cand_nationalite, $r_cand_manuelle)=db_fetch_row($res_recherche, $rech_i);

               $texte_fiche_manuelle=$r_cand_manuelle ? "<br>(Fiche manuelle)":"";

               $r_naissance=date("d/m/Y", $r_cand_date_naissance);

               $texte_email=$r_cand_email!="" ? "[mail=".$r_cand_email."]".$r_cand_email."[/mail]" : "";

               $corps_message.="<tr>
                                 <td class='td-gauche' width='10px'><input type='radio' name='sel_candidat' value='$r_cand_id'></td>
                                 <td class='td-gauche'>
                                    <font class='Texte'>
                                       <a href='$__GESTION_DIR/edit_candidature.php?cid=$r_cand_id' class='lien_bleu_12'>$r_cand_civ. $r_cand_nom $r_cand_prenom</a>
                                       $texte_fiche_manuelle
                                    </font>
                                 </td>
                                 <td class='td-milieu'><font class='Texte'>$r_naissance</font></td>
                                 <td class='td-milieu'><font class='Texte'>$r_cand_lieu_naissance (".$_SESSION["liste_pays_nat_iso"]["$r_cand_pays_naissance"]["pays"].")</font></td>
                                 <td class='td-milieu'><font class='Texte'>".$_SESSION["liste_pays_nat_iso"]["$r_cand_nationalite"]["nationalite"]."</font></td>
                                 <td class='td-milieu'><font class='Texte'>$r_cand_ine</font></td>
                                 <td class='td-droite'><font class='Texte'>$texte_email</font></td>
                              </tr>";
            }

            $corps_message.="</table>
                             <div class='centered_icons_box'>
                                 <input type='image' src='$__ICON_DIR/button_ok_32x32_blanc.png' alt='Valider' name='Valider_form_adresse' value='Valider'>
                            </div>";
         }
         else
         {
            $le_candidat=$civilite=="M" ? "le candidat" : "la candidate";

            $corps_message.="Aucun candidat trouv� avec ces crit�res : recherche manuelle n�cessaire
                             <br />
                             <input type='checkbox' name='mail_inconnu' value='$email'> Envoyer un message automatique invitant $le_candidat � s'enregistrer.
                             <br /><br />
                             <div class='centered_icons_box'>
                                <input type='image' src='$__ICON_DIR/button_ok_32x32_blanc.png' alt='Valider' name='Valider_form_adresse' value='Valider'>
                             </div>";
         }

         db_free_result($res_recherche);

         // Destinataire(s) : administrateurs de niveau 6

         $array_dests=array();

         $res_admins=db_query($dbr,"SELECT $_DBC_acces_id FROM $_DB_acces WHERE $_DBC_acces_niveau='$__LVL_ADMIN' AND $_DBC_acces_reception_msg_systeme='t'");

         // Pr�voir le cas o� aucun admin n'est pr�sent dans la base : envoyer � l'adresse de debug ?
         if($rows_admin=db_num_rows($res_admins))
         {
            for($admin_i=0; $admin_i<$rows_admin; $admin_i++)
            {
               list($admin_id)=db_fetch_row($res_admins, $admin_i);

               $array_dests[$admin_i]=array("id" => $admin_id, "dest_type" => "gestion");
            }
         }
         else
            $array_dests[0]=array("id" => "0");

         db_free_result($res_admins);

         $corps_message=preg_replace("/[ ]+/", " ", preg_replace("/[\r]*[\n]+/","", $corps_message));

         $sujet_message="ASSISTANCE : adresse �lectronique - $identite";

         write_msg_2($dbr, array("id" => "0", "nom" => "Syst�me", "prenom" => "", "src_type" => "gestion", "composante" => "", "universite" => "$__SIGNATURE_COURRIELS"),
                     $array_dests, $sujet_message,$corps_message);

         $succes=1;

         // write_evt("", $__EVT_ID_C_ID, "MAJ Identit�", $candidat_id, $candidat_id, ereg_replace("[']+","''", stripslashes($requete)));
         // db_close($dbr);
      }
   }
   elseif(isset($_SESSION["authentifie"]) && isset($_SESSION["naissance"]))
   {
      $cur_annee=date_fr("Y", $_SESSION["naissance"]);
      $cur_mois=date_fr("m", $_SESSION["naissance"]);
      $cur_jour=date_fr("d", $_SESSION["naissance"]);
   }
   else
      $cur_annee=$cur_mois=$cur_jour="";
   
   // Construction de la liste des pays et nationalit�s (codes ISO) pour son utilisation dans le formulaire
   $_SESSION["liste_pays_nat_iso"]=array();
   
   $res_pays_nat=db_query($dbr, "SELECT $_DBC_pays_nat_ii_iso, $_DBC_pays_nat_ii_insee, $_DBC_pays_nat_ii_pays, $_DBC_pays_nat_ii_nat
                                 FROM $_DB_pays_nat_ii
                                 ORDER BY to_ascii($_DBC_pays_nat_ii_pays)");
                                 
   $rows_pays_nat=db_num_rows($res_pays_nat);
   
   for($p=0; $p<$rows_pays_nat; $p++)
   {
      list($code_iso, $code_insee, $table_pays, $table_nationalite)=db_fetch_row($res_pays_nat, $p);
      
      // Construction uniquement si le code insee est pr�sent (pour les exports APOGEE ou autres)
      if($code_insee!="")
         $_SESSION["liste_pays_nat_iso"]["$code_iso"]=array("pays" => "$table_pays", "nationalite" => $table_nationalite);
/*      
      if($code_insee!="")
         $_SESSION["liste_pays_nat_insee"]["$code_insee"]=array("pays" => "$table_pays", "nationalite" => $table_nationalite);
*/
   }
   
   en_tete_candidat();
   menu_sup_simple();
?>

<div class='main'>
   <?php
      titre_page_icone("Demande de modification de l'adresse �lectronique", "mail_send_32x32_fond.png", 15, "L");

      $message_erreur="";

      if(isset($ancien_nouveau_identiques))
         $message_erreur.="- la nouvelle adresse doit �tre diff�rente de l'ancienne (utilisez le formulaire de renvoi des identifiants si n�cessaire)";
         
      if(isset($new_email_existe))
      {
         $message_erreur.=$message_erreur!="" ? "\n<br>" : "";
         $message_erreur.="- un compte existe d�j� avec le nouveau courriel indiqu� : merci d'utiliser le <a class='lien2' href='../recuperation_identifiants.php'>formulaire de renvoi des identifiants</a>";
      }

      if(isset($email_inegaux))
      {
         $message_erreur.=$message_erreur!="" ? "\n<br>" : "";
         $message_erreur.="- les deux adresses �lectroniques ne correspondent pas";
      }

      if(isset($erreur_date_naissance))
      {
         $message_erreur.=$message_erreur!="" ? "\n<br>" : "";
         $message_erreur.="- le format de votre date de naissance est incorrect (JJ / MM / AAAA)";
      }

      if(isset($erreur_ine_bea))
      {
         $message_erreur.=$message_erreur!="" ? "\n<br>" : "";
         $message_erreur.="- le numero INE ou BEA est incorrect";
      }

      if($message_erreur!="")
      {
          $message_erreur="<strong>Erreur(s)</strong> :\n<br>$message_erreur";
         message("$message_erreur", $__ERREUR);
      }

      if(isset($champ_vide))
         message("Formulaire incomplet: les champs en gras sont <u>obligatoires</u>", $__ERREUR);

      if(isset($succes))
      {
         message("Merci. Un message a �t� envoy� � l'administrateur, il traitera votre demande dans les meilleurs d�lais.
                  <br>Si les informations sont correctes, vous recevrez un courriel contenant vos identifiants.", $__SUCCES);

         print("<div class='centered_icons_box'>
                  <a href='index.php' target='_self' class='lien2'><img src='$__ICON_DIR/back_32x32_fond.png' alt='Retour' border='0'></a>
               </div>\n");
      }
      else
      {
   ?>

   <form action="<?php print("$php_self"); ?>" method="POST">

   <?php
      message("Merci de compl�ter et de valider le formulaire suivant (les champs <strong>en gras</strong> sont <strong>obligatoires</strong>).
               <br><br><strong>Hormis l'adresse �lectronique, les donn�es doivent �tre identiques � celles entr�es lors de votre enregistrement</strong>.", $__INFO);
   ?>

   <table align='center'>
   <tr>
      <td class='td-gauche fond_menu2'>
         <font class='Texte_important_menu2'><strong>Civilit� : </strong></font>
      </td>
      <td class='td-droite fond_menu'>
         <?php
            if(isset($civilite))
               $civ=$civilite;
            elseif(isset($_SESSION["civilite"]))
               $civ=$_SESSION["civilite"];
            else
               $civ="Mme";

            if($civ=="M")
            {
               $selected_M="selected='1'";
               $selected_Mlle="";
               $selected_Mme="";
            }
            else
            {
               if($civ=="Mme")
               {
                  $selected_Mme="selected='1'";
                  $selected_M="";
                  $selected_Mlle="";
               }
               else
               {
                  $selected_Mlle="selected='1'";
                  $selected_M="";
                  $selected_Mme="";
               }
            }

            print("
            <select name='civilite' size='1'>
               <option value='Mme' $selected_Mme>Madame</option>
               <option value='Mlle' $selected_Mlle>Mademoiselle</option>
               <option value='M' $selected_M>Monsieur</option>
            </select>");
         ?>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2'>
         <font class='Texte_important_menu2'><strong>Nom : </strong></font>
      </td>
      <td class='td-droite fond_menu'>
         <input type='text' name='nom' value='<?php if(isset($nom)) echo htmlspecialchars(stripslashes($nom), ENT_QUOTES); elseif(isset($_SESSION["nom"])) echo htmlspecialchars(stripslashes($_SESSION["nom"]), ENT_QUOTES); ?>' size="25" maxlength="30">
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2'>
         <font class='Texte_important_menu2'><strong>Pr�nom : </strong></font>
      </td>
      <td class='td-droite fond_menu'>
         <input type='text' name='prenom' value='<?php if(isset($prenom)) echo htmlspecialchars(stripslashes($prenom), ENT_QUOTES); elseif(isset($_SESSION["prenom"])) echo htmlspecialchars(stripslashes($_SESSION["prenom"]), ENT_QUOTES); ?>' size="25" maxlength="30">
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2'>
         <font class='Texte_menu2'>Deuxi�me pr�nom : </font>
      </td>
      <td class='td-droite fond_menu'>
         <input type='text' name='prenom2' value='<?php if(isset($prenom2)) echo htmlspecialchars(stripslashes($prenom2), ENT_QUOTES); elseif(isset($_SESSION["prenom2"])) echo htmlspecialchars(stripslashes($_SESSION["prenom2"]), ENT_QUOTES); ?>' size="25" maxlength="30">
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2'>
         <font class='Texte_important_menu2'><strong>Date de naissance (jour/mois/annee) : </strong></font>
      </td>
      <td class='td-droite fond_menu'>
         <input type='text' name='jour' value='<?php if(isset($jour)) echo htmlspecialchars($jour,ENT_QUOTES); else echo htmlspecialchars($cur_jour,ENT_QUOTES); ?>' size="2" maxlength="2">/
         <input type='text' name='mois' value='<?php if(isset($mois)) echo htmlspecialchars($mois,ENT_QUOTES); else echo htmlspecialchars($cur_mois,ENT_QUOTES); ?>' size="2" maxlength="2">/
         <input type='text' name='annee' value='<?php if(isset($annee)) echo htmlspecialchars($annee,ENT_QUOTES); else echo htmlspecialchars($cur_annee,ENT_QUOTES); ?>' size="4" maxlength="4">
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2'>
         <font class='Texte_important_menu2'><strong>Ville de naissance : </strong></font>
      </td>
      <td class='td-droite fond_menu'>
         <input type='text' name='lieu_naissance' value='<?php if(isset($lieu_naissance)) echo htmlspecialchars(stripslashes($lieu_naissance), ENT_QUOTES); elseif(isset($_SESSION["lieu_naissance"])) echo htmlspecialchars(stripslashes($_SESSION["lieu_naissance"]), ENT_QUOTES); ?>' size="25" maxlength="60">
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2'>
         <font class='Texte_important_menu2'><strong>Pays de naissance : </strong></font>
      </td>
      <td class='td-droite fond_menu'>
          <select name='pays_naissance' size='1'>
             <option value=''></option>
            <?php
               foreach($_SESSION["liste_pays_nat_iso"] as $code_iso => $array_pays_nat)
               {
                  if($array_pays_nat["pays"]!="")
                  {
                     $selected=(isset($pays_naissance_code) && $pays_naissance_code==$code_iso) || (isset($_SESSION["pays_naissance_code"]) && $_SESSION["pays_naissance_code"]==$code_iso) ? "selected='1'" : "";
                     
                     print("<option value='$code_iso' $selected>$array_pays_nat[pays]</option>\n");
                  }
               }
            ?>
         </select>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2'>
         <font class='Texte_important_menu2'><strong>Nationalit� : </strong></font>
      </td>
      <td class='td-droite fond_menu'>
         <select name='nationalite' size='1'>
             <option value=''></option>
            <?php
               foreach($_SESSION["liste_pays_nat_iso"] as $code_iso => $array_pays_nat)
               {
                  if($array_pays_nat["nationalite"]!="")
                  {
                     $selected=(isset($nationalite_code) && $nationalite_code==$code_iso) || (isset($_SESSION["nationalite_code"]) && $_SESSION["nationalite_code"]==$code_iso) ? "selected='1'" : "";
                     
                     print("<option value='$code_iso' $selected>$array_pays_nat[nationalite]</option>\n");
                  }
               }
            ?>
         </select>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2'>
         <font class='Texte_menu2'>Num�ro INE <strong>ou</strong> BEA : </font>
      </td>
      <td class='td-droite fond_menu'>
         <input type='text' name='num_ine' value='<?php if(isset($num_ine)) echo htmlspecialchars(stripslashes($num_ine), ENT_QUOTES); elseif(isset($_SESSION["numero_ine"])) echo htmlspecialchars($_SESSION["numero_ine"],ENT_QUOTES); ?>' size="25" maxlength="11"> <font class='Texte_menu'><i>(si vous en poss�dez un)</i></font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2'>
         <font class='Texte_menu2'>Ancienne adresse �lectronique : </font>
      </td>
      <td class='td-droite fond_menu2'>
         <input type='text' name='old_email' value='<?php if(isset($old_email)) echo htmlspecialchars(stripslashes($old_email), ENT_QUOTES); ?>' size="40" maxlength="255">
         <font class='Texte_menu'><i>(Vivement conseill�)</i></font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2'>
         <font class='Texte_important_menu2'><strong>Nouvelle adresse �lectronique (<i>email</i>) : </strong></font>
      </td>
      <td class='td-droite fond_menu2'>
         <input type='text' name='email' value='<?php if(isset($email)) echo htmlspecialchars(stripslashes($email), ENT_QUOTES); elseif(isset($_SESSION["email"])) echo htmlspecialchars($_SESSION["email"],ENT_QUOTES); ?>' size="40" maxlength="255">
         &nbsp;&nbsp;<font class='Texte_menu'><strong><u>Une seule adresse</u> dans ce champ.</strong></font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2'>
         <font class='Texte_important_menu2'><strong>Confirmation de la nouvelle adresse �lectronique : </strong></font>
      </td>
      <td class='td-droite fond_menu2'>
         <input type='text' name='emailconf' value='<?php if(isset($emailconf)) echo htmlspecialchars(stripslashes($emailconf), ENT_QUOTES); elseif(isset($_SESSION["email"])) echo htmlspecialchars($_SESSION["email"],ENT_QUOTES); ?>' size="40" maxlength="255">
         <br>
         <font class='Texte_important_menu'>
            <strong>Attention : </strong>
            <br>- v�rifiez bien que les courriels ne sont pas redirig�s dans votre dossier <strong>"Spams"</strong> ou <strong>"Courriers Ind�sirables"</strong>,
            <br>- configurez les <strong>filtres</strong> de votre messagerie pour autoriser l'exp�diteur <strong>"<?php echo $__EMAIL_ADMIN; ?>"</strong> � vous envoyer des courriels.
         </font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2'>
         <font class='Texte_menu2'>Commentaires �ventuels :</font>
      </td>
      <td class='td-droite fond_menu2'>
         <textarea name='autres' cols="40" rows="5"><?php if(isset($autres_infos)) echo htmlspecialchars(stripslashes($autres_infos), ENT_QUOTES); ?></textarea>
      </td>
   </tr>
   </table>

   <div class='centered_icons_box'>
      <a href='index.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Retour' border='0'></a>
      <input type="image" src="<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>" alt="Valider" name="Valider" value="Valider">
      </form>
   </div>

   <?php
      }
   ?>
</div>
<?php
   db_close($dbr);

   pied_de_page_simple();
?>
</body>
</html>

