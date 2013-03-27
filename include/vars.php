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

// Encodage par d�faut pour la fonction htmlspecialchars

$default_htmlspecialchars_encoding="ISO-8859-15";

// Conversion de l'ancienne configuration, si n�cessaire

if(!is_file(dirname(__FILE__)."/../configuration/aria_config.php") && is_file(dirname(__FILE__)."/../configuration/config.php"))
{
   include dirname(__FILE__)."/../configuration/config.php";

   $config_file=fopen(dirname(__FILE__)."/../configuration/aria_config.php", "w+b");

   $file_str="<?php
// ARIA - Configuration g�n�r�e par le script \"gestion/admin/config.php\"\n
// Param�tres de connexion � la base de donn�es PostgreSQL

// Adresse du serveur
\$__DB_HOST = \"$__DB_HOST\";

// Port
\$__DB_PORT = \"$__DB_PORT\";

// Nom de la base
\$__DB_BASE = \"$__DB_BASE\";

// Utilisation du chiffrement SSL
\$__DB_SSLMODE = \"".preg_replace("/sslmode=/i", "", $__DB_SSLMODE)."\";

// Utilisateur
\$__DB_USER = \"$__DB_USER\";

// Mot de passe
\$__DB_PASS = \"".quotemeta($__DB_PASS)."\";

// R�pertoires de l'application
// le reste de la configuration est construite � partir des deux param�tres suivants

// Racine du serveur HTTP (i.e DOCUMENT_ROOT)
\$__ROOT_DIR = \"$__ROOT_DIR\";

// R�pertoire contenant l'application, relativement � \"__ROOT_DIR\"
\$__MOD_DIR = \"$__MOD_DIR\";

// R�pertoire contenant les fichiers includes (absolu)
\$__INCLUDE_DIR_ABS= \"\$__ROOT_DIR/\$__MOD_DIR/include\";

?>";

   fwrite($config_file, $file_str);
   fclose($config_file);

   chmod(dirname(__FILE__)."/../configuration/aria_config.php", 0600);
}

// Construction de variables � partir du fichier de configuration 'aria_config.php'

if(is_file(dirname(__FILE__)."/../configuration/aria_config.php"))
   include dirname(__FILE__)."/../configuration/aria_config.php";

// TEMPORAIRE : les variables contenues dans $_SESSION["config"] sont transform�es en variables globales
// TODO : d�terminer la meilleure m�thode : globales ou sessions ?
if(isset($_SESSION["config"]))
{
   foreach($_SESSION["config"] as $var => $value)
      $GLOBALS["$var"]="$value";
}

// ===============================================================================
// Construction de l'arborescence en fonction des param�tres r�cup�r�s
// ===============================================================================

// Les chemins absolus ne doivent �tre utilis�s que pour la lecture/�criture de fichiers (i.e : pas les php classiques)

// Feuilles de styles et autres fichiers statiques
// (Disparition de "$__CSS_DIR")
$GLOBALS["__STATIC_DIR"]=$_SESSION["config"]["__STATIC_DIR"]="$GLOBALS[__MOD_DIR]/static";

// Documentation en ligne pour les candidats (diff�rente de l'aide contextuelle cot� gestion)
$GLOBALS["__DOC_DIR"]=$_SESSION["config"]["__DOC_DIR"]="$GLOBALS[__MOD_DIR]/doc";

// Gestion et candidats
$GLOBALS["__CAND_DIR"]=$_SESSION["config"]["__CAND_DIR"]="$GLOBALS[__MOD_DIR]/interface";
$GLOBALS["__GESTION_DIR"]=$_SESSION["config"]["__GESTION_DIR"]="$GLOBALS[__MOD_DIR]/gestion";

// Aide contextuelle pour la gestion
$GLOBALS["__GESTION_AIDE_DIR"]=$_SESSION["config"]["__GESTION_AIDE_DIR"]="$GLOBALS[__GESTION_DIR]/aide";

// Fichiers communs
$GLOBALS["__INCLUDE_DIR"]=$_SESSION["config"]["__INCLUDE_DIR"]="$GLOBALS[__MOD_DIR]/include";

// Librairie FPDF
$GLOBALS["__FPDF_DIR"]=$_SESSION["config"]["__FPDF_DIR"]="$GLOBALS[__INCLUDE_DIR]/fpdf";

// Fichiers Candidats
$GLOBALS["__CAND_COMP_STOCKAGE_DIR"]=$_SESSION["config"]["__CAND_COMP_STOCKAGE_DIR"]="$GLOBALS[__CAND_DIR]/fichiers/composantes";

