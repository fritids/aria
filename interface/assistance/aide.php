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

   $php_self=$_SERVER['PHP_SELF'];
   // $_SESSION['CURRENT_FILE']=$php_self;

   // EN-TETE
   en_tete_candidat();

   // MENU SUPERIEUR
   menu_sup_simple();
?>
<div class='main'>
   <?php
       titre_page_icone("[Assistance aux candidats]", "help-browser_32x32_fond.png", 15, "L");
   ?>

   <table align='center' style='padding:0px 40px 20px 40px;'>
   <?php
      if(isset($_GET["s"]) && $_GET["s"]=="auth")
      {
   ?>
   <tr>
      <td class='td-complet fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'>
            <strong>Probl�mes d'identification sur l'application :</strong>
            <br>&#8226;&nbsp;&nbsp;Je suis d�j� enregistr�(e), mais je n'ai plus mes identifiants et depuis, j'ai chang� d'adresse �lectronique (<i>email</i>).
            <br>&#8226;&nbsp;&nbsp;Je me suis tromp�(e) d'adresse �lectronique lors de mon enregistrement, que faire ?
         </font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu' style='white-space:normal;'>
         <font class='Texte' style='vertical-align:top;'>
            Ces deux cas de figure sont courants : utilisez <a href='form_adresse.php' class='lien_bleu_12' style='vertical-align:top;'><strong>ce formulaire</strong></a> pour demander
            une modification de votre adresse �lectronique et le renvoi de vos identifiants.
         </font>
         <br><br>
         <font class='Texte_important'>
            <strong>Vous ne devez en aucun cas vous enregistrer plusieurs fois sur l'interface : si vous poss�dez plusieurs fiches, elles
            risquent d'�tre supprim�es sans pr�avis et vos candidatures ne seront alors pas trait�es.</strong>
         </font>
      </td>
   </tr>
   <?php
      }
      elseif(isset($_GET["s"]) && $_GET["s"]=="doc")
      {
   ?>
   <tr>
      <td class='td-complet fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'>
            <strong>Comment d�poser un dossier de pr�candidature en ligne ?</strong>
         </font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu' style='white-space:normal;'>
         <font class='Texte' style='vertical-align:bottom;'>
            Nous vous conseillons de lire int�gralement la documentation <a href='<?php echo "$__DOC_DIR/documentation.php"; ?>' target='_blank' class='lien_bleu_12'><strong>sur cette page</strong></a>.
            <br><br>
            Le chapitre "<strong>I - D�roulement d'une pr�candidature en ligne</strong>" r�sume en particulier les diff�rentes �tapes d'un d�p�t de dossier.
         </font>
      </td>
   </tr>
   <?php
      }
      elseif(isset($_GET["s"]) && $_GET["s"]=="cursus")
      {
      ?>
   <tr>
      <td class='td-complet fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'>
            <strong>Dans le menu "2-Cursus", toutes mes �tapes sont marqu�es "En attente des justificatifs", comment changer ce statut ?</strong>
         </font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu' style='white-space:normal;'>
         <font class='Texte'>
            Lorsque vous ajoutez une �tape � votre cursus, l'�tat est toujours "En attente des justificatifs" par d�faut. Il indique que la scolarit�
            attend les justificatifs ou qu'elle n'a pas encore trait� ceux qu'elle a re�us (en fonction du nombre de dossiers re�us, le traitement
            peut prendre du temps).
            <br><br>
            Une fois les pi�ces re�ues, la scolarit� validera ou non chaque �tape. S'il manque des documents, vous serez normalement averti(e) via un
            message de l'application.
            <br><br>
            <strong>
               - Si le statut du cursus ne change pas, cela peut simplement signifier que la scolarit� n'a pas encore trait� votre dossier
               <br>- Si votre dossier est marqu� "recevable", cela signifie en g�n�ral que les justificatifs ont �t� trait�s mais que la scolarit� n'a 
               pas modifi� le statut de votre cursus sur l'interface.
            </strong>.
         </font>
      </td>
   </tr>
   <?php
      }
      elseif(isset($_GET["s"]) && $_GET["s"]=="deverrouillage")
      {
      ?>
   <tr>
      <td class='td-complet fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'>
            <strong>Je souhaite d�verrouiller certaines formations pour effectuer des modifications sur ma fiche.</strong>
         </font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu' style='white-space:normal;'>
         <font class='Texte'>
            Vous pouvez demander le d�verrouillage d'une ou plusieurs formations via le formulaire pr�vu � cet effet.
            <br><br>
            <u><strong>Avant de le compl�ter, merci de v�rifier chacun des points suivants :</strong></u>
            <br>
            <ol style='list-style-type:decimal; text-align:justify'>
               <li>Si les informations � modifier sont dans le menu <strong>1 - Identit�</strong>, aucun d�verrouillage n'est
                  n�cessaire : vous pouvez mettre � jour ces informations <strong>� tout moment</strong>.
               </li>
               <li style='padding-top:20px;'>Pour chaque voeu � d�verrouiller, v�rifiez bien que les candidatures sont <strong>encore ouvertes</strong>.
                  Si elles sont closes, vous devez contacter directement la scolarit� et d�tailler les modifications � apporter � votre fiche.
               </li>
               <li style='padding-top:20px;'>Si vos voeux verrouill�s sont r�partis sur plusieurs composantes, alors la modification des 
                  menus <strong>2-Cursus</strong>, <strong>3-Langues</strong>, et <strong>4-Informations compl�mentaires</strong> est
                  vivement d�conseill�e, car ces informations sont communes � toutes les composantes de l'Universit�. Dans ce cas pr�cis, il est �galement 
                  pr�f�rable de contacter directement la scolarit� de l'une des composantes pour qu'elle effectue elle-m�me les modifications.
                  <br><br><strong><u>Attention :</u></strong> si les modifications concernent votre cursus (dipl�mes, notes, ...), veillez � bien pr�venir
                  <u>chaque composante</u> de ces modifications, et v�rifiez bien qu'elles ont re�u les justificatifs � jour.
               </li>
               <li style='padding-top:20px;'>Vous devez �tre <strong>authentifi�(e) sur l'interface</strong> afin d'acc�der au formulaire.</li>
            </ol>

            <div class='centered_box' style='padding-top:20px;'>
               <a href='form_deverrouillage.php' target='_self' class='lien_bleu_12'><strong>Acc�der au formulaire</strong></a>
            </div>

         </font>
      </td>
   </tr>
   <?php
      }
      elseif(isset($_GET["s"]) && $_GET["s"]=="formations")
      {
   ?>
   <tr>
      <td class='td-complet fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'>
            <strong>Dans le menu "5-Pr�candidatures", je ne trouve pas la formation souhait�e dans la liste.</strong>
         </font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu' style='white-space:normal;'>
         <font class='Texte'>
            <u><strong>Plusieurs cas possibles :</strong></u>
            <ul style='text-align:justify'>
               <li>
                  <strong>Aucun d�p�t de dossier n'est n�cessaire pour la formation voulue.</strong>
                  <br>C'est par exemple les cas de nombreuses <strong>1�re ann�es de Licence (L1)</strong> (attention : certains L1 contingent�s n�cessitent le d�p�t d'une candidature). Si vous avez obtenu votre baccalaur�at (ou �quivalent), seule <u>l'inscription</u> est n�cessaire pour entrer en L1, pas le d�p�t d'un dossier de candidature.
                  <br><br>
                  <u>Conseil</u> : en fonction de votre situation et des dipl�mes en votre possession, v�rifiez toujours les conditions et les modalit�s d'acc�s � la formation que vous souhaitez aupr�s de la scolarit�.
               </li>

               <li style='padding-top:20px;'><strong>Aucune session de candidatures n'est ouverte pour cette formation.</strong>
                  <br><u>Solution</u> : si les candidatures <strong>ne sont pas encore ouvertes</strong>, vous devez attendre l'ouverture de la session de candidatures. Si elles sont d�j� closes, vous pouvez contacter la
                  scolarit� pour savoir si elle accepte les candidatures tardives.
               </li>

               <li style='padding-top:20px;'><strong>Le nombre de dossiers que vous pouvez d�poser est limit� dans une composante de l'Universit�, et la limite est d�j� atteinte sur votre fiche.</strong>
                  <br><u>Solution</u> : Vous devez r�fl�chir � la priorit� de vos voeux afin de respecter la limite impos�e par la composante, en supprimant �ventuellement certaines formations s�lectionn�es.
               </li>

               <li style='padding-top:20px;'><strong>La formation recherch�e n'est pas propos�e par la composante que vous avez s�lectionn�e.</strong>
                  <br><u>Solution</u> : Utilisez le menu "Rechercher une formation" (menu sup�rieur de votre fiche) pour trouver la formation souhait�e. En cas de r�ponse positive, la composante qui la propose sera indiqu�e.</strong>
               </li>

               <li style='padding-top:20px;'><strong>La formation n'est pas disponible via l'interface ARIA.</strong>
                  <br><u>Solution</u> :
                  <br>- Si la composante est bien enregistr�e dans l'application (mais pas la formation souhait�e), il se peut
                  que la proc�dure de candidature soit particuli�re. Si aucune information n'est donn�e sur le site Internet de 
                  la composante (ou sur la page d'information qui peut apparaitre lorsque vous la s�lectionnez apr�s votre identification),
                  <u>contactez directement la scolarit�</u> pour obtenir des renseignements sur cette formation.
                  <br>- Si la composante n'est pas dans la liste propos�e, alors celle-ci n'a aucun lien avec l'application ARIA. Vous
                  devez donc consulter son site Internet et/ou sa scolarit� afin d'obtenir des d�tails sur la proc�dure de d�p�t de dossier de 
                  candidature.
               </li>
            </ul>
         </font>
      </td>
   </tr>
   <?php
      }
      elseif(isset($_GET["s"]) && $_GET["s"]=="inscr")
      {
      ?>
   <tr>
      <td class='td-complet fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'>
            <strong>J'ai re�u une lettre (ou un message) confirmant mon admission, mais je ne parviens pas � m'inscrire malgr� les instructions re�ues, que dois-je faire ?</strong>
         </font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu' style='white-space:normal;'>
         <font class='Texte'>
            L'application Aria sur laquelle vous vous trouvez actuellement ne g�re que le d�p�t de dossiers de candidatures. L'<strong>inscription en
            ligne</strong> est une �tape diff�rente, g�r�e par une autre application.
            <br><br>
            Il est donc conseill� d'utiliser les adresses de contact (t�l�phone ou courriel) ou les formulaires d'aides de la page sur laquelle
            vous avez tent� de vous inscrire.
         </font>
      </td>
   </tr>
   <?php
      }
      elseif(isset($_GET["s"]) && $_GET["s"]=="justificatifs")
      {
         if(isset($_GET["v"]) && $_GET["v"]=="1")
         {
   ?>
   <tr>
      <td class='td-complet fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'>
            <strong>La date de verrouillage est pass�e mais je n'ai pas re�u la liste des justificatifs, pourquoi ?</strong>
         </font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu' style='white-space:normal;'>
         <font class='Texte'>
            <u><strong>Plusieurs cas possibles :</strong></u>
             <ul style='text-align:justify'>
               <li><strong>Il manque des renseignements obligatoires dans le menu "6-Autres renseignements".</strong>
               <br><u>Solution</u> : apr�s avoir s�lectionn� la bonne composante, v�rifiez que vous n'avez oubli� aucune question (si ce menu 6 n'apparait pas, 
               aucune information suppl�mentaire n'est demand�e). Une fois les informations manquantes compl�t�es, l'interface retentera automatiquement de 
               verrouiller vos voeux le lendemain.</li>

               <li style='padding-top:20px;'><strong>La fiche a bien �t� verrouill�e, mais vous n'avez pas re�u l'accus� de r�ception indiquant l'envoi des justificatifs.</strong>.
               <br><u>Solution</u> : v�rifiez qu'aucun message non lu n'est en attente dans la messagerie de l'interface Aria.</li>

               <li style='padding-top:20px;'><strong>Aucun justificatif n'a �t� configur� pour cette formation.</strong>
               <br><u>Solution</u> : lorsque cette erreur est rencontr�e, la scolarit� est automatiquement pr�venue et doit th�oriquement r�soudre
               rapidement ce probl�me. Vous devez simplement attendre que l'interface retente le verrouillage de votre voeu le lendemain.</li>

               <li style='padding-top:20px;'><strong>L'interface a rencontr� une autre erreur logicielle et n'a pas r�ussi � g�n�rer la liste des pi�ces � fournir.</strong>
               <br><u>Solution</u> : l'administrateur de l'application doit normalement recevoir une notification automatique d'erreur. Une fois le probl�me
               r�solu, votre voeu devrait �tre verrouill� d�s le lendemain.</li>
            </ul>
         </font>
      </td>
   </tr>
   <?php
         }
         elseif(isset($_GET["a"]) && $_GET["a"]=="1")
         {
   ?>
   <tr>
      <td class='td-complet fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'>
            <strong>J'ai re�u la liste des justificatifs, � qui et comment dois-je envoyer tous ces documents ?</strong>
         </font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu' style='white-space:normal;'>
         <font class='Texte' style='vertical-align:bottom;'>
            Toutes les pi�ces doivent �tre envoy�es par <strong>voie postale</strong>, l'adresse de la scolarit� est normalement
            indiqu�e sur chaque liste de justificatifs. Vous pouvez �galement consulter <a href='<?php echo "$__DOC_DIR/composantes.php"; ?>' target='_blank' class='lien_bleu_12'><strong>cette page</strong></a>
            au cas o� l'adresse postale serait absente.
            <br><br>
            <font class='Texte_important'><strong><u>Important :</u></strong></font>
             <ul style='text-align:justify'>
               <li>N'envoyez jamais les pi�ces par courriel (<i>email</i>), sauf si la scolarit� l'autorise explicitement. Elle doit alors vous fournir une adresse �lectronique sp�cifique. 
               <u>V�rifiez bien que les pi�ces num�ris�es sont lisibles</u> avant de les envoyer.
               <li style='padding-top:10px;'>Tous les documents doivent �tre <u>traduits en fran�ais</u> (sauf si la scolarit� pr�cise le contraire).</li>
               <li style='padding-top:10px;'>Certaines composantes suivent des proc�dures sp�cifiques, lisez bien les consignes donn�es dans la liste des justificatifs, elles sont <strong>prioritaires</strong> sur certaines consignes indiqu�es par l'interface.</li>
            </ul>
         </font>
      </td>
   </tr>

   <?php
         }
         elseif(isset($_GET["n"]) && $_GET["n"]=="1")
         {
   ?>
   <tr>
      <td class='td-complet fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'>
            <strong>J'ai demand� plusieurs formations, combien de fois dois-je envoyer mes justificatifs ?</strong>
         </font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu' style='white-space:normal;'>
         <font class='Texte'>
            Tout d�pend de la composante proposant les formations que vous avez choisies. La proc�dure normale est d'envoyer <strong>un dossier complet</strong> pour 
            <strong>chaque formation</strong> choisie.
            <br><br>
            <font class='Texte_important'><strong><u>Important :</u></strong></font>
            <ul style='text-align:justify'>
               <li><strong>Lisez toujours int�gralement les listes de justificatifs</strong> re�ues : certaines composantes ne vous demanderont qu'un seul dossier.</li>
               <li>L'adresse postale peut �tre <strong>diff�rente</strong> entre deux formations de la m�me composante.</li>
            </ul>
         </font>         
      </td>
   </tr>

   <?php
         }
         elseif(isset($_GET["d"]) && $_GET["d"]=="1")
         {
   ?>
   <tr>
      <td class='td-complet fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'>
            <strong>Je n'ai pas encore les derniers relev�s de notes de mon ann�e en cours, que dois-je faire ?</strong>
         </font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu' style='white-space:normal;'>
         <font class='Texte'>
            Si vous ne poss�dez pas encore tous les documents, <strong>n'attendez pas pour envoyer ceux en votre possession</strong>, vous risqueriez d'�tre hors d�lai.
            <br><br>
            Vous pourrez envoyer le reste des pi�ces d�s leur obtention (par voie postale, ou par courriel <u>si la scolarit� vous l'a explicitement demand�</u>).
         </font>
      </td>
   </tr>
   <?php
         }
      }
      elseif(isset($_GET["s"]) && $_GET["s"]=="navigateur")
      {
   ?>
   <tr>
      <td class='td-complet fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'>
            <strong>L'interface ne s'affiche pas correctement / je reviens toujours sur la page d'accueil m�me lorsque je parviens � m'identifier.</strong>
         </font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu' style='white-space:normal;'>
         <font class='Texte'>
            Un navigateur r�cent est n�cessaire pour utiliser l'application ARIA. Voici une liste <u>non exhaustive</u> de navigateurs recommand�s :
            <br>
             <ul style='text-align:justify'>
               <li><a href='http://www.mozilla.com/firefox/' class='lien_bleu_12' target='_blank'>Mozilla Firefox</a> (gratuit - tous syst�mes d'exploitation)</li>
               <li><a href='http://www.opera.com/' class='lien_bleu_12' target='_blank'>Opera</a> (gratuit - tous syst�mes d'exploitation)</li>
               <li><a href='http://www.google.com/chrome' class='lien_bleu_12' target='_blank'>Google Chrome</a> (gratuit)</li>
               <li><a href='http://www.apple.com/fr/safari/' class='lien_bleu_12' target='_blank'>Apple Safari</a> (gratuit - Mac OS et Windows)</li>
               <li><a href='http://www.konqueror.org/' class='lien_bleu_12' target='_blank'>Konqueror</a> (gratuit - Linux)</li>
               <li>Microsoft Internet Explorer (version 7 ou sup�rieure)</a> (disponible par d�faut sous Microsoft Windows XP et sup�rieur)</li>
            </ul>
            <br>
            Votre navigateur doit �galement supporter les <i>Cookies</i>, vous pouvez vous r�f�rer � la documentation du logiciel
            pour v�rifier sa configuration.
         </font>
      </td>
   </tr>
   <?php
      }
      elseif(isset($_GET["s"]) && $_GET["s"]=="pdf")
      {
   ?>
   <tr>
      <td class='td-complet fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'>
            <strong>J'ai re�u un message contenant des fichiers au format PDF, mais je n'arrive pas � les ouvrir.</strong>
         </font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu' style='white-space:normal;'>
         <font class='Texte'>
            <u><strong>Trois cas possibles :</strong></u>
            <br>- votre navigateur ne sait pas ouvrir ces fichiers,
            <br>- aucun programme n'est disponible sur votre ordinateur pour les lire,
            <br>- le programme est bien install� mais il n'affiche plus le contenu des fichiers.
            <br>
            <br>L'installation de l'un des logiciels suivants devrait vous permettre d'ouvrir ces fichiers (exemples donn�s � titre indicatif) :
            <ul style='text-align:justify'>
               <li><a href='http://www.adobe.com/fr/' class='lien_bleu_12' target='_blank'>Adobe Acrobat Reader</a> (gratuit - la plupart des syst�mes d'exploitation est support�e)</li>
               <li><a href='http://www.foolabs.com/xpdf/index.html' class='lien_bleu_12' target='_blank'>Xpdf</a> (lecteur libre pour Linux)</li>
            </ul>
            <br>Dans le troisi�me cas, la <strong>r�installation</strong> ou la mise � jour du programme existant peuvent r�soudre le probl�me (les param�tres sont souvent r�initialis�s).
            <br><br>
         </font>
      </td>
   </tr>
   <?php
      }
      elseif(isset($_GET["s"]) && $_GET["s"]=="resultats")
      {
      ?>
   <tr>
      <td class='td-complet fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'>
            <strong>Quand et comment obtiendrai-je les r�sultats de mon admission ?</strong>
         </font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu' style='white-space:normal;'>
         <font class='Texte'>
            Pour chaque voeu, vous devez attendre que la commission p�dagogique examine votre dossier (� condition qu'il ait �t� jug� <u>recevable</u>).
            <br><br>
            De plus :
            <br>
             <ul style='text-align:justify'>
               <li>Les dates des commissions peuvent �tre diff�rentes entre les composantes et entre chaque formation, tous les r�sultats ne seront donc pas affich�s au m�me moment ;</li>
               <li>Certaines composantes attendent d'avoir saisi tous les r�sultats avant de les publier sur l'interface (publication diff�r�e) ;</li>
               <li>Aucun r�sultat ne sera donn� par t�l�phone ou par courriel, vous devez imp�rativement attendre la lettre officielle, c'est le seul document faisant foi.</li>
            </ul>
         </font>
      </td>
   </tr>
   <?php
      }
      elseif(isset($_GET["s"]) && $_GET["s"]=="scolarite")
      {
   ?>
   <tr>
      <td class='td-complet fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'>
            <strong>Je souhaite ajouter une formation, mais la session est d�j� ferm�e.</strong>
         </font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu' style='white-space:normal;'>
         <font class='Texte'>
            <u>V�rifiez tout d'abord les dates des diff�rentes sessions pour cette formation :</u>
            <ol style='list-style-type:decimal; text-align:justify'>
               <li>
                  Si une nouvelle session de candidatures est programm�e, vous devez attendre son ouverture afin de pouvoir s�lectionner la formation.
               </li>
               <li style='padding-top:20px;'>
                  Si aucune session n'est pr�vue, utilisez <a href='form_scolarite.php' class='lien_bleu_12' style='vertical-align:top;'><strong>ce formulaire</strong></a>
                  pour contacter directement la scolarit�.
                  <br />Vous devez �tre <strong>identifi�(e) sur l'interface</strong> pour pouvoir acc�der � cette page.
               </li>
            </ol>
         </font>
      </td>
   </tr>

   <?php
      }
      elseif(isset($_GET["s"]) && $_GET["s"]=="contact_scol")
      {
   ?>
   <tr>
      <td class='td-complet fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'>
            <strong>J'ai une question concernant les modalit�s d'acc�s � une formation, � qui dois-je m'adresser ?</strong>
         </font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu' style='white-space:normal;'>
         <font class='Texte'>
            <ol style='list-style-type:decimal; text-align:justify'>
               <li>
                  V�rifiez tout d'abord les informations sur le site de l'Universit�, <a href='http://www.unistra.fr/index.php?id=2107' target='_blank' class='lien_bleu_12' style='vertical-align:top;'><strong>sur cette page</strong></a>.
               </li>
               <li style='padding-top:20px;'>
                  Si vous n'avez pas trouv� l'information que vous cherchiez, utilisez <a href='form_scolarite.php' class='lien_bleu_12' style='vertical-align:top;'><strong>ce formulaire</strong></a>
                  pour contacter directement la scolarit�.
                  <br />Vous devez �tre <strong>identifi�(e) sur l'interface</strong> pour pouvoir acc�der � cette page.
               </li>
            </ol>
         </font>
      </td>
   </tr>

   <?php
      }
      elseif(isset($_GET["s"]) && $_GET["s"]=="contact_scol2")
      {
   ?>
   <tr>
      <td class='td-complet fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'>
            <strong>Je suis un(e) candidat(e) �tranger(e), on me demande d'envoyer des justificatifs ou des pi�ces qui n'existent pas dans mon pays. Que faire ?</strong>
         </font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu' style='white-space:normal;'>
         <font class='Texte' style='vertical-align:top;'>
            Nous vous conseillons d'utiliser <a href='form_scolarite.php' class='lien_bleu_12' style='vertical-align:top;'><strong>ce formulaire</strong></a>
            pour contacter directement la scolarit�. <u>Attention :</u> vous devez �tre identifi�(e) sur l'interface pour pouvoir acc�der � cette page.
         </font>
      </td>
   </tr>

   <?php
      }
      elseif(isset($_GET["s"]) && $_GET["s"]=="contact_admin")
      {
   ?>
   <tr>
      <td class='td-complet fond_menu2' style='padding:4px;'>
         <font class='Texte_menu2'>
            <strong>Mon probl�me ne se trouve pas dans ce tableau, � qui dois-je m'adresser ?</strong>
         </font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu' style='white-space:normal;'>
         <font class='Texte' style='vertical-align:top;'>
            Nous vous conseillons d'envoyer un courriel <a href='mailto:<?php echo $GLOBALS["__EMAIL_SUPPORT"]; ?>' class='lien_bleu_12' style='vertical-align:top;'><strong>� cette adresse</strong></a>
            pour une aide informatique. 
            <br /><br />
            <u><strong>Attention :</strong></u> pr�cisez bien vos <strong>nom</strong>, <strong>pr�nom</strong> et <strong>date de naissance</strong> afin que nous puissions vous identifier 
            sur l'interface Aria.
         </font>
      </td>
   </tr>

   <?php
      }
   ?>
   </table>

   <div class='centered_box' style='padding-top:20px;'>
      <a href='index.php' target='_self' class='lien2'><img border='0' src='<?php echo "$__ICON_DIR/back_32x32.png"; ?>' title='[Retour]' alt='Retour' desc='Retour'></a>
   </div>
</div>
<?php
   pied_de_page_simple();
?>
</body></html>
