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

   if(is_file("../configuration/aria_config.php")) include "../configuration/aria_config.php";
   else die("Fichier \"configuration/aria_config.php\" non trouv�");

   if(is_file("../include/vars.php")) include "../include/vars.php";
   else die("Fichier \"include/vars.php\" non trouv�");

   if(is_file("../include/fonctions.php")) include "../include/fonctions.php";
   else die("Fichier \"include/fonctions.php\" non trouv�");

   if(is_file("../include/db.php")) include "../include/db.php";
   else die("Fichier \"include/db.php\" non trouv�");

   if(is_file("../include/access_functions.php")) include "../include/access_functions.php";
   else die("Fichier \"include/access_functions.php\" non trouv�");

   $dbr=db_connect();

   // Chargement de la configuration
   $load_config=__get_config($dbr);

   if($load_config==FALSE) // config absente : erreur
      $erreur_config=1;
   elseif($load_config==-1) // param�tre(s) manquant(s) : avertissement
      $warn_config=1;

/*
   include "../configuration/aria_config.php";
   include "$__INCLUDE_DIR_ABS/vars.php";
   include "$__INCLUDE_DIR_ABS/db.php";
   include "$__INCLUDE_DIR_ABS/fonctions.php";
*/
/*
   // Include du module PEAR:Mail_Mime
   include('Mail.php');
   include('Mail/mime.php');

   // PEAR mail_mime rapporte quelques 'notice' : on �limine leur affichage
   error_reporting(E_ALL ^ E_NOTICE);
*/

   // Script : on force l'IP locale et le nom
   $_SESSION["auth_ip"]="127.0.0.1";
   $_SESSION["auth_host"]="localhost";

   $php_self=$_SERVER['PHP_SELF'];
   $_SESSION['CURRENT_FILE']=$php_self;

