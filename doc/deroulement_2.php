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
		<font class='Texte3'>
			<b>D�p�t de dossiers de pr�candidature
			<br><br>I - D�roulement d'une pr�candidature en ligne (2/5)</b>
		</font>
	</div>

	<div style='width:80%; text-align:justify; margin:0px auto 0px auto; padding-bottom:30px;'>
		<font class='Texte3'>
			<u><b>Etape 2 </b> : Pr�sentation de l'interface de saisie</u>
		</font>
		<font class='Texte'>
			<br><br>Une fois vos identifiants re�us par courriel, vous pouvez acc�der � l'interface de saisie.

			<br><br><u><b>S�lection de la composante</b></u>
			<br>Avant de compl�ter votre fiche, vous devez choisir dans quelle composante vous d�sirez d�poser un dossier de pr�candidature (sauf si vous avez utilis� un lien
			direct : dans ce cas, la composante est automatiquement s�lectionn�e). Au sein de l'interface, vous pouvez � tout moment s�lectionner une autre composante afin de
			d�poser d'autres dossiers (cf. partie <b>II - <a href='composantes.php' class='lien2a'>Composantes</a></b>).
			<br>
			<br><u><b>Saisie de vos donn�es</b></u>
			Apr�s avoir s�lectionn� la composante et valid� votre choix, vous pouvez commencer � compl�ter vos donn�es. L'interface de	saisie est compos�e
			de plusieurs menus : <b>chaque menu doit �tre compl�t� consciencieusement</b> :
		</font>
		<br><br>
		<font class='Texte3'>
			&#8226;&nbsp;<u><b>Menu 1 : Identit�</b></u>
		</font>
		<font class='Texte'>
			<br>
			<br>Ce menu r�sume les informations entr�es lors de votre enregistrement sur l'interface. Elles sont cruciales, vous pouvez les mettre � jour � tout moment.
			<br><br><br>
		</font>
		<font class='Texte3'>
			&#8226;&nbsp;<u><b>Menu 2 : Cursus</b></u>
		</font>
		<font class='Texte'>
			<br>
			<br>Ce menu vous permet de renseigner toutes les �tapes de votre cursus scolaire, � partir du baccalaur�at (ou �quivalent) inclus.
			<br>
			<br>Lorsque vous devez compl�ter un champ texte, veillez � le remplir de la mani�re la plus exacte possible. Des informations
			incorrectes pourraient affecter le temps de traitement de votre fiche, et vos demandes pourraient ne pas �tre trait�es.
			<br><br>
		</font>
		<font class='Texte_important_14'>
			Chaque �tape devra par la suite �tre justifi�e. Pour chaque formation demand�e, vous recevrez une liste des pi�ces � fournir
			(par voie postale) au service de scolarit� de chaque composante concern�e par vos demandes.
			<br><br>Les dipl�mes en cours de pr�paration seront � justifier d�s l'obtention des r�sultats, <b>mais vous devez envoyer les
			pi�ces d�j� en votre possession sans attendre !</b>
			<br><br>	<br>
		</font>
		<font class='Texte3'>
			&#8226;&nbsp;<u><b>Menu 3 : Langues</b></u>
		</font>
		<font class='Texte'>
			<br>
			<br>Ce menu vous permet de renseigner votre niveau en langues �trang�res. Vous pouvez pr�ciser vos comp�tences (lu, �crit, parl�) pour chaque langue, ainsi que les
			�ventuels dipl�mes obtenus (TOEIC, TOEFL, TCF, ...).
			<br><br><br>
		</font>
		<font class='Texte3'>
			&#8226;&nbsp;<u><b>Menu 4 : Informations compl�mentaires et exp�riences professionnelles</b></u>
		</font>
		<font class='Texte'>
			<br>
			<br>Renseignez ici vos exp�riences professionnelles (formations, stages, emplois), et autres informations (service national, arr�t et reprise d'�tudes ...)
			susceptibles d'int�resser les scolarit�s ou les responsables de Commissions P�dagogiques.
			<br><br><br>
		</font>
		<font class='Texte3'>
			&#8226;&nbsp;<u><b>Menu 5 : Pr�candidatures pour la Composante s�lectionn�e</b></u>
		</font>
		<font class='Texte'>
			<br>
			<br>Ce menu vous permet de s�lectionner les formations pour lesquelles vous souhaitez d�poser une pr�candidature.
			<br>
			<br>Vous devez <b>trier</b> vos voeux </font><font class='Texte_important'><b>par ordre de pr�f�rence d�croissant</b></font>
			<font class='Texte'>(en d'autres termes : ce que vous pr�f�rez tout en haut de la liste). Sur chaque ligne, des fl�ches sont pr�sentes � droite pour
			r�ordonner vos pr�candidatures vers le haut ou vers le bas.
			<br><br>
		</font>
		<font class='Texte_important'><u><b>Remarque concernant les candidatures � choix multiples :</b></u></font>
		<font class='Texte'>
			<br><br>
			Dans certaines composantes, des formations sont automatiquement regroup�es en une seule candidature.
			Cel� signifie que vous devrez, pour ce voeu particulier, trier l� encore par ordre de pr�f�rence d�croissant les formations/sp�cialit�s choisies.
			D'autres fl�ches pr�vues � cet effet, situ�es cette fois sur le cot� gauche, appara�tront automatiquement.
			<br><br><b>Remarque :
			<br>Sauf instruction contraire de la scolarit�, vous devrez envoyer un seul exemplaire des justificatifs demand�s pour ces sp�cialit�s regroup�es.
			<br><br><br>
		</font>
		<font class='Texte3'>
			&#8226;&nbsp;<u><b>Menu 6 : Autres Renseignements obligatoires</b></u>
		</font>
		<font class='Texte'>
			<br>
			<br><b>Ce menu n'appara�t que lorsqu'une composante demande des informations compl�mentaires.</b>
			<br><br>En fonction des formations s�lectionn�s, certaines composantes demandent que vous remplissiez des formulaires sp�ciaux,
			comme le contenu de vos pr�c�dents enseignements, par exemple.
			<br><br>
		</font>
		<font class='Texte_important_14'>
			<b>Ces champs ne sont pas optionnels, v�rifiez bien la pr�sence ou non de ce menu, pour chaque composante</b>
		</font>

		<font class='Texte_important_14'>
			<b>Important</b> : un d�p�t de pr�candidature ne signifie en aucun cas que votre demande sera examin�e par la Commission P�dagogique : votre
			pr�candidature doit d'abord �tre valid�e par les scolarit�s des composantes concern�es (justificatifs de votre cursus et pr�requis satisfaits
			pour les formations demand�es).
		</font>
	</div>
	<div class='centered_box' style='padding-bottom:30px;'>
		<a href='deroulement_1.php' class='lien_bleu_12'><img class='icone icone_texte_d' src='<?php echo "$__ICON_DIR/back_32x32_fond.png"; ?>' border='0'></a>
		<a href='deroulement_1.php' class='lien_bleu_12' style='padding-right:50px;'><b>Etape 1 : Identifiant et mot de passe</b></a>
		<a href='documentation.php' class='lien_bleu_10'>Retour au sommaire</a></td>
		<a href='deroulement_3.php' class='lien_bleu_12' style='padding-left:50px;'><b>Etape 3 : D�lai de modification de votre fiche</b></a>
		<a href='deroulement_3.php' class='lien_bleu_12'><img class='icone icone_texte_g' src='<?php echo "$__ICON_DIR/forward_32x32_fond.png"; ?>' border='0'></a>
	</div>
</div>
<?php
	pied_de_page_candidat();
?>

</body>
</html>

