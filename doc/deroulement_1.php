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
			<br><br>I - D�roulement d'une pr�candidature en ligne (1/5)</b>
		</font>
	</div>

	<div style='width:80%; text-align:justify; margin:0px auto 0px auto; padding-bottom:30px;'>
		<font class='Texte'>
			<br>Le d�p�t d'une pr�candidature en ligne se d�roule en plusieurs �tapes :
			<br><br>
		</font>
		<font class='Texte3'>
			<u><b>Etape 1 </b> : Enregistrement : obtention de votre identifiant et de votre mot de passe</u>
		</font>
		<font class='Texte'>
			<br><br>Apr�s avoir lu et accept� les conditions de la page d'accueil, vous devez remplir le formulaire d'enregistrement afin d'obtenir votre
			identifiant et votre mot de passe.
			<br><br>
			Vous devez en particulier fournir certaines donn�es vous concernant :
			<br>&#8226;&nbsp;&nbsp;votre nom, pr�nom et date de naissance
			<br>&#8226;&nbsp;&nbsp;votre lieu de naissance et votre nationalit� (donn�es importantes car les justificatifs � fournir sont diff�rents selon les cas)
		</font>
		<font class='Texte_important'>
			<br>&#8226;&nbsp;&nbsp;<b>une adresse �lectronique (<i>email</i>) valide</b> : de nombreuses correspondances seront envoy�es � cette adresse
			<br>&#8226;&nbsp;&nbsp;<b>une adresse postale permanente valide</b>, pour les �ventuels courriers officiels d'admission ou de refus
		</font>
		<font class='Texte'>
			<br><br>
			Apr�s validation du formulaire, vous recevrez un identifiant et un mot de passe � l'adresse �lectronique indiqu�e. Ces informations sont
			<b>strictement confidentielles</b> : m�morisez les et <b>ne les divulguez � personne</b> car cel� pourrait engager votre responsabilit�.
			<br><br>
			Si vous avez d�j� d�pos� un dossier sur cette interface (ann�e pr�c�dente, par exemple), il n'est pas n�cessaire de vous enregistrer de nouveau : vous pouvez
			r�utiliser vos identifiants, mais n'oubliez pas de mettre ensuite votre fiche � jour (adresse postale, cursus, ...).
		</font>
	</div>
	<div class='centered_box' style='padding-bottom:30px;'>
		<a href='documentation.php' class='lien_bleu_12'><img class='icone icone_texte_d' src='<?php echo "$__ICON_DIR/rew_32x32_fond.png"; ?>' border='0'></a>
		<a href='documentation.php' class='lien_bleu_12' style='padding-right:50px;'><b>Sommaire</b></a>
		<a href='deroulement_2.php' class='lien_bleu_12' style='padding-left:50px;'><b>Etape 2 : Pr�sentation de l'interface de saisie</b></a>
		<a href='deroulement_2.php' class='lien_bleu_12'><img class='icone icone_texte_g' src='<?php echo "$__ICON_DIR/forward_32x32_fond.png"; ?>' border='0'></a>
	</div>
</div>
<?php
	pied_de_page_candidat();
?>

</body>
</html>

