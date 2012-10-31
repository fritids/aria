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
	// Consultation d'une pi�ce jointe (pertinent pour la partie gestion, alors que les candidats
	//	ne peuvent pas en joindre ?)
	session_name("preinsc_gestion");
	session_start();

	include "../../configuration/aria_config.php";
	include "$__INCLUDE_DIR_ABS/vars.php";
	include "$__INCLUDE_DIR_ABS/fonctions.php";
	include "$__INCLUDE_DIR_ABS/db.php";

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	verif_auth();

	if(!isset($_SESSION["current_dossier"]))
	{
		header("Location:index.php");
		exit();
	}
	else
		$current_dossier=$_SESSION["current_dossier"];

	$dbr=db_connect();

	if(isset($_GET["p"]) && isset($_SESSION["msg_dir"]) && -1!=($params=get_params($_GET['p']))) // chemin complet du message, chiffr�
	{
		if(isset($params["pj"]) && is_file("$_SESSION[msg_dir]/files/$params[pj]"))
		{
			if(!is_dir("$__GESTION_COMP_STOCKAGE_DIR_ABS/$_SESSION[comp_id]/$_SESSION[auth_id]"))
				mkdir("$__GESTION_COMP_STOCKAGE_DIR_ABS/$_SESSION[comp_id]/$_SESSION[auth_id]", 0770, true);
			else
			{
				// Nettoyage puis recr�ation (�vite le cumul des fichiers)
				deltree("$__GESTION_COMP_STOCKAGE_DIR_ABS/$_SESSION[comp_id]/$_SESSION[auth_id]");

				mkdir("$__GESTION_COMP_STOCKAGE_DIR_ABS/$_SESSION[comp_id]/$_SESSION[auth_id]", 0770, true);
			}

			copy("$_SESSION[msg_dir]/files/$params[pj]", "$__GESTION_COMP_STOCKAGE_DIR_ABS/$_SESSION[comp_id]/$_SESSION[auth_id]/$params[pj]");

			echo "<HTML><SCRIPT>document.location='$__GESTION_COMP_STOCKAGE_DIR/$_SESSION[comp_id]/$_SESSION[auth_id]/$params[pj]';</SCRIPT></HTML>";
		}
	}
?>
