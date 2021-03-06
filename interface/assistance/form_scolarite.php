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

   // Choix de la composante
   if(isset($_POST["Suivant"]) || isset($_POST["Suivant_x"]))
   {
      if(array_key_exists("comp_id", $_POST) && $_POST["comp_id"]!="")
      {
         $_SESSION["comp_id"]=$_POST["comp_id"];

         $res_composante=db_query($dbr, "SELECT $_DBC_composantes_nom, $_DBC_universites_nom, $_DBC_universites_img_dir, $_DBC_universites_id, $_DBC_universites_css,
                                                $_DBC_composantes_courriel_scol, $_DBC_composantes_limite_cand_nombre, $_DBC_composantes_limite_cand_annee, 
                                                $_DBC_composantes_limite_cand_annee_mention, $_DBC_composantes_affichage_decisions
                                         FROM $_DB_composantes, $_DB_universites
                                         WHERE $_DBC_composantes_univ_id=$_DBC_universites_id
                                         AND $_DBC_composantes_id='$_SESSION[comp_id]'");

         if(!db_num_rows($res_composante))
         {
            db_close($dbr);
            header("Location:" . base_url($php_self) . "../index.php");
            exit();
         }

         $_SESSION["form_composante_id"]=$_SESSION["comp_id"];

         list($_SESSION["composante"],
              $_SESSION["universite"],
              $_SESSION["img_dir"],
              $_SESSION["univ_id"],
              $_SESSION["css"],
              $_SESSION["courriel_scol"],
              $_SESSION["limite_nombre"],
              $_SESSION["limite_annee"],
              $_SESSION["limite_annee_mention"],
              $_SESSION["affichage_decisions"])=db_fetch_row($res_composante, 0);

         db_free_result($res_composante);
      }
      else
         $erreur_composante=1;
   }
   elseif(!isset($erreur_auth) && (isset($_POST["Valider"]) || isset($_POST["Valider_x"]))) // validation du formulaire
   {
      // v�rification des valeurs entr�es dans le formulaire
      // TODO : v�rifications pouss�es

      $candidat_id=$_SESSION["authentifie"];

      // Formation concern�e par le message
      if(array_key_exists("formation", $_POST))
         $propspec_id=$_POST["formation"];
      else
         $erreur_formation=1;

      if(array_key_exists("demande", $_POST) && trim($_POST["demande"]!=""))
         $demande=$_POST["demande"];
      else
         $erreur_demande=1;

      if(!isset($erreur_formation) && !isset($erreur_demande))
      {
         // Une formation a �t� choisie : on regarde les personnes rattach�es � cette derni�re
         if($formation!=0)
         {
            // Nom de la formation
            $res_formation=db_query($dbr, "SELECT $_DBC_annees_annee, $_DBC_specs_nom, $_DBC_propspec_finalite
                                              FROM $_DB_propspec, $_DB_specs, $_DB_annees
                                           WHERE $_DBC_propspec_id='$formation'
                                           AND $_DBC_propspec_annee=$_DBC_annees_id
                                           AND $_DBC_specs_id=$_DBC_propspec_id_spec");

            if(db_num_rows($res_formation))
            {
               list($nom_annee, $nom_spec, $finalite)=db_fetch_row($res_formation, 0);
               $formation_txt=$nom_annee=="" ? htmlspecialchars("$nom_spec", ENT_QUOTES, $default_htmlspecialchars_encoding) : htmlspecialchars("$nom_annee $nom_spec", ENT_QUOTES, $default_htmlspecialchars_encoding);
               $formation_txt.=$finalite ? " - $tab_finalite[$finalite]" : "";
            }

            db_free_result($res_formation);

            // R�cup�ration des informations du destinataire (avec l'�ventuel message d'absence)
            $result=db_query($dbr, "SELECT $_DBC_courriels_propspec_acces_id, $_DBC_acces_nom, $_DBC_acces_prenom, $_DBC_acces_courriel,
                                           $_DBC_acces_absence_debut, $_DBC_acces_absence_fin, $_DBC_acces_absence_msg,
                                           $_DBC_acces_absence_active
                                       FROM $_DB_courriels_propspec, $_DB_acces
                                    WHERE $_DBC_courriels_propspec_acces_id=$_DBC_acces_id
                                    AND $_DBC_courriels_propspec_propspec_id='$formation'
                                    AND $_DBC_courriels_propspec_type='F'");
         }
         // Si aucun r�sultat ou si aucune formation n'a �t� s�lectionn�e, on s�lectionne :
         // 1/ soit les utilisateurs attach�s aux messages g�n�riques
         // 2/ en cas d'echec au 1/, ceux ayant un niveau d'acc�s sup�rieur � la consultation et qui d�sirent recevoir les messages des scol (bool�en dans la table acces)
         // TODO 11/01/08 : Cr�er un syst�me de gestion d'aliases ?
         
         if((isset($result) && !db_num_rows($result)) || $formation==0)
         {
            $result=db_query($dbr, "SELECT $_DBC_courriels_propspec_acces_id, $_DBC_acces_nom, $_DBC_acces_prenom, $_DBC_acces_courriel,
                                           $_DBC_acces_absence_debut, $_DBC_acces_absence_fin, $_DBC_acces_absence_msg,
                                           $_DBC_acces_absence_active
                                       FROM $_DB_courriels_propspec, $_DB_acces
                                    WHERE $_DBC_courriels_propspec_acces_id=$_DBC_acces_id
                                    AND $_DBC_courriels_propspec_propspec_id='$_SESSION[comp_id]'
                                    AND $_DBC_courriels_propspec_type='C'");
                                    
            if(isset($result) && !db_num_rows($result) || !isset($result))
            {
              $result=db_query($dbr, "SELECT $_DBC_acces_id, $_DBC_acces_nom, $_DBC_acces_prenom, $_DBC_acces_courriel,
                                              $_DBC_acces_absence_debut, $_DBC_acces_absence_fin, $_DBC_acces_absence_msg,
                                              $_DBC_acces_absence_active
                                         FROM $_DB_acces
                                       WHERE ($_DBC_acces_composante_id='$_SESSION[comp_id]'
                                              OR $_DBC_acces_id IN (SELECT $_DBC_acces_comp_acces_id FROM $_DB_acces_comp
                                                                  WHERE $_DBC_acces_comp_composante_id='$_SESSION[comp_id]'))
                                       AND $_DBC_acces_niveau IN ('$__LVL_SCOL_MOINS','$__LVL_SCOL_PLUS','$__LVL_RESP','$__LVL_SUPER_RESP','$__LVL_ADMIN')
                                       AND $_DBC_acces_reception_msg_scol='t'
                                       GROUP BY $_DBC_acces_id, $_DBC_acces_nom, $_DBC_acces_prenom, $_DBC_acces_courriel,
                                                $_DBC_acces_absence_debut, $_DBC_acces_absence_fin, $_DBC_acces_absence_msg,
                                                $_DBC_acces_absence_active");
            }
         }         
         
         $rows_destinataires=db_num_rows($result);

         // $msg_dest="$msg_dest_nom $msg_dest_prenom";
         $msg_dest="";
         $_SESSION["to"]="";
         $corps="\r\n";
         $sujet=isset($formation_txt) ? "[$formation_txt]" : "";

         $now=time();
         $_SESSION["absences"]=array();

         // R�cup�ration du ou des destinataires
         for($i=0; $i<$rows_destinataires; $i++)
         {
            list($msg_dest_id, $msg_dest_nom, $msg_dest_prenom, $msg_dest_email, $absence_debut, $absence_fin,
                  $absence_msg, $absence_active)=db_fetch_row($result, $i);

            $_SESSION["to"].="$msg_dest_id;";

            // Absences
            if($absence_active=="t" && $now>=$absence_debut && $now<=$absence_fin)
               $_SESSION["absences"][$msg_dest_id]=array("nom" => $msg_dest_nom,
                                                         "prenom" => $msg_dest_prenom,
                                                         "message" => $absence_msg);

            $msg_dest.="$msg_dest_nom $msg_dest_prenom;";
         }

/*
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
                                 $identite, n�(e) le ".date("d/m/Y", $_SESSION["naissance"])." � $_SESSION[lieu_naissance] ($_SESSION[pays_naissance])
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
                           <td class='td-droite'><font class='Texte'>$_SESSION[email]</font></td>
                          </tr>
                          <tr>
                           <td class='td-gauche'><font class='Texte'><strong>Demande du candidat :</strong></font></td>
                           <td class='td-droite'><font class='Texte'>$motif</font></td>
                          </tr>
                          </table><br>\n";

         // Destinataire(s) : scolarit� en fonction de la formation choisie

         $array_dests=array();

         $res_admins=db_query($dbr,"SELECT $_DBC_acces_id FROM $_DB_acces WHERE $_DBC_acces_niveau='$__LVL_ADMIN'");

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
         $corps_message=preg_replace("/[[:space:]]+/", " ", ereg_replace("[\r]*[\n]+","", $corps_message));

         $sujet_message="ASSISTANCE : D�verrouillage - $identite";

         write_msg_2($dbr, array("id" => "0", "nom" => "Syst�me", "prenom" => "", "src_type" => "gestion", "composante" => "", "universite" => "$__SIGNATURE_COURRIELS"),
                     $array_dests, $sujet_message,$corps_message);

         $succes=1;

         // write_evt("", $__EVT_ID_C_ID, "MAJ Identit�", $candidat_id, $candidat_id, ereg_replace("[']+","''", stripslashes($requete)));
         // db_close($dbr);
*/
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
   
   en_tete_candidat();
   menu_sup_simple();
?>

<div class='main'>
   <?php
      titre_page_icone("Contacter la scolarit�", "mail_send_32x32_fond.png", 15, "L");

      if(isset($erreur_demande))
         message("Formulaire incomplet: vous devez compl�ter le champ \"Demande\"", $__ERREUR);

      if(isset($erreur_formation))
         message("Erreur : vous devez s�lectionner la formation concern�e par votre demande (ou \"Message g�n�ral...\").", $__ERREUR);

      if(isset($erreur_composante))
         message("Erreur : vous devez s�lectionner la composante concern�e par votre message.", $__ERREUR);

      if(isset($succes))
      {
         message("Votre message a �t� envoy� � la scolarit�.", $__SUCCES);

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

   <!-- <form action="<?php print("$php_self"); ?>" method="POST"> -->

   <?php
      message("Le Service de Scolarit� est le mieux plac� pour vous renseigner. Merci de compl�ter et valider le formulaire ci-dessous.", $__INFO);
   ?>

   <table align='center'>
   <tr>
      <td class='td-complet fond_menu2' style='padding:4px;' colspan='2'>
         <font class='Texte_menu2'><strong>Vous</strong></font>
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
      <td class='td-complet fond_menu2' style='padding:4px;' colspan='2'>
         <font class='Texte_menu2'><strong>Votre requ�te</strong></font>
      </td>
   </tr>
   <?php
      if(!isset($_SESSION["form_composante_id"]))
      {
   ?>
   <form action="<?php print("$php_self"); ?>" method="POST"> 
   <tr>
      <td class='td-gauche fond_menu2'>
         <font class='Texte_important_menu2'><strong>Etape 1 :<br>Composante (scolarit�) concern�e par votre demande :</strong></font>
      </td>
      <td class='td-droite fond_menu'>
         <select name="comp_id">
         <?php
            $res_composantes=db_query($dbr,"SELECT $_DBC_composantes_id, $_DBC_composantes_nom FROM $_DB_composantes
                                            ORDER BY $_DBC_composantes_nom");

            $rows_composantes=db_num_rows($res_composantes);

            if($rows_composantes)
            {
               for($c=0; $c<$rows_composantes; $c++)
               {
                  list($composante_id, $composante_nom)=db_fetch_row($res_composantes, $c);

                  $selected=isset($_SESSION["comp_id"]) && $_SESSION["comp_id"]==$composante_id ? "selected" : "";

                  print("<option value='$composante_id'>$composante_nom</option>\n");
               }
            }

            db_free_result($res_composantes);
         ?>
         </select>
      </td>
   </tr>
   </table>

   <div class='centered_icons_box'>
      <a href='index.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Retour' border='0'></a>
      <input type='image' src='<?php echo "$__ICON_DIR/forward_32x32_fond.png" ?>' alt='Suivant' name='Suivant' value='Suivant'>
   </div>

   <?php
      }
      else
      {
   ?>
   <form action="<?php echo "$__CAND_MSG_DIR/compose.php"; ?>" method="POST">
   <tr>
      <td class='td-gauche fond_menu2'>
         <font class='Texte_menu2'><strong>Composante (scolarit�) s�lectionn�e :</strong></font>
      </td>
      <td class='td-droite fond_menu'>
         <?php
            $res_composantes=db_query($dbr,"SELECT $_DBC_composantes_nom FROM $_DB_composantes
                                            WHERE $_DBC_composantes_id='$_SESSION[form_composante_id]'");

            if(db_num_rows($res_composantes))
               list($composante_nom)=db_fetch_row($res_composantes, 0);
            else
               $composante_nom="";

              db_free_result($res_composantes);
         ?>
         <font class='Texte'><strong><?php echo $composante_nom; ?></strong></font>
      </td>
   </tr>
      <tr>
      <td class='td-gauche fond_menu2'>
         <font class='Texte_important_menu2'><strong>Etape 2 :<br>Formation concern�e par votre demande :</strong></font>
      </td>
      <td class='td-droite fond_menu'>
         <select name='formation'>
            <option value='' disabled=1></option>
            <option value='0'>Message g�n�ral ou formation hors liste de cet �tablissement</option>
         <?php
            $result=db_query($dbr,"SELECT $_DBC_propspec_id, $_DBC_annees_id, $_DBC_annees_annee, $_DBC_specs_nom,
                                          $_DBC_specs_mention_id, $_DBC_mentions_nom, $_DBC_propspec_finalite
                                       FROM $_DB_propspec, $_DB_annees, $_DB_specs, $_DB_mentions
                                    WHERE $_DBC_propspec_id_spec=$_DBC_specs_id
                                    AND $_DBC_propspec_annee=$_DBC_annees_id
                                    AND $_DBC_propspec_comp_id='$_SESSION[form_composante_id]'
                                    AND $_DBC_mentions_id=$_DBC_specs_mention_id
                                    AND $_DBC_propspec_active='1'
                                    AND $_DBC_propspec_manuelle='0'
                                       ORDER BY $_DBC_annees_ordre, $_DBC_specs_mention_id, $_DBC_specs_nom");
            $rows=db_num_rows($result);

            // variables initialis�es � n'importe quoi
            $prev_annee_id="--";
            $prev_mention="";

            if($rows)
            {
               for($i=0; $i<$rows; $i++)
               {
                  list($propspec_id, $annee_id, $annee, $nom, $mention, $mention_nom, $finalite)=db_fetch_row($result,$i);

                  $nom_finalite=$tab_finalite[$finalite];

                  if($annee_id!=$prev_annee_id)
                  {
                     if($i!=0)
                        print("</optgroup>\n");

                     if($annee=="")
                        $annee="Ann�es particuli�res";

                     print("<option value='' disabled=1></option>
                              <optgroup label='-------------- $annee -------------- '>
                              <optgroup label='$mention_nom'>\n");

                     $prev_annee_id=$annee_id;
                  }
                  elseif($prev_mention!=$mention)
                  {
                     print("<option value='' disabled=1></option>
                              <optgroup label='$mention_nom'>\n");
                  }

                  if(isset($candidature) && $candidature==$propspec_id)
                     $selected="selected=1";
                  else
                     $selected="";

                  if($annee=="Ann�es particuli�res")
                     print("<option value='$propspec_id' label=\"$nom $nom_finalite\" $selected>$nom $nom_finalite</option>\n");
                  else
                     print("<option value='$propspec_id' label=\"$annee $nom $nom_finalite\" $selected>$annee $nom $nom_finalite</option>\n");

                  $prev_mention=$mention;
               }
            }
         ?>
         </select>
      </td>
   </tr>
<!--
   <tr>
      <td class='td-gauche fond_menu2'>
         <font class='Texte_important_menu2'><strong>Votre demande � la scolarit� :</strong></font>
      </td>
      <td class='td-droite fond_menu'>
         <textarea name='demande' cols="50" rows="7"><?php if(isset($demande)) echo htmlspecialchars(stripslashes($demande), ENT_QUOTES, $default_htmlspecialchars_encoding); ?></textarea>
      </td>
   </tr>
-->
   </table>

   <div class='centered_icons_box'>
      <a href='index.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Retour' border='0'></a>
      <input type='image' src='<?php echo "$__ICON_DIR/forward_32x32_fond.png" ?>' alt='Suivant' name='Suivant' value='Suivant'>
   </div>

   <?php
         }
         print("</form>");
      }
   ?>
</div>
<?php
   db_close($dbr);

   pied_de_page_simple();
?>
</body>
</html>

