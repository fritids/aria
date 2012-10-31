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

   include "../../../../../configuration/aria_config.php";
   include "$__INCLUDE_DIR_ABS/vars.php";
   include "$__INCLUDE_DIR_ABS/fonctions.php";
   include "$__INCLUDE_DIR_ABS/db.php";

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	// EN-TETE SIMPLIFIEE (pas de composante, pas de menu, rien
	en_tete_simple();

	// MENU SUPERIEUR SIMPLIFIE
	menu_sup_simple();
?>

<div class='main'>
	<?php
		titre_page_icone("[Aide] Module Apog�e : Configuration", "help-browser_32x32_fond.png", 15, "L");
	?>

	<div style='margin-left:auto; margin-right:auto; padding-bottom:20px; width:90%; text-align:justify;'>
		<font class='Texte_16'><u><strong>Fonction principale</strong></u></font>
		<p class='Texte' style='padding-bottom:15px;'>
			<strong>Modifier les param�tres du module APOGEE</strong>
		</p>
	
		<font class='Texte_16'><u><strong>Param�tres</strong></u></font>
      <p class='Texte' style='padding-bottom:15px;'>
         <u><strong>Premi�re lettre du num�ro d'autorisation d'inscription (avec prise de rendez-vous)</strong></u> :
         Lorsqu'un candidat est admis � s'inscrire (en pr�sentiel), un code d'autorisation lui est fourni. Ce code est construit � partir de diverses donn�es :
         <br>- une lettre correspondant � l'universit�
         <br>- les deux derniers chiffres de l'ann�e en cours
         <br>- initiales du candidat
         <br>- date de naissance du candidat
         <br>- code �tape de la formation (<strong>sans</strong> la Version d'Etape)
         <br><br>
         Ce menu sert � param�trer la lettre correspondant � l'universit� s�lectionn�e, le reste du code est ensuite g�n�r� automatiquement (utilisation de
         la <strong>macro "%CODE%"</strong>).
      </p>
      <p class='Texte' style='padding-bottom:15px;'>
         <u><strong>Pr�fixe du code OPI g�n�r� pour les Primo-Entrants</strong></u> :
         Lorsque le script d'extraction des Primo Entrants est ex�cut�, un num�ro d'inscription OPI (diff�rent du num�ro d'autorisation) est g�n�r� pour chaque
         admission. Ce code est un simple compteur incr�ment� automatiquement et pr�fix� par une ou plusieurs lettres. C'est ce pr�fixe que vous devez entrer dans
         ce champ.
         <br><br>
         <strong>Exemple :</strong> si le pr�fixe est "AR", les codes g�n�r�s auront pour format "AR00000001", "AR00000002", etc.
      </p>
		<p class='Texte' style='padding-bottom:15px;'>
			<u><strong>Message envoy� � un candidat Primo Entrant</strong></u> :
         Lorsque le script d'extraction des Primo Entrants est ex�cut�, un message interne est automatiquement envoy� � chaque candidat et pour chaque formation pour
         laquelle il a �t� admis. Pour �tre utile, ce message doit au moins contenir le <strong>Num�ro OPI</strong> g�n�r� par le script (macro <strong>%OPI%</strong>
         dans le message) ainsi que l'adresse du site sur lequel le candidat devra se rendre pour s'inscrire.
		</p>
		<p class='Texte' style='padding-bottom:15px;'>
			<u><strong>Message envoy� � un candidat Admis sous R�serve</strong></u> :
			Un candidat "Admis sous R�serve" n'est en th�orie pas d�finitivement admis : il doit encore apporter des documents prouvant qu'il v�rifie les derni�res conditions 
			impos�es par la scolarit� (la "r�serve" indiqu�e) pour �tre admis.
			<br /><br />
			Toutefois, la plupart de ces candidats v�rifiant habituellement les r�serves �mises, on les autorise souvent � prendre un rendez-vous rapidement (� d�faut de leur 
			permettre une (r�)inscription int�grale en ligne), d'o� la pr�sence d'un message sp�cifique pour ces derniers. Ce message doit normalement contenir le num�ro 
			d'autorisation ("%CODE%") lui permettant de prendre ce rendez-vous.
			<br /><br />
			Ces candidats sont extraits via le m�me script que les primo-entrants, mais le voeu n'est cette fois pas enregistr� (ce qui emp�che l'inscription int�grale).
      </p>

      <font class='Texte_16'><u><strong>D�tails</strong></u></font>
      <p class='Texte' style='padding-bottom:5px;'>
         <u><strong>Les macros suivantes sont utilisables dans le corps du message</strong></u> :
      </p>
      <p class='Texte' style='padding-bottom:5px;'>
         <strong>%OPI%</strong> : Num�ro d'inscription OPI g�n�r� pour permettre l'inscription des Primo-Entrants (IA-Primo)
      </p>
      <p class='Texte' style='padding-bottom:5px;'>
         <strong>%Formation%</strong> : Nom de la formation � laquelle le candidat a �t� admis
      </p>
      <p class='Texte' style='padding-bottom:5px;'>
         <strong>[Signature]</strong> : Signature du message : cette macro sera remplac�e par la valeur du param�tre "Signature des messages de l'application" 
         (cf. Param�trage syst�me). Attention, ce param�tre est diff�rent de la macro %signature% utilis�e dans les mod�les de lettres.
      </p>
      <p class='Texte' style='padding-bottom:5px;'>
         <strong>[lien=adresse html]lien cliquable[/lien]</strong> : lien HTML
         <br>Exemple : [lien=http://www.google.fr]Recherche Google[/lien]
      </p>
      <p class='Texte' style='padding-bottom:5px;'>
         <strong>[mail=adresse �lectronique]lien cliquable[/mail]</strong> : lien vers l'envoi d'un courriel
         <br>Exemple : [mail=admin@domaine.fr]cliquez ici pour envoyer un message[/mail]
      </p>
      <p class='Texte' style='padding-bottom:5px;'>
         <strong>[gras]Texte[/gras]</strong> : Texte en gras
      </p>
      <p class='Texte' style='padding-bottom:5px;'>
         <strong>[italique]Texte[/italique]</strong> : Texte en italique
      </p>
      <p class='Texte' style='padding-bottom:5px;'>
         <strong>[souligner]Texte[/souligner]</strong> : Texte soulign�
      </p>
      <p class='Texte' style='padding-bottom:5px;'>
         <strong>[centrer]Texte[/centrer]</strong> : Texte centr�
      </p>
      <p class='Texte' style='padding-bottom:5px;'>
         <strong>[important]Texte[/important]</strong> : Texte mis en valeur (d�pend de la feuille de style)
      </p>
	</div>
</div>
<?php
	pied_de_page();
?>
</body></html>
