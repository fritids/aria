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

	// Suppression d'un �l�ment
	// Arguments :
	// o : ordre de l'objet (dans le tableau) � supprimer (il faudra d�caler tous les objets suivants).
	// r�cup�ration des variables crypt�es
	
	if(isset($_GET["o"]) && isset($_SESSION["lettre_id"]))
	{
		$lettre_id=$_SESSION["lettre_id"];
		$o=$_GET["o"];
	}
	elseif(isset($_POST["o"]) && isset($_SESSION["lettre_id"]))
	{
		$lettre_id=$_SESSION["lettre_id"];
		$o=$_POST["o"];
	}
	else
	{
		header("Location:index.php");
		exit;
	}

	if(isset($_POST["go"]) || isset($_POST["go_x"]))
	{
		$o=$_POST["o"];

		$dbr=db_connect();

		$a=get_all_elements($dbr, $lettre_id);

		// � priori, tout est bon, on supprime et on d�cale les �l�ments restants

		$nb_elements=count($a);
		$suppr_id=$a["$o"]["id"];
		$suppr_type=$a["$o"]["type"];

		$suppr_table_name=get_table_name($suppr_type);
		$col_ordre=$suppr_table_name["ordre"];
		$col_id=$suppr_table_name["id"];
		$table=$suppr_table_name["table"];

		if($table!="" && $col_id!="" && $col_ordre!="")
		{
			db_query($dbr,"DELETE FROM $table WHERE $col_id='$suppr_id' AND $col_ordre='$o'");

			for($i=($o+1); $i<$nb_elements; $i++)
			{
				$current_ordre=$i;
				$new_ordre=$i-1;
				$current_type=$a["$i"]["type"]; // le type sert juste � savoir dans quelle table on doit modifier l'�l�ment courant
				$current_id=$a["$i"]["id"];

				$current_table_name=get_table_name($current_type);
				$col_ordre=$current_table_name["ordre"];
				$col_id=$current_table_name["id"];
				$table=$current_table_name["table"];

				db_query($dbr,"UPDATE $table SET $col_ordre='$new_ordre' WHERE $col_id='$current_id' AND $col_ordre='$current_ordre'");
			}
			// d�calage termin�		
			db_close($dbr);
			
	      header("Location:editeur.php");
         exit;
		}
		else
		{
			$pb_vars=1;
			db_close($dbr);
		}
	}

	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_simple();
?>

<div class='main'>
	<?php
		titre_page_icone("Supprimer un �l�ment", "trashcan_full_32x32_slick_fond.png", 30, "L");

		if(isset($pb_vars))
			message("Erreur de r�cup�ration des variables : votre navigateur est-il � jour ?", $__ERREUR);

		message("La suppression d'un �l�ment est <b>d�finitive</b> !", $__WARNING);

		print("<br>");

		message("Souhaitez-vous vraiment supprimer cet �l�ment ?", $__QUESTION);

		print("<form method='post' action='$php_self'>
				 <input type='hidden' name='o' value='$o'>

				 <div class='centered_icons_box'>
					<a href='editeur.php' target='_self' class='lien2'><img src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Retour' border='0'></a>
					<input type='image' src='$__ICON_DIR/trashcan_full_32x32_slick_fond.png' alt='Confirmer' name='go' value='Confirmer'>
				</div>

				</form>\n");
	?>
</div>
<?php
	pied_de_page();
?>
</body></html>
