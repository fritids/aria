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
   
   // Le candidat doit imp�rativement �tre authentifi� avant de pouvoir utiliser ce formulaire
   
   if(!isset($_SESSION["authentifie"]))
      $erreur_auth=1;
   
   $dbr=db_connect();

   if(!isset($erreur_auth) && (isset($_POST["Valider"]) || isset($_POST["Valider_x"]))) // validation du formulaire
   {
      // v�rification des valeurs entr�es dans le formulaire
      // TODO : v�rifications pouss�es

      $candidat_id=$_SESSION["authentifie"];

      // Formations � d�verrouiller
      if(array_key_exists("propspec_id", $_POST) && $nb_formations=count($_POST["propspec_id"]))
         $propspec_array=$_POST["propspec_id"];
      else
         $erreur_formations=1;

      if(array_key_exists("motif", $_POST) && trim($_POST["motif"]!=""))
         $motif=$_POST["motif"];
      else
         $erreur_motif=1;

      if(!isset($erreur_formations) && !isset($erreur_motif))
      {
         // Construction du corps du message en fonction des donn�es du formulaire
         // Le corps contient un tableau HTML : il est affich� dans la messagerie, on peut donc utiliser le format d�sir�

         $identite=$_SESSION["prenom2"]!="" ? "$_SESSION[civilite]. $_SESSION[nom] $_SESSION[prenom] ($_SESSION[prenom2])" : "$_SESSION[civilite]. $_SESSION[nom] $_SESSION[prenom]";

         $corps_message="<table cellpadding='4' border='0' valign='top'>
                         <tr>
                           <td class='td-complet fond_menu2' colspan='2'>
                              <font class='Texte_menu2'><strong>D�tails de la requ�te : </strong></font>
                           </td>
                          </tr>
                          <tr>
                           <td class='td-gauche'><font class='Texte'><strong>Candidat(e) :</strong></font></td>
                           <td class='td-droite'>
                              <font class='Texte'>
                                 <strong>$identite</strong>, n�(e) le ".date("d/m/Y", $_SESSION["naissance"])." � $_SESSION[lieu_naissance] ($_SESSION[pays_naissance])
                              </font>
                           </td>
                          </tr>
                          <tr>
                           <td class='td-gauche'><font class='Texte'><strong>Nationalit� :</strong></font></td>
                           <td class='td-droite'><font class='Texte'>$_SESSION[nationalite]</font></td>
                          </tr>
                          <tr>
                           <td class='td-gauche'><font class='Texte'><strong>Num�ro INE :</strong></font></td>
                           <td class='td-droite'><font class='Texte'> $_SESSION[numero_ine]</font></td>
                          </tr>
                          <tr>
                           <td class='td-gauche'><font class='Texte'><strong>Adresse @ :</strong></font></td>
                           <td class='td-droite'><font class='Texte'>[mail=$_SESSION[email]]$_SESSION[email][/mail]</font></td>
                          </tr>
                          <tr>
                           <td class='td-gauche' valign='top'><font class='Texte'><strong>Motif du d�verrouillage :</strong></font></td>
                           <td class='td-droite'><font class='Texte'>".nl2br($motif)."</font></td>
                          </tr>
                          </table><br>\n";

         if($nb_formations==1)
            $corps_message.="<font class='Texte'>
                                 Le candidat a demand� le d�verrouillage de la formation non gris�e suivante. Pour la d�verrouiller, s�lectionnez-l�, modifiez �ventuellement la nouvelle
                                 date limite puis validez (si vous ne modifiez pas la date, le voeu se verrouillera automatiquement le lendemain matin). Si la modification porte sur les 
                                 informations personnelles du candidat (pouvant �tre partag�es par d'autres composantes), vous pouvez �galement d�verrouiller les formations gris�es.
                             </font>\n";
         else
            $corps_message.="<font class='Texte'>
                                 Le candidat a demand� le d�verrouillage des formations non gris�es suivantes. Pour les d�verrouiller, s�lectionnez-les, modifiez �ventuellement les 
                                 nouvelles dates limites puis validez (si vous ne modifiez pas la date, le voeu se verrouillera automatiquement le lendemain matin). Si la modification
                                 porte sur les informations personnelles du candidat (pouvant �tre partag�es par d'autres composantes), vous pouvez �galement d�verrouiller les formations 
                                 gris�es.
                             </font>\n";

         $corps_message.="<br><br>
                           <table cellpadding='4' border='0' width='100%' style='padding-bottom:20px;'>
                           <tr>
                              <td class='td-gauche fond_menu2' colspan='2'><font class='Texte_menu2'><strong>Formation</strong></font></td>
                              <td class='td-milieu fond_menu2'><font class='Texte_menu2'><strong>Date actuelle</strong></font></td>
                              <td class='td-milieu fond_menu2'><font class='Texte_menu2'><strong>Nouvelle date</strong></font></td>
                           </tr>";

         // Formations : les non-coch�es sont gris�es, ainsi que les formations non verrouill�es

         $old_comp_id="";
         $old_groupe_spec="";

         foreach($_SESSION["liste_formations"] as $formation_array)
         {
            $new_j=date("d", $formation_array["lockdate"]);
            $new_m=date("m", $formation_array["lockdate"]);
            $new_y=date("Y", $formation_array["lockdate"]);

            $font_class=in_array($formation_array["propspec_id"], $propspec_array) ? "Texte" : "Textegris";

            // Candidature � choix multiple ? => s�lection et date de verrouillage uniques
            if($old_comp_id!=$formation_array["comp_id"] || $formation_array["groupe_spec"]=="-1" || ($formation_array["groupe_spec"]!=$old_groupe_spec && $formation_array["ordre_spec"]=="1"))
            {
               $selection="<input type='checkbox' name='cand_id[]' value='$formation_array[cand_id]'>";

               $current_date=date("d/m/Y", $formation_array["lockdate"]);

               $new_date="<font class='$font_class'>
                           J: <input type='text' name='jour_verr[".$formation_array["cand_id"]."]' value='$new_j' size='2' maxlength='2'>&nbsp;
                           M: <input type='text' name='mois_verr[".$formation_array["cand_id"]."]' value='$new_m' size='2' maxlength='2'>&nbsp;
                           Y: <input type='text' name='annee_verr[".$formation_array["cand_id"]."]' value='$new_y' maxlength='4' size='4'>
                        </font>";
            }
            else
               $current_date=$new_date=$selection="";

            // Composante
            if($old_comp_id!=$formation_array["comp_id"])
               $corps_message.="<tr>
                                 <td class='td-gauche fond_menu' colspan='4'>
                                    <font class='Texte_menu'><strong>$formation_array[comp_nom]</strong>
                                 </td>
                                </tr>";

            $corps_message.="<tr>
                              <td class='td-gauche' width='10px'>
                                 <input type='hidden' name='spec_nom[".htmlspecialchars(stripslashes($formation_array["cand_id"]))."]' value='".htmlspecialchars(stripslashes($formation_array["nom"]), ENT_QUOTES, $default_htmlspecialchars_encoding)."'>
                                 $selection
                              </td>
                              <td class='td-gauche'><font class='$font_class'>$formation_array[nom]</font></td>
                              <td class='td-milieu'><font class='$font_class'>$current_date</font></td>
                              <td class='td-droite'>$new_date</td>
                           </tr>";

            $old_groupe_spec=$formation_array["groupe_spec"];
            $old_comp_id=$formation_array["comp_id"];
         }

         $corps_message.="<td class='td-gauche'>
                              <input type='checkbox' name='rien' value='1'>
                          </td>
                          <td class='td-gauche' colspan='3'>
                              <font class='Texte'>Ne rien d�verrouiller (case prioritaire) - Compl�ter le message pour indiquer le motif.</font>
                          </td>
                          </table>

                           <font class='Texte'>Apr�s validation, cette demande de d�verrouillage sera automatiquement plac� dans le dossier <strong>\"Trait�s\"</strong>.</font>
                           <br><br>

                           <table cellpadding='4' border='0' valign='top'>
                           <tr>
                              <td class='td-complet fond_menu2'>
                                 <font class='Texte_menu2'>
                                    <strong>Message compl�mentaire � envoyer au candidat (facultatif)</strong>
                                    <br />Note : le message commence automatiquement par \"Bonjour\" et ce qui a �t� valid� dans le formulaire ci-dessus.
                                 </font>
                              </td>
                           </tr>
                           <tr>
                              <td class='td-droite fond_menu'>
                                 <textarea name='message' cols='80' rows='4'></textarea>
                              </td>
                           </tr>
                           </table>

                           <div class='centered_icons_box'>
                              <input type='hidden' name='candidat_id' value='$candidat_id'>
                              <input type='image' src='$__ICON_DIR/button_ok_32x32_blanc.png' alt='Valider' name='Valider_form_deverrouillage' value='Valider'>
                           </div>";

         // Destinataire(s) : administrateurs de niveau 6

         $array_dests=array();

         $res_admins=db_query($dbr,"SELECT $_DBC_acces_id FROM $_DB_acces WHERE $_DBC_acces_niveau='$__LVL_ADMIN' AND $_DBC_acces_reception_msg_systeme='t'");

         // TODO : pr�voir le cas o� aucun admin n'est pr�sent dans la base : envoyer � l'adresse de debug ?
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

         // Nettoyage pour affichage
         $corps_message=preg_replace("/[[:space:]]+/", " ", preg_replace("/[\r]*[\n]+/","", $corps_message));

         $sujet_message="ASSISTANCE : D�verrouillage - $identite";

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
      titre_page_icone("Demande de d�verrouillage", "decrypted_32x32_fond.png", 15, "L");

      if(isset($erreur_motif))
         message("Formulaire incomplet: vous devez indiquer le motif du d�verrouillage", $__ERREUR);

      if(isset($erreur_formations))
         message("Vous devez s�lectionner au moins une formation � d�verrouiller.", $__ERREUR);

      if(isset($succes))
      {
         message("Merci. Un message a �t� envoy� � l'administrateur.
                  <br>Si les informations sont correctes, vous recevrez un message confirmant le d�verrouillage du ou des voeu(x) s�lectionn�(s)", $__SUCCES);

         print("<div class='centered_icons_box'>
                  <a href='index.php' target='_self' class='lien2'><img src='$__ICON_DIR/back_32x32_fond.png' alt='Retour' border='0'></a>
               </div>\n");
      }
      elseif(isset($erreur_auth))
      {
         message("Vous devez �tre authentifi�(e) pour acc�der � ce formulaire.", $__ERREUR);

         print("<div class='centered_icons_box'>
                  <a href='index.php' target='_self' class='lien2'><img src='$__ICON_DIR/back_32x32_fond.png' alt='Retour' border='0'></a>
               </div>\n");
      }
      else
      {
   ?>

   <form action="<?php print("$php_self"); ?>" method="POST">

   <?php
      message("Merci de compl�ter et valider le formulaire suivant (les champs <strong>en gras</strong> sont <strong>obligatoires</strong>).", $__INFO);
   ?>

   <table align='center'>
   <tr>
      <td class='td-complet fond_menu2' style='padding:4px;' colspan='2'>
         <font class='Texte_menu2'><strong>Votre requ�te</strong></font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2'>
         <font class='Texte_menu2'><strong>Identit� : </strong></font>
      </td>
      <td class='td-droite fond_menu'>
         <font class='Texte_menu2'>
            <?php
               if($_SESSION["civilite"]=="M")
                  $civ_texte="M.";
               elseif($_SESSION["civilite"]=="Mme")
                  $civ_texte="Mme.";
               else
                  $civ_texte="Mlle.";

               print("$civ_texte $_SESSION[prenom] $_SESSION[nom]");
            ?>
         </font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2'>
         <font class='Texte_menu2'><strong>Naissance : </strong></font>
      </td>
      <td class='td-droite fond_menu'>
         <font class='Texte_menu'>
            <?php print("Le ".date("d/m/Y", $_SESSION["naissance"])." � $_SESSION[lieu_naissance] ($_SESSION[pays_naissance])"); ?>
         </font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2'>
         <font class='Texte_menu2'><strong>Nationalit� : </strong></font>
      </td>
      <td class='td-droite fond_menu'>
         <font class='Texte_menu'>
            <?php print("$_SESSION[nationalite]"); ?>
         </font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2'>
         <font class='Texte_menu2'><strong>Num�ro INE/BEA : </strong></font>
      </td>
      <td class='td-droite fond_menu'>
         <font class='Texte_menu'>
            <?php
               if(array_key_exists("numero_ine", $_SESSION) && $_SESSION["numero_ine"]!="")
                  print("$_SESSION[numero_ine]");
               else
                  print("<i>Non renseign�</i>\n");
            ?>
         </font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2'>
         <font class='Texte_important_menu2'><strong>Formation(s) � d�verrouiller :</strong></font>
      </td>
      <td class='td-droite fond_menu'>
         <?php
            $res_formations=db_query($dbr,"SELECT $_DBC_cand_id,$_DBC_annees_annee, $_DBC_specs_nom, $_DBC_composantes_id, $_DBC_composantes_nom,
                                                  $_DBC_propspec_id, $_DBC_propspec_finalite, $_DBC_cand_lock, $_DBC_cand_lockdate,
                                                  $_DBC_cand_groupe_spec, $_DBC_cand_ordre_spec
                                             FROM $_DB_annees, $_DB_specs, $_DB_composantes, $_DB_propspec, $_DB_cand
                                          WHERE $_DBC_cand_periode='$__PERIODE'
                                          AND $_DBC_propspec_id=$_DBC_cand_propspec_id
                                          AND $_DBC_propspec_annee=$_DBC_annees_id
                                          AND $_DBC_propspec_id_spec=$_DBC_specs_id
                                          AND $_DBC_propspec_comp_id=$_DBC_composantes_id
                                          AND $_DBC_cand_candidat_id='$_SESSION[authentifie]'
                                          ORDER BY $_DBC_composantes_nom, $_DBC_cand_ordre, $_DBC_cand_groupe_spec, $_DBC_cand_ordre_spec");

            $rows_formations=db_num_rows($res_formations);            

            if($rows_formations)
            {
               $nb_locked=0;

               // Conservation du nom des formations (pour �viter une nouvelle requ�te lors de l'envoi du message)
               $_SESSION["nom_formations"]=array();

               print("<table cellpadding='1' cellspacing='0' border='0' width='100%'>\n");

               $old_comp_id="";
               $old_groupe_spec="";

               for($f=0; $f<$rows_formations; $f++)
               {
                  list($cand_id, $annee, $spec, $composante_id, $composante, $propspec_id, $finalite, $locked, $lockdate, $groupe_spec, $ordre_spec)=db_fetch_row($res_formations, $f);

                  $nom_formation=$annee=="" ? "$spec" : "$annee $spec";
                  $nom_formation.=$tab_finalite["$finalite"]=="" ? "" : " $tab_finalite[$finalite]";

                  $_SESSION["liste_formations"][$f]=array("propspec_id" => "$propspec_id",
                                                          "cand_id" => "$cand_id",
                                                          "comp_id" => "$composante_id",
                                                          "comp_nom" => "$composante",
                                                          "nom" => "$nom_formation",
                                                          "locked" => "$locked",
                                                          "lockdate" => "$lockdate",
                                                          "groupe_spec" => "$groupe_spec",
                                                          "ordre_spec" => "$ordre_spec");

                  if(isset($propspec_array) && in_array($propspec_id, $propspec_array))
                     $checked="checked";
                  else
                     $checked="";

                  if($old_comp_id!=$composante_id)
                  {
                     if($f)
                        print("<tr>
                                 <td colspan='3' height='10px'></td>
                              </tr>\n");

                     print("<tr>
                              <td colspan='2'><font class='Texte'><strong>$composante</strong></font></td>
                              <td class='td-droite'><font class='Texte'><strong>Verrouillage</strong></font></td>
                           </tr>\n");

                     $old_comp_id=$composante_id;
                  }

                  $lockdate_txt=date("d/m/Y", $lockdate);

                  // La formation n'est s�lectionnable que si elle est d�j� verrouill�e
                  if($locked)
                  {
                     // ne pas oublier le cas des candidature � choix multiples (un seul d�verrouillage et une seule date commune)
                     if($groupe_spec=="-1" || ($old_groupe_spec!=$groupe_spec && $ordre_spec==1))
                     {
                        $nb_locked++;

                        print("<tr>
                                 <td><input type='checkbox' name='propspec_id[]' value='$propspec_id' $checked></td>
                                 <td class='td-milieu'><font class='Texte'>$nom_formation</font></td>
                                 <td class='td-droite'><font class='Texte'>$lockdate_txt</font></td>
                              </tr>\n");
                     }
                     else
                        print("<tr>
                                 <td></td>
                                 <td class='td-milieu'><font class='Texte'>$nom_formation</font></td>
                                 <td class='td-droite'><font class='Texte'></font></td>
                              </tr>\n");
                  }
                  else
                     print("<tr>
                              <td></td>
                              <td class='td-milieu'><font class='Textegris'>$nom_formation</font></td>
                              <td class='td-droite'><font class='Textegris'>$lockdate_txt</font></td>
                           </tr>\n");

                  $old_groupe_spec=$groupe_spec;
               }

               print("</table>\n");
            }
            else
            {
               print("<font class='Texte_important_menu2'>Aucune formation � d�verrouiller !</font>\n");
               $err_formations=1;
            }

            db_free_result($res_formations);
         ?>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2'>
         <font class='Texte_important_menu2'><strong>D�tails de la demande de d�verrouillage :</strong></font>
      </td>
      <td class='td-droite fond_menu'>
         <textarea name='motif' cols="50" rows="7"><?php if(isset($motif)) echo htmlspecialchars(stripslashes($motif), ENT_QUOTES, $default_htmlspecialchars_encoding); ?></textarea>
      </td>
   </tr>
   </table>

   <div class='centered_icons_box'>
      <a href='index.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Retour' border='0'></a>
      <?php
         if(!isset($err_formations) && isset($nb_locked) && $nb_locked!=0)
            print("<input type='image' src='$__ICON_DIR/button_ok_32x32_fond.png' alt='Valider' name='Valider' value='Valider'>\n");
      ?>
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

