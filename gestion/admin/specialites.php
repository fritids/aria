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
	// Ajout - Modification - Suppression des sp�cialit�s

	session_name("preinsc_gestion");
	session_start();

	include "../../configuration/aria_config.php";
	include "$__INCLUDE_DIR_ABS/vars.php";
	include "$__INCLUDE_DIR_ABS/fonctions.php";
	include "$__INCLUDE_DIR_ABS/db.php";


	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	verif_auth("$__GESTION_DIR/login.php");
	
	if(!in_array($_SESSION['niveau'], array("$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
	{
		header("Location:$__MOD_DIR/gestion/noaccess.php");
		exit();
	}

	// Ajout, Modification ou suppression
	if(array_key_exists("a", $_GET) && ctype_digit($_GET["a"]))
		$_SESSION["ajout_spec"]=$_GET["a"]==1 ? 1 : 0;
	elseif(!isset($_SESSION["ajout_spec"]))
		$_SESSION["ajout_spec"]=0;

	if(array_key_exists("s", $_GET) && ctype_digit($_GET["s"]))
		$_SESSION["suppression"]=$_GET["s"]==1 ? 1 : 0;
	elseif(!isset($_SESSION["suppression"]))
		$_SESSION["suppression"]=0;

	if(array_key_exists("m", $_GET) && ctype_digit($_GET["m"]))
		$_SESSION["modification"]=$_GET["m"]==1 ? 1 : 0;
	elseif(!isset($_SESSION["modification"]))
		$_SESSION["modification"]=0;

	if(isset($_GET["succes"]))
		$succes=$_GET["succes"];

	$dbr=db_connect();

	if((isset($_POST["modifier"]) || isset($_POST["modifier_x"])) && array_key_exists("spec_id", $_POST) && ctype_digit($_POST["spec_id"]))
	{
		$spec_id=$_POST["spec_id"];
		$_SESSION["modification"]=1;
	}

	if((isset($_POST["supprimer"]) || isset($_POST["supprimer_x"])) && array_key_exists("spec_id", $_POST) && ctype_digit($_POST["spec_id"]))
	{
		$spec_id=$_POST["spec_id"];
		$_SESSION["suppression"]=1;
	}

	if(isset($_POST["conf_supprimer"]) || isset($_POST["conf_supprimer_x"]))
	{
		$spec_id=$_POST["spec_id"];

		if(db_num_rows(db_query($dbr, "SELECT * FROM $_DB_specs WHERE $_DBC_specs_id='$spec_id'"))==1)
		{
			db_query($dbr,"DELETE FROM $_DB_specs WHERE $_DBC_specs_id='$spec_id'");

			write_evt($dbr, $__EVT_ID_G_ADMIN, "SUPPR Sp�cialit� $spec_id", "", $spec_id);

			header("Location:$php_self?succes=1");
		}
		else
			header("Location:$php_self?erreur_suppr=1");

		db_close($dbr);

		exit();
	}
	elseif(isset($_POST["valider"]) || isset($_POST["valider_x"]))
	{
		if(isset($_POST["spec_id"]))
			$spec_id=$_POST["spec_id"];

		$new_nom=ucfirst(trim($_POST["nom"]));
		$new_nom_court=ucfirst(trim($_POST["nom_court"]));
		$new_mention=$_POST["mention"];

		// Champs vides
		if($new_nom=="" || $new_nom_court=="" || $new_mention=="")
			$champs_vides=1;

		// r�cup�ration des valeurs courantes, en cas de modification
		if($_SESSION["ajout_spec"]==0 && isset($spec_id))
		{
			$result=db_query($dbr,"SELECT $_DBC_specs_nom, $_DBC_specs_nom_court, $_DBC_specs_mention_id
												FROM $_DB_specs
											WHERE $_DBC_specs_id='$spec_id'");

			$rows=db_num_rows($result);

			if(!$rows)
			{
				$_SESSION["modification"]=1;
				$spec_id_existe_pas=1;
			}
			else
			{
				list($current_nom,$current_nom_court,$current_mention)=db_fetch_row($result,0);
				db_free_result($result);

				if($current_nom!=$new_nom || $current_mention!=$new_mention)
				{
					if(db_num_rows(db_query($dbr, "SELECT * FROM $_DB_specs
																WHERE $_DBC_specs_nom ILIKE '$new_nom'
															 AND $_DBC_specs_mention_id='$new_mention'
															 AND $_DBC_specs_id!='$spec_id'")))
						$specialite_existe=1;
				}
			}
		}
		// En cas d'ajout : v�rification d'unicit� nom/mention
		// Attention : deux sp�cialit�s peuvent avoir le m�me nom dans deux mentions diff�rentes
		elseif(db_num_rows(db_query($dbr,"SELECT * FROM $_DB_specs
														WHERE $_DBC_specs_nom ILIKE '$new_nom'
													 AND $_DBC_specs_mention_id='$new_mention'")))
				$specialite_existe=1;

		if(!isset($champs_vides) && !isset($specialite_existe))
		{
			if((!isset($_SESSION["ajout_spec"]) || $_SESSION["ajout_spec"]==0) && isset($spec_id))
			{
				db_query($dbr,"UPDATE $_DB_specs SET 	$_DBU_specs_nom='$new_nom',
																	$_DBU_specs_nom_court='$new_nom_court',
																	$_DBU_specs_comp_id='$_SESSION[comp_id]',
																	$_DBU_specs_mention_id='$new_mention'
									WHERE $_DBU_specs_id='$spec_id'");

				write_evt($dbr, $__EVT_ID_G_ADMIN, "MAJ Sp�cialit� $spec_id", "", $spec_id);
			}
			else
			{
				$new_spec_id=db_locked_query($dbr, $_DB_specs, "INSERT INTO $_DB_specs VALUES('##NEW_ID##', '$new_nom', '$new_nom_court', '$_SESSION[comp_id]', '$new_mention')");

				write_evt($dbr, $__EVT_ID_G_ADMIN, "AJOUT Sp�cialit� $new_spec_id ($new_nom)", "", $new_spec_id);
			}

			db_close($dbr);
			header("Location:$php_self?succes=1");

			exit;
		}
	}
	
	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>

<div class='main'>
	<?php
		if($_SESSION["ajout_spec"]==1)
			titre_page_icone("Ajouter une sp�cialit�", "add_32x32_fond.png", 30, "L");
		elseif(isset($_SESSION["action"]) && $_SESSION["action"]=="modification")
			titre_page_icone("Modifier une sp�cialit� existante", "edit_32x32_fond.png", 30, "L");
		elseif(isset($_SESSION["action"]) && $_SESSION["action"]=="suppression")
			titre_page_icone("Supprimer une sp�cialit�", "trashcan_full_34x34_slick_fond.png", 30, "L");
		else
			titre_page_icone("Gestion des sp�cialit�s", "", 30, "L");

		// Messages d'erreur et de succ�s

		if(isset($specialite_existe))
			message("Erreur : cette sp�cialit� existe d�j� dans cette mention.", $__ERREUR);

		if(isset($spec_id_existe_pas) || isset($_GET["erreur_suppr"]))
			message("Erreur : l'identifiant demand� est incorrect (probl�me de coh�rence de la base ?)", $__ERREUR);

		if(isset($champs_vides))
			message("Erreur : les champs en <strong>gras</strong> sont <strong>obligatoires</strong>.", $__ERREUR);


		if(isset($succes) && $succes==1)
		{
			if($_SESSION["modification"]==1)
			{
				message("La sp�cialit� a �t� modifi�e avec succ�s.", $__SUCCES);
				unset($_SESSION["modification"]);
			}
			elseif($_SESSION["ajout_spec"]==1)
			{
				message("La sp�cialit� a �t� cr��e avec succ�s.", $__SUCCES);
				unset($_SESSION["ajout_spec"]);
			}
			elseif($_SESSION["suppression"]==1)
			{
				message("La sp�cialit� a �t� supprim�e avec succ�s.", $__SUCCES);
				unset($_SESSION["suppression"]);
			}
		}

		print("<form action='$php_self' method='POST' name='form1'>\n");

		if((!isset($_SESSION["ajout_spec"]) || $_SESSION["ajout_spec"]==0) && (!isset($_SESSION["modification"]) || $_SESSION["modification"]==0) && (!isset($_SESSION["suppression"]) || $_SESSION["suppression"]==0))
		{
			  // Choix de la spec � modifier
			$result=db_query($dbr,"SELECT $_DBC_specs_id, $_DBC_specs_nom, $_DBC_specs_nom_court, $_DBC_specs_mention_id,
													$_DBC_mentions_nom
												FROM $_DB_specs, $_DB_mentions
											WHERE $_DBC_mentions_id=$_DBC_specs_mention_id
											AND $_DBC_specs_comp_id='$_SESSION[comp_id]'
												ORDER BY $_DBC_mentions_nom, $_DBC_specs_nom ASC");

			$rows=db_num_rows($result);

			if($rows)
			{
				print("<table cellpadding='4' cellspacing='0' align='center'>
						<tr>
							<td class='fond_menu2' align='right'>
								<font class='Texte_menu2' style='font-weight:bold;'>Sp�cialit� : </font>
							</td>
							<td class='fond_menu' align='left'>
								<select name='spec_id' size='1'>
									<option value=''></option>\n");

				$old_mention="";

				for($i=0; $i<$rows; $i++)
				{
					list($spec_id,$spec_nom,$spec_nom_court, $spec_mention, $mention_nom)=db_fetch_row($result,$i);

					if($spec_mention!=$old_mention)
					{
						if($i)
							print("</optgroup>
									 <option value='' label='' disabled></option>\n");

						$val=htmlspecialchars($mention_nom, ENT_QUOTES);

						print("<optgroup label='$val'>\n");

						$old_mention=$spec_mention;
					}

					print("<option value='$spec_id' label=\"$spec_nom\">$spec_nom</option>\n");
				}

				print("		</optgroup>
							</select>
							</td>
						</tr>
						</table>\n");
			}
			else
			{
				$no_elements=1;

            $message="Il n'y a actuellement aucune sp�cialit� enregistr�e dans cette composante.";

            // V�rification du nombre de mentions disponibles pour la cr�ation
            if(!db_num_rows(db_query($dbr, "SELECT * FROM $_DB_mentions WHERE $_DBC_mentions_comp_id='$_SESSION[comp_id]'")))
            {
               $no_mentions=1;
               $message.="<br>De plus, vous devez dans un premier temps cr�er une ou plusieurs mentions avant de pouvoir cr�er une sp�cialit�.";
            }

            message($message, $__INFO);
			}

			print("<div class='centered_icons_box'>
						<a href='index.php' target='_self' class='lien_bleu_12'><img class='icone' src='$__ICON_DIR/back_32x32_fond.png' alt='Retour' border='0'></a>\n");

         if(!isset($no_mentions))
            print("<a href='$php_self?a=1' target='_self' class='lien_bleu_12'><img class='icone' src='$__ICON_DIR/add_32x32_fond.png' alt='Ajouter' title='[Ajouter une sp�cialit�]' border='0'></a>\n");

			if(!isset($no_elements))
				print("<input type='image' class='icone' src='$__ICON_DIR/edit_32x32_fond.png' alt='Modifier' name='modifier' value='Modifier' title='[Modifier une sp�cialit�]'>
						 <input type='image' class='icone' src='$__ICON_DIR/trashcan_full_32x32_slick_fond.png' alt='Supprimer' name='supprimer' value='Supprimer' title='[Supprimer une sp�cialit�]'>\n");

			print("</div>
					<script language='javascript'>
						document.form1.spec_id.focus()
					</script>\n");
		}
		elseif(isset($_SESSION["suppression"]) && $_SESSION["suppression"]==1)
		{
			print("<form action='$php_self' method='POST' name='form1'>
						<input type='hidden' name='spec_id' value='$spec_id'>");

			$result=db_query($dbr, "SELECT $_DBC_specs_nom FROM $_DB_specs WHERE $_DBC_specs_id='$spec_id'");

			list($spec_nom)=db_fetch_row($result,0);

			db_free_result($result);

			// Liste des sp�cialit�s utilis�es dans des candidatures d�j� d�pos�es

			$result_utilisees=db_query($dbr, "SELECT count(*), $_DBC_cand_periode FROM $_DB_cand, $_DB_propspec
															WHERE $_DBC_propspec_id=$_DBC_cand_propspec_id
															AND $_DBC_propspec_id_spec='$spec_id'
														 GROUP BY $_DBC_cand_periode
														 ORDER BY $_DBC_cand_periode");

			$res_utilisees=db_num_rows($result_utilisees);

			if($res_utilisees)
			{
				$txt_utilisees="";

				for($i=0; $i<$res_utilisees; $i++)
				{
					list($cnt_utilisees, $cnt_periode)=db_fetch_row($result_utilisees, $i);

					$txt_utilisees.="<strong>$cnt_periode - ".($cnt_periode+1)."</strong> : $cnt_utilisees candidatures<br>";
				}
			}

			db_free_result($result_utilisees);

			$texte_complet=isset($txt_utilisees) ? "<br><br><strong>Utilisation actuelle</strong> : <br>$txt_utilisees<br>Pour les ann�es universitaires ant�rieures, l'historique et les statistiques seront perdus." : "<br><br><strong>Note</strong> : cette sp�cialit� n'est utilis�e par aucune candidatures (suppression sans danger).";

			message("<strong>Attention</strong> : toutes les formations et candidatures li�es � cette sp�cialit� seront <strong>supprim�es</strong>.
						$texte_complet
						<center>
							<br><br>Souhaitez vous vraiment supprimer la sp�cialit� \"$spec_nom\" ?
						</center>", $__QUESTION);

			print("<div class='centered_icons_box'>
						<a href='$php_self?s=0' target='_self' class='lien_bleu_12'><img class='icone' src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Annuler' title='[Annuler la suppression]' border='0'></a>
						<input type='image' class='icone' src='$__ICON_DIR/trashcan_full_34x34_slick_fond.png' alt='Supprimer' title='[Confirmer la suppression]' name='conf_supprimer' value='Supprimer'>
					 </div>\n");
		}
		elseif((isset($spec_id) && isset($_SESSION["modification"]) && $_SESSION["modification"]==1) || (isset($_SESSION["ajout_spec"]) && $_SESSION["ajout_spec"]==1))
		{
			// ajout ou modification (on r�cup�re les infos actuelles)
			if($_SESSION["ajout_spec"]==1)
			{
				if(!isset($current_nom)) // un seul test devrait suffire ...
					$new_nom=$new_nom_court=$new_mention="";
			}
			else
			{
				$result=db_query($dbr,"SELECT $_DBC_specs_nom, $_DBC_specs_nom_court, $_DBC_specs_mention_id FROM $_DB_specs
											  WHERE $_DBC_specs_id='$spec_id'");

				list($new_nom,$new_nom_court,$new_mention)=db_fetch_row($result,0);

				db_free_result($result);
			}

			print("<form action='$php_self' method='POST' name='form1'>\n");

			if(isset($spec_id))
				print("<input type='hidden' name='spec_id' value='$spec_id'>\n");
	?>

	<table align='center'>
	<tr>
		<td class='fond_menu2' colspan='2' style='padding:4px 20px 4px 20px;'>
			<font class='Texte_menu2'>
				<b>&#8226;&nbsp;&nbsp;Informations</b>
			</font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'><font class='Texte_menu2'><b>Nom de la sp�cialit� : </b></font></td>
		<td class='td-droite fond_menu'><input type='text' name='nom' value='<?php if(isset($new_nom)) echo htmlspecialchars($new_nom, ENT_QUOTES); ?>' maxlength='192' size='80'></td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'><font class='Texte_menu2'><b>Nom court : </b></font></td>
		<td class='td-droite fond_menu'><input type='text' name='nom_court' value='<?php if(isset($new_nom_court)) echo htmlspecialchars($new_nom_court, ENT_QUOTES); ?>' maxlength='92' size='80'></td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'><font class='Texte_menu2'><b>Mention</b></font></td>
		<td class='td-droite fond_menu'>
			<select name='mention'>
				<?php
					$result2=db_query($dbr, "SELECT $_DBC_mentions_id, $_DBC_mentions_nom
														FROM $_DB_mentions
													 WHERE $_DBC_mentions_comp_id='$_SESSION[comp_id]'
														ORDER BY $_DBC_mentions_nom ASC");

					$rows2=db_num_rows($result2);

					for($i=0; $i<$rows2; $i++)
					{
						list($mention_id, $mention_nom)=db_fetch_row($result2,$i);

						$selected=(isset($new_mention) && $new_mention==$mention_id) ? "selected" : "";

						print("<option value='$mention_id' $selected>$mention_nom</option>\n");
					}

					db_free_result($result2);
				?>
			</select>
		</td>
	</tr>
	</table>

	<script language='javascript'>
		document.form1.nom.focus()
	</script>

	<div class='centered_icons_box'>
		<a href='<?php echo "$php_self?m=0&a=0"; ?>' target='_self' class='lien_bleu_12'><img class='icone' src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Annuler' border='0'></a>
		<input type="image" class='icone' src="<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>" alt="Valider" name="valider" value="Valider">
		</form>
	</div>

	<?php
		}
		db_close($dbr);
	?>

</div>
<?php
	pied_de_page();
?>
</body></html>

