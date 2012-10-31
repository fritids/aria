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
			<br><br>I - D�roulement d'une pr�candidature en ligne (4/5)</b>
		</font>
	</div>

	<div style='width:80%; text-align:justify; margin:0px auto 0px auto; padding-bottom:30px;'>
		<font class='Texte3'>
			<u><b>Etape 4 </b> : Verrouillage et justificatifs</u>
		</font>
		<font class='Texte'>
			<br><br>Une fois le d�lai imparti �coul�, chaque voeu est <strong>automatiquement verrouill�</strong>, la formation en question ne peut alors plus �tre 
			modifi�e sur votre fiche.
			<br><br>
			<b>Attention :</b>
		</font>
		<font class='Texte_important'>
			<br>Votre cursus ne pourra plus �tre modifi� apr�s le verrouillage d'une formation (toutes composantes confondues). Veillez � bien compl�ter votre fiche
			<b>avant</b> le verrouillage des formations choisies !
		</font>
		<font class='Texte'>
			<br><br>
			Cependant, certains �l�ments pourront toujours �tre modifi�s (comme les �l�ments de votre identit�, en cas de changement d'adresse par exemple).
			<br><br>
			Vous recevrez alors, au plus tard 24 heures apr�s le verrouillage d'un voeu, un courriel de notification vous invitant � consulter la <b>messagerie interne de l'application</b>.
			C'est via cette messagerie que vous pourrez consulter la liste des pi�ces justificatives � envoyer <b>par voie postale</b> au
			service de scolarit� de chaque composante concern�e par vos demandes.
			<br><br>La messagerie interne est accessible depuis votre fiche (menu "Messagerie").
		</font>
		<br><br>
		<b>Remarques :</b>
		<font class='Texte_important'>
			<br>- Vous devrez imp�rativement envoyer l'int�gralit� des pi�ces demand�es, et faire remplir les �ventuels formulaires par l'�quipe p�dagogique de votre
			�tablissement actuel.
			<br>- Pour les dipl�mes en cours de pr�paration, les justificatifs devront �tre envoy�s d�s leur obtention (n'attendez pas pour envoyer le reste des pi�ces demand�es)
			<br>- Sauf instruction contraire d'une scolarit�, vous devrez envoyer un exemplaire de vos justificatifs <b>pour chaque formation demand�e</b> (et donc pour
			chaque courriel re�u).
			<br>- <b>Aucun dossier incomplet ne sera trait�</b>.
			<br><br><br>
		</font>
	</div>
	<div class='centered_box' style='padding-bottom:30px;'>
		<a href='deroulement_3.php' class='lien_bleu_12'><img class='icone icone_texte_d' src='<?php echo "$__ICON_DIR/back_32x32_fond.png"; ?>' border='0'></a>
		<a href='deroulement_3.php' class='lien_bleu_12' style='padding-right:50px;'><b>Etape 3 : D�lai de modification de votre fiche</b></a>
		<a href='documentation.php' class='lien_bleu_10'>Retour au sommaire</a>
		<a href='deroulement_5.php' class='lien_bleu_12' style='padding-left:50px;'><b>Etape 5 : Suivi et d�cision</b></a>
		<a href='deroulement_5.php' class='lien_bleu_12'><img class='icone icone_texte_g' src='<?php echo "$__ICON_DIR/forward_32x32_fond.png"; ?>' border='0'></a>
	</div>
</div>
<?php
	pied_de_page_candidat();
?>

</body>
</html>

