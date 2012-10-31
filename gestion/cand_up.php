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
	// REMARQUE :
	// On aurait pu fusionner cand_up et cand_down, mais leur fonctionnement est l�g�rement diff�rent.
	// En dupliquant, on s'affranchit de pas mal d'erreurs et on am�liore la lisibilit�.

	session_name("preinsc_gestion");
	session_start();

	include "../configuration/aria_config.php";
	include "$__INCLUDE_DIR_ABS/vars.php";
	include "$__INCLUDE_DIR_ABS/fonctions.php";
	include "$__INCLUDE_DIR_ABS/db.php";

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	verif_auth();

	if(!in_array($_SESSION['niveau'], array("$__LVL_SCOL_MOINS","$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
	{
		header("Location:$__MOD_DIR/gestion/noaccess.php");
		exit();
	}

	if($_SESSION['tab_candidat']['lock']!=1)
	{
		header("Location:edit_candidature.php");
		exit();
	}

	$candidat_id=$_SESSION["candidat_id"];

// r�cup�ration des param�tres pass�s en GET
	if(array_key_exists("cand_id",$_GET) &&  is_numeric($_GET["cand_id"])) // id de candidature
		$cand_id=$_GET["cand_id"];
	else
	{
		header("Location:edit_candidature.php");
		exit;
	}

	// si on change l'ordre d'une sp�cialit� au sein d'une candidature � choix multiples ...
	if(array_key_exists("groupe",$_GET) && is_numeric($_GET["groupe"]))
		$groupe=$_GET["groupe"];

	$dbr=db_connect();
	
	$result=db_query($dbr,"SELECT $_DBC_cand_ordre,$_DBC_cand_ordre_spec FROM $_DB_cand
									WHERE $_DBC_cand_candidat_id='$candidat_id'
									AND 	$_DBC_cand_id='$cand_id'");

	$rows=db_num_rows($result);

	if($rows)
	{
		list($ordre_actuel,$ordre_spec_actuel)=db_fetch_row($result,0);

		// colonne diff�rente selon qu'on r�ordonne une candidature ou une sp�cialit�
		if(isset($groupe)) // sp�cialit�
		{
			$ordre_actuel=$ordre_spec_actuel;
			$colonne_ordre="$_DBU_cand_ordre_spec";
			$condition_groupe="AND $_DBU_cand_groupe_spec='$groupe'";
		}
		else
		{
			$colonne_ordre="ordre";
			$condition_groupe="";
		}

		if($ordre_actuel!=1) // si =1, on ne  change rien (test de pr�caution - 1 = ordre minimal)
		{
			$ordre_cible=$ordre_actuel-1;
			// l'ordre 0 est utilis� comme swap
			db_query($dbr,"UPDATE $_DB_cand SET $colonne_ordre='0'
									WHERE $_DBU_cand_candidat_id='$candidat_id'
									AND $_DBU_cand_periode='$__PERIODE'
									AND $colonne_ordre='$ordre_cible' $condition_groupe;

								UPDATE $_DB_cand SET $colonne_ordre='$ordre_cible'
									WHERE $_DBU_cand_candidat_id='$candidat_id'
									AND $_DBU_cand_periode='$__PERIODE'
									AND $colonne_ordre='$ordre_actuel' $condition_groupe;

								UPDATE $_DB_cand SET $colonne_ordre='$ordre_actuel'
									WHERE $_DBU_cand_candidat_id='$candidat_id'
									AND $_DBU_cand_periode='$__PERIODE'
									AND $colonne_ordre='0' $condition_groupe");
		}
	}
	
	db_free_result($result);
	db_close($dbr);
	
	header("Location:edit_candidature.php");
	exit;
		
?>

