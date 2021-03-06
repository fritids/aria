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
	<?php
		titre_page_icone("Conditions d'utilisation de l'interface", "", 15, "C");
	?>

	<div style='width:85%; margin-left:auto; margin-right:auto; white-space:nowrap;'>
		<div>
			<font class='Texte3'><strong><u>Cette interface permet :</u></strong></font>
			<ul class='Texte'>
				<li>d'effectuer une ou plusieurs pr�candidatures dans cette universit�</li>
				<li>d'acc�l�rer le traitement de votre candidature</li>
				<li>de modifier votre fiche jusqu'� 48h apr�s avoir saisi votre premier voeu</li>
				<li>de suivre l'�volution de vos demandes m�me apr�s verrouillage de votre fiche</li>
			</ul>
		</div>
		<div>
			<font class='Texte3'><strong><u>Cependant, cette interface :</u></strong></font>
			<ul class='Texte'>
				<li>ne donne pas acc�s � toutes les formations pour certaines composantes (dossiers papier uniquement)</li>
				<li>est inutile pour un acc�s de plein droit � une formation (pas de commission p�dagogique)</li>
				<li>ne garantit <font class='Texte_important'><strong>EN AUCUN CAS</strong></font> une admission dans quelque formation que ce soit</li>
				<li>ne vous dispense pas de fournir les justificatifs de votre cursus et autres pi�ces par <strong>voie postale</strong>.
			</ul>
		</div>
	</div>
</div>
<?php
	pied_de_page_candidat();
?>

</body>
</html>