// Module de messagerie / Candidats
$GLOBALS["__CAND_MSG_DIR"]=$_SESSION["config"]["__CAND_MSG_DIR"]="$GLOBALS[__CAND_DIR]/messagerie";

// Messagerie Candidats / stockage des messages
$GLOBALS["__CAND_MSG_STOCKAGE_DIR"]=$_SESSION["config"]["__CAND_MSG_STOCKAGE_DIR"]="$GLOBALS[__CAND_DIR]/fichiers/messagerie";

// Syst�me d'assistance pour les candidats
$GLOBALS["__CAND_ASSISTANCE_DIR"]=$_SESSION["config"]["__CAND_ASSISTANCE_DIR"]="$GLOBALS[__CAND_DIR]/assistance";

// Fichiers des composantes (gestion)
$GLOBALS["__GESTION_COMP_STOCKAGE_DIR"]=$_SESSION["config"]["__GESTION_COMP_STOCKAGE_DIR"]="$GLOBALS[__GESTION_DIR]/fichiers/composantes";

// Fichiers publics
$GLOBALS["__PUBLIC_DIR"]=$_SESSION["config"]["__PUBLIC_DIR"]="$GLOBALS[__MOD_DIR]/fichiers/composantes"; // R�serv� aux fichiers t�l�chargeables : justificatifs

// Messagerie Gestion
$GLOBALS["__GESTION_MSG_DIR"]=$_SESSION["config"]["__GESTION_MSG_DIR"]="$GLOBALS[__GESTION_DIR]/messagerie";
$GLOBALS["__GESTION_MSG_STOCKAGE_DIR"]=$_SESSION["config"]["__GESTION_MSG_STOCKAGE_DIR"]="$GLOBALS[__GESTION_DIR]/fichiers/messagerie";

// Modules (plugins) additionnels
$GLOBALS["__PLUGINS_DIR"]=$_SESSION["config"]["__PLUGINS_DIR"]="$GLOBALS[__GESTION_DIR]/admin/modules";

// Images, ic�nes et logo par d�faut
// __IMG_DIR est particuli�re : si une universit� dispose d'autres ic�nes dans un autre r�pertoire (cf Menu Administration / Universit�s),
// alors ce r�pertoire est prioritaire sur celui-ci.

$GLOBALS["__IMG_DIR"]=$_SESSION["config"]["__IMG_DIR"]=isset($_SESSION["img_dir"]) ? "$GLOBALS[__MOD_DIR]/images/$_SESSION[img_dir]" : "$GLOBALS[__MOD_DIR]/images";
$GLOBALS["__ICON_DIR"]=$_SESSION["config"]["__ICON_DIR"]="$GLOBALS[__IMG_DIR]/icones";
$GLOBALS["__LOGO_DEFAUT"]=$_SESSION["config"]["__LOGO_DEFAUT"]="$GLOBALS[__ICON_DIR]/logo.png";

// ==================================================================================
//                   CHEMINS ABSOLUS POUR LES REPERTOIRES PRECEDENTS
//      Automatiquement g�n�r�s - aucune modification ne devrait �tre n�cessaire
// ==================================================================================

