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
	// SUPPRESSION AUTOMATIQUE DES FICHES ORPHELINES (SANS CANDIDATURE)	
	// APRES 30 JOURS D'INACTIVITE (DEPUIS LA DERNIERE CONNEXION)
	// HORS FICHES CREEES MANUELLEMENT

	session_name("preinsc");
	session_start();
	
	include "../configuration/aria_config.php";
	include "$__INCLUDE_DIR_ABS/vars.php";
	include "$__INCLUDE_DIR_ABS/db.php";
	include "$__INCLUDE_DIR_ABS/fonctions.php";

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	// Script : on force l'IP locale et le nom
	$_SESSION["auth_ip"]="127.0.0.1";
	$_SESSION["auth_host"]="localhost";

	$current_date=time();
	
	$limite=$current_date-(30*86400); // date - 30 jours

	// Date courante au format AA MM JJ HH MM SS MS(5)
	// (lorsqu'on se base sur les identifiants, i.e sur la date de cr�ation de la fiche)
	$limite_id=new_id($limite);

	$dbr=db_connect();

	$result=db_query($dbr, "SELECT $_DBC_candidat_id, $_DBC_candidat_nom, $_DBC_candidat_prenom, $_DBC_candidat_email,
											 $_DBC_candidat_connexion
										FROM $_DB_candidat
									WHERE $_DBC_candidat_manuelle='0'
									AND $_DBC_candidat_id NOT IN (SELECT distinct($_DBC_cand_candidat_id) FROM $_DB_cand)
									AND ($_DBC_candidat_connexion < '$limite'
										 OR ($_DBC_candidat_connexion='0' AND $_DBC_candidat_id < '$limite_id'))");

	$rows=db_num_rows($result);

	if($rows)
	{
		$liste="";

		for($i=0; $i<$rows; $i++)
		{
			list($candidat_id, $candidat_nom, $candidat_prenom, $candidat_email, $candidat_connexion)=db_fetch_row($result, $i);

			$date_connexion=date_fr("j F Y", $candidat_connexion);

			$liste.="$candidat_id - $candidat_nom $candidat_prenom - $candidat_email - Connexion : $date_connexion\n";

			db_query($dbr, "DELETE FROM $_DB_candidat WHERE $_DBC_candidat_id='$cand_id'");
		}

		// Liste envoy�e par mail tous les jours � l'admin
		$corps_message="Les $rows fiches suivantes ont �t� supprim�es : \n\n$liste";
		mail($__EMAIL_ADMIN,"Candidatures : rapport de suppression", $corps_message);
	}

	db_free_result($result);
	db_close($dbr);
?>
