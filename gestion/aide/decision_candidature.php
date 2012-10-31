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
		titre_page_icone("[Aide] Candidature : d�cision de la commission p�dagogique", "help-browser_32x32_fond.png", 15, "L");
	?>

	<div style='margin-left:auto; margin-right:auto; padding-bottom:20px; width:90%; text-align:justify;'>
		<font class='Texte_16'><u><b>Fonction principale</b></u></font>
		<p class='Texte'>
			<b>Saisir une d�cision rendue par la Commission P�dagogique et/ou une date de convocation � un entretien</b>
		</p>
		<p class='Texte' style='padding-bottom:15px;'>
			Cette page est accessible uniquement lorsque le voeu concern� est verrouill� et que la candidature est
			recevable.
		</p>

		<font class='Texte_16'><u><b>Fonctionnalit�s et options</b></u></font>

		<p class='Texte' style='padding-bottom:15px;'>
			<u><b>S�lection de la d�cision</b></u> : d�cision partielle ou finale rendue par la commission (les choix
			disponibles sont configurables dans le menu Administration). Chaque d�cision pourra �tre rattach�e � un ou
			plusieurs mod�les de lettres, en fonction des besoin de votre �tablissement :
		</p>

		<font class='Texte'>
		<table cellpadding='2' cellspacing='0' border='1' align='center' width='100%'>
		<tr>
			<td align='justify' valign='top' nowrap>&#8226; Admis (1)</td>
			<td align='justify' valign='top'>Admission d�finitive du candidat</td>
		</tr>
		<tr>
			<td align='justify' valign='top' nowrap>&#8226; Admis apr�s recours (1)</td>
			<td align='justify' valign='top'>Admission d�finitive du candidat apr�s la validation d'un recours</td>
		</tr>
		<tr>
			<td align='justify' valign='top' nowrap>&#8226; Admis apr�s entretien (1)</td>
			<td align='justify' valign='top'>Admission d�finitive du candidat apr�s entretien compl�mentaire</td>
		</tr>
		<tr>
			<td align='justify' valign='top' nowrap>&#8226; Admis depuis la liste compl�mentaire (1)</td>
			<td align='justify' valign='top'>Admission d�finitive du candidat par passage de la liste compl�mentaire vers la liste principale</td>
		</tr>
		<tr>
			<td align='justify' valign='top' nowrap>&#8226; Admis sous r�serve (2)</td>
			<td align='justify' valign='top'>Le candidat est admis si la r�serve impos�e est v�rifi�e (par exemple : obtention du dipl�me en cours de pr�paration)</td>
		</tr>
		<tr>
			<td align='justify' valign='top' nowrap>&#8226; Convocable � l'entretien (3)</td>
			<td align='justify' valign='top'>Le candidat devra se pr�senter pour un entretien compl�mentaire</td>
		</tr>
		<tr>
			<td align='justify' valign='top' nowrap>&#8226; Dossier transmis (2)</td>
			<td align='justify' valign='top'>
				Le candidat n'est pas admis dans la formation demand�e, mais la commission a propos� son admission dans
				une autre formation (ann�e N-1 par exemple).
				<br>- Si la formation "cible" est propos�e dans le m�me �tablissement, elle doit �tre saisie dans le champ
				"Transmission => Nouvelle formation". La candidature est alors automatiquement cr��e dans	l'interface et
				pourra �tre trait�e ind�pendamment si besoin.
				<br>- Dans le cas contraire, elle peut �tre saisie en toute lettre dans le champ situ� juste en dessous et
				le dossier devra �tre transf�r� manuellement dans l'�tablissement cible.
				<br><br><b>Remarque</b> : cette d�cision n'est utile que si le candidat est <b>admis</b> (sous r�serve
				ou non) dans la formation cible, elle est inutile sinon. Un mod�le de lettre sp�cial peut �tre cr�� pour
				cette d�cision, indiquant � la fois au candidat qu'il a �t� refus� pour cette formation mais qu'il peut
				toutefois accepter le transfert propos� par la commission (cf. macros de l'Editeur de lettres).
			</td>
		</tr>
		<tr>
			<td align='justify' valign='top' nowrap>&#8226; D�sistement</td>
			<td align='justify' valign='top'>
				Le candidat n'a finalement pas souhait� poursuivre sa candidature. Cette d�cision est surtout utile pour
				produire une lettre de confirmation � destination du dossier et/ou du candidat.
			</td>
		</tr>
		<tr>
			<td align='justify' valign='top' nowrap>&#8226; En attente (2)</td>
			<td align='justify' valign='top'>
				La commission ne s'est pas d�finitivement prononc�e sur la candidature, des �lements compl�mentaires
				peuvent �tre demand�s au candidat (nuance par rapport � "Admis sous r�serve").
			</td>
		</tr>
		<tr>
			<td align='justify' valign='top' nowrap>&#8226; Liste compl�mentaire</td>
			<td align='justify' valign='top'>
				Le candidat est plac� sur liste compl�mentaire. Si un rang est indiqu� dans le champ correspondant, les
				candidats pr�sents sur la suite de la liste seront d�cal�s. Dans le cas contraire, le candidat est
				automatiquement plac� en queue de liste (le tri peut se faire ult�rieurement, mais attention � votre
				mod�le de lettre si le rang y figure).
			</td>
		</tr>
		<tr>
			<td align='justify' valign='top' nowrap>&#8226; Liste compl�mentaire apr�s entretien</td>
			<td align='justify' valign='top'>
				Suite � l'entretien pass� par le candidat, ce dernier est plac� sur liste compl�mentaire. Les remarques
				concernant la d�cision "Liste compl�mentaire" s'appliquent �galement.
			</td>
		</tr>
		<tr>
			<td align='justify' valign='top' nowrap>&#8226; Non trait�e</td>
			<td align='justify' valign='top'>
				Etat par d�faut lorsque la d�cision n'a pas �t� saisie. Aucune validation n'est n�cessaire.
			</td>
		</tr>
		<tr>
			<td align='justify' valign='top' nowrap>&#8226; Refus (2)</td>
			<td align='justify' valign='top'>Le candidat est refus�.</td>
		</tr>
		<tr>
			<td align='justify' valign='top' nowrap>&#8226; Refus apr�s entretien (2)</td>
			<td align='justify' valign='top'>Suite � l'entretien pass� par le candidat, ce dernier est refus�.</td>
		</tr>
		<tr>
			<td align='justify' valign='top' nowrap>&#8226; Refus apr�s recours (2)</td>
			<td align='justify' valign='top'>Le candidat a d�pos� un recours mais il n'a pas �t� accept�.</td>
		</tr>
		</table>
		</font>

		<p class='Texte'>	
			(1) aucun champs suppl�mentaire n'est requis, vous pouvez valider directement le formulaire
			<br>(2) un motif ou une r�serve doit obligatoirement �tre saisi pour pouvoir valider cette d�cision
			<br>(3) vous devez saisir la date, l'heure et le lieu de l'entretien
		</p>
		<p class='Texte'>
			<u><b>Transmission : choix de la nouvelle formation</b></u> : la Commission P�dagogique peut refuser une candidature
			tout en proposant un choix plus adapt� au candidat. Lorsque c'est le cas, on parle de transmission de dossier
			(d�cision "Dossier Transmis") et c'est ici que vous devez indiquer la formation propos�e par la Commission. Si
			la formation se trouve dans une autre composante, utilisez le champ libre pr�vu � cet effet, il faudra alors
			transf�rer les pi�ces du dossier � la composante cible.
		</p>
		<p class='Texte'>
			<u><b>Rang sur liste compl�mentaire</b></u> : lorsque le candidat est plac� sur liste compl�mentaire et que vous
			connaissez le rang sur cette liste, vous devez l'indiquer ici. Si vous laissez ce champ vide, le candidat sera
			automatiquement plac� en queue de liste. Note : il faut avoir s�lectionn� la d�cision "Liste compl�mentaire"
			(apr�s entretien ou non) pour que ce champ soit pris en compte.
		</p>
		<p class='Texte'>
			<u><b>Entretien</b></u> : pour certaines formations propos�es par votre �tablissement, les candidats doivent
			passer un entretien compl�mentaire. Ces champs servent � entrer la date et l'heure de la convocation.
			<br>Note 1 : si vous	laissez vides les champs "Salle" et "Lieu", les valeurs par d�faut seront utilis�es (cf.
			configuration de la composante)
			<br>Note 2 : si la formation ne n�cessite aucun entretien, ces champs n'appara�tront pas (cf. configuration
			de la formation).
		</p>
		<p class='Texte'>
			<u><b>Confirmation du candidat</b></u> : si vos mod�les de lettres poss�dent un talon r�ponse demandant au
			candidat de confirmer sa candidature, vous pourrez indiquer la r�ponse � cet endroit.
		</p>
		<p class='Texte'>
			<u><b>Forcer la date des lettres</b></u> : si la date des lettres g�n�r�es par l'interface n'est pas correcte ou
			si vous traitez une candidature avant la date de la Commission P�dagogique, vous pouvez forcer la date de la
			lettre pour cette candidature. </font><font class='Texte_important'>Ce champ doit �tre utilis� avec prudence</font>.
			<font class='Texte'>
		</p>
		<p class='Texte'>
			<u><b>Motifs de refus, de r�serve ou de mise en attente</b></u> : en fonction de la d�cision saisie, vous devez
			indiquer le ou les motifs relatifs � cette derni�re. Si un motif n'apparait pas dans la liste pr�d�finie (cases
			� cocher), vous pouvez utiliser le champ libre dans la partie droite. C'est notamment le cas des r�serves et des
			mises en attente.
			<br>Note : pour ajouter des motifs, consultez l'aide du menu Configuration.
		</p>
		<p class='Texte'>
			<u><b>Validation</b></u> : l'ic�ne verte sous le formulaire vous permet de valider la saisie. En cas d'erreur,
			l'interface restera sur cet �cran et vous indiquera pourquoi la validation a �chou� (par exemple : absence de
			motif dans le cas d'un refus).
		</p>
	</div>
</div>
<?php
	pied_de_page();
?>
</body></html>