$GLOBALS["__MOD_DIR_ABS"]=$_SESSION["config"]["__MOD_DIR_ABS"]="$GLOBALS[__ROOT_DIR]/$GLOBALS[__MOD_DIR]";
$GLOBALS["__STATIC_DIR_ABS"]=$_SESSION["config"]["__STATIC_DIR_ABS"]="$GLOBALS[__ROOT_DIR]/$GLOBALS[__STATIC_DIR]";
$GLOBALS["__INCLUDE_DIR_ABS"]=$_SESSION["config"]["__INCLUDE_DIR_ABS"]="$GLOBALS[__ROOT_DIR]/$GLOBALS[__INCLUDE_DIR]";
$GLOBALS["__FPDF_DIR_ABS"]=$_SESSION["config"]["__FPDF_DIR_ABS"]="$GLOBALS[__ROOT_DIR]/$GLOBALS[__FPDF_DIR]";
$GLOBALS["__CAND_COMP_STOCKAGE_DIR_ABS"]=$_SESSION["config"]["__CAND_COMP_STOCKAGE_DIR_ABS"]="$GLOBALS[__ROOT_DIR]/$GLOBALS[__CAND_COMP_STOCKAGE_DIR]";
$GLOBALS["__CAND_MSG_STOCKAGE_DIR_ABS"]=$_SESSION["config"]["__CAND_MSG_STOCKAGE_DIR_ABS"]="$GLOBALS[__ROOT_DIR]/$GLOBALS[__CAND_MSG_STOCKAGE_DIR]";
$GLOBALS["__CAND_ASSISTANCE_DIR_ABS"]=$_SESSION["config"]["__CAND_ASSISTANCE_DIR_ABS"]="$GLOBALS[__ROOT_DIR]/$GLOBALS[__CAND_ASSISTANCE_DIR]";
$GLOBALS["__PUBLIC_DIR_ABS"]=$_SESSION["config"]["__PUBLIC_DIR_ABS"]="$GLOBALS[__ROOT_DIR]/$GLOBALS[__PUBLIC_DIR]";
$GLOBALS["__GESTION_COMP_STOCKAGE_DIR_ABS"]=$_SESSION["config"]["__GESTION_COMP_STOCKAGE_DIR_ABS"]="$GLOBALS[__ROOT_DIR]/$GLOBALS[__GESTION_COMP_STOCKAGE_DIR]";
$GLOBALS["__GESTION_MSG_STOCKAGE_DIR_ABS"]=$_SESSION["config"]["__GESTION_MSG_STOCKAGE_DIR_ABS"]="$GLOBALS[__ROOT_DIR]/$GLOBALS[__GESTION_MSG_STOCKAGE_DIR]";
$GLOBALS["__IMG_DIR_ABS"]=$_SESSION["config"]["__IMG_DIR_ABS"]="$GLOBALS[__ROOT_DIR]/$GLOBALS[__IMG_DIR]";
$GLOBALS["__ICON_DIR_ABS"]=$_SESSION["config"]["__ICON_DIR_ABS"]="$GLOBALS[__ROOT_DIR]/$GLOBALS[__ICON_DIR]";
$GLOBALS["__LOGO_DEFAUT_ABS"]=$_SESSION["config"]["__LOGO_DEFAUT_ABS"]="$GLOBALS[__ROOT_DIR]/$GLOBALS[__LOGO_DEFAUT]";
$GLOBALS["__PLUGINS_DIR_ABS"]=$_SESSION["config"]["__PLUGINS_DIR_ABS"]="$GLOBALS[__ROOT_DIR]/$GLOBALS[__PLUGINS_DIR]";

// Construction de la date limite pour distinguer les anciennes candidatures des nouvelles (= ann�e suivante)
// On se basera sur l'identifiant d'une candidature (format AA MM JJ HH MM SS MS(5), sans les espaces)
// $__PERIODE : ann�e concern�e (candidatures 2007-2008 => p�riode 2007)

// P�riode "absolue" = ann�e universitaire pour les candidats actuels
// Obsol�te : valeur construite � partir de la configuration

/*
if(!array_key_exists("__PERIODE_ABSOLUE", $GLOBALS))
   $__PERIODE_ABSOLUE=(date('n') < $GLOBALS["__MOIS_LIMITE_CANDIDATURE"]) ? date('Y')-1 : date('Y');
*/
// P�riode configur�e par un gestionnaire
if(isset($_SESSION["current_user_periode"]) && ctype_digit($_SESSION["current_user_periode"]))
   $__PERIODE=$_SESSION["current_user_periode"];

/*
else
   $__PERIODE=$__PERIODE_ABSOLUE;
*/

// ============================================================================
//    Constantes pour diff�rentes listes
// ============================================================================

// Identifiant de l'utilisateur syst�me (exp�diteur des messages automatiques)
$__USER_SYSTEME_ID=0;

// Etat des pr�candidatures
$__PREC_ANNULEE=-2;
$__PREC_NON_RECEVABLE=-1;
$__PREC_NON_TRAITEE=0;
$__PREC_RECEVABLE=1;
$__PREC_EN_ATTENTE=2;
$__PREC_PLEIN_DROIT=3;


// TODO : tableau � int�grer dans la base de donn�es, comme les d�cisions
$tab_recevabilite=array("$__PREC_ANNULEE" => "Annul�e par le candidat",
                        "$__PREC_NON_RECEVABLE" => "Non recevable",
                        "$__PREC_NON_TRAITEE" => "Non trait�e",
                        "$__PREC_RECEVABLE" => "Recevable",
                        "$__PREC_EN_ATTENTE" => "En attente",
                        "$__PREC_PLEIN_DROIT" => "Plein droit");


