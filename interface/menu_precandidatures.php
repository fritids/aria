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
   if(!isset($_SESSION["authentifie"]))
   {
      session_write_close();
      header("Location:../index.php");
      exit();
   }

   if(!isset($_SESSION["comp_id"]) || (isset($_SESSION["comp_id"]) && $_SESSION["comp_id"]==""))
   {
      session_write_close();
      header("Location:composantes.php");
      exit();
   }

   print("<div class='centered_box'>
            <font class='TitrePage_16'>$_SESSION[onglet] - Vos pr�candidatures - </font><font class='Texte_important_16'>Tri�es par ordre de pr�f�rence d�croissant</font>
         </div>\n");

   // On a besoin du nombre de candidatures d�j� d�pos�e pour l'ann�e courante, pour v�rifier si la
   // limite n'est pas atteinte
   // (une candidature � choix multiples compte comme une seule candidature)
   $result_periode=db_query($dbr,"SELECT max($_DBC_cand_ordre) FROM $_DB_cand, $_DB_propspec
                                    WHERE $_DBC_cand_candidat_id='$candidat_id'
                                    AND $_DBC_cand_propspec_id=$_DBC_propspec_id
                                    AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                                    AND $_DBC_cand_periode='$__PERIODE'");

   // on aura un r�sultat, m�me vide
   list($nb_cand_periode_actuelle)=db_fetch_row($result_periode,0);
   db_free_result($result_periode);

   $nb_cand_periode_actuelle=$nb_cand_periode_actuelle=="" ? 0 : $nb_cand_periode_actuelle;

   // Limite du nombre de candidatures (en fonction des param�tres de la composante)

   if($_SESSION["limite_nombre"]!=0)
   {
      if($nb_cand_periode_actuelle<$_SESSION["limite_nombre"])
      {
         $nb_cand_ajoutables=$_SESSION["limite_nombre"]-$nb_cand_periode_actuelle;

         // On ne peut ajouter une pr�candidature que si le cursus a �t� rempli
         if(!db_num_rows(db_query($dbr,"SELECT * FROM $_DB_cursus WHERE $_DBC_cursus_candidat_id='$candidat_id'")))
            message("Vous devez compl�ter votre <strong>cursus</strong> (onglet 2 dans le menu gauche) avant d'ajouter une pr�candidature.", $__WARNING);
         else
         {
            $date_courante=time();

            if(db_num_rows(db_query($dbr, "SELECT * FROM $_DB_propspec, $_DB_session
                                             WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                                             AND $_DBC_propspec_id=$_DBC_session_propspec_id
                                             AND $_DBC_session_ouverture<='$date_courante'
                                             AND $_DBC_session_fermeture>='$date_courante'")))
            {
               print("<div class='centered_box'>
                        <a href='ajout_candidature.php' target='_self' class='lien2'><img class='icone' src='$__ICON_DIR/add_22x22_fond.png' border='0' alt='Ajouter' desc='Ajouter'></a>
                        <a href='ajout_candidature.php' target='_self' class='lien2'>Ajouter une pr�candidature (vous pouvez encore en ajouter $nb_cand_ajoutables)</a>
                     </div>\n");
            }
            else
               message("Les formations pour cette composante ne sont pas ou plus ouvertes.", $__INFO);
         }
      }
      else
      {
         $cand_actives=$_SESSION["limite_nombre"];

         message("Vous ne pouvez plus ajouter de pr�candidatures dans cette composante (maximum : $cand_actives)", $__ERREUR);
      }
   }
   else // aucune limite de pr�candidatures
   {
      $date_courante=time();

      if(!db_num_rows(db_query($dbr,"SELECT * FROM $_DB_cursus WHERE $_DBC_cursus_candidat_id='$candidat_id'")))
         message("Vous devez compl�ter votre <strong>cursus</strong> (onglet 2) avant d'ajouter une pr�candidature.", $__WARNING);

      elseif(db_num_rows(db_query($dbr, "SELECT * FROM $_DB_propspec, $_DB_session
                                          WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                                          AND $_DBC_propspec_id=$_DBC_session_propspec_id
                                          AND $_DBC_session_ouverture<='$date_courante'
                                          AND $_DBC_session_fermeture>='$date_courante'")))
      {
         print("<div class='centered_box'>
                  <a href='ajout_candidature.php' target='_self' class='lien2'><img class='icone' src='$__ICON_DIR/add_22x22_fond.png' border='0' alt='Ajouter' desc='Ajouter' style='vertical-align:middle'></a>
                  <a href='ajout_candidature.php' target='_self' class='lien2'>Ajouter une pr�candidature</a>
                </div>\n");
      }
      else
         message("Les formations pour cette composante ne sont pas ou plus ouvertes.", $__INFO);
   }

   $crypt_params=crypt_params("comp_id=$_SESSION[comp_id]");

   print("<div class='centered_box'>
            <a class='lien2' href='$__DOC_DIR/limites.php?p=$crypt_params' target='_blank'><img class='icone' src='$__ICON_DIR/clock_32x32_fond.png' border='0' alt='Dates' desc='Dates' style='vertical-align:middle;'></a>
            <a href='$__DOC_DIR/limites.php?p=$crypt_params' target='_blank' class='lien2'>Voir les DATES LIMITES des formations propos�es par cette composante</a>
         </div>\n");

?>

<table style='margin:0px auto 0px auto; padding-bottom:20px;'>

<?php
   // candidatures
   $result=db_query($dbr,"SELECT $_DBC_cand_id, $_DBC_cand_periode, $_DBC_annees_annee, $_DBC_annees_annee_longue, $_DBC_specs_nom,
                                 $_DBC_cand_motivation_decision, $_DBC_cand_statut, $_DBC_cand_ordre_spec, $_DBC_cand_groupe_spec,
                                 $_DBC_cand_ordre, $_DBC_decisions_id, $_DBC_decisions_texte, $_DBC_cand_liste_attente,
                                 $_DBC_cand_transmission_dossier, $_DBC_cand_recours, $_DBC_cand_vap_flag, $_DBC_cand_talon_reponse,
                                 $_DBC_propspec_id, $_DBC_propspec_finalite, $_DBC_propspec_frais, $_DBC_cand_statut_frais,
                                 $_DBC_session_id, $_DBC_session_reception, $_DBC_cand_lock, $_DBC_cand_lockdate,
                                 $_DBC_propspec_affichage_decisions, $_DBC_cand_entretien_date,
                                 $_DBC_cand_entretien_heure, $_DBC_cand_entretien_lieu, $_DBC_cand_entretien_salle
                              FROM $_DB_cand, $_DB_specs, $_DB_annees, $_DB_decisions, $_DB_propspec, $_DB_session
                           WHERE $_DBC_cand_candidat_id='$candidat_id'
                           AND $_DBC_propspec_id=$_DBC_session_propspec_id
                           AND $_DBC_propspec_annee=$_DBC_annees_id
                           AND $_DBC_propspec_id_spec=$_DBC_specs_id
                           AND $_DBC_cand_decision=$_DBC_decisions_id
                           AND $_DBC_cand_propspec_id=$_DBC_propspec_id
                           AND $_DBC_cand_session_id=$_DBC_session_id
                           AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                           AND $_DBC_cand_periode=$_DBC_session_periode
                              ORDER BY $_DBC_cand_periode DESC, $_DBC_cand_ordre ASC, $_DBC_cand_ordre_spec ASC");

                           // AND $_DBC_cand_periode='$__PERIODE'
                           // AND $_DBC_session_periode='$__PERIODE'
   $rows=db_num_rows($result);

   // compteur pour le calcul des frais
   $total_frais_dossiers=0;

/*
   if($_SESSION["lock"]!=1) // Fiche non verrouill�e (globalement)
   {
*/
      // Colonnes
      // 1 : supprimer une candidature
      // 2 : fl�ches pour r�ordonner les candidatures � choix multiples 
      // 3 : Nom de la formation (sp�cialit�) et (en dessous) Statut de la pr�candidature 
      // 4 : Frais de dossiers

      $colspan_global=4;   
      $colspan_annee="colspan='2'";
      $td_class="td-milieu";
/*
   }
   else // Fiche verrouill�e globalement
   {
      // Colonnes
      // 1 : Nom de la formation (sp�cialit�) et (en dessous) Statut de la pr�candidature
      // 2 : Frais de dossiers

      $colspan_global=3;
      $colspan_annee="";
      $td_class="td-gauche";
   }
*/
   $old_groupe_spec=-1; // initialisation � une valeur n�gative (positive = num�ro de groupe)

   $old_periode="--";

   // ================================
   //    Boucle sur les candidatures
   // ================================

   for($i=0; $i<$rows; $i++)
   {
      list($cand_id, $cand_periode, $annee_courte, $annee_longue, $nom_specialite, $motivation_decision,$statut,$ordre_spec, $groupe_spec, $ordre,
            $decision_id, $decision_texte, $rang_liste_attente, $transmission_dossier, $recours,$vap, $talon_reponse, $propspec_id,
            $finalite, $frais_dossiers, $statut_frais, $session_id, $limite_reception, $cand_lock, $cand_lockdate, $affichage_decisions,
            $ent_date, $ent_heure, $ent_lieu, $ent_salle)=db_fetch_row($result,$i);

      // D�termination de la session de candidature
      $res_session=db_query($dbr, "SELECT $_DBC_session_id FROM $_DB_session
                                   WHERE $_DBC_session_propspec_id='$propspec_id'
                                   AND $_DBC_session_periode='$__PERIODE'
                                   ORDER BY $_DBC_session_ouverture, $_DBC_session_fermeture");

      $nb_sessions=db_num_rows($res_session);

      if($nb_sessions)
      {
         $array_sessions=db_fetch_all($res_session);
         $session_num=array_search(array("id" => $session_id), $array_sessions);

         if($session_num!==FALSE)
            $session_num="Session " . ($session_num+1);
         else
            $session_num="Session : inconnue";
/*
         else // Probl�me : aucune session d�finie pour cette candidature
              // TODO 2008 : �crire une fonction pour envoyer un mail d'erreur � l'administrateur
*/         
      }
      else
         $session_num="Session : inconnue";

      db_free_result($res_session);                              

      $vap_flag=$vap ? "<strong>VAP/VAE</strong> " : "";

      $derniere_candidature=($i==($rows-1)) ? 1 : 0;

      $choix_multiples_txt="";

      $unique=1;

      // si groupe_spec est >= 0, on a une candidature � choix multiples : il faut afficher l'ordre diff�remment
      // (en d�finissant un rowspan dans le tableau)
      // Note 1 : on n'effectue la requ�te qu'une fois
      // Note 2 : s'il n'y a qu'une candidature dans ce groupe, on ne met pas l'ordre (il faut aller chercher groupe_spec
      // du r�sultat suivant dans la requete)

      // Candidature � choix multiples
      if($groupe_spec>=0)
      {
         // dates communes pour tout le groupe ?
         
         $res_options_groupe=db_query($dbr, "SELECT $_DBC_groupes_spec_dates_communes 
                                                FROM $_DB_groupes_spec 
                                             WHERE $_DBU_groupes_spec_groupe='$groupe_spec' 
                                             AND $_DBC_groupes_spec_propspec_id='$propspec_id'");
                                             
         if(db_num_rows($res_options_groupe))
         {
            list($dates_communes)=db_fetch_row($res_options_groupe, 0);
            
            if($dates_communes!='t' && $dates_communes!='f')
               $dates_communes='f';
         }
         else
            $dates_communes='f';
         
         if(!$derniere_candidature) // on regarde le groupe de la pr�candidature suivante, s'il y en a une
         {
            // ATTENTION : EN CAS DE MODIFICATION DE LA REQUETE, LE RANG PEUT CHANGER (todo : am�liorer ce syst�me en utilisant les noms des colonnes)
            list($next_groupe_spec)=db_fetch_result($result, ($i+1), 8);

            // La candidature suivante est dans le m�me groupe : on n'affiche pas le bord inf�rieur
            if($next_groupe_spec==$groupe_spec)
            {
               $choix_multiples_txt="- Candidature � choix multiples";
               $colspan_suppr="";
               $unique=0;
            }
         }
         else // toute derni�re candidature
            $next_groupe_spec="-1";

         // Par rapport � la candidature pr�c�dente :
         if($groupe_spec==$old_groupe_spec) // m�me groupe
         {
            $nouveau_groupe=0;
            $unique=0;

            // On affiche l'ordre de la pr�candidature au sein du groupe
            $ordre_spec_txt="$ordre_spec - ";

            $choix_multiples_txt="- Candidature � choix multiples";

            $colspan_suppr="";
         }
         elseif(!$derniere_candidature) // nouveau groupe et pas la derni�re candidature
         {
            $nouveau_groupe=1;

            $result2=db_query($dbr,"SELECT $_DBC_cand_statut FROM $_DB_cand, $_DB_propspec
                                    WHERE $_DBC_cand_candidat_id='$candidat_id'
                                    AND $_DBC_cand_propspec_id=$_DBC_propspec_id
                                    AND $_DBC_propspec_comp_id=$_SESSION[comp_id]
                                    AND $_DBC_cand_groupe_spec='$groupe_spec'
                                    AND $_DBC_cand_periode='$__PERIODE'");

            $nb_choix=db_num_rows($result2);

            // Parmi les candidatures dans ce groupe, on regarde si la recevabilit� de l'une d'elles a �t� trait�e
            // Si oui : on affichera individuellement les status, pour chaque voeux du groupe
            // Si non : on affichera un statut global (en t�te du tableau) invitant le candidat � envoyer les documents

            $une_recevabilite=FALSE;

            for($r=0; $r<$nb_choix; $r++)
            {
               list($r_statut)=db_fetch_row($result2, $r);

               if($r_statut!=$__PREC_NON_TRAITEE)
                  $une_recevabilite=TRUE;
            }

            // Ordre global : la taille (en nombre de lignes de tableau) d�pend du nombre de candidatures dans ce groupe
            $rowspan_ordre_global=3*$nb_choix;

            db_free_result($result2);

            if($next_groupe_spec==$groupe_spec)
            {
               $ordre_spec_txt="$ordre_spec - ";
               $colspan_suppr="";
               $unique=0;
            }
            else // candidature � choix multiples, mais isol�e
            {
               $ordre_spec_txt="";
               $colspan_suppr="colspan='2'";
            }

            // On cr�e un espace entre la nouvelle pr�candidature et la pr�c�dente
            if($i!=0)
               print("<tr>
                        <td class='fond_page' colspan='$colspan_global' style='height:10px;'></td>
                     </tr>\n");
         }
         else // nouveau groupe et derni�re candidature : consid�r� comme une candidature normale
         {
            $nouveau_groupe=1;
            $colspan_suppr="colspan='2'";
            $ordre_spec_txt="";
            $une_recevabilite=TRUE; // force l'affichage individuel

            if($i!=0)
               print("<tr>
                        <td class='fond_page' colspan='$colspan_global' style='height:10px;'></td>
                     </tr>\n");
         }
      }
      else // Groupe = -1 : candidature � choix unique
      {
         $nouveau_groupe=1;
         $colspan_suppr="colspan='2'";
         $rowspan_ordre_global=3;

         // Pas d'affichage de l'ordre de la sp�cialit�
         $ordre_spec_txt="";

         // On cr�e un espace entre la nouvelle pr�candidature et la pr�c�dente
         if($i!=0)
            print("<tr>
                     <td class='fond_page' colspan='$colspan_global' style='height:10px;'></td>
                  </tr>\n");
      }

      if($cand_lock) // Candidature verrouill�e
      {
         switch($statut)
         {
            case $__PREC_NON_TRAITEE   :   // pr�candidature non trait�e
                                          $font_class='Texte_menu';
                                          $limite_reception_txt=date_fr("j F Y", $limite_reception);
                                          $statut_txt="En attente des justificatifs
                                                       <br>
                                                       <font class='Texte_important_menu'>
                                                         <b>Sauf consigne contraire de cette scolarit�, vous devez faire parvenir les pi�ces demand�es avant le $limite_reception_txt</b>
                                                       </font>";
                                          $crypt_params=crypt_params("cand_id=$cand_id&groupe=$groupe_spec&ordre_spec=$ordre_spec&annuler=1");
                                          $lien_suppr="<a href='suppr_cand.php?p=$crypt_params' class='lien_rouge12'><img src='$__ICON_DIR/trashcan_full_16x16_slick_menu.png' alt='Annuler' border='0'></a>" ;
                                          $motivation_rec_txt="";
                                          break;

            case $__PREC_PLEIN_DROIT   :   // entr�e de plein droit
                                          $font_class='Textevert_menu';
                                          $statut_txt="Vous entrez de plein droit dans cette formation";
                                          $crypt_params=crypt_params("cand_id=$cand_id&groupe=$groupe_spec&ordre_spec=$ordre_spec&annuler=1");
                                          // $lien_suppr="<a href='suppr_cand.php?p=$crypt_params' class='lien_rouge12'><img src='$__ICON_DIR/trashcan_full_16x16_slick_menu.png' alt='Annuler' border='0'></a>" ;
                                          $lien_suppr="";
                                          $motivation_rec_txt="";
                                          break;

            case $__PREC_RECEVABLE   :      // pr�candidature recevable
                                          $font_class='Textevert_menu';
                                          $statut_txt="Dossier complet (justificatifs valid�s)";
                                          $crypt_params=crypt_params("cand_id=$cand_id&groupe=$groupe_spec&ordre_spec=$ordre_spec&annuler=1");
                                          
                                          if($decision_id=="$__DOSSIER_NON_TRAITE")
                                             $lien_suppr="<a href='suppr_cand.php?p=$crypt_params' class='lien_rouge12'><img src='$__ICON_DIR/trashcan_full_16x16_slick_menu.png' alt='Annuler' border='0'></a>" ;
                                          else
                                             $lien_suppr="";
                                                     
                                          $motivation_rec_txt="";
                                          break;

            case $__PREC_EN_ATTENTE   :      // pr�candidature en attente
                                          $font_class='Texteorange';
                                          $statut_txt="Pr�candidature en attente";
                                          $crypt_params=crypt_params("cand_id=$cand_id&groupe=$groupe_spec&ordre_spec=$ordre_spec&annuler=1");                                         
                                          $lien_suppr="<a href='suppr_cand.php?p=$crypt_params' class='lien_rouge12'><img src='$__ICON_DIR/trashcan_full_16x16_slick_menu.png' alt='Annuler' border='0'></a>" ;
                                          $motivation_rec_txt=$motivation_decision=="" ? "" : "(Motivation : " . htmlspecialchars(stripslashes($motivation_decision), ENT_QUOTES, $default_htmlspecialchars_encoding) . ")";
                                          break;

            case $__PREC_NON_RECEVABLE   :   // pr�candidature non recevable
                                          $font_class='Texte_important_menu';
                                          $statut_txt="Pr�candidature non recevable";
                                          $lien_suppr="";
                                          $motivation_rec_txt=$motivation_decision=="" ? "" : "(Motivation : " . htmlspecialchars(stripslashes($motivation_decision), ENT_QUOTES, $default_htmlspecialchars_encoding) . ")";
                                          break;

            case $__PREC_ANNULEE   :      // pr�candidature annul�e
                                          $font_class='Textegris';
                                          $statut_txt="Annul�e";
                                          $lien_suppr="";
                                          $motivation_rec_txt="";
                                          break;

            default   :   // par d�faut : pr�candidature non trait�e
                        $font_class='Texte_menu';
                        $limite_reception_txt=date_fr("j F Y", $limite_reception);
                        $statut_txt="En attente des justificatifs
                                     <br>
                                     <font class='Texte_important_menu'>
                                       <b>Sauf consigne contraire de cette scolarit�, vous devez faire parvenir les pi�ces demand�es le plus rapidement possible (<u>au plus tard le $limite_reception_txt</u>)</b>
                                     </font>";
                        $crypt_params=crypt_params("cand_id=$cand_id&groupe=$groupe_spec&ordre_spec=$ordre_spec&suppr=1");
                        $lien_suppr="<a href='suppr_cand.php?p=$crypt_params' class='lien_rouge12'><img src='$__ICON_DIR/trashcan_full_16x16_slick_menu.png' alt='Supprimer' border='0'></a>" ;
                        break;
         }
      }
      else
      {
         // pr�candidature non verrouill�e : si la d�cision a d�j� �t� prise (d�verrouillage post-d�cision), la suppression n'est plus possible
         
         $font_class='Texte_menu';
         $lockdate_txt=date_fr("j F Y", $cand_lockdate);
         $statut_txt="Verrouillage le $lockdate_txt<br>La liste des justificatifs vous sera envoy�e � cette date.";
         $motivation_rec_txt="";
                  
         if($decision_id==$__DOSSIER_NON_TRAITE)
         {
            $crypt_params=crypt_params("cand_id=$cand_id&groupe=$groupe_spec&ordre_spec=$ordre_spec&suppr=1");
            $lien_suppr="<a href='suppr_cand.php?p=$crypt_params' class='lien_rouge12'><img src='$__ICON_DIR/trashcan_full_16x16_slick_menu.png' alt='Supprimer' border='0'></a>" ;
         }
         else
            $lien_suppr="";
      }

      // PERIODE
      if($cand_periode!=$old_periode)
      {
      
         if($cand_periode>=$__PERIODE)
         {
            $texte_voeux="Vos voeux pour l'ann�e $cand_periode-".($cand_periode+1);
            $couleur_classe_fond="fond_menu";
            $couleur_classe_fond2="fond_menu2";
            $tr_sup="";
            
            $force_publication=0;
         }
         else
         {
            $texte_voeux="Pour information : vos pr�c�dents voeux pour l'ann�e $cand_periode-".($cand_periode+1);
            $couleur_classe_fond="fond_gris_E";
            $couleur_classe_fond2="fond_gris_C";
            
            $tr_sup=($old_periode==$__PERIODE) ? "<tr><td class='fond_page' colspan='$colspan_global' height='20px'></td></tr>" : "";
            
            $force_publication=1;
         }

         print("$tr_sup
                <tr>
                  <td class='td-complet $couleur_classe_fond2' colspan='$colspan_global'>
                     <font class='Texte'>
                        <strong>$texte_voeux</strong>
                     </font>
                  </td>
                </tr>\n");

         $old_periode=$cand_periode;

         // Changement de p�riode : on calcule le nombre de voeux d�pos�s pour celle-ci (pour les fl�ches)
         // (une candidature � choix multiples compte comme une seule candidature)
         $result_periode=db_query($dbr,"SELECT max($_DBC_cand_ordre) FROM $_DB_cand, $_DB_propspec
                                          WHERE $_DBC_cand_candidat_id='$candidat_id'
                                          AND $_DBC_cand_propspec_id=$_DBC_propspec_id
                                          AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                                          AND $_DBC_cand_periode='$cand_periode'");

         // on aura un r�sultat, m�me vide
         list($nb_cand)=db_fetch_row($result_periode,0);
         db_free_result($result_periode);

         $nb_cand=$nb_cand=="" ? 0 : $nb_cand;
      }

      // S�lection de la p�riode suivante pour les fl�ches
      if(($i+1)<$rows)
      {
         $res_array=db_fetch_array($result, ($i+1), PGSQL_ASSOC);
         $next_periode=$res_array["periode"];
      }
      else
         $next_periode="--";

      // On commence � remplir la ligne du tableau

      // ===================================
      //          Ann�e et ordre global
      // ===================================
      if($nouveau_groupe)
      {
         print("<tr>
                  <td class='td-gauche $couleur_classe_fond2'>
                     <font class='Texte_menu2'>");

         if(!$cand_lock)
         {
            $crypt_params=crypt_params("cand_id=$cand_id");

            if($ordre>1)
               print("<a href='cand_up.php?p=$crypt_params' target='_self' class='lien2' style='vertical-align:middle'><img style='vertical-align:middle' src='$__ICON_DIR/up_16x16_menu2.png' alt='Monter' border='0'></a> \n");

            if($ordre<$nb_cand && $next_periode==$cand_periode)
               print("<a href='cand_down.php?p=$crypt_params' target='_self' class='lien2' style='vertical-align:middle'><img style='vertical-align:middle' src='$__ICON_DIR/down_16x16_menu2.png' alt='Descendre' border='0'></a>\n");
         }

         print("</font>
               </td>
               <td class='$td_class $couleur_classe_fond2' $colspan_annee>
                  <font class='Texte_menu2'>
                     <b>Choix n�$ordre - $annee_longue</b> $choix_multiples_txt <b>- $session_num</b></font>
                  </font>
               </td>
               <td class='td-gauche fond_page'></td>
            </tr>\n");
      }

      // ========================
      //       Sp�cialit�
      // ========================

      // Candidature � choix multiples non verrouill�e : on affiche les infos de verrouillage une seule fois, au dessus
      // cas particulier : si les dates limites sont distinctes, on les affiche individuellement
      if(!$unique && $groupe_spec!=-1 && (isset($dates_communes) && $dates_communes=="t") && $nouveau_groupe && (!$cand_lock || ($cand_lock && isset($une_recevabilite) && $une_recevabilite==FALSE)))
      {
         print("<tr>
                  <td class='td-gauche $couleur_classe_fond' width='20px' style='vertical-align:middle;'></td>
                  <td class='$td_class $couleur_classe_fond' colspan='2'>
                     <font class='Texte_menu'>Statut : </font><font class='$font_class'>$statut_txt $motivation_rec_txt</font>
                  </td>
                  <td class='td-droite $couleur_classe_fond'>
                     <font class='$font_class'>\n");

         if($frais_dossiers)
         {
            switch($statut_frais)
            {
               case 0   :   // vide (en attente)
                           print("<b>Frais</b> : En attente");
                           break;

               case 1   :   // frais pay�s
                           print("<b>Frais</b> : Acquitt�s");
                           break;

               case 2   :   // Candidat Boursier
                           print("<b>Frais</b> : Candidat boursier");
                           break;

               case -1   :   // non pay�s
                           print("<b>Frais</b> : Non acquitt�s");
                           break;

               default :    // vide
                           print("<b>Frais</b> : En attente");
                           break;
            }
         }

         print("</font>
               </td>
            </tr>\n");
      }

      // Suppression / Annulation 

      $rowspan_suppr=($unique || $groupe_spec==-1 || (isset($une_recevabilite) && $une_recevabilite==TRUE)) ? "3" : "2";
   
      print("<tr>
               <td class='td-gauche $couleur_classe_fond' rowspan='$rowspan_suppr' width='20px' style='vertical-align:middle;' $colspan_suppr>
                  $lien_suppr
               </td>\n");

      // Groupe � choix multiples (avec plusieurs choix) ? => fl�ches
      if($groupe_spec!=-1 && ((isset($next_groupe_spec) && $next_groupe_spec==$groupe_spec) || (isset($old_groupe_spec) && $old_groupe_spec==$groupe_spec)))
      {
         print("<td class='td-milieu $couleur_classe_fond' rowspan='$rowspan_suppr' nowrap='true' style='vertical-align:middle; white-space:nowrap;'>
                  <font class='Texte_menu'>\n");

         if(!$cand_lock)
         {
            $crypt_params2=crypt_params("cand_id=$cand_id&groupe=$groupe_spec");

            if($ordre_spec!=1)
               print("<a href='cand_up.php?p=$crypt_params2' target='_self' class='lien2'><img src='$__ICON_DIR/up_16x16_menu.png' alt='Monter' width='16' height='16' border='0'></a> \n");

            if($ordre_spec!=$nb_choix)
               print("<a href='cand_down.php?p=$crypt_params2' target='_self' class='lien2'><img src='$__ICON_DIR/down_16x16_menu.png' alt='Descendre' width='16' height='16' border='0'></a> \n");
         }

         print("</font>
               </td>\n");
      }
      
      $annee=$annee_courte=="" ? "" : $annee_courte;
      
      print("<td class='$td_class $couleur_classe_fond' style='white-space:normal;'>
               <font class='Texte_menu'>
                  <b><u>$ordre_spec_txt$annee $nom_specialite $tab_finalite[$finalite] $vap_flag</u></b>
               </font>
            </td>\n");

      // Affichage des Frais de dossiers
      if($frais_dossiers!="" && $frais_dossiers>0 && $statut!=$__PREC_ANNULEE)
      {
         $frais_dossiers_txt="<font class='$font_class'>Frais : $frais_dossiers eur</font>";
         $total_frais_dossiers+=$frais_dossiers;
      }
      else
         $frais_dossiers_txt="";
   
      print("<td class='td-droite $couleur_classe_fond'>
               $frais_dossiers_txt
            </td>
         </tr>\n");

      // ================================================
      //          RECEVABILITE
      // ================================================

      // Si les formations ne sont pas group�es OU si les formations group�es ont �t� trait�es
      // on affiche la recevabilit� et le statut des frais pour chaque formation
      if($unique || $groupe_spec==-1 || (isset($une_recevabilite) && $une_recevabilite==TRUE))
      {
         print("<tr>
                  <td class='$td_class $couleur_classe_fond'>
                     <font class='Texte_menu'>Statut : </font><font class='$font_class'>$statut_txt $motivation_rec_txt</font>
                  </td>\n");

         // ======================================
         //    Statut des frais de dossiers
         // ======================================

         print("<td class='td-droite $couleur_classe_fond'>
                  <font class='$font_class'>\n");

         if($frais_dossiers)
         {
            switch($statut_frais)
            {
               case 0   :   // vide (en attente)
                           print("<b>Frais</b> : En attente");
                           break;

               case 1   :   // frais pay�s
                           print("<b>Frais</b> : Acquitt�s");
                           break;

               case 2   :   // Candidat Boursier
                           print("<b>Frais</b> : Candidat boursier");
                           break;

               case -1   :   // non pay�s
                           print("<b>Frais</b> : Non acquitt�s");
                           break;

               default :    // vide
                           print("<b>Frais</b> : En attente");
                           break;
            }
         }

         print("</font>
               </td>
            </tr>\n");
      }

      // ===============================================================
      //       SECTION COMPEDA POUR LES PRE-CANDIDATURES RECEVABLES
      // ===============================================================

      if($cand_lock && $statut == $__PREC_RECEVABLE)
      {
         switch($talon_reponse)
         {
            case 0   :   // talon non renvoy� (par d�faut)
                        $talon_txt="";
                        break;

            case 1   :   // talon renvoy�, inscription confirm�e
                        $talon_txt="<br>Talon r�ponse : Admission confirm�e";
                        break;

            case -1   :   // talon renvoy�, inscription refus�e
                        $talon_txt="<br>Talon r�ponse : Admission d�clin�e";
                        break;

            default : // talon non renvoy� (par d�faut)
                        $talon_txt="";
                        break;
         }

         if($decision_id<0) // pour les dossiers n�cessitant encore un traitement
            $font='Texteorange';
         elseif($decision_id>0 && $decision_id!=$__DOSSIER_REFUS && $decision_id!=$__DOSSIER_REFUS_RECOURS && $decision_id!=$__DOSSIER_REFUS_ENTRETIEN) // dossiers trait�s
            $font='Textevert_menu';
         else
            $font='Texte_important_menu';

         if($recours)
            $decision_texte .= " (sur recours)";

         if($decision_id==$__DOSSIER_LISTE || $decision_id==$__DOSSIER_LISTE_ENTRETIEN)
            $rang="- <b>Rang : $rang_liste_attente</b>";
         else
            $rang="";

         if(!empty($motivation_decision))
         {
            $motif_txt="";

            $motif_array=explode("|",$motivation_decision);
            $cnt=count($motif_array);

            for($j=0; $j<$cnt; $j++)
            {
               $motif_id=$motif_array[$j];

               if(is_numeric($motif_id)) // motif provenant de la table motifs_refus
               {
                  $result2=db_query($dbr,"SELECT $_DBC_motifs_refus_motif, $_DBC_motifs_refus_motif_long
                                             FROM $_DB_motifs_refus
                                          WHERE $_DBC_motifs_refus_id='$motif_id'");
                  $rows2=db_num_rows($result2);

                  if($rows2)
                     list($txt,$txt_long)=db_fetch_row($result2,0);
                  else
                     $txt=$txt_long="";

                  db_free_result($result2);
               }
               else // motif libre
               {
                  // nettoyage
                  $txt_long="";
                  // $txt=str_replace("@","",$motif_array[$j]);
                  $txt=preg_replace("/^@/","", $motif_array[$j]);
               }

               if(!empty($txt_long))
                  $txt=$txt_long;

               if(!$j)
                  $motif_txt="<br><b>Motif/D�tails : </b><br>".nl2br($txt);
               else
                  $motif_txt.="<br>".nl2br($txt);
            }
         }
         else
            $motif_txt="";

         // D�cision masqu�e ou publi�e ?
         // Conditions de publication : 1/ Masqu�e par d�faut (composante) et manuellement publi�e
         //                       OU    2/ Publi�e par d�faut (composante)
         //                       OU    3/ D�cision n�cessitant des infos � afficher (entretiens, ...)
         //			  OU    4/ Ann�e universitaire pr�c�dente

         $lien_lettre="";
/*
         if((array_key_exists("affichage_decisions", $_SESSION) && (($_SESSION["affichage_decisions"]==0 && $affichage_decisions) || $_SESSION["affichage_decisions"]==1 || $_SESSION["affichage_decisions"]==2))
            || $decision_id==$__DOSSIER_ENTRETIEN || $decision_id==$__DOSSIER_ENTRETIEN_TEL || $decision_id==$__DOSSIER_EN_ATTENTE || (isset($force_publication) && $force_publication==1))
*/            
         if((array_key_exists("affichage_decisions", $_SESSION) && (($_SESSION["affichage_decisions"]==0 && $affichage_decisions) || $_SESSION["affichage_decisions"]==1 || $_SESSION["affichage_decisions"]==2))
            || $decision_id==$__DOSSIER_ENTRETIEN || $decision_id==$__DOSSIER_ENTRETIEN_TEL || (isset($force_publication) && $force_publication==1))
         {
            $decision_texte_complet="<font class='$font' style='vertical-align:middle;'><b>$decision_texte</b> $rang</font>
                                     <font class='Texte_menu'>";

            if($decision_id==$__DOSSIER_ENTRETIEN || $decision_id==$__DOSSIER_ENTRETIEN_TEL)
            {
               if($ent_date!="" && $ent_date!=0)
                  $ent_date_txt=date_fr("l jS F Y", $ent_date);
               else
                  $ent_date_txt="";

               $ent_heure=date("H", $ent_date);

               if($ent_heure!=0)
               {
                  $ent_minute=date("i", $ent_date);

                  $ent_heure_txt=" � $ent_heure" . "h$ent_minute";
               }
               else
                  $ent_heure_txt="";

               $decision_texte_complet.="<br><b>Date et lieu : </b>$ent_date_txt$ent_heure_txt, $ent_salle<br>$ent_lieu";

               // $decision_texte_complet.="<br><b>Date et lieu : </b>$ent_date, $ent_heure, $ent_salle<br>$ent_lieu";
            }
            elseif($decision_id==$__DOSSIER_EN_ATTENTE || $decision_id==$__DOSSIER_SOUS_RESERVE)
               $decision_texte_complet.=$motif_txt;
            else
               $decision_texte_complet.="$motif_txt
                                         $talon_txt";
            
            $decision_texte_complet.="</font>";
            
            // Lien pour g�n�rer la lettre ?
            
            if((isset($_SESSION["affichage_decisions"]) && $_SESSION["affichage_decisions"]==2) || $affichage_decisions==2)
            {
               if(db_num_rows(db_query($dbr, "SELECT * FROM $_DB_lettres_dec, $_DB_lettres, $_DB_lettres_propspec
                                               WHERE $_DBC_lettres_id=$_DBC_lettres_dec_lettre_id
                                               AND $_DBC_lettres_propspec_lettre_id=$_DBC_lettres_id
                                               AND $_DBC_lettres_propspec_propspec_id='$propspec_id'
                                               AND $_DBC_lettres_comp_id='$_SESSION[comp_id]'
                                               AND $_DBC_lettres_dec_dec_id='$decision_id'"))
                   || (db_num_rows(db_query($dbr, "SELECT * FROM $_DB_lettres_dec, $_DB_lettres
                                                   WHERE $_DBC_lettres_id=$_DBC_lettres_dec_lettre_id
                                                   AND $_DBC_lettres_comp_id='$_SESSION[comp_id]'
                                                   AND $_DBC_lettres_dec_dec_id='$decision_id'
                                                   AND $_DBC_lettres_choix_multiples='1'"))
                      && ($groupe_spec==$old_groupe_spec || $groupe_spec==$old_groupe_spec)
                      && $groupe_spec!=-1))
               {            
                  $param_lettre=crypt_params("cand_id=$cand_id");
                  $lien_lettre="<br><a href='lettres.php?p=$param_lettre' target='_blank' class='lien_menu_gauche'><img style='vertical-align:middle;' src='$__ICON_DIR/player_fwd_16x16_menu.png' border='0'> T�l�charger la lettre de d�cision</a>";
               }
               else // pas de lettre : on affiche un texte g�n�rique
                   $lien_lettre="<br><font class='Texte'><i>Lettre de d�cision actuellement indisponible</i></font>";
            }
         }
         else
            $decision_texte_complet="<font class='Texte_menu'><i><b>En attente de publication</b></i></font>";

         print("<tr>
                  <td class='$td_class $couleur_classe_fond' style='vertical-align:top; padding-bottom:15px;'>
                     <font class='Texte_menu'>D�cision de la Commission P�dagogique : </font>
                     $decision_texte_complet
                     $lien_lettre
                  </td>
                  <td class='td-droite $couleur_classe_fond'></td>
               </tr>\n");
      }
      else
      {
         print("<tr>
                  <td class='$td_class $couleur_classe_fond' style='padding-bottom:15px;'>\n");

         if($statut==$__PREC_RECEVABLE && $decision_id!=$__DOSSIER_NON_TRAITE)
            print("<font class='Texte_menu'>
                     Commission : $decision_texte
                     </font>\n");

         print("</td>
                <td class='td-droite $couleur_classe_fond' style='padding-bottom:15px;'></td>
              </tr>\n");
      }
      $old_groupe_spec=$groupe_spec;
   } // fin de la boucle sur les candidatures

//   if($_SESSION["lock"]==1 && $total_frais_dossiers)
   if($total_frais_dossiers)
      print("<tr>
               <td colspan='2'></td>
               <td colspan='2' style='padding-right:20px; padding-left:10px;'>
                  <font class='Texte'><b>Total : $total_frais_dossiers euros</b></font>
               </td>
            </tr>\n");
/*
   elseif($total_frais_dossiers)
      print("<tr>
               <td></td>
               <td style='padding-right:20px; padding-left:10px;' colspan='2'>
                  <font class='Texte'><b>Total : $total_frais_dossiers euros</b></font>
               </td>
            </tr>\n");
*/
   print("</table>\n");
?>
