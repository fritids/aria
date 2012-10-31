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
		titre_page_icone("[Aide] Traitement de d�cisions et g�n�ration de documents en masse", "help-browser_32x32_fond.png", 15, "L");
	?>
		
	<div style='margin-left:auto; margin-right:auto; padding-bottom:20px; width:90%; text-align:justify;'>
		<font class='Texte_16'><u><b>Fonction principale</b></u></font>
		<p class='Texte' style='padding-bottom:15px;'>
			<b>Effectuer des op�rations simples sur un grand nombre de fiches.</b>
		</p>

		<font class='Texte_16'><u><b>Fonctionnalit�s et options</b></u></font>

		<p class='Texte'>
			<u><b>Saisie des d�cisions en masse</b></u> : lorsque les formulaires de Commissions sont retourn�s compl�t�s 
			en scolarit�, les d�cisions doivent �tre report�es sur l'interface afin de pouvoir poursuivre le traitement des
			candidatures. Lorsque les d�cisions sont simples (admission, refus avec un motif unique, ...), il est possible de
			les saisir � la cha�ne pour une formation donn�e.
		</p>
		<p class='Texte' style='padding-bottom:15px;'>
			<u>Remarque :</u> cette saisie est plus efficace si les formulaires de Commissions sont pr�alablement tri�s par
			formation et par ordre alphab�tique des candidats.
		</p>
		<p class='Texte'>
			<u><b>Consultation des saisies ant�rieures et g�n�ration des lettres (format PDF)</b></u> : lorsque vous validez une saisie en
			masse, le traitement est enregistr� et un lien est cr�� sur la page. Ce lien vous permet de g�n�rer les lettres
			qui correspondent au traitement que vous venez de valider. Lorsque le nombre de d�cisions saisies d�passe un certain
			seuil, l'interface cr�e plusieurs liens afin de r�duire le d�lai de g�n�ration des documents. Il faudra donc cliquer
			sur chaque lien "Partie 1", "Partie 2" , ..., "Partie N" pour obtenir tous les documents correspondants.
		</p>
		<p class='Texte' style='padding-bottom:15px;'>
			<u>Remarque :</u> Lorsqu'une fiche est trait�e plusieurs fois (par exemple : si la premi�re d�cision est "Convoqu�
			� l'entretien", et la seconde "Admis"), seul le <b>dernier traitement</b> est conserv� dans l'historique des saisies
			en masse, ceci emp�che donc de g�n�rer un nouveau courrier pour une d�cision obsol�te.
		</p>
		<p class='Texte' style='padding-bottom:15px;'>
			<u><b>G�n�ration des R�capitulatifs (format PDF)</b></u> : certains candidats oublient de joindre le r�capitulatif de leurs
			fiches au reste de leurs justificatifs. Cette fonctionnalit� vous permet de g�n�rer ces documents en masse, en
			fonction d'une formation et d'un intervalle de dates.
		</p>
		<p class='Texte' style='padding-bottom:15px;'>
			<u><b>G�n�ration des Formulaires de Commissions (format PDF)</b></u> : les formulaires de Commissions doivent �tre
			g�n�r�s avant les Commissions P�dagogiques : ils sont utilis�s par leurs membres pour y �crire la d�cision (cases
			� cocher) avec le(s) motif(s) appropri�s (motifs pr�d�finis et/ou motifs libres si besoin). Ces formulaires doivent
			ensuite �tre retourn�s � la scolarit� pour que les d�cisions soient report�es sur l'interface.
		</p>
		<p class='Texte' style='padding-bottom:15px;'>
			<u><b>G�n�ration d'une liste de passage � un entretien (format PDF)</b></u> : lorsque les candidats sont convoqu�s
			� un entretien compl�mentaire (avant d�cision finale de la Commission P�dagogique), il peut �tre utile de g�n�rer
			les listes de passage en fonction de la date et de la salle utilis�e. Ces listes peuvent ensuite �tre coll�es aux
			portes de ces salles ou sur le tableau d'affichage destin� aux �tudiants, par exemple.
		</p>
		<p class='Texte'>
			<u><b>G�n�ration des lettres de d�cisions (format PDF)</b></u> : une fois les d�cisions saisies (via les saisies en
			masse ou individuelles), vous pouvez g�n�rer toutes les lettres relatives � ces derni�res.
		</p>
		<p class='Texte'>
			<u>Remarque 1 :</u> v�rifiez bien qu'au moins une lettre est associ�e � chaque d�cision et formation (dans l'Editeur
			de lettres). Si aucun document n'est reli�, rien ne sera g�n�r�.
		</p>
		<p class='Texte' style='padding-bottom:15px;'>
			<u>Remarque 2 :</u> plusieurs lettres peuvent �tre g�n�r�es pour une m�me d�cision (par exemple : une lettre officielle
			d'admission peut �tre coupl�e aux modalit�s d'inscription administrative ainsi qu'� une note d'information sur la
			date et l'heure d'une �ventuelle r�union de rentr�e).
		</p>
		<p class='Texte'>
			<u><b>Export de donn�es au format brut (format CSV)</b></u> : une fois les d�cisions saisies et les courriers envoy�s,
			la scolarit� peut avoir besoin de r�cup�rer la liste des candidats admis pour efectuer d'autres traitements compl�mentaires
			non pr�vus par l'interface. L'interface offre la possibilit� d'extraire certaines donn�es dans un fichier au format
			"CSV" ("Comma Separated Values", i.e "valeurs s�par�es par une virgule"). Une fois les donn�es � extraire s�lectionn�es,
			il suffit de t�l�charger le fichier et l'ouvrir � l'aide d'un tableur (OpenOffice, Microsoft Excel, ...) pour les
			manipuler.
		</p>
		<p class='Texte'>
			<u>Remarque 1 :</u> pour ces fichiers CSV, le s�parateur de champs � indiquer au tableur est <b>le point virgule</b>.
		</p>
		<p class='Texte' style='padding-bottom:15px;'>
			<u>Remarque 2 :</u></font> <font class='Texte_important'><b>ces donn�es doivent �tre extraites � des fins p�dagogiques
			uniquement. Leur exploitation est r�glement�e (ce sont des donn�es nominatives et personnelles), <b>votre responsabilit�
			peut donc �tre engag�e</b>.</font>
		</p>
	</div>
</div>
<?php
	pied_de_page();
?>
</body></html>
