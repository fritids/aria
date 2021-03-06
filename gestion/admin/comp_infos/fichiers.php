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

	$dbr=db_connect();

	// Ajouter / modifier un encadr�

	if(isset($_SESSION["info_doc_id"]))
		$info_doc_id=$_SESSION["info_doc_id"];
	else
	{
		header("Location:index.php");
		exit;
	}

	if(isset($_GET["a"]) && isset($_GET["o"])) // Nouvel �l�ment
	{
		$_SESSION["ordre"]=$ordre=$_GET["o"];
		$_SESSION["ordre_max"]=$_SESSION["cbo"];
		$_SESSION["ajout"]=1;

		$action="Ajouter";
	}
	elseif(isset($_GET["o"])) // Modification
	{
		$_SESSION["ordre"]=$ordre=$_GET["o"];

		$action="Modifier";

		// R�cup�ration des infos actuelles
		$result=db_query($dbr,"SELECT $_DBC_comp_infos_fichiers_texte, $_DBC_comp_infos_fichiers_fichier FROM $_DB_comp_infos_fichiers
															WHERE $_DBC_comp_infos_fichiers_info_id='$info_doc_id'
															AND $_DBC_comp_infos_fichiers_ordre='$ordre'");
		$rows=db_num_rows($result);
		if($rows)
		{
			list($texte,$fichier)=db_fetch_row($result,0);
			db_free_result($result);
		}
		else
		{
			db_close($dbr);
			header("Location:index.php");
			exit();
		}
	}

	if(isset($_SESSION["ajout"]) && $_SESSION["ajout"]==1)
		$action="Ajouter";
	else
		$action="Modifier";

	if(isset($_POST["go_envoyer"]) || isset($_POST["go_envoyer_x"]))
	{
		$texte=trim($_POST['texte']);

		if(!isset($_SESSION["ajout"]))
		{
			if(empty($texte))
				$texte=$fichier;

			db_query($dbr,"UPDATE $_DB_comp_infos_fichiers SET $_DBU_comp_infos_fichiers_texte='$texte'
												WHERE $_DBU_comp_infos_fichiers_info_id='$info_doc_id'
												AND $_DBU_comp_infos_fichiers_ordre='$_SESSION[ordre]'");

			db_close($dbr);
			header("Location:index.php");
			exit();
		}
		else
		{
			// informations li�es au fichier upload�
			$file_name=$_FILES["fichier"]["name"];
			$file_size=$_FILES["fichier"]["size"];
			$file_tmp_name=$_FILES["fichier"]["tmp_name"];
			$file_error=$_FILES["fichier"]["error"]; // PHP > 4.2.0 uniquement

			$file_name=html_entity_decode(validate_filename($file_name),ENT_QUOTES, $default_htmlspecialchars_encoding);

			if($file_size>16777216)
				$trop_gros=1;
			elseif($file_name=="none" || $file_name=="" || !is_uploaded_file($_FILES["fichier"]["tmp_name"]))
				$fichier_vide=1;
			else
			{
				if(!is_dir("$__CAND_COMP_STOCKAGE_DIR_ABS/$_SESSION[comp_id]"))
					mkdir("$__CAND_COMP_STOCKAGE_DIR_ABS/$_SESSION[comp_id]", 0770, TRUE);

				$destination_path="$__CAND_COMP_STOCKAGE_DIR_ABS/$_SESSION[comp_id]/$file_name";

				$x=0;
				while(is_file("$destination_path")) // le fichier existe deja : on change le nom en ajoutant un num�ro
				{
					$test_file_name=$x. "-$file_name";
					$destination_path="$__CAND_COMP_STOCKAGE_DIR_ABS/$_SESSION[comp_id]/$test_file_name";
					$x++;
				}

				if(!move_uploaded_file($file_tmp_name, $destination_path))
					$erreur_copie_fichier=1;
				else
				{
					$id_fichier=time();

					if(isset($test_file_name) && $test_file_name!="")
						$file_name=$test_file_name;

					// D�termination de l'ordre
					if($_SESSION["ordre"]!=$_SESSION["ordre_max"]) // On n'ins�re pas l'�l�ment en dernier : d�callage
					{
						// 1 - Reconstruction des �l�ments (comme pour la suppression)
						$a=get_all_elements($dbr, $info_doc_id);
						$nb_elements=count($a);

						for($i=$nb_elements; $i>$_SESSION["ordre"]; $i--)
						{
							$current_ordre=$i-1;
							$new_ordre=$i;
							$current_type=$a["$current_ordre"]["type"]; // le type sert juste � savoir dans quelle table on doit modifier l'�l�ment courant
							$current_id=$a["$current_ordre"]["id"];

							$current_table_name=get_table_name($current_type);
							$col_ordre=$current_table_name["ordre"];
							$col_id=$current_table_name["id"];
							$table=$current_table_name["table"];

							db_query($dbr,"UPDATE $table SET $col_ordre='$new_ordre'
																WHERE $col_id='$current_id'
																AND $col_ordre='$current_ordre'");
						}
					}

					if(empty($texte))
						$texte=$file_name;

					db_query($dbr,"INSERT INTO $_DB_comp_infos_fichiers VALUES ('$info_doc_id', '$texte', '$file_name', '$_SESSION[ordre]')");

					db_close($dbr);
					header("Location:index.php");
					exit();
				}
			}
		}
	}

	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>

<div class='main'>
	<?php
		titre_page_icone("$action un fichier", "editcopy_32x32_fond.png", 30, "L");

		print("<form enctype='multipart/form-data' action='$php_self' method='POST'>\n");

		if(isset($erreur_copie_fichier))
			message("Erreur : impossible de copier le fichier re�u. Merci de contacter l'administrateur.", $__ERREUR);

		if(isset($trop_gros))
			message("Erreur : le fichier envoy� est trop gros (max : 16Mo)", $__ERREUR);

		if(isset($fichier_vide))
			message("Erreur : aucun fichier s�lectionn�.", $__ERREUR);
	?>

	<table align='center'>
	<tr>
		<td class='fond_menu2' colspan='2' style='padding:4px 20px 4px 20px;'>
			<font class='Texte_menu2'>
				<b>&#8226;&nbsp;&nbsp;Donn�es du fichier</b>
			</font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>S�lection du fichier : </b></font>
		</td>
		<td class='td-droite fond_menu'>
			<?php
				if(isset($_SESSION["ajout"]))
					print("<input type='file' name='fichier'>
								<input type='hidden' name='MAX_FILE_SIZE' value='16777216'>\n");
				else
					print("<font class='Texte'>$fichier</font>\n");
			?>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' style='vertical-align:top;'>
			<font class='Texte_menu2'><b>Description</b></font>
		</td>
		<td class='td-droite fond_menu' style='white-space:normal; vertical-align:top;'>
			<input type='text' name='texte' value='<?php if(isset($texte)) echo htmlspecialchars(stripslashes($texte), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' maxlength='128' size='60'>
			<br><br>
			<font class='Texte_menu'>
				<i>La description est le texte qui appara�t sur la page d'information, sur lequel le candidat devra cliquer pour obtenir le fichier</i>
			</font>
		</td>
	</tr>
	</table>

	<div class='centered_icons_box'>
		<a href='index.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Retour' border='0'></a>
		<input type="image" src="<?php echo "$__ICON_DIR/mail_forward_32x32_fond.png"; ?>" alt="Envoyer ce fichier" name="go_envoyer" value="Envoyer ce fichier">
		</form>
	</div>

</div>
<?php
	db_close($dbr);
	pied_de_page();
?>			
</body></html>
