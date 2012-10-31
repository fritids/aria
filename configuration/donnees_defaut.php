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
// Ce fichier contient les donn�es ins�r�es par d�faut lorsqu'une composante est cr��e (d�cision, motifs de refus, etc.)
// Si vous conservez ces donn�es, vous devrez si besoin les modifier ou les compl�ter via l'interface de configuration.

// DECISIONS
// Insertion si le param�tre $__DEFAUT_DECISIONS=1 est pr�sent dans le fichier de configuration
function insert_default_decisions($dbr, $comp_id)
{
	db_query($dbr, "INSERT INTO $GLOBALS[_DB_decisions_comp] (SELECT '$comp_id', $GLOBALS[_DBC_decisions_id] FROM $GLOBALS[_DB_decisions] ORDER BY id)");
}


// Motifs de refus : tableau de tableaux � trois �l�ments :
// - le texte court : utilis� dans les menus et dans les lettres lorsqu'il n'y a pas de texte long.
// - le texte long (facultatif) est utilis� comme paragraphe dans les lettres (si renseign�).
// - un bool�en (0 ou 1) indiquant si le motif est exclusif (devant �tre utilis� seul, et prioritaire sur les autres) ou non.

// Pour ne pas ins�rer de motifs par d�faut, deux solutions :
// - soit mettre � variable $__DEFAUT_MOTIFS dans le fichier de configuration (pr�f�r�)
// - soit commenter la variable $__DEFAUT_MOTIFS_REFUS
// - soit supprimer son contenu

$__DEFAUT_MOTIFS_REFUS=array(
	array("R�sultats insuffisants", "", 0),
	array("R�sultats insuffisants � l'examen du BTS", "", 0),
	array("R�sultats insuffisants en 1er cycle", "", 0),
	array("Pas d'avis favorable de poursuite d'�tudes", "", 0),
	array("Pr�requis en informatique non satisfaits", "", 0),
	array("Cursus inadapt�", "", 0),
	array("Nombre maximum d'inscriptions atteint",
			"Le dossier n'a pu etre retenu compte tenu du nombre de dossiers soumis et de leur qualit�.", 1),
	array("Non pr�sentation � l'entretien de s�lection","", 0),
	array("Pas d'offre apprentissage adapt�e",
			"Pas de proposition de contrat d'apprentissage en ad�quation avec votre parcours", 0),
	array("Candidat sans contrat d'apprentissage",
			"Vous ne justifiez pas de la signature d'un contrat d'apprentissage.", 0));


// =================================================================
// 			Fonctions relatives aux donn�es d�crites ci-dessus
// =================================================================

// Fonction d'insertion des motifs par d�faut
// Deux arguments obligatoires :
// - $dbr : base de donn�es ouverte
// - $comp_id : identifiant de la composante
 
function insert_default_motifs($dbr, $comp_id)
{
	if(array_key_exists("__DEFAUT_MOTIFS_REFUS", $GLOBALS) && is_array($GLOBALS["__DEFAUT_MOTIFS_REFUS"]))
	{
		foreach($GLOBALS["__DEFAUT_MOTIFS_REFUS"] as $array_decision)
		{
			// Chaque �l�ment de $GLOBALS["__DEFAUT_MOTIFS_REFUS"] doit �galement �tre un tableau �
			// trois �l�ments (texte court / texte long / caract�re exclusif)
			// Le contenu de ces �l�ments rel�ve de l'administrateur ...
			if(is_array($array_decision) && count($array_decision)==3)
			{
				// Calcul du nouvel identifiant
				// Avec max(), on aura un r�sultat, m�me vide
				list($new_id)=db_fetch_row(db_query($dbr, "SELECT max($GLOBALS[_DBC_motifs_refus_id])+1 FROM $GLOBALS[_DB_motifs_refus]"), 0);

				if($new_id=="") $new_id=0;

				$exclusif=$array_decision[2]=='1' ? 1 : 0;
				$motif_court=str_replace("'", "''", preg_replace("/[']+/", "'", $array_decision[0]));
				$motif_long=str_replace("'", "''", preg_replace("/[']+/", "'", $array_decision[1]));

				// TODO : cr�er des fonctions d'acc�s pour toutes ces op�rations ...
				db_query($dbr, "INSERT INTO $GLOBALS[_DB_motifs_refus] VALUES ('$new_id', '$motif_court', '$motif_long', '$exclusif', '$comp_id')");
			}
		}
	}
	
	// Mise � jour de la s�quence
   db_query($dbr, "SELECT setval('motifs_refus_id_seq', (select max($GLOBALS[_DBU_motifs_refus_id]) from $GLOBALS[_DB_motifs_refus]))");
}
