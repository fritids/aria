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

	verif_auth("../../login.php");

	// D�placement des �l�ments
	// Arguments :
	// - cid=current_id : id de l'objet � d�placer
	// - ct=current_type : type de l'objet � d�placer
	// - tid=target_id : id de l'objet suivant (ou pr�c�dent) avec lequel on va �changer l'ordre
	// - tt=current_type : type de l'objet suivant (ou pr�c�dent) avec lequel on va �changer l'ordre
	
	if(isset($_GET["co"]) && isset($_GET["ct"]) && isset($_GET["dir"]) && isset($_GET["tt"]) && isset($_SESSION["lettre_id"]))
	{
		$lettre_id=$_SESSION["lettre_id"];
		
		// ordre de l'�l�ment � d�placer
		$co=$_GET["co"];
			
		// type de l'�l�ment � d�placer
		$ct=$_GET["ct"];
			
		// type de l'objet suivant/pr�c�dent
		$tt=$_GET["tt"];

		// direction du d�placement (0:vers le haut, 1:vers le bas)
		$dir=$_GET["dir"];
	}
	else
	{
		header("Location:index.php");
		exit;
	}
	
	if(!isset($co) || !isset($ct) || !isset($tt) || !isset($dir) || $ct<0 || $ct>8 || $tt<0 || $tt>8)
	{
		// il manque des arguments : retour � l'index
		header("Location:index.php");
		exit;
	}
	
	// d�termine le nom de la table des �l�ments source &destination en fonction du type de chacun
	$current_table_name=get_table_name($ct);
	$current_table=$current_table_name["table"];
	$current_table_ordre=$current_table_name["ordre"];
	$current_table_id=$current_table_name["id"];

	$target_table_name=get_table_name($tt);
	$target_table=$target_table_name["table"];
	$target_table_ordre=$target_table_name["ordre"];
	$target_table_id=$target_table_name["id"];

	$dbr=db_connect();
	
	if($dir==0)
		$ordre_cible=$co-1;
	else
		$ordre_cible=$co+1;

	$temp_ordre=time();

	// var temporaire (contrainte sur id+ordre dans la table) puis �change
	db_query($dbr,"	UPDATE $target_table SET $target_table_ordre='$temp_ordre'
											WHERE $target_table_id='$lettre_id'
											AND $target_table_ordre='$ordre_cible';
										UPDATE $current_table SET $current_table_ordre='$ordre_cible'
											WHERE $current_table_id='$lettre_id'
											AND $current_table_ordre='$co';
										UPDATE $target_table SET $target_table_ordre='$co'
											WHERE $target_table_id='$lettre_id'
											AND $target_table_ordre='$temp_ordre'");

	db_close($dbr);
	
	header("Location:editeur.php");
	exit;
	
?>
