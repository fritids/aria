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

	include "../configuration/aria_config.php";
	include "$__INCLUDE_DIR_ABS/vars.php";
	include "$__INCLUDE_DIR_ABS/fonctions.php";
	include "$__INCLUDE_DIR_ABS/db.php";

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	unset($_SESSION["mails_masse"]);
	unset($_SESSION["from"]);

	verif_auth();

	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();

	// Nettoyages des variables inutiles
	unset($_SESSION["checked_message"]);
	unset($_SESSION["requete"]);
?>
<div class='main'>
	<?php
		titre_page_icone("Recherches diverses", "xmag_32x32_fond.png", 30, "L");

		if(isset($_GET["s"]) && ctype_digit($_GET["s"]))
		{
			if($_GET["s"]==0)
				$message="Aucun message envoy�";
			elseif($_GET["s"]==1)
				$message="1 message envoy� avec succ�s";
			elseif($_GET["s"]>1)
				$message="$_GET[s] messages envoy�s avec succ�s";

			message($message, $__INFO);
		}
	?>

	<table align='center' style='padding-bottom:100px;'>
	<tr>
		<td class='td-complet fond_menu2' style='padding:4px;'>
			<font class='Texte_menu2'><b>Vous souhaitez chercher ... </b></font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu'>
			<a href='recherche_nominative.php' target='_self' class='lien_menu_gauche'>&#8226;&nbsp;&nbsp;Des candidats, par nom ou par courriel</a>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu'>
			<a href='recherche_generale.php' target='_self' class='lien_menu_gauche'>&#8226;&nbsp;&nbsp;Des candidats, par formations, statuts des fiches, ...</a>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu'>
			<a href='recherche_formation.php' target='_self' class='lien_menu_gauche'>&#8226;&nbsp;&nbsp;Des formations, par intitul� ou par mention.</a>
		</td>
	</tr>
	</table>
</div>
<?php
	pied_de_page();
?>
</body></html>
