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
   // Message affich� lorsqu'une page n'a pas �t� trouv�e.
   // L'authentification n'est pas n�cessaire.
   session_name("preinsc");
   session_start();

   include "../../configuration/aria_config.php";
   include "$__INCLUDE_DIR_ABS/vars.php";
   include "$__INCLUDE_DIR_ABS/fonctions.php";
   include "$__INCLUDE_DIR_ABS/db.php";

   if(isset($_SESSION["CURRENT_FILE"]))
      $_SESSION["from_page"]=$_SESSION["CURRENT_FILE"];
   else
      $_SESSION["from_page"]="../index.php";

   unset($_SESSION["form_composante_id"]);

   // EN-TETE
   en_tete_candidat();

   // MENU SUPERIEUR
   menu_sup_simple();
?>
<div class='main'>
   <?php
       titre_page_icone("[Assistance aux candidats] - Accueil", "help-browser_32x32_fond.png", 15, "L");

      message("Ce syst�me d'aide permet de vous orienter en fonction de vos questions et de faciliter certaines demandes.", $__INFO);
   ?>

   <table align='center' style='padding-bottom:20px;'>
   <tr>
      <td class='td-complet fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'><strong>Enregistrement et connexion</strong></font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu'>
         <a href='aide.php?s=navigateur' target='_self' class='lien_bleu_12'>&#8226;&nbsp;&nbsp;L'interface ne s'affiche pas correctement / je reviens toujours sur la page d'accueil, m�me lorsque je parviens � m'identifier.</a>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu'>
         <a href='aide.php?s=auth' target='_self' class='lien_bleu_12'>&#8226;&nbsp;&nbsp;Je suis d�j� enregistr�(e), mais je n'ai plus mes identifiants et depuis, j'ai chang� d'adresse �lectronique (<i>email</i>) ...</a>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu'>
         <a href='aide.php?s=auth' target='_self' class='lien_bleu_12'>&#8226;&nbsp;&nbsp;Je me suis tromp�(e) d'adresse �lectronique lors de mon enregistrement, que faire ?</a>
      </td>
   </tr>
   <tr>
      <td class='td-complet fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'><strong>Candidature et formations</strong></font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu'>
         <a href='aide.php?s=doc' target='_self' class='lien_bleu_12'>&#8226;&nbsp;&nbsp;Comment d�poser un dossier de pr�candidature en ligne ?</a>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu'>
         <a href='aide.php?s=contact_scol' target='_self' class='lien_bleu_12'>&#8226;&nbsp;&nbsp;J'ai une question concernant les modalit�s d'acc�s � une formation, � qui dois-je m'adresser ?</a>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu'>
         <a href='aide.php?s=formations' target='_self' class='lien_bleu_12'>&#8226;&nbsp;&nbsp;Dans le menu "5-Pr�candidatures", je ne trouve pas la formation souhait�e dans la liste.</a>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu'>
         <!-- <a href='form_scolarite.php?t=1' target='_self' class='lien_bleu_12'>&#8226;&nbsp;&nbsp;Je souhaite ajouter une formation, mais la session est d�j� ferm�e.</a> -->
         <a href='aide.php?s=scolarite' target='_self' class='lien_bleu_12'>&#8226;&nbsp;&nbsp;Je souhaite ajouter une formation, mais la session est d�j� ferm�e.</a>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu'>
         <!-- <a href='form_deverrouillage.php' target='_self' class='lien_bleu_12'>&#8226;&nbsp;&nbsp;Je souhaite d�verrouiller certaines formations pour effectuer des modifications sur ma fiche.</a> -->
         <a href='aide.php?s=deverrouillage' target='_self' class='lien_bleu_12'>&#8226;&nbsp;&nbsp;Je souhaite d�verrouiller certaines formations pour effectuer des modifications sur ma fiche.</a>
      </td>
   </tr>
   <tr>
      <td class='td-complet fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'><strong>Liste des justificatifs et pi�ces � envoyer</strong></font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu'>
         <a href='aide.php?s=justificatifs&v=1' target='_self' class='lien_bleu_12'>&#8226;&nbsp;&nbsp;La date de verrouillage est pass�e mais je n'ai pas re�u la liste des justificatifs, pourquoi ?</a>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu'>
         <a href='aide.php?s=pdf' target='_self' class='lien_bleu_12'>&#8226;&nbsp;&nbsp;J'ai re�u un message contenant des fichiers au format PDF, mais je n'arrive pas � les ouvrir.</a>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu'>
         <a href='aide.php?s=justificatifs&a=1' target='_self' class='lien_bleu_12'>&#8226;&nbsp;&nbsp;J'ai re�u la liste des justificatifs, � qui et comment dois-je envoyer tous ces documents ?</a>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu'>
         <a href='aide.php?s=justificatifs&n=1' target='_self' class='lien_bleu_12'>&#8226;&nbsp;&nbsp;J'ai demand� plusieurs formations, combien de fois dois-je envoyer mes justificatifs ?</a>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu'>
         <a href='aide.php?s=cursus' target='_self' class='lien_bleu_12'>&#8226;&nbsp;&nbsp;Dans le menu "2-Cursus", toutes mes �tapes sont marqu�es "En attente des justificatifs", comment changer ce statut ?</a>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu'>
         <a href='aide.php?s=justificatifs&d=1' target='_self' class='lien_bleu_12'>&#8226;&nbsp;&nbsp;Je n'ai pas encore les derniers relev�s de notes de mon ann�e en cours, que dois-je faire ?</a>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu'>
         <a href='aide.php?s=contact_scol2' target='_self' class='lien_bleu_12'>&#8226;&nbsp;&nbsp;Je suis un(e) candidat(e) �tranger(e), on me demande d'envoyer des justificatifs ou des pi�ces qui n'existent pas dans mon pays. Que faire ?</a>
      </td>
   </tr>
   <tr>
      <td class='td-complet fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'><strong>Admission et Inscription</strong></font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu'>
         <a href='aide.php?s=resultats' target='_self' class='lien_bleu_12'>&#8226;&nbsp;&nbsp;Quand et comment obtiendrai-je les r�sultats de mon admission ?</a>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu'>
         <a href='aide.php?s=inscr' target='_self' class='lien_bleu_12'>&#8226;&nbsp;&nbsp;J'ai re�u une lettre d'admission, et je ne parviens pas � m'inscrire malgr� les instructions re�ues, que dois-je faire ?</a>
      </td>
   </tr>
   <tr>
      <td class='td-complet fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'><strong>Autres :</strong></font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu'>
         <a href='aide.php?s=contact_admin' target='_self' class='lien_bleu_12'>&#8226;&nbsp;&nbsp;Mon probl�me ne se trouve pas dans ce tableau, � qui dois-je m'adresser ?</a>
      </td>
   </tr>
   </table>
   
   <div class='centered_box' style='padding-bottom:20px;'>
      <a href='<?php echo $_SESSION["from_page"]; ?>' target='_self' class='lien2'><img border='0' src='<?php echo "$__ICON_DIR/back_32x32.png"; ?>' title='[Retour]' alt='Retour' desc='Retour'></a>
   </div>
</div>
<?php
   pied_de_page_simple();
?>
</body></html>
