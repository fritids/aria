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

	// EN-TETE SIMPLIFIEE (pas de composante, pas de menu, rien
	en_tete_simple();

	// MENU SUPERIEUR SIMPLIFIE
	menu_sup_simple();
?>

<div class='main'>
	<?php
		titre_page_icone("[Aide] Afficher toutes les fiches des candidats", "help-browser_32x32_fond.png", 15, "L");
	?>

	<div style='margin-left:auto; margin-right:auto; padding-bottom:20px; width:90%; text-align:justify;'>
		<font class='Texte_16'><u><b>Fonction principale</b></u></font>
		<p class='Texte'>
			<b>Afficher tous les candidats ayant d�pos� au moins un voeu dans votre �tablissement.</b>
		</p>
		<p class='Texte' style='padding-bottom:15px;'>
			Tous les candidats sont list�s, quelque soit l'�tat du verrouillage de leurs voeux. Vous pouvez acc�der
			� n'importe quelle fiche en cliquant sur le nom du candidat.
		</p>

		<font class='Texte_16'><u><b>Fonctionnalit�s et options</b></u></font>

		<p class='Texte'>
			<u><b>Fiches orphelines</b></u> : il arrive que certains candidats ne s�lectionnent aucune formation, quelque
			soit l'�tablissement propos�. On parle alors de <b>fiches orphelines</b> : elles "n'appartiennent" � aucun
			�tablissement.
		</p>
		<p class='Texte' style='padding-bottom:15px;'>
			Pour voir ces fiches, cliquez sur "Montrer �galement ces fiches orphelines". Attention : lorsque ces fiches sont
			nombreuses, l'affichage peut prendre quelques minutes.
		</p>
		<p class='Texte'>
			<u><b>Filtre des fiches</b></u> : si vous souhaitez afficher temporairement les candidats � une seule formation,
			s�lectionnez cette derni�re dans le menu d�roulant, puis validez. Si vous souhaitez que ce filtre soit actif
			lors de vos	connexions suivantes, cliquez sur "D�finir ce filtre par d�faut".
		</p>
		<p class='Texte'>
			Pour annuler le filtre, s�lectionnez "Montrer toutes les formations" dans la liste, puis validez de nouveau.
		</p>
	</div>
</div>
<?php
	pied_de_page();
?>
</body></html>