// Justification des cursus
$__CURSUS_NON_JUSTIFIE=-3;
$__CURSUS_DES_OBTENTION=-2;
$__CURSUS_PIECES=-1;
$__CURSUS_EN_ATTENTE=0;
$__CURSUS_VALIDE=1;
$__CURSUS_NON_NECESSAIRE=2;
$__CURSUS_INSCRIPTION=3; // Documents � fournir lors de l'inscription administrative

// Types d'�l�ments pour les dossiers
$__ELEM_TYPE_FORM=0;
$__ELEM_TYPE_UN_CHOIX=1;
$__ELEM_TYPE_MULTI_CHOIX=2;


// ========================================== </D�cisions> ===============================================
// TODO :
// - revoir le caract�re "d�cision d�finitive" : ajouter un flag dans la base de donn�es
// - ajouter la gestion int�grale de ces d�cisions dans l'interface d'administration
// => mises � jour assez lourdes, les d�cisions �tant pr�sentes partout
// En attendant, il est vivement d�conseill� de modifier les d�cisions existantes.

// Inf�rieures � 0 : traitement non termin�
$__DOSSIER_ADMIS_AVANT_CONFIRMATION=-7;
$__DOSSIER_ENTRETIEN_TEL=-6;
$__DOSSIER_LISTE_ENTRETIEN=-5;
$__DOSSIER_ENTRETIEN=-4;
$__DOSSIER_LISTE=-3;
$__DOSSIER_EN_ATTENTE=-2;
$__DOSSIER_SOUS_RESERVE=-1;

// �gale � 0 : non trait�
$__DOSSIER_NON_TRAITE=0;

// sup�rieures � 0 : d�cision prise
$__DOSSIER_ADMIS=1;
$__DOSSIER_REFUS=2;
$__DOSSIER_TRANSMIS=3;
$__DOSSIER_REFUS_ENTRETIEN=4;
$__DOSSIER_ADMIS_ENTRETIEN=5;
$__DOSSIER_ADMIS_LISTE_COMP=6;
$__DOSSIER_ADMIS_RECOURS=7;
$__DOSSIER_REFUS_RECOURS=8;
$__DOSSIER_DESISTEMENT=9;
$__DOSSIER_ADMISSION_CONFIRMEE=10;

// Listes des d�cisions � afficher si une formation ne n�cessite pas d'entretiens
$__DOSSIER_DECISIONS_SANS_ENTRETIEN=array("-7","-3","-2","-1","0","1","2","3","6","7","8","9","10");

// Liste des d�cisions � n'afficher que si la formation n�cessite un entretien compl�mentaire
// (ces d�cisions sont celles pouvant �tre prises AVANT l'entretien)
$__DOSSIER_DECISIONS_AVANT_ENTRETIEN=array("-7","-6", "-5","-4","-3","-2","-1","1","2","3","6","10");

// Liste des d�cisions � n'afficher que si la formation n�cessite un entretien compl�mentaire
// (ces d�cisions sont celles pouvant �tre prises APRES l'entretien)
$__DOSSIER_DECISIONS_APRES_ENTRETIEN=array("-7","-5","4","5","6","7","8","9","10");

$__DOSSIER_DECISIONS_COURTES=array(
"-7" => "A-AC",
"-6" => "E-TEL",
"-5" => "LC-AE",
"-4" => "E",
"-3" => "LC", 
"-2" => "EA", 
"-1" => "SR", 
"0" => "NT", 
"1" => "A", 
"2" => "R", 
"3" => "DT", 
"4" => "R-AE", 
"5" => "A-AE",
"6" => "A-LC", 
"7" => "A-REC", 
"8" => "R-REC",
"9" => "D",
"10" => "AD-C");

// ========================================== </D�cisions> ===============================================

// Statut des frais de dossiers (todo : � mettre dans la base)
$__STATUT_FRAIS_EN_ATTENTE=0;
$__STATUT_FRAIS_ACQUITTES=1;
$__STATUT_FRAIS_BOURSIER=2;
$__STATUT_FRAIS_DISPENSE=3;
$__STATUT_FRAIS_NON_ACQUITTES=-1;

// =======================================================================================================
// Finalit�s des formations (todo : � mettre dans la base)
$__FIN_CLASSIQUE=0;
$__FIN_RECH=1;
$__FIN_PRO=2;

$tab_finalite=array("0" => "", "1" => "- Recherche", "2" => "- Pro");
$tab_finalite_abbregee=array("0" => "", "1" => "R", "2" => "P");
$tab_finalite_semicomplete=array("0" => "", "1" => "Recherche", "2" => "Professionnelle");
$tab_finalite_lettres=array("0" => "", "1" => "Finalit� Recherche", "2" => "Finalit� Professionnelle");
$tab_finalite_complete=$tab_finalite_lettres; // alias
// =======================================================================================================

