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

	include "../../configuration/aria_config.php";
	include "$__INCLUDE_DIR_ABS/vars.php";
	include "$__INCLUDE_DIR_ABS/fonctions.php";
	include "$__INCLUDE_DIR_ABS/db.php";

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	if(isset($_GET["aide_onglet"]) && ctype_digit($_GET["aide_onglet"]))
		$aide_onglet=$_GET["aide_onglet"];
	elseif(isset($_SESSION["onglet"]) && ctype_digit($_SESSION["onglet"]))
		$aide_onglet=$_SESSION["onglet"];
	else
		$aide_onglet=0;

	// EN-TETE SIMPLIFIEE (pas de composante, pas de menu : rien)
	en_tete_simple();

	// MENU SUPERIEUR SIMPLIFIE
	menu_sup_simple();

?>

<div class='main'>
	<?php
		titre_page_icone("[Aide] Traitement de la fiche d'un candidat", "help-browser_32x32_fond.png", 15, "L");
	?>
	
	<div style='margin-left:auto; margin-right:auto; padding-bottom:20px; width:90%; text-align:justify;'>
		<font class='Texte_16'><u><b>Fonction principale</b></u></font>
		<p class='Texte'>
			<b>Afficher toutes les informations li�es � un candidat</b>
		</p>
		<p class='Texte'>
			La fiche d'un candidat est commune � tous les �tablissements : � l'exception des menus 5 et 6, tous les gestionnaires 
			voient les m�mes informations (identit�, cursus, langues, informations compl�mentaires).
		</p>
		<p class='Texte'>
			La colonne gauche pr�sente un menu donnant acc�s aux diff�rents �l�ments de la fiche. En haut de la partie centrale, 
			vous pouvez voir � tout moment le nom du candidat, sa date de naissance ainsi qu'un lien permettant de lui envoyer un
			message (le lien redirige vers la messagerie de l'application).
		</p>
		<p class='Texte'>
			Pour les menus 2 � 5, vous ne pouvez ajouter ou modifier des informations que si au moins un des voeux du candidat
			est verrouill� dans votre �tablissement. Un candidat ne pourra plus modifier les menus 2 � 5 si l'un de ses voeux
			a �t� verrouill� <b>quelle que soit la composante</b> (ceci �vite de fournir des informations diff�rentes aux
			composantes).
		</p>
		<p class='Texte_important'>
			Attention : les donn�es entr�es par les candidats sont personnelles et elles leur appartiennent. Il faut donc �tre
			prudent lors de leur manipulation et leur utilisation en dehors du cadre p�dagogique est <b>strictement interdite</b>.
		</p>

		<?php
			if($aide_onglet!=1)
				print("<p class='Texte'>
							<a href='$php_self?aide_onglet=1#onglet1' target='_self'><img class='icone' src='$__ICON_DIR/plus_11x11.png' width='11' border='0' title='Identit�' desc='D�tails'></a>
							<a href='$php_self?aide_onglet=1#onglet1' class='lien_bleu_12' target='_self'><b>Menu \"1 - Identit�\"</b></a>
						 </p>\n");
			else
			{
		?>
		<p class='Texte'>
			<a href='<?php echo "$php_self?aide_onglet=0#onglet1"; ?>' target='_self'><img class='icone' src='<?php echo "$__ICON_DIR/moins_11x11.png"; ?>' width='11' border='0' title='Fermer' desc='Fermer'></a><u><b>Menu "1 - Identit�"</b></u>
			<br>Informations entr�es par le candidat lors de son enregistrement. Vous pouvez compl�ter ou corriger ces
			informations en cliquant sur "Modifier ces informations".
		</p>
		<p class='Texte'>
			Ces informations sont importantes : elles sont utilis�es dans les mod�les de lettres g�n�r�es apr�s d�cision de la
			Commission P�dagogique. En cas de probl�me avec ces mod�les, il est conseill� de v�rifier le format des donn�es
			(adresse postale, par exemple).
		</p>
		<p class='Texte'>
			Le candidat pourra �galement modifier ces informations, m�me si ses voeux sont verrouill�s.
		</p>
		<a name='onglet1'>

		<?php
			}

			if($aide_onglet!=2)
				print("<p class='Texte'>
							<a href='$php_self?aide_onglet=2#onglet2' target='_self'><img class='icone' src='$__ICON_DIR/plus_11x11.png' width='11' border='0' title='Cursus' desc='D�tails'></a>
							<a href='$php_self?aide_onglet=2#onglet2' class='lien_bleu_12' target='_self'><b>Menu \"2 - Cursus\"</b></a>
						 </p>\n");
			else
			{
		?>
		<p class='Texte'>
			<a href='<?php echo "$php_self?aide_onglet=0#onglet2"; ?>' target='_self'><img class='icone' src='<?php echo "$__ICON_DIR/moins_11x11.png"; ?>' width='11' border='0' title='Fermer' desc='Fermer'></a><u><b>Menu "2 - Cursus"</b></u>
			<br>Etudes et dipl�mes du candidat � partir du baccalaur�at (en th�orie).
		</p>
		<p class='Texte'>
			- au moins un voeu du candidat doit �tre verrouill� pour pouvoir modifer son cursus.
			<br>- chaque �tape peut �tre modifi�e en cliquant sur son intitul� ou sur l'ann�e
			<br>- pour supprimer une �tape, cliquez sur la poubelle sur la ligne correspondante
			<br>- lorsque vous recevez les justificatifs du candidat, vous devez indiquer le statut de chaque �tape � l'aide
			des menus d�roulants ("En attente des pi�ces", "Pi�ces manquantes", "Justificatifs valid�s" ...). Le champ
			"Pr�cision" sert � indiquer la nature des pi�ces manquantes, si le candidat a omis de les joindre.
			<br>- le bouton "Valider" permet d'enregistrer <b>l'ensemble</b> du formulaire (toutes les �tapes du cursus
			sont enregistr�es d'un coup : il est inutile de valider chaque �tape). Un courriel est alors automatiquement
			envoy� au candidat, lui indiquant le statut des justificatifs envoy�s.
			<br>- <b>la validation du cursus est primordiale</b> : les lettres d'admissions s'appuient souvent sur le cursus
			du candidat, et seules les �tapes justifi�es (i.e valid�es sur l'interface) pourront �tre prises en compte. De plus, des
			rappels automatiques sont envoy�s aux candidats lorsque leurs justificatifs ne sont pas valid�s. En l'absence de validation,
			certains candidats risquent donc de recevoir des rappels injustifi�s.
		</p>
		<a name='onglet2'>

		<?php
			}

			if($aide_onglet!=3)
				print("<p class='Texte'>
							<a href='$php_self?aide_onglet=3#onglet3' target='_self'><img class='icone' src='$__ICON_DIR/plus_11x11.png' width='11' border='0' title='Langues' desc='D�tails'></a>
							<a href='$php_self?aide_onglet=3#onglet3' class='lien_bleu_12' target='_self'><b>Menu \"3 - Langues\"</b></a>
						 </p>\n");
			else
			{
		?>
		<p class='Texte'>
			<a href='<?php echo "$php_self?aide_onglet=0#onglet3"; ?>' target='_self'><img class='icone' src='<?php echo "$__ICON_DIR/moins_11x11.png"; ?>' width='11' border='0' title='Fermer' desc='Fermer'></a><u><b>Menu "3 - Langues"</b></u>
			<br>Langues maitris�es par le candidat, avec le niveau (lu / �crit / parl� / langue maternelle),
			le nombre d'ann�es d'�tudes pour cette langue ainsi que les �ventuels tests de niveau et concours pass�s.
		</p>
		<p class='Texte'>
			Ces informations sont modifiables lorsqu'un voeu du candidat est verrouill� dans votre �tablissement.
		</p>
		<a name='onglet3'>

		<?php
			}

			if($aide_onglet!=4)
				print("<p class='Texte'>
							<a href='$php_self?aide_onglet=4#onglet4' target='_self'><img class='icone' src='$__ICON_DIR/plus_11x11.png' width='11' border='0' title='Informations Compl�mentaires' desc='D�tails'></a>
							<a href='$php_self?aide_onglet=4#onglet4' class='lien_bleu_12' target='_self'><b>Menu \"4 - Infos Compl�mentaires\"</b></a>
						 </p>\n");
			else
			{
		?>		
		<p class='Texte'>
			<a href='<?php echo "$php_self?aide_onglet=0#onglet4"; ?>' target='_self'><img class='icone' src='<?php echo "$__ICON_DIR/moins_11x11.png"; ?>' width='11' border='0' title='Fermer' desc='Fermer'></a><u><b>Menu "4 - Infos Compl�mentaires"</b></u>
			<br>Autres informations que le candidat souhaite fournir pour appuyer sa candidature : projets, formations, stages,
			d'emplois, ...
		</p>
		<p class='Texte'>
			Ces informations sont modifiables lorsqu'un voeu du candidat est verrouill� dans votre �tablissement.
		</p>
		<a name='onglet4'>

		<?php
			}

			if($aide_onglet!=5)
				print("<p class='Texte'>
							<a href='$php_self?aide_onglet=5#onglet5' target='_self'><img class='icone' src='$__ICON_DIR/plus_11x11.png' width='11' border='0' title='Autres Renseignements' desc='D�tails'></a>
							<a href='$php_self?aide_onglet=5#onglet5' class='lien_bleu_12' target='_self'><b>Menu \"5 - Autres Renseignements\"</b></a>
						 </p>\n");
			else
			{
		?>		
		<p class='Texte'>
			<a href='<?php echo "$php_self?aide_onglet=0#onglet5"; ?>' target='_self'><img class='icone' src='<?php echo "$__ICON_DIR/moins_11x11.png"; ?>' width='11' border='0' title='Fermer' desc='Fermer'></a><u><b>Menu "5 - Autres Renseignements"</b></u>
			<br>Ce menu est dynamique : si des �l�ments ont �t� cr��s dans le Constructeur de dossiers, les questions
			correspondantes et les r�ponses apport�es par le candidat seront affich�es dans ce menu. Dans le cas contraire,
			ce dernier restera vide.
		</p>
		<p class='Texte'>
			Si les questions sont pos�es pour chaque formation choisie par le candidat, vous verrez apparaitre plusieurs fois
			le m�me �l�ment (une par formation choisie). Ce comportement ainsi que d'autres optiones peut �tre modifi� dans
			les param�tres des �lements (cf. Constructeur de dossiers).
		</p>
		<p class='Texte'>
			Ces informations sont modifiables lorsqu'un voeu du candidat est verrouill� dans votre �tablissement.
		</p>
		<a name='onglet5'>

		<?php
			}

			if($aide_onglet!=6)
				print("<p class='Texte'>
							<a href='$php_self?aide_onglet=6#onglet6' target='_self'><img class='icone' src='$__ICON_DIR/plus_11x11.png' width='11' border='0' title='Pr�candidatures' desc='D�tails'></a>
							<a href='$php_self?aide_onglet=6#onglet6' class='lien_bleu_12' target='_self'><b>Menu \"6 - Pr�candidatures\"</b></a>
						 </p>\n");
			else
			{
		?>
		<p class='Texte'>
			<a name='onglet6'>
			<a href='<?php echo "$php_self?aide_onglet=0#onglet6"; ?>' target='_self'><img class='icone' src='<?php echo "$__ICON_DIR/moins_11x11.png"; ?>' width='11' border='0' title='Fermer' desc='Fermer'></a><u><b>Menu "6 - Pr�candidatures"</b></u>
			<br>Affichage des voeux du candidat dans l'�tablissement courant, tri�s par ordre de pr�f�rence d�croissant. Pour
			chaque voeu, vous pouvez:
			<br>- modifier l'ordre de pr�f�rence et la formation choisie
			<br>- modifier la date du verrouillage (date modifiable � l'aide du mini-formulaire et du bouton "Changer la
			date" pour valider la nouvelle)
			<br>- le verrouiller/d�verrouiller manuellement (si le candidat a oubli� des informations sur sa fiche, par exemple)
			<br>- modifier le statut de la recevabilit� (dossier complet ou non) et les motivations si le dossier est en attente
			<br>- g�n�rer le "Formulaire de Commission" lorsque la candidature est recevable
			<br>- modifier la d�cision de la commission p�dagogique (cliquez sur "Commission" pour acc�der � sa saisie)
			<br>- g�n�rer les documents officiels si une d�cision a �t� saisie et qu'un mod�le de lettre existe pour la
			formation <b>et</b> la d�cision
			<br>- le supprimer
		</p>
		<p class='Texte'>
			<u><b>Traitement d'un voeu</b></u>
			<br>Une candidature se traite en deux �tapes : la <b>recevabilit�</b> et la <b>commission p�dagogique</b>.
		</p>
		<p class='Texte'>
			<b>1 - Recevabilit�</b>
			<br>Elle r�pond aux deux questions : "Le dossier est-il complet ? Les pr�requis sont-ils satisfaits ?" Si la
			r�ponse est oui pour les deux, alors le dossier est recevable. Pour chaque voeu, plusieurs options sont
			disponibles pour la recevabilit� :
			<br>&#8226; <u>Non trait�e</u> : �tat par d�faut (la recevabilit� n'a pas �t� �tudi�e)
			<br>&#8226; <u>Recevable</u> : le dossier est complet, il peut passer en commission p�dagogique
			<br>&#8226; <u>Non recevable</u> : les pr�requis ne sont pas satisfaits, le dossier ne passera donc pas en
			commission. La motivation n'est pas n�cessaire, une phrase type est envoy�e au candidat. </font>
			<font class='Texte_important'><b>Lorsque vous validez ce choix, un message est envoy� au candidat, ce qui n'est pas
			le cas pour un dossier recevable</b></font>.
			<br>&#8226; <u>Plein droit</u> : statut pour les candidats qui n'auraient pas du d�poser de dossier pour cette
			formation car ils entrent de plein droit dans cette derni�re. <b>Un message est envoy� au candidat lors de la
			validation</b>.
			<br>&#8226; <u>Mettre en attente</u> : s'il manque une pi�ce au dossier ou si une condition n'est pas encore
			satisfaite, il peut �tre mis en attente. Le champ <b>motivation</b> doit �tre compl�t� avec le motif de la mise
			en attente, et un message est envoy� au candidat. <b>Attention</b> : cette option ne doit pas �tre utilis�e
			lorsque seuls les r�sultats de l'ann�e en cours sont manquants (le candidat ne les poss�de pas encore). Dans ce
			cas, le dossier est recevable et le candidat pourra �ventuellement �tre "Admis sous r�serve" par la Commission
			P�dagogique.
		</p>
		<p class='Texte'>
			<b>2 - Commission P�dagogique</b>
			<br>lorsqu'un dossier est valid� "recevable", deux liens apparaissent alors :
			<br>&#8226; <u>Form. Commission</u> : g�n�ration du formulaire de commission (format PDF) permettant � cette derni�re
			d'�crire sa d�cision motiv�e, avec signature de son Pr�sident (et Vice-Pr�sident s'il y a lieu).
			<br>&#8226; <u>Commission</u> : apr�s retour du formulaire, cliquez sur ce dernier pour acc�der � la page de saisie
			de la d�cision de la Commission.
		</p>

		<?php
			}

			if($aide_onglet!=7)
				print("<p class='Texte'>
							<a href='$php_self?aide_onglet=7#onglet7' target='_self'><img class='icone' src='$__ICON_DIR/plus_11x11.png' width='11' border='0' title='Mode Manuel' desc='D�tails'></a>
							<a href='$php_self?aide_onglet=7#onglet7' class='lien_bleu_12' target='_self'><b>Menu \"7 - Mode Manuel\"</b></a>
						 </p>\n");
			else
			{
		?>
		<p class='Texte'>
			<a href='<?php echo "$php_self?aide_onglet=0#onglet7"; ?>' target='_self'><img class='icone' src='<?php echo "$__ICON_DIR/moins_11x11.png"; ?>' width='11' border='0' title='Fermer' desc='Fermer'></a><u><b>Menu "7 - Mode Manuel"</b></u>
			<br>Actions particuli�res sur la fiche du candidat :
			<br>- modifier son adresse �lectronique (<i>email</i>) en cas d'erreur ou de changement
			<br>- envoyer un courriel contenant son identifiant et son mot de passe
			<br>- envoyer un message (interne � l'application) contenant le r�capitulatif de la fiche et la liste des
			justificatifs pour les voeux verrouill�s
			<br>- supprimer enti�rement la fiche (avec les pr�cautions d'usage).
		</p>
		<a name='onglet7'>

		<?php
			}

			if($aide_onglet!=8)
				print("<p class='Texte'>
							<a href='$php_self?aide_onglet=8#onglet8' target='_self'><img class='icone' src='$__ICON_DIR/plus_11x11.png' width='11' border='0' title='Documents PDF' desc='D�tails'></a>
							<a href='$php_self?aide_onglet=8#onglet8' class='lien_bleu_12' target='_self'><b>Menu \"8 - Documents PDF\"</b></a>
						 </p>\n");
			else
			{
		?>
		<p class='Texte'>
			<a href='<?php echo "$php_self?aide_onglet=0#onglet8"; ?>' target='_self'><img class='icone' src='<?php echo "$__ICON_DIR/moins_11x11.png"; ?>' width='11' border='0' title='Fermer' desc='Fermer'></a><u><b>Menu "8 - Documents PDF"</b></u>
			<br>Ce menu vous permet de g�n�rer le r�capitulatif et les listes de justificatifs du candidat ainsi que
			les formulaires de commission p�dagogique (pour les voeux verrouill�es uniquement).
		</p>
		<a name='onglet8'>

		<?php
			}
			
			if($aide_onglet!=9)
				print("<p class='Texte'>
							<a href='$php_self?aide_onglet=9#onglet9' target='_self'><img class='icone' src='$__ICON_DIR/plus_11x11.png' width='11' border='0' title='Historique' desc='D�tails'></a>
							<a href='$php_self?aide_onglet=9#onglet9' class='lien_bleu_12' target='_self'><b>Menu \"9 - Historique\"</b></a>
						 </p>\n");
			else
			{
		?>
		<p class='Texte'>
			<a href='<?php echo "$php_self?aide_onglet=0#onglet9"; ?>' target='_self'><img class='icone' src='<?php echo "$__ICON_DIR/moins_11x11.png"; ?>' width='11' border='0' title='Fermer' desc='Fermer'></a><u><b>Menu "9 - Historique"</b></u>
			<br>Informations sur les �v�nements li�s � la fiche du candidat :
			<br>- candidatures dans les autres �tablissements li�s � l'interface (avec les d�cisions)
			<br>- candidatures des ann�es pr�c�dentes, �galement avec les d�cisions
			<br>- historique des actions (Gestion et Candidat) : date des d�cisions, g�n�ration des lettres, ...
		</p>
		<p class='Texte'>
			Seuls les �ven�ments communs ou relatifs � votre �tablissement sont visibles. Le candidat n'a pas acc�s � ce
			menu.
		</p>
		<a name='onglet9'>
		<?php
			}
		?>
	</div>
</div>
<?php
	pied_de_page();
?>
</body></html>
