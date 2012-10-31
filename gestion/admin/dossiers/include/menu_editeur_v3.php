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
<div class='menu_haut_2'>
	<form method='POST' action='ajout_element.php'>
	<input type='hidden' name='act' value='1'>
		<a href='element.php?a=1' target='_self'><img class='icone_menu_haut_2' name='Lien' border='0' src='<?php echo "$__ICON_DIR/add_16x16_menu2.png"; ?>' alt='+'></a>
		<a href='element.php?a=1' target='_self' class='lien_menu_haut_2'>Cr�er un �l�ment</a>
		<a href='element.php' target='_self'><img class='icone_menu_haut_2' name='Lien' border='0' src='<?php echo "$__ICON_DIR/edit_16x16_menu2.png"; ?>' alt='+'></a>
		<a href='element.php' target='_self' class='lien_menu_haut_2'>Modifier un �l�ment</a>
		<a href='suppr_element_base.php' target='_self'><img class='icone_menu_haut_2' name='Lien' border='0' src='<?php echo "$__ICON_DIR/trashcan_full_16x16_slick_menu2.png"; ?>' alt='+'></a>
		<a href='suppr_element_base.php' target='_self' class='lien_menu_haut_2'>Supprimer un �l�ment de la Base de Donn�es</a>
		<input style='vertical-align:middle; padding-right:5px;' type='image' src='<?php echo "$__ICON_DIR/randr_16x16_menu2.png"; ?>' border='0' name='ajout_element' alt='+' value=''><input type='submit' class='menu_haut_2' name='ajout_element' alt='Rattacher un �l�ment' value='Rattacher un �l�ment'>
</div>
