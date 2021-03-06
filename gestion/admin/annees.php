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
		$_SESSION["ajout_annee"]=$_GET["a"]==1 ? 1 : 0;
	elseif(!isset($_SESSION["ajout_annee"]))
		$_SESSION["ajout_annee"]=0;

	if(array_key_exists("s", $_GET) && ctype_digit($_GET["s"]) && array_key_exists("id", $_GET) && ctype_digit($_GET["id"]))
	{
		$_SESSION["suppression"]=$_GET["s"]==1 ? 1 : 0;
		$annee_id=$_GET["id"];
	}
	elseif(!isset($_SESSION["suppression"]))
		$_SESSION["suppression"]=0;

	if(array_key_exists("m", $_GET) && ctype_digit($_GET["m"]) && array_key_exists("id", $_GET) && ctype_digit($_GET["id"]))
	{
		$_SESSION["modification"]=$_GET["m"]==1 ? 1 : 0;
		$annee_id=$_GET["id"];
	}
	elseif(!isset($_SESSION["modification"]))
		$_SESSION["modification"]=0;

	$dbr=db_connect();
	
	if(isset($_GET["succes"]) && ctype_digit($_GET["succes"]))
		$succes=$_GET["succes"];

	// Changement de l'ordre
	if(isset($_GET["id"]) && ctype_digit($_GET["id"]) && ((isset($_GET["up"]) && $_GET["up"]==1) || (isset($_GET["down"]) && $_GET["down"]==1)))
	{
		$annee_id=$_GET["id"];

		if(db_num_rows(db_query($dbr,"SELECT * FROM $_DB_annees WHERE $_DBC_annees_id='$annee_id'")))
		{
			if(isset($_GET["up"]))
			{
				db_query($dbr, "UPDATE $_DB_annees SET $_DBU_annees_ordre=$_DBU_annees_ordre-1 WHERE $_DBU_annees_id='$annee_id';
									 UPDATE $_DB_annees SET $_DBU_annees_ordre=$_DBU_annees_ordre+1
										WHERE $_DBU_annees_ordre=(SELECT $_DBC_annees_ordre FROM $_DB_annees
																			WHERE $_DBC_annees_id='$annee_id')
										AND $_DBU_annees_id!='$annee_id'");
			}
			elseif(isset($_GET["down"]))
			{
				db_query($dbr, "UPDATE $_DB_annees SET $_DBU_annees_ordre=$_DBU_annees_ordre+1 WHERE $_DBU_annees_id='$annee_id';
									 UPDATE $_DB_annees SET $_DBU_annees_ordre=$_DBU_annees_ordre-1
										WHERE $_DBU_annees_ordre=(SELECT $_DBC_annees_ordre FROM $_DB_annees
																			WHERE $_DBC_annees_id='$annee_id')
										AND $_DBU_annees_id!='$annee_id'");
			}
		}
	}

	if((isset($_POST["modifier"]) || isset($_POST["modifier_x"])) && array_key_exists("annee_id", $_POST) && ctype_digit($_POST["annee_id"]))
	{
		$annee_id=$_POST["annee_id"];
		$_SESSION["modification"]=1;
	}

	if((isset($_POST["supprimer"]) || isset($_POST["supprimer_x"])) && array_key_exists("annee_id", $_POST) && ctype_digit($_POST["annee_id"]))
	{
		$annee_id=$_POST["annee_id"];
		$_SESSION["suppression"]=1;
	}

	if(isset($_POST["valider"]) || isset($_POST["valider_x"]))
	{
		$new_annee_nom=trim($_POST['nom']);
		$new_annee_nom_complet=trim($_POST['nom_complet']);

		if($new_annee_nom_complet=="")
			$nom_complet_vide=1;

		// D�termination de l'ID de l'ann�e
		if(isset($_POST["annee_id"]))
			$annee_id=$_POST["annee_id"];

		// unicit� de l'ann�e
		if(isset($annee_id) && ctype_digit($annee_id))
		{
			if(db_num_rows(db_query($dbr,"SELECT $_DBC_annees_id FROM $_DB_annees
													WHERE $_DBC_annees_annee ILIKE '$new_annee_nom'
													AND $_DBC_annees_id!='$annee_id'")))
				$nom_existe="1";
		}

		if(!isset($nom_existe) && !isset($nom_complet_vide)) // on peut poursuivre
		{
			if($_SESSION["ajout_annee"]==0)
			{
				db_query($dbr,"UPDATE $_DB_annees SET	$_DBU_annees_annee='$new_annee_nom',
																	$_DBU_annees_annee_longue='$new_annee_nom_complet'
									WHERE $_DBU_annees_id='$annee_id'");

				write_evt($dbr, $__EVT_ID_G_ADMIN, "MAJ Ann�e $annee_id", "", $annee_id);
			}
			else
			{
				$res_ordre=db_query($dbr,"SELECT max($_DBC_annees_ordre)+1 FROM $_DB_annees");

				list($new_annee_ordre)=db_fetch_row($res_ordre, 0);

				if($new_annee_ordre=="")
					$new_annee_id=$new_annee_ordre=0;
				else
				{
					$res_id=db_query($dbr,"SELECT max($_DBC_annees_id)+1 FROM $_DB_annees");

					list($new_annee_id)=db_fetch_row($res_id, 0);

					db_free_result($res_id);
				}

				db_free_result($res_ordre);
				

				db_query($dbr, "INSERT INTO $_DB_annees VALUES ('$new_annee_id','$new_annee_nom', '$new_annee_nom_complet', '$new_annee_ordre')");

				write_evt($dbr, $__EVT_ID_G_ADMIN, "Nouvelle ann�e $new_annee_id", "", $new_annee_id);
			}

			db_close($dbr);

			header("Location:$php_self?succes=1");
			exit;
		}
	}
	elseif(isset($_POST["conf_supprimer"]) || isset($_POST["conf_supprimer_x"]))
	{
		$annee_id=$_POST["annee_id"];

		if(db_num_rows(db_query($dbr,"SELECT * FROM $_DB_annees WHERE $_DBC_annees_id='$annee_id'")))
		{
			db_query($dbr,"UPDATE $_DB_annees SET $_DBU_annees_ordre=$_DBU_annees_ordre-1
									WHERE $_DBU_annees_ordre>(SELECT $_DBC_annees_ordre FROM $_DB_annees
																	  WHERE $_DBC_annees_id='$annee_id');
								DELETE FROM $_DB_annees WHERE $_DBC_annees_id='$annee_id';");

			write_evt($dbr, $__EVT_ID_G_ADMIN, "Suppression ann�e $annee_id", "", $annee_id);

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
		if($_SESSION["ajout_annee"]==1)
			titre_page_icone("Ajouter une ann�e", "add_32x32_fond.png", 15, "L");
		elseif($_SESSION["modification"]==1)
			titre_page_icone("Modifier une ann�e existante", "edit_32x32_fond.png", 15, "L");
		elseif($_SESSION["suppression"]==1)
			titre_page_icone("Supprimer une ann�e", "trashcan_full_32x32_slick_fond.png", 15, "L");
		else
			titre_page_icone("Gestion des ann�es", "", 15, "L");

		if(isset($nom_complet_vide))
			message("Erreur : le champ 'nom complet' ne doit pas �tre vide", $__ERREUR);

		if(isset($nom_existe))
			message("Erreur : cette ann�e existe d�j� !", $__ERREUR);

		if(isset($succes))
		{
			if($_SESSION["modification"]==1)
			{
				message("L'ann�e a �t� modifi�e avec succ�s.", $__SUCCES);
				$_SESSION["modification"]=0;
			}
			elseif($_SESSION["ajout_annee"]==1)
			{
				message("L'ann�e a �t� cr��e avec succ�s.", $__SUCCES);
				$_SESSION["ajout_annee"]=0;
			}
			elseif($_SESSION["suppression"]==1)
			{
				message("L'ann�e a �t� supprim�e avec succ�s.", $__SUCCES);
				$_SESSION["suppression"]=0;
			}
		}

		message("<center>
						Attention : cette liste est <strong>partag�e par toutes les composantes</strong>.
						<br>Toute modification affectera <strong>toutes les formations et toutes les candidatures associ�es</strong>.
					</center>", $__WARNING);

		print("<form action='$php_self' method='POST' name='form1'>\n");

		if($_SESSION["ajout_annee"]==0 && $_SESSION["modification"]==0 && $_SESSION["suppression"]==0) // choix de l'ann�e � modifier
		{
			$result=db_query($dbr, "SELECT $_DBC_annees_id, $_DBC_annees_annee, $_DBC_annees_annee_longue, $_DBC_annees_ordre
													FROM $_DB_annees
												ORDER BY $_DBC_annees_ordre");

			$rows=db_num_rows($result);

			print("<table cellpadding='4' cellspacing='0' align='center'>
					<tr>
						<td class='td-complet fond_menu2' colspan='4'>
							<font class='Texte_menu2'><strong>Ann�es</font>
						</td>
					</tr>\n");

			for($i=0; $i<$rows; $i++)
			{
				list($annee_id, $annee_nom, $annee_nom_complet, $annee_ordre)=db_fetch_row($result,$i);

				// le nom court peut �tre vide
				$annee_txt=$annee_nom=="" ? $annee_nom_complet : "$annee_nom_complet ($annee_nom)";

				// v�rification de l'ordre et rectification si n�cessaire
				if($annee_ordre!=$i)
				{
					db_query($dbr,"UPDATE $_DB_annees SET $_DBU_annees_ordre='$i' WHERE $_DBC_annees_id='$annee_id'");
					$annee_ordre=$i;
				}

				print("<tr>
							<td class='fond_menu' width='16'>\n");

				if($i>0)
					print("<a href='$php_self?id=$annee_id&up=1' target='_self' class='lien2' style='vertical-align:middle'><img style='vertical-align:middle' src='$__ICON_DIR/up_16x16_menu.png' alt='Monter' border='0'></a> \n");

				print("</td>
						 <td class='fond_menu' width='16'>\n");

				if($i!=($rows-1))
					print("<a href='$php_self?id=$annee_id&down=1' target='_self' class='lien2' style='vertical-align:middle'><img style='vertical-align:middle' src='$__ICON_DIR/down_16x16_menu.png' alt='Descendre' border='0'></a>\n");

				print("</td>
						<td class='td-droite fond_menu'>
							<a href='$php_self?id=$annee_id&m=1' target='_self' class='lien2' style='vertical-align:middle'>$annee_txt</a>
						</td>
						<td class='td-droite fond_menu'>
							<a href='$php_self?id=$annee_id&s=1' target='_self' class='lien2' style='vertical-align:middle'><img style='vertical-align:middle' src='$__ICON_DIR/cancel_16x16_menu.png' alt='Supprimer' border='0'></a>
						</td>
					</tr>\n");
			}

			db_free_result($result);

			print("</table>

					<div class='centered_icons_box'>
						<a href='index.php' target='_self' class='lien_bleu_12'><img class='icone' src='$__ICON_DIR/back_32x32_fond.png' alt='Retour' border='0'></a>
						<a href='$php_self?a=1' target='_self' class='lien_bleu_12'><img class='icone' src='$__ICON_DIR/add_32x32_fond.png' alt='Ajouter' title='[Ajouter une ann�e]' border='0'></a>
					</div>\n");
		}
		elseif($_SESSION["suppression"]==1)
		{
			// TODO : ajouter des m�canismes de protection pour :
			// - ne pas supprimer la composante lorsqu'il s'agit de la composante courante
			// - ne pas supprimer la derni�re composante de la base (?)

			print("<input type='hidden' name='annee_id' value='$annee_id'>");

			$result=db_query($dbr,"SELECT $_DBC_annees_annee, $_DBC_annees_annee_longue FROM $_DB_annees
											WHERE $_DBC_annees_id='$annee_id'");

			list($annee_nom, $annee_nom_complet)=db_fetch_row($result,0);

			// le nom court peut �tre vide
			$annee_txt=$annee_nom=="" ? $annee_nom_complet : "$annee_nom_complet ($annee_nom)";

			db_free_result($result);

			$txt_avertissement="";

			$result=db_query($dbr,"SELECT count(*) FROM $_DB_propspec WHERE $_DBC_propspec_annee='$annee_id'");

			list($nb_formations)=db_fetch_row($result, 0);

			db_free_result($result);

			$result=db_query($dbr,"SELECT count(*) FROM $_DB_cand
											WHERE $_DBC_cand_propspec_id IN (SELECT $_DBC_propspec_id FROM $_DB_propspec
																						WHERE $_DBC_propspec_annee='$annee_id')");

			list($nb_candidatures)=db_fetch_row($result, 0);

			if($nb_formations!="0" && $nb_candidatures!="0")
				$txt_avertissement="<br><strong>Il y a actuellement $nb_formations formations et $nb_candidatures candidatures utilisant cette 'ann�e'.</strong><br>";
			elseif($nb_formations!="0")
				$txt_avertissement="<br><strong>Il y a actuellement $nb_formations formations (mais aucune candidature) utilisant cette 'ann�e'.</strong><br>";
			else
				$txt_avertissement="<br><strong>Aucune formation (ni aucune candidature) n'utilise actuellement cette 'ann�e'.</strong><br>";

			// le cas $nb_formations=="" et $nb_candidatures!="" n'est pas possible

			db_free_result($result);

			// TODO : actuellement, l'avertissement suivant est vrai. Faut-il pr�f�rer l'orphelinat pour ces �l�ments ?
			message("<center>
							La suppression entrainera automatiquement celle de toutes les formations li�es � cette ann�e, ainsi que les candidatures associ�es.
							$txt_avertissement
							<br>ATTENTION, CECI EST LA DERNIERE CONFIRMATION !
						</center>", $__WARNING);

			message("Souhaitez vous vraiment supprimer l'ann�e \"$annee_txt\" ?", $__QUESTION);

			print("<div class='centered_icons_box'>
						<a href='$php_self?s=0' target='_self' class='lien_bleu_12'><img class='icone' src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Annuler' border='0'></a>
						<input type='image' class='icone' src='$__ICON_DIR/trashcan_full_34x34_slick_fond.png' alt='Supprimer' title='[Confirmer la suppression]' name='conf_supprimer' value='supprimer'>
						</form>
					</div>\n");
		}
		elseif((isset($annee_id) && $_SESSION["modification"]==1) || $_SESSION["ajout_annee"]==1) // ann�e choisie, on r�cup�re les infos actuelles
		{
			if($_SESSION["ajout_annee"]==1)
			{
				if(!isset($annee_nom)) // un seul test devrait �tre suffisant
					$annee_nom=$annee_nom_complet="";
			}
			else
			{
				$result=db_query($dbr,"SELECT $_DBC_annees_annee, $_DBC_annees_annee_longue
												FROM $_DB_annees
											WHERE $_DBC_annees_id='$annee_id'");

				list($current_annee_nom, $current_annee_nom_complet)=db_fetch_row($result,0);

				db_free_result($result);
			}

			print("<form name='form1' method='POST' action='$php_self'>\n");

			if(isset($annee_id))
				print("<input type='hidden' name='annee_id' value='$annee_id'>\n");
	?>
	<table align='center'>
	<tr>
		<td class='fond_menu2' colspan='2' style='padding:4px 20px 4px 20px;'>
			<font class='Texte_menu2'><b>&#8226;&nbsp;&nbsp;Informations</b></font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'>Nom court (peut �tre vide) :</font>
		</td>
		<td class='td-droite fond_menu'>
			<input type='text' name='nom' value='<?php if(isset($new_annee_nom)) echo htmlspecialchars(stripslashes($new_annee_nom), ENT_QUOTES, $default_htmlspecialchars_encoding); elseif(isset($current_annee_nom)) echo htmlspecialchars(stripslashes($current_annee_nom), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' maxlength='20' size='30'>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Nom complet :</b></font>
		</td>
		<td class='td-droite fond_menu'>
			<input type='text' name='nom_complet' value='<?php if(isset($new_annee_nom_complet)) echo htmlspecialchars(stripslashes($new_annee_nom_complet), ENT_QUOTES, $default_htmlspecialchars_encoding); elseif(isset($current_annee_nom_complet)) echo htmlspecialchars(stripslashes($current_annee_nom_complet), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' maxlength='80' size='85'>
		</td>
	</tr>
	</table>

	<div class='centered_icons_box'>
		<?php
			if(isset($success))
				print("<a href='index.php' target='_self'><img class='icone' src='$__ICON_DIR/back_32x32_fond.png' alt='Retour' border='0'></a>");
			else
				print("<a href='$php_self?m=0&a=0' target='_self'><img class='icone' src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Annuler' border='0'></a>");
		?>
		<input type='image' class='icone' src='<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>' alt='Valider' title='[Confirmer la cr�ation]' name='valider' value='Valider'>
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
<script language="javascript">
	document.form1.nom.focus()
</script>

</body></html>
