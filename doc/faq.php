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

	include "../configuration/aria_config.php";
	include "$__INCLUDE_DIR_ABS/vars.php";
	include "$__INCLUDE_DIR_ABS/db.php";
	include "$__INCLUDE_DIR_ABS/fonctions.php";

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	en_tete_candidat_simple();
	menu_sup_simple();
?>

<div class='main'>
	<div class='centered_box'>
		<p style='margin-top:0px; padding-top:4px;'><font class='Titre'>D�p�t de dossiers de pr�candidature</font></p>
		<p><strong>III - Questions / R�ponses</strong>
		<p><a href='documentation.php' class='lien2a'><img src='<?php echo "$__ICON_DIR/back_32x32_fond.png"; ?>' alt='Retour' border='0'></a></p>
	</div>

	<div style='width:90%; text-align:justify; margin:0px auto 0px auto; padding-bottom:30px;'>
		<ul style='list-style-type:none; padding-bottom:40px;'>
			<li><a href='#Q1' class='lien2a'>1 - Je me suis inscrit sur l'interface, mais je ne trouve pas les dossiers � t�l�charger. O� sont-ils ?</a></li>
			<li><a href='#Q2' class='lien2a'>2 - En d�posant ma candidature en ligne, je dois tout de m�me envoyer des documents par courrier � la Scolarit�. Pourquoi et quel est alors l'avantage de l'interface en ligne ?</a></li>
			<li><a href='#Q3' class='lien2a'>3 - Dans l'onglet "Cursus", je dois renseigner l'ann�e en cours, comment la justifier puisque je n'ai pas encore le dipl�me ?</a></li>
			<li><a href='#Q4' class='lien2a'>4 - Je n'ai acc�s � aucune imprimante, que dois-je faire avec les documents qui m'ont �t� envoy�s sur la messagerie ?</a></li>
			<li><a href='#Q5' class='lien2a'>5 - Le d�lai imparti pour remplir ma fiche est termin�. Les formations sont verrouill�es, mais j'avais encore des modifications � effectuer. Que dois-je faire ?</a></li>
			<li><a href='#Q6' class='lien2a'>6 - La date d'une formation est pass�e, mais je n'ai pas re�u le message comme annonc�. Que s'est-il pass� ?</a></li>
			<li><a href='#Q7' class='lien2a'>7 - Quel est le format des pi�ces jointes au message r�capitulatif ? Avec quel programme dois-je les ouvrir ?</a></li>
			<li><a href='#Q8' class='lien2a'>8 - J'ai effectu� tout ou partie de ma scolarit� dans cette universit�. Dois-je quand m�me renvoyer tous les justificatifs demand�s ?</a></li>
			<li><a href='#Q9' class='lien2a'>9 - J'ai une question � poser mais elle n'appara�t ni dans la documentation, ni sur cette page, � qui dois-je m'adresser ?</a></li>
		</ul>

		<a name="Q1">
		<p class='fond_menu Texte_menu' style='font-weight:bold;'>&nbsp;&nbsp;1 - Q : Je me suis inscrit(e) sur l'interface, mais je ne trouve pas les dossiers � t�l�charger. O� sont-ils ?</p>
		<p class='Texte'>
			<strong>R : </strong>Sur l'Interface de Pr�candidatures, il n'est <u>plus n�cessaire</u> de t�l�charger de dossier papier ou PDF.
		</p>
		<p class='Texte'>
			L'interface EST votre dossier, avec vos informations personnelles, votre cursus, votre niveau en langues �trang�res, vos motivations, etc.
			Une fois tous les onglets consciencieusement remplis (identit�, cursus, ... sans oublier les <u>FORMATIONS demand�es</u>),
			il vous suffit ensuite d'attendre le verrouillage automatique de vos voeux (le d�lai par d�faut est 48 heures). Ce d�lai vous est laiss�
			pour que vous puissiez modifier tranquillement votre fiche, sans contrainte particuli�re.
			</font>
		</p>
		<p class='Texte' style='padding-bottom:20px;'>
			Une fois les formations verrouill�es, vous recevrez (sur la messagerie de l'interface, avec notification par courriel) la liste des justificatifs � renvoyer � la scolarit� PAR VOIE POSTALE.
		</p>

		<a name="Q2">
		<p class='fond_menu Texte_menu' style='font-weight:bold;'>&nbsp;&nbsp;2 - Q : En d�posant ma candidature en ligne, je dois tout de m�me envoyer des documents par courrier � la Scolarit�. Pourquoi et quel est alors l'avantage de l'interface en ligne ?</p>
		<p class='Texte'>
			<strong>R : </strong>Les pi�ces � fournir par courrier sont n�cessaires car elles servent � justifier les informations que vous avez entr�es sur l'interface, notamment au niveau de
			votre cursus. Ces pi�ces seront conserv�es dans votre dossier, et � ce titre, aucun justificatif ne sera accept� par voie �lectronique (<i>e-mail</i>).
			L'avantage du d�p�t de pr�candidatures est qu'il acc�l�re consid�rablement le temps de traitement de l'ensemble des dossiers.
		</p>
		<p class='Texte' style='padding-bottom:20px;'>
			Les premi�res cons�quences �videntes sont les suivantes :
			<br>- vous saurez rapidement si votre pr�candidature est <b>recevable ou non</b> (avant m�me de passer par la Commission P�dagogique)
			<br>- apr�s la Commission P�dagogique, vous recevrez une r�ponse (admission, liste d'attente, admission sous r�serve, refus, ...) plus rapidement.
		</p>

		<a name="Q3">
		<p class='fond_menu Texte_menu' style='font-weight:bold;'>&nbsp;&nbsp;3 - Q : Dans l'onglet "Cursus", je dois renseigner l'ann�e en cours, comment la justifier puisque je n'ai pas encore le dipl�me ?</p>
		<p class='Texte' style='padding-bottom:20px;'>
			<strong>R : </strong>Les justificatifs seront � transmettre � la Scolarit� le plus rapidement possible. En effet, dans la mesure o� les admissions
			d�pendent des dipl�mes obtenus, votre dossier pourra selon les cas �tre "Admis sous r�serve", c'est � dire en attente des
			derniers justificatifs de votre part. <b>N'attendez surtout pas d'obtenir ce dipl�me pour envoyer les justificatifs que vous poss�dez d�j� !</b>.
		</p>

		<a name="Q4">
		<p class='fond_menu Texte_menu' style='font-weight:bold;'>&nbsp;&nbsp;4 - Q : Je n'ai acc�s � aucune imprimante, que dois-je faire avec les documents qui m'ont �t� envoy�s sur la messagerie ?</strong></p>
		<p class='Texte' style='padding-bottom:20px;'>
			<strong>R : </strong>Ces documents doivent <b>imp�rativement</b> �tre imprim�s afin d'�tre renvoy�s au service de Scolarit�. Si vous n'avez acc�s � aucune imprimante, vous pouvez
			faire une demande de dossier papier (dans les diff�rents services de Scolarit�), mais vous perdrez les avantages des pr�candidatures en ligne.
		</p>

		<a name="Q5">
		<p class='fond_menu Texte_menu' style='font-weight:bold;'>&nbsp;&nbsp;5 - Q : Le d�lai imparti pour remplir ma fiche est termin�. Les formations sont verrouill�es, mais j'avais encore des modifications � effectuer. Que dois-je faire ?</strong></p>
		<p class='Texte' style='padding-bottom:20px;'>
			<strong>R : </strong>Si vous avez une requ�te particuli�re, vous pouvez passer par le menu "Contacter la Scolarit�" (menu sup�rieur de l'interface
			de saisie) afin de poser une question au service de Scolarit� de la Composante s�lectionn�e. La r�ponse vous sera envoy�e
			via la messagerie interne � l'application (vous recevrez normalement une notification de ce message par courriel).
		</p>

		<a name="Q6">
		<p class='fond_menu Texte_menu' style='font-weight:bold;'>&nbsp;&nbsp;6 - Q : La date d'une formation est pass�e, mais je n'ai pas re�u le message comme annonc�. Que s'est-il pass� ?</strong></p>
		<p class='Texte'>
			<strong>R : </strong>Vous devriez normalement recevoir le message au plus tard 24 heures apr�s le verrouillage d'une formation, ce message s'accompagnant d'une
			notification envoy�e par courriel (les courriels sont envoy�s � 5 heures du matin, heure GMT+1). Il arrive parfois que ces courriels de notification
			soient <b>consid�r�s comme des pourriels</b> (<i>spams</i>) et effac�s automatiquement. Dans tous les cas, consultez r�guli�rement <b>votre messagerie
			ainsi que celle de l'interface</b> !
		</p>
		<p class='Texte' style='padding-bottom:20px;'>Si en revanche vous ne recevez pas les messages sur la <b>messagerie de l'interface</b>, vous pouvez envoyer un courriel
			<a href='mailto:<?php echo $__EMAIL_SUPPORT; ?>?subject=[Pr�candidatures - R�capitulatif non re�u]' class='lien2a'>� cette adresse</a>, en pr�cisant
			bien :
			<br>- vos nom, pr�nom et date de naissance
			<br>- l'identifiant utilis� pour vous connecter � l'interface en ligne
			<br>- la Composante pour laquelle le document n'a pas �t� re�u
		</p>

		<a name="Q7">
		<p class='fond_menu Texte_menu' style='font-weight:bold;'>&nbsp;&nbsp;7 - Q : Quel est le format des pi�ces jointes au message r�capitulatif ? Avec quel programme dois-je les ouvrir ?</strong></p>
		<p class='Texte' style='padding-bottom:20px;'>
			<strong>R : </strong>Les pi�ces jointes sont au format <b>PDF</b> :
			<br>- sous Unix/Linux, vous pouvez ouvrir ces documents gr�ce � des outils tels que <b>xpdf</b>, <b>kpdf</b> (KDE), <b>gpdf</b>
			(Gnome), <b>Adobe Acroread</b>, ...
			<br>- sous Microsoft Windows, vous pouvez t�l�charger <b>Adobe Reader</b><a href='http://www.adobe.fr/products/acrobat/readstep2_allversions.html' class="lien2a" target="_blank">
			sur cette page</a>
		</p>

		<a name="Q8">
		<p class='fond_menu Texte_menu' style='font-weight:bold;'>&nbsp;&nbsp;8 - Q : J'ai effectu� tout ou partie de ma scolarit� dans cette universit�. Dois-je quand m�me renvoyer tous les justificatifs demand�s ?</strong></p>
		<p class='Texte'>
			<strong>R : </strong>Oui.
		</p>
		<p class='Texte' style='padding-bottom:20px;'>
			Ces pi�ces �tant syst�matiquement archiv�es d'une ann�e sur l'autre, tout l'avantage des pr�candidatures en ligne (notamment au niveau des d�lais de
			traitement) serait perdu compte tenu du temps n�cessaire � la recherche de votre dossier dans ces archives.
		</p>

		<a name="Q9">
		<p class='fond_menu Texte_menu' style='font-weight:bold;'>&nbsp;&nbsp;9 - Q : J'ai une question � poser mais elle n'appara�t ni dans la documentation, ni sur cette page, � qui dois-je m'adresser ?</strong></p>
		<p class='Texte'>
			<strong>R : </strong>L'adresse � laquelle envoyer votre question d�pend de la nature de cette derni�re :
			<br>- pour toute question d'ordre administratif (conditions d'admission, d�p�t des dossiers, ...), veuillez passer par le lien
			"Contacter la scolarit�" apr�s avoir s�lectionn� la composante voulue.
			<br>- pour signaler une erreur ou probl�me technique avec l'interface, merci d'utiliser plut�t <a href="mailto:<?php echo $__EMAIL_SUPPORT; ?>subject=[Pr�candidatures - Probl�me technique]" class='lien2a'>cette adresse</a>
		</p>
	</div>
</div>
<?php
	pied_de_page_candidat();
?>

</body>
</html>