// Mode de la fiche (obsolete)

$__MODE_PREC=0;
$__MODE_COMPEDA=1;

// types de messages
$__ERREUR=0;
$__SUCCES=1;
$__WARNING=2;
$__QUESTION=3;
$__INFO=4;

// Niveaux (utilisateurs & admin)
$__LVL_ADMIN=60;
$__LVL_SUPER_RESP=50;
$__LVL_RESP=40;
$__LVL_SCOL_PLUS=30;
$__LVL_SCOL_MOINS=20;
$__LVL_SAISIE=15;
$__LVL_CONSULT=10;
$__LVL_SUPPORT=5;
$__LVL_DESACTIVE=-10;

$tab_niveau=array("$__LVL_ADMIN" => "Administrateur de l'application",
                  "$__LVL_SUPER_RESP" => "Responsable de scolarit� centrale",
                  "$__LVL_RESP" => "Responsable administratif",
                  "$__LVL_SCOL_PLUS" => "Scolarit� avec droits suppl�mentaires",
                  "$__LVL_SCOL_MOINS" => "Scolarit� avec droits limit�s", 
                  "$__LVL_SAISIE" => "Saisie uniquement", 
                  "$__LVL_CONSULT" => "Consultation uniquement",
                  "$__LVL_SUPPORT" => "Support Informatique",
                  "$__LVL_DESACTIVE" => "Compte d�sactiv�");

$tab_niveau_menu=array("$__LVL_ADMIN" => "[A]",
                       "$__LVL_SUPER_RESP" => "[RA+]",
                       "$__LVL_RESP" => "[RA]",
                       "$__LVL_SCOL_PLUS" => "[Sc+]",
                       "$__LVL_SCOL_MOINS" => "[Sc]",
                       "$__LVL_SAISIE" => "[Sa]",
                       "$__LVL_CONSULT" => "[Co]",
                       "$__LVL_SUPPORT" => "[SUP]",
                       "$__LVL_DESACTIVE" => "[OFF]");

// ======================================================================================
//                           TYPES DE MESSAGES CONFIGURABLES
// ======================================================================================

$__MSG_TYPE_VERROUILLAGE="1";

