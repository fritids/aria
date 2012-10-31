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
		titre_page_icone("[Aide] Page d'accueil", "help-browser_32x32_fond.png", 15, "L");
	?>
		
	<div style='margin-left:auto; margin-right:auto; padding-bottom:20px; width:90%; text-align:justify;'>
		<font class='Texte_16'><u><strong>Fonction principale</strong></u></font>
		<p class='Texte'>
			<strong>Afficher les pr�candidatures verrouill�es en attente de recevabilit� ou de d�cision de la Commission P�dagogique.</strong>
		</p>
		<p class='Texte'>
			L'affichage est s�par� en deux colonnes. Dans la premi�re, seules les pr�candidatures n'ayant re�u
			aucun traitement sont pr�sentes, alors que dans la seconde, il s'agit des pr�candidatures partiellement
			trait�es ("en attente", sur listes compl�mentaires, admission sous r�serve, ...).
		</p>
		<p class='Texte'>
			Les pr�candidatures pour lesquelles une d�cision finale a �t� prise n'apparaissent plus dans ces listes,
			mais vous pourrez toujours retrouver la fiche d'un candidat via le menu "Recherche".
		</p>
		<p class='Texte' style='padding-bottom:15px;'>
			Vous pouvez acc�der � n'importe quelle fiche en cliquant sur le nom du candidat.
		</p>

		<font class='Texte_16'><u><strong>Fonctionnalit�s et options</strong></u></font>
		<p class='Texte'>
			<u><strong>Modes "Recevabilit�" et "Commission P�dagogique"</strong></u> : cette page peut afficher soit les pr�candidatures en
			attente de recevabilit� (i.e si elles r�pondent � la question "le dossier est-il complet et les pr�requis sont-ils
			satisfaits pour passer devant la Commission ?"), soit les pr�candidatures en attente de la d�cision de la 
			Commission P�dagogique.
		</p>
		<p class='Texte'>
			Lorsqu'une pr�candidature est valid�e Recevable, elle disparait du mode Recevabilit� et passe automatiquement
			dans les listes du mode Commission.
		</p>

		<div class='centered_box'>
			<font class='Texte'>
				Pour passer d'un mode � l'autre, il suffit de cliquer sur l'ic�ne suivante : 	<img style='vertical-align:middle;' src='<?php echo "$__ICON_DIR/reload_32x32_fond.png"; ?>' border='0' alt=''>
			</font>
		</div>

		<p class='Texte'>
			<u><strong>Filtre des fiches</strong></u> : si vous souhaitez afficher temporairement une seule formation, s�lectionnez
			cette derni�re dans le menu d�roulant, puis validez. Si vous souhaitez que ce filtre soit actif lors de vos
			connexions suivantes, cliquez sur "D�finir ce filtre par d�faut".
		</p>
		<p class='Texte' style='padding-bottom:15px;'>
			Pour annuler le filtre, s�lectionnez "Montrer toutes les formations" dans la liste, puis validez de nouveau.
		</p>
		<p class='Texte' style='padding-bottom:15px;'>
			<u><strong>Tri des fiches</strong></u> : vous avez la possibilit� de trier les listes par date croissante (tri par d�faut),
			par nom, par formation et par moyenne du dernier dipl�me mentionn� par le candidat (attention � ce tri, car
			tous les candidats ne respectent pas la fa�on d'entrer cette moyenne).
		</p>
	</div>
</div>
<?php
	pied_de_page();
?>
</body></html>