/*
   Ce script est pr�vu pour �tre ex�cut� par le crontab de la machine h�bergeant l'interface Pr�candidatures
   Principe :
   1/ Connexion quotidienne � la base, de pr�f�rence lorsque personne n'y touche (la nuit : 23h00 ou 5h00, par exemple)
   2/ Verrouillage des candidatures dont la date de verrouillage est d�pass�e, en fonction des composantes
*/

   // Mode test (pour v�rifier les conditions et les messages)
   // => Indiquer l'id d'un candidat test et ex�cuter le script manuellement

   $mode_test=0;

   if(isset($mode_test) && $mode_test==1)
      $condition_test="AND $_DBC_candidat_id='7040216274800000'";
   else
      $condition_test="";

   $justificatifs_vides=array();

   $date=time();

   // Boucle sur les Universit�s enregistr�es dans la base de donn�es
   $res_univ=db_query($dbr,"SELECT $_DBC_universites_id, $_DBC_universites_nom FROM $_DB_universites
                            ORDER BY $_DBC_universites_id");

   $rows_univ=db_num_rows($res_univ);

   for($u=0; $u<$rows_univ; $u++)
   {
      list($__UNIV, $univ_nom)=db_fetch_row($res_univ, $u);

      $result=db_query($dbr,"SELECT $_DBC_candidat_id, $_DBC_candidat_civilite, $_DBC_candidat_nom, $_DBC_candidat_prenom, $_DBC_candidat_email,  
                                    $_DBC_cand_id, $_DBC_cand_lockdate, $_DBC_composantes_id, $_DBC_composantes_nom, $_DBC_composantes_scolarite, 
                                    $_DBC_composantes_courriel_scol, $_DBC_annees_annee, $_DBC_specs_nom, $_DBC_propspec_id, $_DBC_propspec_finalite, 
                                    $_DBC_session_fermeture, $_DBC_session_reception, $_DBC_propspec_frais, $_DBC_cand_groupe_spec, 
                                    $_DBC_cand_ordre_spec, $_DBC_cand_rappels, $_DBC_cand_periode, $_DBC_cand_decision
                                 FROM $_DB_candidat, $_DB_composantes, $_DB_universites, $_DB_propspec, $_DB_annees, $_DB_specs,
                                      $_DB_session, $_DB_cand
                              WHERE $_DBC_composantes_univ_id=$_DBC_universites_id
                              AND $_DBC_candidat_id=$_DBC_cand_candidat_id
                              AND $_DBC_propspec_id=$_DBC_cand_propspec_id
                              AND $_DBC_annees_id=$_DBC_propspec_annee
                              AND $_DBC_propspec_id_spec=$_DBC_specs_id
                              AND $_DBC_cand_session_id=$_DBC_session_id
                              AND $_DBC_session_propspec_id=$_DBC_propspec_id
                              AND $_DBC_composantes_id=$_DBC_propspec_comp_id
                              AND $_DBC_composantes_univ_id='$__UNIV'
                              AND '$date' > $_DBC_cand_lockdate
                              AND $_DBC_cand_lock='0'
                              AND $_DBC_cand_periode IN ('$__PERIODE', '".($__PERIODE-1)."', '".($__PERIODE+1)."')
                              AND $_DBC_session_periode=$_DBC_cand_periode
                              AND $_DBC_propspec_manuelle='0'
                              $condition_test
                                 ORDER BY $_DBC_candidat_id, $_DBC_propspec_comp_id, $_DBC_cand_groupe_spec, $_DBC_cand_ordre_spec");

      $rows=db_num_rows($result);

      if($rows)
      {
         $old_candidat_id="";

         for($i=0; $i<$rows; $i++) // boucle for() globale sur les candidats de la base, dans l'universit� courante
         {
            list($candidat_id,$cand_civ,$cand_nom,$cand_prenom, $cand_email, $cand_id, $cand_lockdate, $comp_id, $comp_nom, $adr_scol, 
                  $courriel_scol, $annee, $spec_nom, $propspec_id, $finalite, $date_fermeture, $date_reception, $frais, 
                  $groupe_spec, $ordre_spec, $nb_rappels, $current_periode, $decision_id)=db_fetch_row($result,$i);

            $formation=$annee=="" ? "$spec_nom" : "$annee $spec_nom";
            $formation.=$tab_finalite[$finalite]=="" ? "" : " $tab_finalite[$finalite]";

            switch($cand_civ)
            {
               case "M" :       $ne_le="N� le";
                              $civ_mail="M.";
                              break;

               case   "Mlle" :   $ne_le="N�e le";
                              $civ_mail="Mlle";
                              break;

               case   "Mme"   :    $ne_le="N�e le";
                              $civ_mail="Mme";
                              break;

               default      :   $ne_le="N� le";
                              $civ_mail="M.";
            }

            // Verrouillage de la fiche : uniquement si tous les champs obligatoires ont �t� compl�t�s
            // 1/ On s�lectionne les �lements obligatoires rattach�s aux formations choisies par le candidat
            $result_ob=db_query($dbr, "SELECT $_DBC_dossiers_elems_id, $_DBC_dossiers_elems_unique, $_DBC_dossiers_ef_propspec_id
                                          FROM $_DB_dossiers_elems, $_DB_dossiers_ef
                                       WHERE $_DBC_dossiers_elems_id=$_DBC_dossiers_ef_elem_id
                                       AND $_DBC_dossiers_elems_obligatoire='t'
                                       AND $_DBC_dossiers_ef_propspec_id='$propspec_id'");

            $rows_ob=db_num_rows($result_ob);

            $contenu_vide=0;

            for($j=0; $j<$rows_ob; $j++)
            {
               list($elem_ob_id, $elem_ob_unique, $fil_id)=db_fetch_row($result_ob, $j);

               // on regarde si cet �l�ment existe dans les contenus compl�t�s par le candidat

               if($elem_ob_unique=="t") // demande unique : le candidat remplit une seule fois le champ
               {
                  $condition_fil="0";
                  /*
                  $condition_periode="(SELECT max($_DBC_cand_periode) FROM $_DB_cand WHERE $_DBC_cand_candidat_id='$candidat_id'
                                        AND $_DBC_cand_propspec_id IN (SELECT $_DBC_propspec_id FROM $_DB_propspec WHERE $_DBC_propspec_comp_id='$comp_id'))";
                                        */
               }
               else
               {
                  $condition_fil="$fil_id";
                  /*
                  $condition_periode="(SELECT max($_DBC_cand_periode) FROM $_DB_cand WHERE $_DBC_cand_candidat_id='$candidat_id'
                                                                                      AND ($_DBC_cand_propspec_id='$fil_id')) ";
                                                                                      */
               }

               $result_contenu=db_query($dbr,"SELECT $_DBC_dossiers_elems_contenu_para FROM $_DB_dossiers_elems_contenu
                                             WHERE $_DBC_dossiers_elems_contenu_candidat_id='$candidat_id'
                                             AND  $_DBC_dossiers_elems_contenu_elem_id='$elem_ob_id'
                                             AND $_DBC_dossiers_elems_contenu_comp_id='$comp_id'
                                             AND $_DBC_dossiers_elems_contenu_periode='$current_periode'
                                             AND $_DBC_dossiers_elems_contenu_propspec_id='$condition_fil'");

               if(!db_num_rows($result_contenu))
                  $contenu_vide=1;
               else
               {
                  list($contenu_para)=db_fetch_row($result_contenu, 0);

                  if(trim($contenu_para)=="")
                     $contenu_vide=1;
               }

               db_free_result($result_contenu);

               // si un contenu est vide : inutile de continuer : on stoppe la boucle, on envoie un message au candidat
               // et on passe � la suite
               if($contenu_vide==1)
               {
                  if($mode_test==1)
                     print("Debug : Fiche non verrouillable (contenu manquant) : $civ_mail $cand_nom $cand_prenom => $comp_nom\n");
               
                  // 1 : on stoppe la boucle
                  $j=$rows_ob;

                  // 2 : si le nombre max de rappels n'est pas atteint, on en envoie un et on d�calle le verrouillage

                  if(!isset($__MAX_RAPPELS) || !ctype_digit($__MAX_RAPPELS) || $__MAX_RAPPELS<=0)
                     $__MAX_RAPPELS=3;

                  if($nb_rappels<$__MAX_RAPPELS)
                  {
                     $requete="$_DBU_cand_rappels=$_DBU_cand_rappels+1";

                     // MAJ de la date de verrouillage si la date limite le permet encore
                     if(!isset($__AJOUT_VERROUILLAGE_JOURS) || !is_int($__AJOUT_VERROUILLAGE_JOURS) || $__AJOUT_VERROUILLAGE_JOURS<=0)
                        $__AJOUT_VERROUILLAGE_JOURS=2;
   
                     if($cand_lockdate+(86400*$__AJOUT_VERROUILLAGE_JOURS)<$date_fermeture)
                        $requete.=", $_DBU_cand_lockdate=$_DBU_cand_lockdate+($__AJOUT_VERROUILLAGE_JOURS*86400)";

                     db_query($dbr, "UPDATE $_DB_cand SET $requete WHERE $_DBU_cand_id='$cand_id'");

                     // 3 : pour informer le candidat, on calcule la date de fermeture de la session pour cette formation
                     $date_fermeture_txt=date_fr("j F Y", $date_fermeture);

                     // 4 : on envoie le message
                     $corps_message="\nBonjour $civ_mail ". stripslashes($cand_nom) . ",

Votre candidature pour la formation \"<strong>$formation</strong>\" ne peut actuellement pas �tre verrouill�e pour la composante suivante :

<strong>$comp_nom</strong>

En effet, des renseignements obligatoires compl�mentaires sont demand�s pour la ou les formations que vous avez s�lectionn�es.

<strong>Vous recevez ce message car vous n'avez pas compl�t� ces renseignements.</strong>

Pour compl�ter les informations manquantes, s�lectionnez la composante indiqu�e, puis, sur votre fiche, cliquez sur l'onglet num�ro 6 (\"6 - Autres Renseignements\") dans le menu gauche et compl�tez les renseignements demand�s.

Si vous ne compl�tez pas ces informations, votre dossier ne pourra pas �tre examin� par la Scolarit�.

<b>IMPORTANT : date limite pour compl�ter votre fiche : $date_fermeture_txt. Pass�e cette date, votre candidature ne pourra plus �tre examin�e.</b>

Cordialement,

--
$adr_scol

$comp_nom
$univ_nom";

                     $dest_array=array("0" => array("id"       => "$candidat_id",
                                                   "civ"      => "$cand_civ",
                                                   "nom"       => "$cand_nom",
                                                   "prenom"    => "$cand_prenom",
                                                   "email"      => "$cand_email"));

                     if($decision_id==$__DOSSIER_NON_TRAITE)
                     {
                        write_msg("", array("id" => "0", "nom" => "Syst�me", "prenom" => ""), $dest_array,
                                 "Informations manquantes - rappel ".($nb_rappels+1), $corps_message, "$cand_nom $cand_prenom");
                     
                        write_evt($dbr, $__EVT_ID_S_LOCK, "Echec du verrouillage : renseignements incomplets (rappel ".($nb_rappels+1).")", $candidat_id, $cand_id);
                     }
                  }
               }
            }

            db_free_result($result_ob);

            // Verification effectu�e, on d�cide si on peut verrouiller ou pas

            if($contenu_vide==0)
            {
               // ================================================================
               //          JUSTIFICATIFS A ENVOYER : 1 message par voeu
               // ================================================================

               // Cette requ�te est uniquement faite pour v�rifier la pr�sence de justificatifs pour cette formation
               // TODO 8/1/2008 : SIMPLIFIER en int�grant dans la requ�te globale ? (avec un CASE)
               $result3=db_query($dbr, "SELECT $_DBC_justifs_id, $_DBC_justifs_titre, $_DBC_justifs_texte, $_DBC_justifs_jf_nationalite
                                          FROM $_DB_justifs, $_DB_justifs_jf
                                          WHERE $_DBC_justifs_jf_propspec_id='$propspec_id'
                                          AND $_DBC_justifs_jf_justif_id=$_DBC_justifs_id
                                          ORDER BY $_DBC_justifs_jf_ordre");

               $rows3=db_num_rows($result3);
               db_free_result($result3);

               if(!$rows3) // Aucun �l�ment : on pr�vient l'administrateur et on ne verrouille pas la fiche
                  $justificatifs_vides[$propspec_id]="$comp_id - $formation\n";
               else
               {
                  // message sp�cifique � la composante ?
                  
                  $res_message=db_query($dbr, "SELECT $_DBC_messages_contenu FROM $_DB_messages
                                               WHERE $_DBC_messages_type='$__MSG_TYPE_VERROUILLAGE'
                                               AND $_DBC_messages_comp_id='$comp_id'
                                               AND $_DBC_messages_actif='t'");
                                         
                  if(db_num_rows($res_message))
                     list($corps_message_composante)=db_fetch_row($res_message, 0);
                  else
                     $corps_message_composante=$__MSG_TYPES['$__MSG_TYPE_VERROUILLAGE']['defaut'];
                     
                  db_free_result($res_message);

                  if(!isset($mode_test) || (isset($mode_test) && $mode_test!=1))
                  {
                     // Autres fichiers li�s aux justificatifs
                     $result4=db_query($dbr, "SELECT distinct($_DBC_justifs_fichiers_nom)
                                                FROM $_DB_justifs_fichiers, $_DB_justifs_ff
                                                WHERE $_DBC_justifs_fichiers_id=$_DBC_justifs_ff_fichier_id
                                                AND $_DBC_justifs_ff_propspec_id='$propspec_id'
                                                AND $_DBC_justifs_fichiers_comp_id='$comp_id'");

                     $rows4=db_num_rows($result4);

                     if($rows4)
                     {
                        $liste_fichiers="";

                        for($l=0; $l<$rows4; $l++)
                        {
                           list($fichier_nom)=db_fetch_row($result4, $l);

                           // On n'utilise pas de variables de chemins dans les messages, car si les chemins changent,
                           // les liens ne seront plus valides
                           // Solution : utilisation de la macro ###texte### le "texte" sera automatiquement remplac� par
                           // $GLOBALS[texte] lors de l'ouverture du message
                           if(is_file("$GLOBALS[__PUBLIC_DIR_ABS]/$comp_id/justificatifs/$fichier_nom"))
                              $liste_fichiers.="<br>- <a href='###__PUBLIC_DIR###/$comp_id/justificatifs/$fichier_nom' target='_blank' class='lien_bleu_12'><b>$fichier_nom</b></a>";
                           else
                           {
                              $hdrs_err = array("From" => "$__EMAIL_ADMIN",
                                                "Subject" => "Pr�candidatures : erreur de fichier");

                              mail($courriel_scol,"[ERREUR Pr�candidatures] - Fichier non trouv�", "Bonjour,\n\nCeci est un message automatique de l'Application de Gestion des Candidatures en ligne.\n\nLors de l'envoi des Justificatifs, le fichier suivant n'a pu �tre trouv� sur le serveur : \n\nFichier : $__PUBLIC_DIR_ABS/$comp_id/justificatifs/$fichier_nom\n\n(Il est possible que ce fichier ait �t� supprim� par erreur, le candidat ne l'a alors pas re�u)\n\nUne copie de ce courriel a �t� envoy� � l'administrateur.\n\nCordialement,\n\nL'Application ARIA :)");

                              // Copie � l'admin
                              mail($__EMAIL_ADMIN,"[DBG - ERREUR Pr�candidatures] - Fichier non trouv�", "Lors de l'envoi des Justificatifs, le fichier suivant n'a pu �tre trouv� sur le serveur : \n\nFichier : $__PUBLIC_DIR_ABS/$comp_id/justificatifs/$fichier_nom\n\nCandidat : $civ_mail $cand_nom $cand_prenom\n\nUne copie de ce courriel a �t� envoy� � la scolarit� concern�e.\n\nCordialement,\n\nL'Application ARIA :)");
                           }
                        }

                        if($liste_fichiers!="")
                        {
                           $les_fichiers_suivants=$rows4==1 ? "le fichier suivant" : "les fichiers suivants";
                           $les_pieces_jointes_suivantes=$rows4==1 ? "la pi�ce jointe suivante" : "les pi�ces jointes suivantes";

                           $corps_fichiers="4/ Vous devez �galement t�l�charger $les_fichiers_suivants et suivre les instructions : " . $liste_fichiers;
                        }
                        else
                           $corps_fichiers="";
                     }
                     else
                        $corps_fichiers="";

                     unset($liste_specs);

                     // candidature � choix multiples ?
                     if($groupe_spec!=-1)
                     {
                        $liste_specs=array("$cand_id" => array("annee" => "$annee",
                                                               "spec" => "$spec_nom",
                                                               "finalite" => "$finalite",
                                                               "formation" => "$formation"));

                        // on regarde le nombre de candidatures concern�es (elles sont tri�es dans la requete globale)
                        // attention, algo un peu limite ...
                        for($search=($i+1); $search<$rows; $search++)
                        {
                           $next_candidat_id=db_fetch_result($result, $search, 0); //   /!\

                           // print("DBG cid : current : $candidat_id next : $next_candidat_id\n");

                           if($next_candidat_id==$candidat_id)
                           {
                              $next_groupe=db_fetch_result($result, $search, 19); //   /!\

                              // print("DBG : groupe : current : $groupe_spec Next : $next_groupe\n");

                              if($next_groupe!=$groupe_spec)
                                 $search=$rows; // = break;
                              else
                              {
                                 $next_cand_id=db_fetch_result($result, $search, 5);

                                 $liste_specs[$next_cand_id]=array();

                                 $liste_specs[$next_cand_id]["annee"]=db_fetch_result($result, $search, 11);
                                 $liste_specs[$next_cand_id]["spec"]=db_fetch_result($result, $search, 12);
                                 $liste_specs[$next_cand_id]["finalite"]=db_fetch_result($result, $search, 14);

                                 $liste_specs[$next_cand_id]["formation"]=$liste_specs[$next_cand_id]["annee"]=="" ? $liste_specs[$next_cand_id]["spec"] : $liste_specs[$next_cand_id]["annee"] . " " . $liste_specs[$next_cand_id]["spec"];

                                 $liste_specs[$next_cand_id]["formation"].=$tab_finalite[$liste_specs[$next_cand_id]["finalite"]]=="" ? "" : " " . $tab_finalite[$liste_specs[$next_cand_id]["finalite"]];
                              }
                           }
                        }
                     }

                     // On a tout : verrouillage de la formation et envoi du message
                     if(isset($liste_specs) && count($liste_specs)>1)
                     {
                        $sujet="[$comp_nom] - IMPORTANT - Suite de la proc�dure - candidature � choix multiples";

                        $nom_formation_corps="ce groupe de formations";

                        foreach($liste_specs as $next_cand_id => $array_specs)
                        {
                           $corps_message.="<b>- " . $liste_specs[$next_cand_id]["formation"] . "</b>\n";

                           db_query($dbr,"UPDATE $_DB_cand SET $_DBU_cand_lock='1' WHERE $_DBU_cand_id='$next_cand_id'");

                           // on avance la boucle d'autant de candidatures que de choix multiples du m�me groupe
                           $i++;
                        }
                     }
                     else
                     {
                        $sujet="IMPORTANT - Suite de la proc�dure - $formation";

                        db_query($dbr,"UPDATE $_DB_cand SET $_DBU_cand_lock='1' WHERE $_DBU_cand_id='$cand_id'");

                        $corps_message="\nCandidature : <b>$formation</b>\n";

                        $nom_formation_corps="la formation \"$formation\"";
                     }

                     $limite_reception_txt=date_fr("j F Y", $date_reception);

                     $prefixe=$corps_message; // conservation de l'ent�te pour le message sp�cifique � la composante

$corps_message.="\n\nBonjour $civ_mail ". ucwords(mb_strtolower(stripslashes($cand_nom))) .",

Le d�lai imparti pour modifier cette formation est �chu. Apr�s r�ception de l'ensemble des pi�ces requises (liste dans ce message), vos demandes pourront �tre trait�es par la ou les scolarit�s.

La proc�dure � suivre est maintenant la suivante :

1/ Cliquez sur chacun des liens suivants :
<a href='###__CAND_DIR###/gen_recapitulatif.php?comp_id=$comp_id' target='_blank' class='lien_bleu_12'><b>- r�capitulatif des informations que vous avez saisies</b> (format PDF)</a>
<a href='###__CAND_DIR###/gen_justificatifs.php?cand_id=$cand_id' target='_blank' class='lien_bleu_12'><b>- liste des justificatifs � nous faire parvenir par voie postale pour $nom_formation_corps</b> (format PDF)</a>

2/ Enregistrez puis imprimez ces documents PDF. Conservez-les car ils pourront vous reservir plus tard.

3/ Envoyez ces documents ainsi que les pi�ces demand�es dans le document \"Justificatifs\" par courrier � l'adresse postale indiqu�e dans ce message (<b>sauf</b> si une adresse sp�cifique est pr�cis�e dans la liste des justificatifs).

$corps_fichiers

<font class='Texte_important'><b>IMPORTANT</b> :

Sauf consignes contraires de la scolarit� <b>(v�rifiez bien le document \"Liste des justificatifs\" ci-dessus)</b> :

- vous devez envoyer vos justificatifs � la scolarit� le plus rapidement possible (n'attendez pas la date limite du $limite_reception_txt). Les dossiers hors d�lais seront examin�s lors de la session suivante. Si aucune autre session n'est pr�vue, votre dossier risque de ne pas �tre trait�.
- pour les candidatures � choix multiples (sp�cialit�s regroup�es dans le menu 5-Pr�candidatures), vous devez envoyer <b>autant d'exemplaires</b> de vos justificatifs <b>que de formations s�lectionn�es</b> dans cette composante. Si vous n'envoyez pas vos justificatifs en plusieurs exemplaires, toutes vos candidatures <b>ne pourront pas �tre trait�es</b>.</font>


Vous pouvez d�s � pr�sent suivre l'�volution de votre fiche en ligne (sur cette interface) et vous recevrez prochainement d'autres messages concernant le traitement de votre dossier.

Aucune information suppl�mentaire sur l'�tat de votre candidature ne sera donn�e par t�l�phone.


<b>Rappel</b> : le d�p�t d'une pr�candidature en ligne ne constitue en aucun cas une admission dans la ou les formations demand�es.


Cordialement,


--
$adr_scol

$comp_nom
$univ_nom";

                     $corps_message2=parse_macros($corps_message_composante);

                     // Macros sp�cifiques aux justificatifs (� int�grer dans une autre fonction ?)
                     $new_corps=preg_replace("/%justificatifs%/i", "<a href='###__CAND_DIR###/gen_justificatifs.php?cand_id=$cand_id' target='_blank' class='lien_bleu_12'><b>- liste des justificatifs � nous faire parvenir par voie postale pour $nom_formation_corps</b> (format PDF)</a>", $corps_message2);
                     $new_corps=preg_replace("/%recapitulatif%/i", "<a href='###__CAND_DIR###/gen_recapitulatif.php?comp_id=$comp_id' target='_blank' class='lien_bleu_12'><b>- r�capitulatif des informations que vous avez saisies</b> (format PDF)</a>", $new_corps);
                     $new_corps=preg_replace("/%date_limite%/i", $limite_reception_txt, $new_corps);
                     $new_corps=preg_replace("/%adresse_scolarite%/i", $adr_scol, $new_corps);
                     $new_corps=preg_replace("/%composante%/i", $comp_nom, $new_corps);
                     $new_corps=preg_replace("/%universite%/i", $univ_nom, $new_corps);
                     $new_corps=preg_replace("/%civ%/i", $civ_mail, $new_corps);
                     $new_corps=preg_replace("/%nom%/i", ucwords(mb_strtolower(stripslashes($cand_nom))), $new_corps);
                     
                     if($liste_fichiers!="")
                        $prefixe.="Ce message contient $les_pieces_jointes_suivantes : $liste_fichiers\n\nCliquez sur les liens pour les t�l�charger, puis suivez les instructions.\n";
                     
                     $message_complet="$prefixe"."$new_corps";
                     
                     $dest_array=array("0" => array("id"       => "$candidat_id",
                                                    "civ"      => "$cand_civ",
                                                    "nom"       => "$cand_nom",
                                                    "prenom"    => "$cand_prenom",
                                                    "email"      => "$cand_email"));

                     if($decision_id==$__DOSSIER_NON_TRAITE)
                     {
                        write_msg("", array("id" => "0", "nom" => "Syst�me", "prenom" => "", "composante" => "$comp_nom", "universite" => "$univ_nom"),
                                 $dest_array, $sujet, $corps_message, "$cand_nom $cand_prenom");

                        write_evt("", $__EVT_ID_S_LOCK, "Verrouillage automatique", $candidat_id, $comp_id);
                     }

                     // Si le debug est activ�, on envoie le message en interne au compte administrateur
                     if($GLOBALS["__DEBUG"]=="t" && $GLOBALS["__DEBUG_LOCK"]=="t" && !isset($mode_test))
                     {
                        $corps_message="ID : $candidat_id\nCourriel : $cand_email\n\n" . $corps_message;

                        $dest_array=array("0" => array("id"       => "0",
                                                        "civ"         => "",
                                                       "nom"       => "Syst�me",
                                                       "prenom"    => "",
                                                       "email"      => $GLOBALS["__EMAIL_ADMIN"]));

                        // Envoi du message � l'utilisateur "Syst�me" (id=0)
                        write_msg("", array("id" => "0", "nom" => "Syst�me", "prenom" => ""), $dest_array,
                                 "$GLOBALS[__DEBUG_SUJET] - $comp_nom - Verrouillage - $civ_mail $cand_prenom $cand_nom - $formation", $corps_message,
                                 "Syst�me");
                     }
                  }
                  else
                     print("Debug : Verrouillage de la fiche de $civ_mail $cand_nom $cand_prenom => $comp_nom\n");
               }

               $envoi_ok=1;

               $old_candidat_id=$candidat_id;
            } // Fin du if($contenu_vide)
         }  // fin de la boucle for() globale sur les candidats
      }

      // S'il y a des justificatifs vides : mail direct � l'admin (les fiches concern�es n'ont normalement pas �t� verrouill�es)
      if(count($justificatifs_vides))
      {
         $justifs_txt="";

         foreach($justificatifs_vides as $propspec_id => $comp_formation)
            $justifs_txt.="$comp_formation ($propspec_id)\n";

         $headers="From: $__EMAIL_ADMIN" . "\r\n" . "Reply-To: $__EMAIL_ADMIN";
         mail($__EMAIL_ADMIN, "[$univ_nom - Verrouillages : justificatifs vides]", "Fiches NON verrouill�es pour les formations suivantes : \n\n" . $justifs_txt, $headers);
      }

      db_free_result($result);
   }

   db_free_result($res_univ);

   db_close($dbr);
?>
