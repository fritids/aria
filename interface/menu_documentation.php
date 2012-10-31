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
	// V�rifications compl�mentaires au cas o� ce fichier serait appel� directement
	if(!isset($_SESSION["authentifie"]))
	{
		session_write_close();
		header("Location:../index.php");
		exit();
	}

	if(!isset($_SESSION["comp_id"]) || (isset($_SESSION["comp_id"]) && $_SESSION["comp_id"]==""))
	{
		session_write_close();
		header("Location:composantes.php");
		exit();
	}

	print("<div class='centered_box'>
				<font class='TitrePage_16'>$_SESSION[onglet] - Documentation</font>
			</div>\n");

?>

<div class='fond_menu margin_10'>
	<font class='Texte_menu'><strong>1. Vous �tes enregistr�(e) dans l'application : que faire maintenant ?</strong></font>
</div>
<div class='margin_10'>
	<font class='Texte'>
		<p class='no_margin'>Sur l'Interface de Pr�candidatures, il n'est <u>plus n�cessaire</u> de t�l�charger de dossier papier ou PDF : cette
		interface EST votre dossier, elle devra contenir toutes les informations qui vous sont demand�es.</p>

		<p>Dans le menu gauche, vous devez <strong>COMPLETER CHAQUE SECTION</strong>, de l'Identit� (num�ro 1) aux pr�candidatures (num�ro 5).</p>

		<p class='Texte_important'>Attention : les menus 2, 3 et 4 sont <strong>communs � toutes les composantes</strong> (au cas o� vous voudriez d�poser des voeux dans
		plusieurs �tablissements). Remplissez ces informations <strong>une fois pour toutes</strong>, car si l'un de vos voeux est verrouill�
		par une composante, vous ne pourrez plus les modifier !</p>

		<p>Tous ces renseignements sont <b>OBLIGATOIRES</b>. Si vous ne les compl�tez pas, votre dossier risque de <strong>NE PAS
		ETRE EXAMINE</strong>.</p>
	</font>
</div>

<div class='fond_menu margin_10'>
	<font class='Texte_menu'><strong>2. Onglet Sp�cial : "Autres renseignements"</strong></font>
</div>
<div class='margin_10'>
	<font class='Texte'>
		<p class='no_margin'>Pour certaines formations choisies, des <strong>renseignements suppl�mentaires</strong> vous sont demand�s.</p>

		<p>Si c'est le cas, <strong>apr�s avoir s�lectionn� au moins l'une de ces formations</strong>, vous verrez appara�tre
		une <strong>SECTION N�6</strong> que vous devrez <strong>�galement compl�ter</strong>. Les informations demand�es
		sont l� encore <strong>OBLIGATOIRES</strong>.</p>
	</font>
</div>

<div class='fond_menu margin_10'>
	<font class='Texte_menu'><strong>3. Ensuite ?</strong></font>
</div>
<div class='margin_10'>
	<font class='Texte'>
		<p class='no_margin'>Une fois votre fiche remplie, vous devez <strong>attendre le verrouillage (automatique) de chaque formation
		demand�e</strong>. La date de ce verrouillage est visible dans le menu <strong>5 - Pr�candidatures</strong>, sur
		chaque voeu formul�. Pendant ce temps d'attente, vous pouvez modifier librement les voeux s�lectionn�s dans cette
		composante.</p>

		<p>D�s qu'un voeu est verrouill�, la <strong>liste des justificatifs</strong> � transmettre � la scolarit�
		<strong>PAR VOIE POSTALE UNIQUEMENT</strong> vous sera envoy�e.</p>

		<p class='Texte_important'>N'oubliez pas de consulter <strong>REGULIEREMENT</strong> votre <strong>messagerie
		�lectronique</strong> afin de suivre l'�volution de votre fiche et les demandes qui pourraient vous �tre
		faites.</p>
	</font>
</div>

<div class='fond_menu margin_10'>
	<font class='Texte_menu'><strong>4. Mode d'emploi complet</strong></font>
</div>
<div class='margin_10'>
	<font class='Texte'>
		<p class='no_margin'>Avant de poser une question � la scolarit�, merci de lire <strong>LE MODE D'EMPLOI</strong> � l'adresse suivante :</p>
		<div style='text-align:center; padding-top:10px;'>
			<a class='lien_rouge_14' href='<?php echo "$__DOC_DIR/documentation.php"; ?>' target='_blank'><strong>Mode d'emploi</strong></a>
		</div>
	</font>
</div>
