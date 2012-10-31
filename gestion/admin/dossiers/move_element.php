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
	include "include/editeur_fonctions.php";

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	verif_auth("$__GESTION_DIR/login.php");

	// D�placement des �l�ments
	// Arguments :
	// - co= ordre de l'objet � d�placer
	// - dir : direction (haut / bas)

	if(isset($_GET["dir"]) && ($_GET["dir"]==0 || $_GET["dir"]==1) && isset($_GET["co"]) && ctype_digit($_GET["co"]))
	{
		$dir=$_GET["dir"];
		$co=$_GET["co"];
	}
	else
	{
		// il manque des arguments : retour � l'index
		header("Location:index.php");
		exit;
	}

	$dbr=db_connect();

	if(isset($_SESSION["filtre_dossier"]) && $_SESSION["filtre_dossier"]!="-1")
		$propspec_id=$_SESSION["filtre_dossier"];
	elseif(isset($_GET["pid"]) && ctype_digit($_GET["pid"])
			 && db_num_rows(db_query($dbr, "SELECT * FROM $_DB_propspec WHERE $_DBC_propspec_id='$_GET[pid]'
														AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'")))
		$propspec_id=$_GET["pid"];

	if(!isset($propspec_id))
	{
		// il manque des arguments : retour � l'index
		db_close($dbr);
		header("Location:index.php");
		exit;
	}

	if($dir==0)
		$ordre_cible=$co-1;
	else
		$ordre_cible=$co+1;

	$temp_ordre=rand(100, 32000);

	// var temporaire (contrainte sur id+ordre dans la table) puis �change

	db_query($dbr,"UPDATE $_DB_dossiers_ef SET $_DBU_dossiers_ef_ordre='$temp_ordre'
							WHERE $_DBU_dossiers_ef_propspec_id='$propspec_id'
							AND 	$_DBU_dossiers_ef_ordre='$ordre_cible';

						UPDATE $_DB_dossiers_ef SET $_DBU_dossiers_ef_ordre='$ordre_cible'
							WHERE $_DBU_dossiers_ef_propspec_id='$propspec_id'
							AND $_DBU_dossiers_ef_ordre='$co';

						UPDATE $_DB_dossiers_ef SET $_DBU_dossiers_ef_ordre='$co'
							WHERE $_DBU_dossiers_ef_propspec_id='$propspec_id'
							AND $_DBU_dossiers_ef_ordre='$temp_ordre'");

	db_close($dbr);
	
	header("Location:index.php");
	exit;
	
?>
