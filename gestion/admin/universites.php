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

	verif_auth("$__GESTION_DIR/login.php");

	if($_SESSION['niveau']!=$__LVL_ADMIN)
	{
		header("Location:$__GESTION_DIR/noaccess.php");
		exit();
	}

	// Ajout, Modification ou suppression
	if(array_key_exists("a", $_GET) && ctype_digit($_GET["a"]))
		$_SESSION["ajout_univ"]=$_GET["a"]==1 ? 1 : 0;
	elseif(!isset($_SESSION["ajout_univ"]))
		$_SESSION["ajout_univ"]=0;

	if(array_key_exists("s", $_GET) && ctype_digit($_GET["s"]))
		$_SESSION["suppression"]=$_GET["s"]==1 ? 1 : 0;
	elseif(!isset($_SESSION["suppression"]))
		$_SESSION["suppression"]=0;

	if(array_key_exists("m", $_GET) && ctype_digit($_GET["m"]))
		$_SESSION["modification"]=$_GET["m"]==1 ? 1 : 0;
	elseif(!isset($_SESSION["modification"]))
		$_SESSION["modification"]=0;

	$dbr=db_connect();
	
	if(isset($_GET["succes"]) && ctype_digit($_GET["succes"]))
		$succes=$_GET["succes"];

	if((isset($_POST["modifier"]) || isset($_POST["modifier_x"])) && array_key_exists("univ_id", $_POST) && ctype_digit($_POST["univ_id"]))
	{
		$univ_id=$_POST["univ_id"];
		$_SESSION["modification"]=1;
	}

	if((isset($_POST["supprimer"]) || isset($_POST["supprimer_x"])) && array_key_exists("univ_id", $_POST) && ctype_digit($_POST["univ_id"]))
	{
		$univ_id=$_POST["univ_id"];
		$_SESSION["suppression"]=1;
	}

	if(isset($_POST["valider"]) || isset($_POST["valider_x"]))
	{
		$univ_id=isset($_POST["univ_id"]) ? $_POST["univ_id"] : "";
		
		$univ_nom=trim($_POST['nom']);
		$univ_adresse=trim($_POST['adresse']);

		$univ_lettres_couleur_texte=trim($_POST['lettres_couleur_texte']); // A d�placer dans les param�tres d'une composante ?

		$univ_img_dir=trim($_POST['img_dir']);

		if(!is_dir("$__IMG_DIR_ABS/$univ_img_dir")) // si le r�pertoire n'existe pas, on conserve quand m�me la valeur
			$erreur_img_dir=1;

		//	$univ_logo=$_FILES["fichier_logo"]["name"];
		$univ_logo="logo.jpg";
		$univ_logo_size=$_FILES["fichier_logo"]["size"];
		$univ_logo_tmp_name=$_FILES["fichier_logo"]["tmp_name"];

		if(isset($_POST["css_file"]))
			$univ_css_file=trim($_POST['css_file']);
		else
		{
			$univ_css_file="";
			$erreur_css=1;
		}

		// $univ_lettres_couleur_texte="#000000";

		// unicit� de l'universit�
		if(isset($_SESSION["modification"]) && $_SESSION["modification"]==1)
		{
			if(db_num_rows(db_query($dbr,"SELECT $_DBC_universites_id FROM $_DB_universites
													WHERE $_DBC_universites_nom ILIKE '$univ_nom'
													AND $_DBC_universites_id!='$univ_id'")))
				$nom_existe="1";
		}

		// Gestion du logo
		if($univ_logo_tmp_name!="" && !isset($erreur_img_dir))
		{
			$path="$__IMG_DIR_ABS/$univ_img_dir";
			$destination_file="$path/$univ_logo";

			list($image_width, $image_height, $image_type)=getimagesize($univ_logo_tmp_name);

			if($univ_logo_size>200000)
				$file_wrong_size=1;
			elseif($image_type!=IMAGETYPE_JPEG)
				$image_wrong_type=1;
			else
			{
				if(!move_uploaded_file($univ_logo_tmp_name, $destination_file))
					$move_error=1;
			}
		}

		// Champs obligatoires
		if($univ_nom=="") $nom_vide=1;
		if($univ_adresse=="") $adresse_vide=1;

		if(!isset($nom_existe) && !isset($nom_vide) && !isset($adresse_vide) && !isset($erreur_format_couleur_texte) && !isset($image_wrong_type) 
         && !isset($file_wrong_size) && !isset($move_error)) // on peut poursuivre
		{
			if($_SESSION["ajout_univ"]==0)
			{
				db_query($dbr,"UPDATE $_DB_universites SET	$_DBU_universites_nom='$univ_nom',
																			$_DBU_universites_adresse='$univ_adresse',																			
																			$_DBU_universites_img_dir='$univ_img_dir',
																			$_DBU_universites_css='$univ_css_file',
																			$_DBU_universites_couleur_texte_lettres='$univ_lettres_couleur_texte'
										WHERE $_DBU_universites_id='$univ_id'");

				write_evt($dbr, $__EVT_ID_G_ADMIN, "MAJ universit� $univ_id", "", $univ_id);

				// Si l'universit� modifi�e est celle courante, on met � jour les variables de session de l'utilisateur
				if($univ_id==$_SESSION["univ_id"])
				{
					$_SESSION["universite"]=$univ_nom;
					$_SESSION["img_dir"]=$univ_img_dir;
					$_SESSION["css"]=$univ_css_file;
			 	}
			}
			else
			{
				$res_univ=db_query($dbr,"SELECT max($_DBC_universites_id)+1 FROM $_DB_universites");
				
				if(db_num_rows($res_univ))
					list($new_univ_id)=db_fetch_row($res_univ, 0);
				else // Aucune universit�, on initialise l'id � 1
					$new_univ_id=1;
					
				if($new_univ_id=="" || !ctype_digit($new_univ_id))
					$new_univ_id=1;

				db_free_result($res_univ);

				db_query($dbr,"INSERT INTO $_DB_universites VALUES ('$new_univ_id','$univ_nom', '$univ_adresse', '$univ_img_dir', '$univ_css_file', '$univ_lettres_couleur_texte')");

				write_evt($dbr, $__EVT_ID_G_ADMIN, "Nouvelle universit� (id $new_univ_id)", "", $new_univ_id);
			}

			db_close($dbr);

			header("Location:$php_self?succes=1");
			exit;
		}
	}
	elseif(isset($_POST["conf_supprimer"]) || isset($_POST["conf_supprimer_x"]))
	{
		$univ_id=$_POST["univ_id"];

		if(db_num_rows(db_query($dbr,"SELECT * FROM $_DB_universites WHERE $_DBU_universites_id='$univ_id'")))
		{
			db_query($dbr,"DELETE FROM $_DB_universites WHERE $_DBU_universites_id='$univ_id'");
			db_close($dbr);

			header("Location:$php_self?succes=1");
			exit;
		}
		else
			$id_existe_pas=1;
	}

	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>

<div class='main'>
	<?php
		if($_SESSION["ajout_univ"]==1)
			titre_page_icone("Ajouter une universit�", "universite_32x32_fond.png", 30, "L");
		elseif($_SESSION["modification"]==1)
			titre_page_icone("Modifier une universit� existante", "edit_32x32_fond.png", 30, "L");
		elseif($_SESSION["suppression"]==1)
			titre_page_icone("Supprimer une universit�", "trashcan_full_34x34_slick_fond.png", 30, "L");
		else
			titre_page_icone("Gestion des universit�s", "universite_32x32_fond.png", 30, "L");

		if(isset($nom_vide))
			message("Erreur : le champ 'nom' ne doit pas �tre vide", $__ERREUR);

		if(isset($adresse_vide))
			message("Erreur : le champ 'adresse' ne doit pas �tre vide", $__ERREUR);

		if(isset($nom_existe))
			message("Erreur : cette universit� existe d�j� !", $__ERREUR);

		if(isset($file_wrong_size))
			message("Erreur : la taille du logo est limit�e � 200ko", $__ERREUR);

		if(isset($image_wrong_type))
			message("Erreur : le logo doit �tre au format JPEG", $__ERREUR);

		if(isset($move_error))
			message("Erreur lors de la copie du logo : merci de contacter rapidement l'administrateur.", $__ERREUR);

		if(isset($succes))
		{
			if($_SESSION["modification"]==1)
			{
				message("L'universit� a �t� modifi�e avec succ�s.", $__SUCCES);
				$_SESSION["modification"]=0;
			}
			elseif($_SESSION["ajout_univ"]==1)
			{
				message("L'universit� a �t� cr��e avec succ�s.", $__SUCCES);
				$_SESSION["ajout_univ"]=0;
			}
			elseif($_SESSION["suppression"]==1)
			{
				message("L'universit� a �t� supprim�e avec succ�s.", $__SUCCES);
				$_SESSION["suppression"]=0;
			}
		}

		print("<form name=\"form1\" enctype=\"multipart/form-data\" method=\"POST\" action=\"$php_self\">
					<input type='hidden' name='MAX_FILE_SIZE' value='200000'>\n");

		if($_SESSION["ajout_univ"]==0 && $_SESSION["modification"]==0 && $_SESSION["suppression"]==0) // choix de l'universit� � modifier
		{
			$result=db_query($dbr, "SELECT $_DBC_universites_id, $_DBC_universites_nom FROM $_DB_universites
											ORDER BY $_DBC_universites_nom ASC");

			$rows=db_num_rows($result);

			print("<table cellpadding='4' cellspacing='0' border='0' align='center'>
					<tr>
						<td class='td-gauche fond_menu2'>
							<font class='Texte_menu2'><strong>Universit� : </strong></font>
						</td>
						<td class='td-droite fond_menu'>
							<select name='univ_id' size='1'>
								<option value=''></option>\n");

			for($i=0; $i<$rows; $i++)
			{
				list($univ_id, $univ_nom)=db_fetch_row($result,$i);

				$selected=(isset($_SESSION["universite_id"]) && $_SESSION["universite_id"]==$univ_id) ? "selected='1'" : "";

				print("<option value='$univ_id' $selected>" . htmlspecialchars($univ_nom, ENT_QUOTES, $default_htmlspecialchars_encoding) . "</option>\n");
			}

			db_free_result($result);

			print("		</optgroup>
						</select>
						</td>
					</tr>
					</table>

					<div class='centered_icons_box'>
						<a href='index.php' target='_self' class='lien_bleu_12'><img class='icone' src='$__ICON_DIR/back_32x32_fond.png' alt='Retour' border='0'></a>
						<a href='$php_self?a=1' target='_self' class='lien_bleu_12'><img class='icone' src='$__ICON_DIR/add_32x32_fond.png' alt='Ajouter' title='[Ajouter une universit�]' border='0'></a>\n");

			if($rows)
				print("<input type='image' class='icone' src='$__ICON_DIR/edit_32x32_fond.png' alt='Modifier' name='modifier' value='Modifier' title='[Modifier une universit�]'>
						 <input type='image' class='icone' src='$__ICON_DIR/trashcan_full_32x32_slick_fond.png' alt='Supprimer' name='supprimer' value='Supprimer' title='[Supprimer une universit�]'>\n");

			print("</form>
					</div>
					 <script language='javascript'>
						document.form1.univ_id.focus()
					 </script>\n");
		}
		elseif($_SESSION["suppression"]==1)
		{
			print("<input type='hidden' name='univ_id' value='$univ_id'>");

			$result=db_query($dbr,"SELECT $_DBC_universites_nom FROM $_DB_universites
											WHERE $_DBC_universites_id='$univ_id'");

			list($univ_nom)=db_fetch_row($result,0);

			db_free_result($result);

			// TODO : actuellement, l'avertissement suivant est vrai. Faut-il pr�f�rer l'orphelinat pour ces �l�ments ?
			message("<center>
							La suppression entrainera automatiquement l'effacement de toutes les composantes, formations et utilisateurs rattach�s � cette universit�.
							<br>ATTENTION, CECI EST LA DERNIERE CONFIRMATION !
						</center>", $__WARNING);

			message("Souhaitez vous vraiment supprimer l'universit� \"$univ_nom\" ?", $__QUESTION);

			print("<div class='centered_icons_box'>
						<a href='$php_self?s=0' target='_self' class='lien_bleu_12'><img class='icone' src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Annuler' border='0'></a>
						<input type='image' class='icone' src='$__ICON_DIR/trashcan_full_34x34_slick_fond.png' alt='Supprimer' title='[Confirmer la suppression]' name='conf_supprimer' value='supprimer'>
						</form>
					 </div>\n");
		}
		elseif((isset($univ_id) && $_SESSION["modification"]==1) || $_SESSION["ajout_univ"]==1) // universit� choisie, on r�cup�re les infos actuelles
		{
			if($_SESSION["ajout_univ"]==1)
			{
				if(!isset($univ_nom)) // un seul test devrait �tre suffisant
				{
					$univ_nom=$univ_adresse=$univ_img_dir=$univ_css_file=$univ_lettres_couleur_texte="";
				}
			}
			else
			{
				$result=db_query($dbr,"SELECT $_DBC_universites_nom, $_DBC_universites_adresse, $_DBC_universites_img_dir, $_DBC_universites_css,
														$_DBC_universites_couleur_texte_lettres
												FROM $_DB_universites
											WHERE $_DBC_universites_id='$univ_id'");

				list($current_univ_nom,$current_univ_adresse,$current_univ_img_dir,$current_univ_css_file, $current_univ_lettres_couleur_texte)=db_fetch_row($result,0);

				db_free_result($result);
			}

			if(isset($univ_id))
				print("<input type='hidden' name='univ_id' value='$univ_id'>\n");
	?>

	<table align='center'>
	<tr>
		<td colspan='2' class='td-complet fond_menu2'>
			<font class='Texte_menu2'>
				<b>&#8226;&nbsp;&nbsp;Informations</b>
			</font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Nom de l'universit� :</b></font>
		</td>
		<td class='td-droite fond_menu'>
			<input type='text' name='nom' value='<?php if(isset($univ_nom)) echo htmlspecialchars(stripslashes($univ_nom), ENT_QUOTES, $default_htmlspecialchars_encoding); elseif(isset($current_univ_nom)) echo htmlspecialchars(stripslashes($current_univ_nom), ENT_QUOTES, $default_htmlspecialchars_encoding);?>' maxlength='92' size='60'>
		</td>
	</tr>

	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Adresse postale :</b></font>
		</td>
		<td class='td-droite fond_menu'>
			<textarea name='adresse' rows='4' cols='60'><?php
				if(isset($univ_adresse)) echo htmlspecialchars(stripslashes($univ_adresse), ENT_QUOTES, $default_htmlspecialchars_encoding);
				elseif(isset($current_univ_adresse)) echo htmlspecialchars(stripslashes($current_univ_adresse), ENT_QUOTES, $default_htmlspecialchars_encoding);
			?></textarea>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Sous-r�pertoire contenant images et ic�nes : </b></font>
		</td>
		<td class='td-droite fond_menu'>
			<input type='text' name='img_dir' value='<?php if(isset($univ_img_dir)) echo htmlspecialchars(stripslashes($univ_img_dir), ENT_QUOTES, $default_htmlspecialchars_encoding); elseif(isset($current_univ_img_dir)) echo htmlspecialchars(stripslashes($current_univ_img_dir), ENT_QUOTES, $default_htmlspecialchars_encoding);?>' maxlength='256' size='60'>
			&nbsp;<font class='Texte_menu'><i>(Chemin relatif au r�pertoire "<?php echo "$__IMG_DIR/"; ?>")</i>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Logo</b></font>
		</td>
		<td class='td-droite fond_menu' colspan='2'>
			<font class='Texte_menu'>
				<input type='file' name='fichier_logo'>&nbsp;&nbsp;<i>Format impos� : <b>jpeg</b>. Dimensions recommand�es : L:260px H:120px. Max 200ko.</i>
				<?php
					if(!isset($current_univ_img_dir))
						$current_univ_img_dir=$univ_img_dir;

					if(is_file("$__IMG_DIR_ABS/$current_univ_img_dir/logo.jpg"))
						print("<br>Fichier actuel : \"<strong>" . preg_replace("/[\/]+/","/", "$__IMG_DIR/$current_univ_img_dir/logo.jpg") . "</strong>\"
								 <br>Attention : cet emplacement d�pend du r�pertoire indiqu� pr�c�demment.");
					else
						print("<br>Aucun fichier <i><strong>logo.jpg</strong></i> dans le r�pertoire \"$__IMG_DIR_ABS/$current_univ_img_dir\"");
				?>
			</font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Fichier CSS : </b></font>
		</td>
		<td class='td-droite fond_menu'>
			<?php
				if(isset($univ_css_file))
					$form_univ_css_file=$univ_css_file;
				elseif(isset($current_univ_css_file))
					$form_univ_css_file=$current_univ_css_file;
				else
					$form_univ_css_file="";

				if(is_dir($__STATIC_DIR_ABS))
				{
					print("<select name='css_file' size='1'>");
					$contenu_dir_css=scandir($__STATIC_DIR_ABS);

					foreach($contenu_dir_css as $dir_css_file)
					{
						if($dir_css_file!="." && $dir_css_file!=".." && substr($dir_css_file, -3)=="css")
						{
							$selected=($form_univ_css_file==$dir_css_file) ? "selected='1'" : "";

							print("<option value='$dir_css_file' $selected>$dir_css_file</option>\n");
						}
					}

					print("</select>\n");
				}
				else
					print("<font class='Texte_important_menu'><b>Erreur : le r�pertoire $__STATIC_DIR_ABS n'existe pas !</b></font>\n");
			?>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Couleur du texte du logo dans les lettres : </b></font>
		</td>
		<td class='td-droite fond_menu'>
			<input type='text' name='lettres_couleur_texte' value='<?php if(isset($current_univ_lettres_couleur_texte)) echo htmlspecialchars(stripslashes($current_univ_lettres_couleur_texte), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' maxlength='7' size='60'>
		</td>
	</tr>
	</table>

	<div class='centered_icons_box'>
		<?php
			if(isset($succes))
				print("<a href='index.php' target='_self'><img class='icone' src='$__ICON_DIR/back_32x32_fond.png' alt='Retour' border='0'></a>");
			else
				print("<a href='$php_self?m=0&a=0' target='_self'><img class='icone' src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Annuler' border='0'></a>");
		?>
		<input type='image' class='icone' src='<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>' alt='Valider' title='[Confirmer la cr�ation]' name='valider' value='Valider'>
		</form>
	</div>

	<script language="javascript">
		document.form1.nom.focus()
	</script>

	<?php
		}
		db_close($dbr);
	?>
</div>
<?php
	pied_de_page();
?>

</body></html>
