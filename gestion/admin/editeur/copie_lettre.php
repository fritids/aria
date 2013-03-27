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

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	verif_auth("../../login.php");

	if(!in_array($_SESSION["niveau"], array("$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
	{
		header("Location:$__MOD_DIR/gestion/noaccess.php");
		exit();
	}

	$dbr=db_connect();

	// r�cup�ration de l'id de la lettre � dupliquer (param�tre optionnel)
   if(isset($_GET["lettre_id"]) && ctype_digit($_GET["lettre_id"]))
		$lettre_source=$_GET["lettre_id"];

	if(isset($_POST["go"]) || isset($_POST["go_x"]))
	{
		$lettre_source=$_POST["source"];
		$comp_destination=$_POST["destination"];

		$memes_decisions=$_POST["memes_decisions"];

		$new_titre=trim($_POST["new_titre"]);

		if($lettre_source!="" && $comp_destination!="")
		{
			// R�cup�ration des infos de la lettre source
			$result=db_query($dbr, "SELECT $_DBC_lettres_titre FROM $_DB_lettres WHERE $_DBC_lettres_id='$lettre_source'");
			list($current_titre)=db_fetch_row($result,0);
			db_free_result($result);

			$titre_test=($new_titre=="") ? $current_titre : $new_titre;
			$x=2;
			
			while("0"!=db_num_rows(db_query($dbr,"SELECT * FROM $_DB_lettres WHERE $_DBC_lettres_comp_id='$comp_destination' AND $_DBC_lettres_titre ILIKE '".preg_replace("/[']+/","''", stripslashes($titre_test))."'")))
			{
				$titre_test=($new_titre=="") ? $current_titre . "#$x" : $new_titre . "$x";
				$x++;
			}
			
			$new_titre=str_replace("'","''", stripslashes($titre_test));

			// r�cup�ration de certains param�tres par d�faut de la composante destination
			$res_comp=db_query($dbr,"SELECT $_DBC_composantes_adr_pos_x, $_DBC_composantes_adr_pos_y, $_DBC_composantes_corps_pos_x,
													  $_DBC_composantes_corps_pos_y, $_DBC_composantes_largeur_logo
												FROM $_DB_composantes
											WHERE $_DBC_composantes_id='$comp_destination'");

			if(db_num_rows($res_comp)) // toujours vrai � cet endroit (sauf si la composante a �t� effac�e entretemps ...)
				list($comp_adr_pos_x, $comp_adr_pos_y, $comp_corps_pos_x, $comp_corps_pos_y, $comp_largeur_logo)=db_fetch_row($res_comp,0);
			else
			{
				$comp_adr_pos_x=109;
				$comp_adr_pos_y=42;
				$comp_corps_pos_x=60;
				$comp_corps_pos_y=78;
				$comp_largeur_logo=33;
			}

			db_free_result($res_comp);

			// Cr�ation de la lettre
			$default_lang='FR';
			$new_lettre_id=db_locked_query($dbr, $_DB_lettres, "INSERT INTO $_DB_lettres VALUES ('##NEW_ID##', '$comp_destination', '$new_titre', '', '', '', '', '0','TRUE','TRUE','TRUE','TRUE','TRUE','0','1','TRUE','$comp_adr_pos_x','$comp_adr_pos_y','TRUE', '$comp_corps_pos_x', '$comp_corps_pos_y','$default_lang')");

			// R�cup�ration et copie des �l�ments d'une lettre � l'autre
			// Paragraphes
			$result=db_query($dbr, "SELECT $_DBC_para_ordre, $_DBC_para_texte, $_DBC_para_gras, $_DBC_para_italique,
													 $_DBC_para_align, $_DBC_para_taille
												FROM $_DB_para
											WHERE $_DBC_para_lettre_id='$lettre_source'");

			$rows=db_num_rows($result);

			for($i=0; $i<$rows; $i++)
			{
				list($para_ordre, $para_txt, $para_gras, $para_italique, $para_align, $para_taille)=db_fetch_row($result, $i);

				$para_txt=str_replace("'","''", $para_txt);

				db_query($dbr, "INSERT INTO $_DB_para VALUES('$new_lettre_id', '$para_ordre', '$para_txt', '$para_gras', '$para_italique', '$para_align', '$para_taille')");
			}

			db_free_result($result);

			// Encadr�s
			$result=db_query($dbr, "SELECT $_DBC_encadre_texte, $_DBC_encadre_txt_align,  $_DBC_encadre_ordre FROM $_DB_encadre
											WHERE $_DBC_encadre_lettre_id='$lettre_source'");

			$rows=db_num_rows($result);

			for($i=0; $i<$rows; $i++)
			{
				list($encadre_texte, $encadre_align, $encadre_ordre)=db_fetch_row($result, $i);

				$encadre_texte=str_replace("'","''", $encadre_texte);

				db_query($dbr, "INSERT INTO $_DB_encadre VALUES('$new_lettre_id', '$encadre_ordre', '$encadre_texte', '$encadre_align')");
			}

			db_free_result($result);

			// S�parateurs
			$result=db_query($dbr, "SELECT $_DBC_sepa_ordre, $_DBC_sepa_nb_lignes FROM $_DB_sepa
											WHERE $_DBC_sepa_lettre_id='$lettre_source'");

			$rows=db_num_rows($result);

			for($i=0; $i<$rows; $i++)
			{
				list($sepa_ordre, $sepa_nb_lignes)=db_fetch_row($result, $i);
				db_query($dbr, "INSERT INTO $_DB_sepa VALUES('$new_lettre_id', '$sepa_ordre', '$sepa_nb_lignes')");
			}

			db_free_result($result);
			

			// D�cisions
			if($memes_decisions==1)
			{
				// Copie des d�cisions en respectant l'activation de ces derni�res dans la composante cible
				$result=db_query($dbr, "SELECT $_DBC_lettres_dec_dec_id FROM $_DB_lettres_dec
													WHERE $_DBC_lettres_dec_lettre_id='$lettre_source'
													AND $_DBC_lettres_dec_dec_id IN (SELECT distinct($_DBC_decisions_comp_dec_id) FROM $_DB_decisions_comp
																								WHERE $_DBC_decisions_comp_comp_id='$comp_destination')");

				$rows=db_num_rows($result);

				for($i=0; $i<$rows; $i++)
				{
					list($dec_id)=db_fetch_row($result, $i);
					db_query($dbr, "INSERT INTO $_DB_lettres_dec VALUES('$new_lettre_id', '$dec_id')");
				}

				db_free_result($result);
			}
			else
			{
				// Application des nouvelles d�cisions
				if(array_key_exists("decision_id", $_POST))
				{
				   foreach($_POST["decision_id"] as $dec_id)
						db_query($dbr, "INSERT INTO $_DB_lettres_dec VALUES ('$new_lettre_id', '$dec_id')");
				}
			}

			$succes=1;
		}
		else
			$erreur_source_destination=1;

	}

	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>

<div class='main'>
	<div class='menu_haut_2'>
		<a href='index.php' target='_self'><img class='icone_menu_haut_2' border='0' src='<?php echo "$__ICON_DIR/abiword_16x16_menu2.png"; ?>'></a>
		<a href='index.php' target='_self' class='lien_menu_haut_2'>Liste des lettres</a>
		<a href='tableau.php' target='_self'><img class='icone_menu_haut_2' border='0' src='<?php echo "$__ICON_DIR/kdeprint_report_16x16_menu2.png"; ?>'></a>
		<a href='tableau.php' target='_self' class='lien_menu_haut_2'>Tableau r�capitulatif</a>
		<?php
			if(in_array($_SESSION['niveau'], array("$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
			{
		?>

			<a href='parametres.php' target='_self'><img class='icone_menu_haut_2' border='0' src='<?php echo "$__ICON_DIR/preferences_16x16_menu2.png"; ?>' alt='parametres'></a>
			<a href='parametres.php' target='_self' class='lien_menu_haut_2'>Param�tres par d�faut</a>
		<?php
			}
		?>
			<a href='editeur.php?lettre_id=-1'  target='_self'><img class='icone_menu_haut_2' border='0' src='<?php echo "$__ICON_DIR/add_16x16_menu2.png"; ?>' alt='+'></a>
			<a href='editeur.php?lettre_id=-1'  target='_self' class='lien_menu_haut_2'>Cr�er une nouvelle lettre</a>
	</div>
	<?php
		titre_page_icone("Dupliquer une lettre", "editcopy_32x32_fond.png", 15, "L");

		if(isset($succes))
		{
			message("Lettre dupliqu�e avec succ�s. <b>N'oubliez pas de v�rifier les formations attach�es � la nouvelle lettre.</b>", $__SUCCES);
			$new_titre="";
		}
		elseif(isset($erreur_source_destination))
			message("Erreur : vous devez s�lectionner une source et une destination valides.", $__ERREUR);
		else
			message("<center>Une fois la copie effectu�e, n'oubliez pas de <b>v�rifier les propri�t�s</b> de la nouvelle lettre et de la <b>relier aux formations</b> ad�quates !</center>", $__WARNING);

		// Choix de la lettre source
		if($_SESSION["niveau"] == $__LVL_ADMIN)
		{
			// Administrateur : toutes les composantes
			$result=db_query($dbr,"SELECT $_DBC_lettres_id, $_DBC_lettres_titre, $_DBC_composantes_nom,
													$_DBC_composantes_id, $_DBC_composantes_univ_id, $_DBC_universites_nom
											FROM $_DB_lettres, $_DB_composantes, $_DB_universites
										WHERE $_DBC_lettres_comp_id=$_DBC_composantes_id
										AND $_DBC_composantes_univ_id=$_DBC_universites_id
											ORDER BY $_DBC_universites_nom, $_DBC_composantes_nom ASC, lower($_DBC_lettres_titre) ASC");
		}
		else
		{
			// Utilisateur "simple" : uniquement les lettres des composantes auxquelles il a acc�s
			$result=db_query($dbr,"SELECT $_DBC_lettres_id, $_DBC_lettres_titre, $_DBC_composantes_nom,
													$_DBC_composantes_id, $_DBC_composantes_univ_id, $_DBC_universites_nom
											FROM $_DB_lettres, $_DB_composantes, $_DB_universites, $_DB_acces_comp
										WHERE $_DBC_lettres_comp_id=$_DBC_composantes_id
										AND $_DBC_acces_comp_composante_id=$_DBC_composantes_id
										AND $_DBC_acces_comp_acces_id='$_SESSION[auth_id]'
										AND $_DBC_composantes_univ_id=$_DBC_universites_id
											ORDER BY $_DBC_universites_nom, $_DBC_composantes_nom ASC, lower($_DBC_lettres_titre) ASC");
		}

		$rows=db_num_rows($result);

		if($rows)
		{
			print("<form action='$php_self' method='POST' name='form1'>

						<table align='center'>
						<tr>
							<td class='fond_menu2' colspan='2' style='padding:4px 20px 4px 20px;'>
								<font class='Texte_menu2'>
									<b>&#8226;&nbsp;&nbsp;Copie</b>
								</font>
							</td>
						</tr>
						<tr>
							<td class='td-gauche fond_menu2'>
								<font class='Texte_menu2'><b>Titre de la lettre � dupliquer : </b></font>
							</td>
							<td class='td-droite fond_menu'>
								<select name='source'>\n");

			$old_comp="";
			$old_univ="";

			for($i=0; $i<$rows; $i++)
			{
				list($lettre_id, $lettre_titre, $comp_nom, $comp_id, $univ_id, $univ_nom)=db_fetch_row($result,$i);

				if($univ_id!=$old_univ)
				{
					if($i!=0)
						print("</optgroup>
									<option value='' label='' disabled></option>\n");

					print("<optgroup label='----- ".htmlspecialchars(stripslashes($univ_nom), ENT_QUOTES, $default_htmlspecialchars_encoding)." -----'>\n");

					$old_univ=$univ_id;
				}

				if($comp_id!=$old_comp)
				{
					if($i!=0)
						print("</optgroup>
									<option value='' label='' disabled></option>\n");

					print("<optgroup label='".htmlspecialchars($comp_nom, ENT_QUOTES, $default_htmlspecialchars_encoding)."'>\n");

					$old_comp=$comp_id;
				}

				$selected=(isset($lettre_source) && $lettre_source==$lettre_id) ? "selected='1'" : "";

				print("<option value='$lettre_id' label=\"$lettre_titre\" $selected>$lettre_titre</option>\n");
			}

			db_free_result($result);

		?>
				</select>
			</td>
		</tr>
		<tr>
			<td class='td-gauche fond_menu2'>
				<font class='Texte_menu2'><b>Titre de la lettre destination : </b><br>(laissez vide pour conserver le m�me titre)</font>
			</td>
			<td class='td-droite fond_menu'>
				<input type='text' name='new_titre' value='<?php if(isset($new_titre)) echo htmlspecialchars($new_titre, ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size='40' maxlenght='96'>
			</td>
		</tr>
		<tr>
			<td class='td-gauche fond_menu2'>
				<font class='Texte_menu2'><b>Composante destination : </b></font>
			</td>
			<td class='td-droite fond_menu'>
			<?php
				// Composante destination : uniquement si l'utilisateur a des droits ad�quates dans une autre composante

				if($_SESSION["niveau"] == $__LVL_ADMIN)
				{
					// Administrateur : toutes les composantes
					$result=db_query($dbr,"SELECT $_DBC_composantes_nom, $_DBC_composantes_id, $_DBC_composantes_univ_id, $_DBC_universites_nom
														FROM $_DB_composantes, $_DB_universites
													WHERE $_DBC_composantes_univ_id=$_DBC_universites_id
														ORDER BY $_DBC_composantes_univ_id, $_DBC_composantes_nom ASC");
				}
				else
				{
					// Utilisateur "simple" : on s�lectionne la ou les composantes � laquelle il a acc�s
					$result=db_query($dbr,"SELECT $_DBC_composantes_nom, $_DBC_composantes_id, $_DBC_composantes_univ_id, $_DBC_universites_nom
														FROM $_DB_composantes, $_DB_universites, $_DB_acces_comp
													WHERE $_DBC_composantes_univ_id=$_DBC_universites_id
													AND $_DBC_acces_comp_composante_id=$_DBC_composantes_id
													AND $_DBC_acces_comp_acces_id='$_SESSION[auth_id]'
														ORDER BY $_DBC_composantes_univ_id, $_DBC_composantes_nom ASC");
				}

				$rows=db_num_rows($result);

				if($rows==1) // une seule composante : inutile d'afficher la liste
					print("<font class='Texte_menu'><i>Vous n'avez acc�s � aucune autre composante</i></font>
								<input type='hidden' name='destination' value='$_SESSION[comp_id]'>\n");
				else
				{
					print("<select name='destination'>\n");

					$old_univ="";

					for($i=0; $i<$rows; $i++)
					{
						list($comp_nom, $comp_id, $univ_id, $univ_nom)=db_fetch_row($result,$i);

						if($univ_id!=$old_univ)
						{
							if($i!=0)
								print("</optgroup>
											<option value='' label='' disabled></option>\n");

							print("<optgroup label='----- ".htmlspecialchars(stripslashes($univ_nom), ENT_QUOTES, $default_htmlspecialchars_encoding)." -----'>\n");

							$old_univ=$univ_id;
						}

						$val=htmlspecialchars($comp_nom, ENT_QUOTES, $default_htmlspecialchars_encoding);

						print("<option value='$comp_id' label=\"$val\">$val</option>\n");
					}

					print("</select>\n");
				}

				db_free_result($result);
			?>
			</td>
		</tr>
		<tr>
			<td class='td-gauche fond_menu2'>
				<font class='Texte_menu2'>Lier la nouvelle lettre aux m�mes d�cisions ?<br>(prioritaire sur le champ suivant)</font>
			</td>
			<td class='td-droite fond_menu'>
				<font class='Texte_menu'>
					<input type='radio' name='memes_decisions' value='1' checked>&nbsp;Oui
					&nbsp;&nbsp;<input type='radio' name='memes_decisions' value='0'>&nbsp;Non
				</font>
			</td>
		</tr>
		<tr>
			<td class='td-gauche fond_menu2'>
				<font class='Texte_menu2'><b>Si "non" :</b><br>D�cisions li�es � la nouvelle lettre :</font>
			</td>
			<td class='td-droite fond_menu'>
				<table border='0' width='100%' cellpadding="2">
					<?php
						$result2=db_query($dbr,"SELECT $_DBC_decisions_id, $_DBC_decisions_texte FROM $_DB_decisions ORDER BY $_DBC_decisions_texte");

						$rows2=db_num_rows($result2);

						for($j=0; $j<$rows2; $j++)
						{
							list($decision_id, $decision_texte)=db_fetch_row($result2, $j);

							if(!($j%2))
								print("<tr>\n");

							print("<td align='left'>
										<font class='Texte_menu'>
											<input type='checkbox' name='decision_id[]' value='$decision_id'>&nbsp;&nbsp;$decision_texte
										</font>
									</td>\n");

							if($j%2)
								print("</tr>\n");
						}

						if($j%2)
							print("<td></td>
									</tr>\n");

						db_free_result($result2);
					?>
				</table>
			</td>
		</tr>
		</table>

		<div class='centered_icons_box'>
			<a href='index.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/back_32x32_fond.png"; ?>' alt='Annuler' border='0'></a>
			<input type="image" src="<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>" alt="Valider" name="go" value="Valider">
			</form>
		</div>

	<?php
		}
		else
		{
			message("Aucune lettre n'a encore �t� cr��e : aucune copie n'est possible.", $__WARNING);

			print("<div class='centered_box'>
						<a href='index.php' target='_self' class='lien2'><img src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Annuler' border='0'></a>
					</div>\n");
		}

		db_close($dbr);
	?>
</div>
<?php
	pied_de_page();
?>

</body></html>
