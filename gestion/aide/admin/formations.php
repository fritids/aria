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

	include "../../../configuration/aria_config.php";
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
		titre_page_icone("[Aide] Cr�ation / modification d'une formation", "help-browser_32x32_fond.png", 15, "L");
	?>

	<div style='margin-left:auto; margin-right:auto; padding-bottom:20px; width:90%; text-align:justify;'>
		<font class='Texte_16'><u><b>Fonction principale</b></u></font>
		<p class='Texte' style='padding-bottom:15px'>
			<b>Cr�er une formation � partir d'�l�ments pr�d�finis (ann�e, sp�cialit�/mention, ...)</b>
		</p>

		<font class='Texte_16'><u><b>Options</b></u></font>
		<p class='Texte' style='padding-bottom:15px'>
			<u><b>Ann�e</b></u> : s�lection du niveau de la formation, de la Licence 1�re ann�e (L1) au Master 2�me ann�e
			(M2) en passant par la Licence Professionnelle. Pour les formations particuli�res (pr�parations aux concours,
			Dipl�mes d'Universit�s, Capacit� ...), vous pouvez utiliser la valeur "Ann�e particuli�re".
		</p>
		<p class='Texte' style='padding-bottom:15px'>
			<u><b>Mention / Sp�cialit�</b></u> : intitul� de la formation parmi ceux cr��s pr�c�demment.
		</p>
		<p class='Texte' style='padding-bottom:15px'>
			<u><b>Finalit�</b></u> : Recherche, Professionnelle ou aucune.
		</p>
		<p class='Texte' style='padding-bottom:15px'>
			<u><b>Formation Activ�e ?</b></u> : vous permet de rendre visible/invisible cette formation sur l'interface,
			sans pour autant la supprimer compl�tement. Cette fonction est surtout utile si vous ne savez pas encore si
			la formation sera ouverte ou non, ou bien si la formation est supprim�e mais que vous d�sirez conserver des
			statistiques sur les candidatures des ann�es pr�c�dentes.
		</p>
		<p class='Texte' style='padding-bottom:15px'>
			<u><b>Formation g�r�e manuellement ?</b></u> : si "Oui", alors vous pourrez utiliser cette formation en ligne
			(i.e ajouter manuellement des candidatures et les traiter, g�n�rer des courriers, etc), mais les candidats ne
			la verront pas et ne pourront donc pas la s�lectionner.
		</p>
		<p class='Texte' style='padding-bottom:15px'>
			<u><b>Code Etape Apog�e</b></u> : si votre �tablissement est li� � Apog�e pour les inscriptions administratives,
			vous aurez besoin de g�n�rer un code confidentiel pour chaque candidature retenue. Ce code secret s'appuie en g�n�ral 
			sur le Code Etape de la formation, vous pouvez donc le renseigner ici.
		</p>
		<p class='Texte' style='padding-bottom:15px'>
			<u><b>Frais de dossiers</b></u> : montant des frais demand�s au candidat, en euros. Dans l'�diteur de justificatifs,
			n'oubliez pas d'indiquer les modalit�s de paiement.
		</p>
		<p class='Texte' style='padding-bottom:15px'>
			<u><b>Responsable de la formation</b></u> : civilit�, nom et pr�nom du ou de la responsable de cette formation.
			Ce champ pourra �tre utilis� dans les mod�les de lettres via une macro pr�vue � cet effet.
		</p>
		<p class='Texte' style='padding-bottom:15px'>
			<u><b>Courriel du responsable</b></u> : adresse �lectronique (<i>email</i>) du ou de la responsable de la
			formation. L� encore, une macro existe pour les mod�les de lettres.
		</p>
		<p class='Texte' style='padding-bottom:15px'>
			<u><b>Formation s�lective</b></u> : indique si la formation est s�lective ou non. Ce champ n'est actuellement pas
			exploit�, mais il pourrait l'�tre � l'avenir, il est donc conseill� de bien renseigner ce champ.
		</p>
		<p class='Texte' style='padding-bottom:15px'>
			<u><b>Convocation � l'entretien</b></u> : indique si les candidats � cette formation doivent ou non passer un
			entretien compl�mentaire au d�p�t du dossier. Ce param�tre a une influence sur le traitement des candidatures,
			il est donc importante de bien le renseigner.
		</p>
	</div>
</div>
<?php
	pied_de_page();
?>
</body></html>
