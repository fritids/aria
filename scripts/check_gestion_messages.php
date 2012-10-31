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
   // V�rification quotidienne des messages en attente pour les gestionnaires
   // Chaque membre re�oit un message de notification s'il a des messages non lus dans la
   // bo�te de r�ception

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
   $php_self=$_SERVER['PHP_SELF'];
   $_SESSION['CURRENT_FILE']=$php_self;

   // Script : on force l'IP locale et le nom
   $_SESSION["auth_ip"]="127.0.0.1";
   $_SESSION["auth_host"]="localhost";

   $current_date=time();

   $limite=$current_date-(30*86400); // date - 30 jours
      
   // Date courante au format AA MM JJ HH MM SS MS(5)
   // (lorsqu'on se base sur les identifiants, i.e sur la date de cr�ation de la fiche)
   $limite_id=new_id($limite);

   $result=db_query($dbr, "SELECT $_DBC_acces_id, $_DBC_acces_nom, $_DBC_acces_prenom, $_DBC_acces_courriel,
                                  $_DBC_acces_absence_debut, $_DBC_acces_absence_fin
                              FROM $_DB_acces
                           WHERE ($_DBC_acces_absence_debut >= '$current_date'
                                  OR $_DBC_acces_absence_fin <= '$current_date'
                                  OR $_DBC_acces_absence_active='f')
                           AND $_DBC_acces_niveau!='$__LVL_DESACTIVE'
                           AND $_DBC_acces_reception_msg_scol='t'");

   $rows=db_num_rows($result);

   if($rows)
   {
      $headers="From: $__EMAIL_ADMIN" . "\r\n" . "Reply-To: $__EMAIL_ADMIN";
      
      for($i=0; $i<$rows; $i++)
      {
         list($acces_id, $acces_nom, $acces_prenom, $acces_courriel)=db_fetch_row($result, $i);

         // Sous r�pertoire
         $sous_rep=sous_rep_msg($acces_id);

         $files=glob("$__GESTION_MSG_STOCKAGE_DIR_ABS/$sous_rep/$acces_id/$__MSG_INBOX/*.0");

         $nb=count($files);
      
         $s=$nb>1 ? "s" : "";
         
         if($nb)
         {
            $liste_messages="R�sum� : \n";

            foreach($files as $filename)
            {
               $array_file=@file("$filename");

               if($array_file!==FALSE)
               {
                  $expediteur=trim($array_file["1"]);
                  $sujet=stripslashes(trim($array_file["4"]));
               }

               $liste_messages.="- $expediteur\t$sujet\n";
            }

            $corps_message="[ARIA - V�rification quotidienne]\n\nBonjour, \n\nVous avez actuellement $nb message$s non lu$s sur l'interface de gestion des candidatures.\n\nRappel de l'adresse : $__URL_GESTION\n\n$liste_messages\n\nCordialement,\n\nLe syst�me ;-)";
            mail($acces_courriel,"Candidatures : message$s en attente", $corps_message, $headers);
            // echo "$acces_courriel\n$corps_message\n=============================\n\n";
         }
      }
   }

   db_free_result($result);
   db_close($dbr);
?>