$__MSG_TYPES=array($__MSG_TYPE_VERROUILLAGE => array("titre" => "Verrouillage de la fiche d'un candidat",
                                                     "desc" => "Message envoy� automatiquement lors du verrouillage de la fiche d'un candidat :
- son objectif est de fournir au candidat la suite de la proc�dure concernant les justificatifs � fournir (cf. Editeur de Justificatifs),
- la pr�sence de pi�ces jointes li�es aux justificatifs est automatiquement signal�e et les liens sont ajout�s (aucune macro n'est n�cessaire).",
                                                     "liste_macros" => "<ul>
<li><strong><font class='Texte_important'>%justificatifs%</font></strong> : lien vers le fichier PDF contenant la liste des justificatifs</li>
<li><strong><font class='Texte_important'>%recapitulatif%</font></strong> : lien vers le fichier PDF contenant le r�capitulatif de la fiche du candidat</li>
<li><strong><font class='Texte_important'>%date_limite%</font></strong> : date limite de r�ception des pi�ces demand�es (cf. dates des sessions de candidatures)</li>
<li><strong>[gras]...[/gras]</strong> : texte en gras</li>
<li><strong>[souligner]...[/souligner]</strong> : texte soulign�</li>
<li><strong>[important]...[/important]</strong> : texte mis en valeur</li>
<li><strong>[mail=adresse@]texte[/mail]</strong> : lien pour l'envoi d'un courriel</li>
<li><strong>[lien=http://adresse]texte[/lien]</strong> : lien vers une page HTML</li>
</ul>",
                                                     "defaut" => "\n\nBonjour %Civ% %Nom%,\n
Le d�lai imparti pour modifier cette formation est �chu. Apr�s r�ception de l'ensemble des pi�ces requises (liste dans ce message), vos demandes pourront �tre trait�es par la ou les scolarit�s.\n
La proc�dure � suivre est maintenant la suivante :\n
1/ Cliquez sur chacun des liens suivants :
%recapitulatif%
%justificatifs%\n
2/ Enregistrez puis imprimez ces documents PDF. Conservez-les car ils pourront vous reservir plus tard.\n
3/ Envoyez ces documents ainsi que les pi�ces demand�es dans le document \"Justificatifs\" par courrier � l'adresse postale indiqu�e dans ce message (<b>sauf</b> si une adresse sp�cifique est pr�cis�e dans la liste des justificatifs).\n
[important][gras]IMPORTANT[/gras] :\n
Sauf consignes contraires de la scolarit� [gras](v�rifiez bien le document \"Liste des justificatifs\" ci-dessus)[/gras] :\n
- vous devez envoyer vos justificatifs � la scolarit� le plus rapidement possible (n'attendez pas la date limite du %date_limite%). Les dossiers hors d�lais seront examin�s lors de la session suivante. Si aucune autre session n'est pr�vue, votre dossier risque de ne pas �tre trait�.
- pour les candidatures � choix multiples (sp�cialit�s regroup�es dans le menu 5-Pr�candidatures), vous devez envoyer [gras]autant d'exemplaires[/gras] de vos justificatifs [gras]que de formations s�lectionn�es[/gras] dans cette composante. Si vous n'envoyez pas vos justificatifs en plusieurs exemplaires, toutes vos candidatures [gras]ne pourront pas �tre trait�es[/gras].[/important]\n\n
Vous pouvez d�s � pr�sent suivre l'�volution de votre fiche en ligne (sur cette interface) et vous recevrez prochainement d'autres messages concernant le traitement de votre dossier.\n
Aucune information suppl�mentaire sur l'�tat de votre candidature ne sera donn�e par t�l�phone.\n\n
[gras]Rappel[/gras] : le d�p�t d'une pr�candidature en ligne ne constitue en aucun cas une admission dans la ou les formations demand�es.\n\n
Cordialement,\n\n
--
%adresse_scolarite%\n
%composante%
%universite%")
);


// ======================================================================================
//                           HISTORIQUE
// ======================================================================================

// Ev�nements de l'historique (�v�nements communs)
$__EVT_ID_COMP=0;           // s�lection de composante
$__EVT_ID_LOGIN=1;          // connexion
$__EVT_ID_REINIT=2;         // r�initialisation du pass

// Historique - �v�nements candidats
$__EVT_ID_C_REG=3;          // enregistrement
$__EVT_ID_C_ID=4;           // identit�
$__EVT_ID_C_CURSUS=5;       // cursus
$__EVT_ID_C_LANG=6;         // langues
$__EVT_ID_C_INFO=7;         // infos compl�mentaires
$__EVT_ID_C_RENS=8;         // autres renseignements
$__EVT_ID_C_PREC=9;         // pr�candidatures
$__EVT_ID_C_MSG=10;         // messages candidat -> gestion
$__EVT_ID_C_DOC=11;         // Documents PDF
$__EVT_UD_C_RECUP=12;       // R�cup�ration des identifiants

// Historique - �v�nements gestion
$__EVT_ID_G_ID=104;         // identit� (du candidat)
$__EVT_ID_G_CURSUS=105;     // cursus ('')
$__EVT_ID_G_LANG=106;       // langues ('')
$__EVT_ID_G_INFO=107;       // infos compl�mentaires ('')
$__EVT_ID_G_RENS=108;       // autres renseignements ('')
$__EVT_ID_G_PREC=109;       // pr�candidatures ('')
$__EVT_ID_G_MSG=110;        // messages gestion -> candidat
$__EVT_ID_G_MAN=111;        // mode manuel (� d�tailler)
$__EVT_ID_G_LISTE=112;      // liste compl�mentaire
$__EVT_ID_G_MASSE=113;      // gestion de masse
$__EVT_ID_G_ADMIN=114;      // admin
$__EVT_ID_G_DOC=115;        // Documents PDF
$__EVT_ID_G_SESSION=116;    // Sessions
$__EVT_ID_G_LOCK=117;       // Verrouillage manuel
$__EVT_ID_G_UNLOCK=118;     // D�verrouillage manuel
$__EVT_ID_G_LOCKDATE=119;   // Modification de la date du verrouillage
$__EVT_ID_G_FILTRES=120;    // Filtre entre les formations

// Historique - �venement syst�me (automatiques)
$__EVT_ID_S_LOCK=200;       // Verrouillage (script)
$__EVT_ID_S_UNLOCK=201;      // D�verrouillage (cet �v�nement n'arrive jamais via le script :)

// Nom des �v�nements dans un tableau pour affichage sur la page Historique
$tab_evenements=array(
"$__EVT_ID_COMP" => "S�lection composante",
"$__EVT_ID_LOGIN" => "Connexion",
"$__EVT_ID_REINIT" => "R�initialisation du mot de passe",
"$__EVT_ID_C_REG" => "Enregistrement",
"$__EVT_ID_C_ID" => "Menu Identit�",
"$__EVT_ID_C_CURSUS" => "Menu Cursus",
"$__EVT_ID_C_LANG" => "Menu Langues",
"$__EVT_ID_C_INFO" => "Menu Infos Compl�mentaires",
"$__EVT_ID_C_RENS" => "Menu Autres Renseignements",
"$__EVT_ID_C_PREC" => "Menu Pr�candidatures",
"$__EVT_ID_C_MSG" => "Message Candidat => Gestion",
"$__EVT_ID_C_DOC" => "Documents PDF",
"$__EVT_ID_G_ID" => "Menu Identit�",
"$__EVT_ID_G_CURSUS" => "Menu Cursus",
"$__EVT_ID_G_LANG" => "Menu Langues",
"$__EVT_ID_G_INFO" => "Menu Infos Compl�mentaires",
"$__EVT_ID_G_RENS" => "Menu Autres Renseignements",
"$__EVT_ID_G_PREC" => "Menu Pr�candidatures",
"$__EVT_ID_G_MSG" => "Message Gestion => Candidat",
"$__EVT_ID_G_MAN" => "Menu Mode Manuel",
"$__EVT_ID_G_LISTE" => "Liste Compl�mentaire",
"$__EVT_ID_G_MASSE" => "Gestion de Masse",
"$__EVT_ID_G_ADMIN" => "Admin",
"$__EVT_ID_G_DOC" => "Documents PDF",
"$__EVT_ID_G_SESSION" => "Sessions",
"$__EVT_ID_S_LOCK" => "Verrouillage (script)",
"$__EVT_ID_S_UNLOCK" => "D�verrouillage",
"$__EVT_ID_G_LOCK" => "Verrouillage manuel",
"$__EVT_ID_G_UNLOCK" => "D�verrouillage manuel",
"$__EVT_ID_G_LOCKDATE" => "Date verrouillage",
"$__EVT_ID_G_FILTRES" => "Filtres formations");


// ======================================================================================
//                                 MESSAGERIE INTEGREE
// ======================================================================================

// Messagerie - Dossiers
$__MSG_INBOX=1;
$__MSG_SENT=2;
$__MSG_TRAITES=3;
$__MSG_TRASH=4;

$__MSG_DOSSIERS=array(   "$__MSG_INBOX" => "Bo�te de r�ception",
                        "$__MSG_SENT" => "Envoy�s",
                        "$__MSG_TRAITES" => "Trait�s",
                        "$__MSG_TRASH" => "Corbeille",);

// Flag pour empecher l'envoi d'un message de notification
$__FLAG_MSG_NO_NOTIFICATION=0;
$__FLAG_MSG_NOTIFICATION=1;

// ======================================================================================


// Justificatifs : conditions sur les nationalit�s

$__COND_NAT_TOUS=0;
$__COND_NAT_FR=1;
$__COND_NAT_NON_FR=2;
$__COND_NAT_HORS_UE=3;
$__COND_NAT_UE=4;

// A compl�ter en fonction de l'adh�sion de nouveaux pays
// Liste obsol�te
$__PAYS_UE=array(
'Allemagne', 'Allemand','Allemande',
'Autriche', 'Autrichien', 'Autrichienne',
'Belgique', 'Belge',
'Bulgarie', 'Bulgare',
'Chypre', 'Chypriotte','Chypriote',
'Danemark','Danois','Danoise',
'Espagne','Espagnol','Espagnole',
'Estonie','Estonien','Estonienne',
'Finlande','Finlandais','Finlandaise',
'Gr�ce','Greque','Grec','Gr�que',
'Hongrie','Hongrois','Hongroise',
'Irlande','Irlandais','Irlandaise',
'Italie','Italien','Italienne',
'Lettonie','Letton','Lettone',
'Lituanie','Lituanien','Lituanienne',
'Luxembourg','Luxembourgeois','Luxembourgeoise',
'Malte','Maltais','Maltaise',
'Pays-Bas','N�erlandais','N�erlandaise','Neerlandais','Neerlandaise',
'Pologne','Polonais','Polonaise',
'Portugal','Portugais','Portugaise',
'Royaume-Uni','Anglais','Anglaise',
'R�publique Tch�que','Tch�que','Tcheque',
'Roumanie','Roumain','Roumaine',
'Slovaquie','Slovaque',
'Slov�nie','Slov�ne','Slovene','Slov�ne',
'Su�de', 'Su�dois','Su�doise','Suedois', 'Suedoise');

// Nouvelle liste
$__PAYS_UE_ISO=array('DE','AT','BE','BG','CY','DK','ES','EE','FI','GR','HU','IE','IT','LV','LT','LU','MT','NL','PL','PT','GB','CZ','RO','SK','SI','SE');


// Mois (pour �viter certaines boucles)
$__MOIS=array(
"1" => "Janvier",
"2" => "F�vrier",
"3" => "Mars",
"4" => "Avril",
"5" => "Mai",
"6" => "Juin",
"7" => "Juillet",
"8" => "Ao�t",
"9" => "Septembre",
"01" => "Janvier",
"02" => "F�vrier",
"03" => "Mars",
"04" => "Avril",
"05" => "Mai",
"06" => "Juin",
"07" => "Juillet",
"08" => "Ao�t",
"09" => "Septembre",
"10" => "Octobre",
"11" => "Novembre",
"12" => "D�cembre");

if(!isset($_SESSION["css"]))
   $_SESSION["css"]="typo.css";

/*
if(!isset($_SESSION["couleur_fond"]) || !isset($_SESSION["couleur_menu"]) || !isset($_SESSION["couleur_menu2"]) || !isset($_SESSION["fond_page"]))
{
   $_SESSION["fond_page"]="#FFFFFF";
   $_SESSION["couleur_fond"]="#F0FAFF";
   $_SESSION["couleur_menu"]="#DDEEFF";
   $_SESSION["couleur_menu2"]="#CCDDEE";
}
*/


// MENU SUPERIEUR
$__NO_MENU=-1;
$__MENU_COMP=1;
$__MENU_FICHE=2;
$__MENU_RECH=3;
$__MENU_MSG=4;
$__MENU_DOC=5;

// MENUS COLONNE GAUCHE
// TODO : le menu 3 est sp�cifique � l'ufr mathinfo  : attention � l'export !
$menu=array(   '0' => '0 - Documentation',
               '1' => '1 - Identit�',
               '2' => '2 - Cursus',
               '3' => '3 - Langues',
               '4' => '4 - Infos Compl�mentaires',
               // '6' => '6 - Candidatures Ext�rieures',
               '5' => '5 - Pr�candidatures');
/*
$menu_gestion=array(   '1' => '1 - Identit�',
                     '2' => '2 - Cursus',
                     '3' => '3 - Langues',
                     '4' => '4 - Infos Compl�mentaires',
                     '5' => '5 - Autres Renseignements',
                     '6' => '6 - Pr�candidatures',
                     '7' => '7 - Mode Manuel',
                     '8' => '8 - Documents PDF',
                     '9' => '9 - Historique',
                     '10' => '10 - Messagerie');
*/                     

$menu_gestion=array(   '1' => '1 - Identit�',
                     '2' => '2 - Cursus',
                     '3' => '3 - Langues',
                     '4' => '4 - Infos Compl�mentaires',
                     '5' => '5 - Autres Renseignements',
                     '6' => '6 - Pr�candidatures',
                     '7' => '7 - Mode Manuel',
                     '8' => '8 - Documents PDF',
                     '9' => '9 - Historique',
                     '10' => '10 - Messagerie');

// Sous menus pour la page de configuration de l'interface
$menu_config=array(
'1' => '1 - Param�tres HTTP',
'2' => '2 - Param�tres LDAP',
'3' => '3 - Administration',
'4' => '4 - Param�tres interface',
'5' => '5 - Debug');

// Source des comptes

$__COMPTE_MANUEL="0";
$__COMPTE_LDAP="1";

$__SOURCE_COMPTE=array("$__COMPTE_MANUEL" => "Compte manuel",
                       "$__COMPTE_LDAP" => "LDAP");

// Redirections
// Code utilis� pour g�n�rer des fichiers redirigeant les utilisateurs (candidats / gestionnaires) lorsqu'ils essaient
// d'acc�der � des r�pertoires auxquels ils ne devraient pas (messagerie notamment, les r�pertoires doivent rester "lisibles"
// par Apache mais pas pour les utilisateurs, d'o� la cr�ation d'indexes dynamiques

$__REDIRECTION_CANDIDAT=
"<?php
   include \"$__MOD_DIR_ABS/configuration/aria_config.php\";

   Header(\"Location:\$__MOD_DIR/index.php\");
   exit();
?>";


$__REDIRECTION_GESTION=
"<?php
   include \"$__MOD_DIR_ABS/configuration/aria_config.php\";

   Header(\"Location:\$__GESTION_DIR/login.php\");
   exit();
?>";

?>
