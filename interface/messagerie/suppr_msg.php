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
	session_name("preinsc");
	session_start();

	include "../../configuration/aria_config.php";
	include "$__INCLUDE_DIR_ABS/vars.php";
	include "$__INCLUDE_DIR_ABS/fonctions.php";
	include "$__INCLUDE_DIR_ABS/db.php";	

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	if(!isset($_SESSION["authentifie"]))
	{
		session_write_close();
		header("Location:../../index.php");
		exit();
	}

	if(!isset($_SESSION["comp_id"]) || (isset($_SESSION["comp_id"]) && $_SESSION["comp_id"]==""))
	{
		session_write_close();
		header("Location:../composantes.php");
		exit();
	}

	if(!isset($_SESSION["current_dossier"]))
	{
		session_write_close();
		header("Location:index.php");
		exit();
	}
	
	if(isset($_GET["p"]) && -1!=($params=get_params($_GET['p']))) // identifiant du message en param�tre crypt�
	{
		if(isset($params["msg"]))
		{
			$file=$params["msg"];

			// R�pertoire ?

			if(is_dir("$__CAND_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[authentifie]/$_SESSION[current_dossier]/$file"))
			{
				if($_SESSION["current_dossier"]!=$__MSG_TRASH) // d�placement vers le dossier "Corbeille"
				{
					if(!is_dir("$__CAND_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[authentifie]/$__MSG_TRASH"))
					{
						if(FALSE==mkdir("$__CAND_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[authentifie]/$__MSG_TRASH", 0770, TRUE))
						{
							mail($__EMAIL_ADMIN, "[Pr�candidatures] - Erreur de cr�ation de r�pertoire", "R�pertoire : $__CAND_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[authentifie]/$__MSG_TRASH\n\nUtilisateur : $_SESSION[nom] $_SESSION[prenom]");
							die("Erreur syst�me lors de la cr�ation d'un r�pertoire. Un message a �t� envoy� � l'administrateur.");
						}
					}

					rename("$__CAND_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[authentifie]/$_SESSION[current_dossier]/$file", "$__CAND_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[authentifie]/$__MSG_TRASH/$file");				
				}
				else
					deltree("$__CAND_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[authentifie]/$_SESSION[current_dossier]/$file");
			}
			elseif(is_file("$__CAND_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[authentifie]/$_SESSION[current_dossier]/$file.0")
					 || is_file("$__CAND_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[authentifie]/$_SESSION[current_dossier]/$file.1"))
			{
				if(is_file("$__CAND_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[authentifie]/$_SESSION[current_dossier]/$file.0"))
				{
					$fichier="$__CAND_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[authentifie]/$_SESSION[current_dossier]/$file.0";
					$real_file="$file.0";
				}
				else
				{
					$fichier="$__CAND_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[authentifie]/$_SESSION[current_dossier]/$file.1";
					$real_file="$file.1";
				}

				if($_SESSION["current_dossier"]!=$__MSG_TRASH) // d�placement vers le dossier "Corbeille"
				{
					if(!is_dir("$__CAND_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[authentifie]/$__MSG_TRASH"))
					{
						if(FALSE==mkdir("$__CAND_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[authentifie]/$__MSG_TRASH", 0770, TRUE))
						{
							mail($__EMAIL_ADMIN, "[Pr�candidatures] - Erreur de cr�ation de r�pertoire", "R�pertoire : $__CAND_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[authentifie]/$__MSG_TRASH\n\nUtilisateur : $_SESSION[nom] $_SESSION[prenom]");
							die("Erreur syst�me lors de la cr�ation d'un r�pertoire. Un message a �t� envoy� � l'administrateur.");
						}
					}

					rename("$fichier", "$__CAND_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[authentifie]/$__MSG_TRASH/$real_file");
				}
				else // Suppression compl�te
					@unlink("$fichier");
			}
		}
	}

	session_write_close();
	header("Location:index.php");
	exit();

?>
