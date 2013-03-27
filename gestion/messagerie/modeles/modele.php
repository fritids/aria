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
	// include "include/editeur_fonctions.php";

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	verif_auth("$__GESTION_DIR/login.php");

	$dbr=db_connect();
	
	if(isset($_GET["succes"]))
		$succes=$_GET["succes"];

	if((isset($_POST["go_suivant"]) || isset($_POST["go_suivant_x"])) && array_key_exists("modele_id", $_POST))
	{
		$modele_id=$_POST["modele_id"];
		$resultat=1;
	}
	elseif(isset($_POST["go_valider"]) || isset($_POST["go_valider_x"]))
	{
		$dbr=db_connect();

		$intitule=trim($_POST['intitule']);
		$texte=$_POST['texte'];

		if(!isset($_SESSION["ajout"]) && array_key_exists("modele_id", $_POST)) // Modification d'un mod�le existant
		{
			$new_id=$_POST["modele_id"];

			// unicit�
			if(db_num_rows(db_query($dbr,"SELECT * FROM $_DB_msg_modeles
													WHERE ($_DBC_msg_modeles_intitule ILIKE '$intitule' OR $_DBC_msg_modeles_texte ILIKE '$texte')
													AND $_DBC_msg_modeles_id!='$new_id'
													AND $_DBC_msg_modeles_acces_id='$_SESSION[auth_id]'")))
				$modele_existe="1";
		}
		elseif(db_num_rows(db_query($dbr,"SELECT * FROM $_DB_msg_modeles
													 WHERE ($_DBC_msg_modeles_intitule ILIKE '$intitule'
														OR $_DBC_msg_modeles_texte ILIKE '$texte')
													 AND $_DBC_msg_modeles_acces_id='$_SESSION[auth_id]'")))
			$modele_existe="1";

		// v�rification des champs
		if($intitule=="")
			$intitule_vide=1;

		if($texte=="")
			$texte_vide=1;

		if((isset($new_id) && $new_id!="") || isset($_SESSION["ajout"]))
		{
			if(!isset($modele_existe) && !isset($intitule_vide) && !isset($texte_vide)) // on peut poursuivre
			{
				// Modification
				if(!isset($_SESSION["ajout"]) && isset($new_id))
					db_query($dbr,"UPDATE $_DB_msg_modeles SET $_DBU_msg_modeles_intitule='$intitule',
																			 $_DBU_msg_modeles_texte='$texte'
										WHERE $_DBU_msg_modeles_id='$new_id'");
				else
					$new_id=db_locked_query($dbr, $_DB_msg_modeles, "INSERT INTO $_DB_msg_modeles VALUES ('##NEW_ID##', '$_SESSION[auth_id]', '$intitule', '$texte')");

				db_close($dbr);

				header("Location:$php_self?succes=1");
				exit;
			}
			else
				db_close($dbr);
		}
		else
			$erreur_selection=1;
	}
	
	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>

<div class='main'>
	<?php
		if(isset($_GET["a"]) || isset($_SESSION["ajout"]))
		{
			$_SESSION["ajout"]=1;
			titre_page_icone("Cr�er un mod�le de courriel", "add_32x32_fond.png", 15, "L");
		}
		else
			titre_page_icone("Modifier un mod�le de courriel existant", "edit_32x32_fond.png", 15, "L");

		if(isset($intitule_vide))
			message("Erreur : le champ 'Intitul�' ne doit pas �tre vide", $__ERREUR);

		if(isset($infos_vides))
			message("Erreur : le champ 'Contenu' ne doit pas �tre vide", $__ERREUR);

		if(isset($modele_existe))
			message("Erreur : cet intitul� de mod�le existe d�j�.", $__ERREUR);

		if(isset($succes))
		{
			if(!isset($_SESSION["ajout"]))
				message("Le mod�le a �t� modifi�e avec succ�s.", $__SUCCES);
			else
				message("Le mod�le a �t� cr�� avec succ�s.", $__SUCCES);
		}

		$dbr=db_connect();

		if(!isset($resultat) && !isset($_GET["a"]) && !isset($_SESSION["ajout"])) // choix de l'�l�ment � modifier
		{
			$result=db_query($dbr, "SELECT $_DBC_msg_modeles_id, $_DBC_msg_modeles_intitule
												FROM $_DB_msg_modeles
											WHERE $_DBC_msg_modeles_acces_id='$_SESSION[auth_id]'
												ORDER BY $_DBC_msg_modeles_intitule ASC");

			$rows=db_num_rows($result);

			if($rows)
			{
				print("<form action='$php_self' method='POST' name='form1'>
						 <center>
							<font class='Texte'>Mod�le � modifier : </font>
							<select name='modele_id' size='1'>\n");

				$old_univ="";

				for($i=0; $i<$rows; $i++)
				{
					list($modele_id, $intitule)=db_fetch_row($result,$i);

					$value=htmlspecialchars($intitule, ENT_QUOTES, $default_htmlspecialchars_encoding);

					print("<option value='$modele_id' label=\"$value\">$value</option>\n");
				}

				db_free_result($result);

				print("</select>
						</center>

						<div class='centered_icons_box'>\n");

				if(isset($succes))
					print("<a href='../index.php' target='_self'><img src='$__ICON_DIR/rew_32x32_fond.png' alt='Retour' border='0'></a>&nbsp;&nbsp;\n");
				else
					print("<a href='../index.php' target='_self'><img src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Retour' border='0'></a>&nbsp;&nbsp;\n");

				print("<input type='image' src='$__ICON_DIR/forward_32x32_fond.png' alt='Suivant' name='go_suivant' value='Suivant'>
						 </form>
						</div>\n");
			}
			else
			{
				message("Il n'y a actuellement aucun mod�le � modifier.", $__ERREUR);

				print("<div class='centered_box'>
							<a href='../index.php' target='_self'><img src='$__ICON_DIR/back_32x32_fond.png' alt='Retour' border='0'></a>
						 </div>\n");
			}

			if(isset($erreur_selection))
				message("Erreur de s�lection du mod�le", $__ERREUR);
		}
		else // �l�ment choisi, on r�cup�re les infos actuelles
		{
			if(isset($_GET["a"]) || isset($_SESSION["ajout"]))
			{
				if(!isset($intitule)) // un seul test devrait suffire
					$intitule=$titre=$texte="";
			}
			else
			{
				$result=db_query($dbr,"SELECT $_DBC_msg_modeles_intitule, $_DBC_msg_modeles_texte
													FROM $_DB_msg_modeles
												WHERE $_DBC_msg_modeles_id='$modele_id'");

				list($intitule, $texte)=db_fetch_row($result,0);

				db_free_result($result);
			}

			print("<form name='form1' enctype='multipart/form-data' method='POST' action='$php_self'>\n");

			if(isset($modele_id) && ctype_digit($modele_id))
				print("<input type='hidden' name='modele_id' value='$modele_id'>\n");
		?>
		<table align='center'>
		<tr>
			<td class='td-gauche fond_menu2' colspan='2' style='padding:4px 20px 4px 20px;'>
				<font class='Texte_menu2'>
					<b>&#8226;&nbsp;&nbsp;Informations</b>
				</font>
			</td>
		</tr>
		<tr>
			<td class='td-gauche fond_menu2'>
				<font class='Texte_menu2'><b>Intitul� du mod�le</b></font>
			</td>
			<td class='td-droite fond_menu'>
				<input type='text' name='intitule' value='<?php if(isset($intitule)) echo htmlspecialchars(stripslashes($intitule), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' maxlength='196' size='70'>
			</td>
		</tr>
		<tr>
			<td class='td-gauche fond_menu2'>
				<font class='Texte_menu2'><b>Contenu du message</b></font>
			</td>
			<td class='td-droite fond_menu'>
				<textarea name='texte' rows='18' cols='80'><?php
					if(isset($texte)) echo htmlspecialchars(stripslashes($texte), ENT_QUOTES, $default_htmlspecialchars_encoding);
				?></textarea>
			</td>
		</tr>
		</table>

		<div class='centered_icons_box'>
			<?php
				if(!isset($_SESSION["ajout"]))
					print("<a href='$php_self' target='_self'><img src='$__ICON_DIR/back_32x32_fond.png' alt='Retour' border='0'></a>&nbsp;&nbsp;\n");
				elseif(isset($_GET["succes"]))
					print("<a href='../index.php' target='_self'><img src='$__ICON_DIR/back_32x32_fond.png' alt='Retour' border='0'></a>&nbsp;&nbsp;\n");

				if(!isset($succes))
					print("<a href='../index.php' target='_self'><img src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Annuler' border='0'></a>&nbsp;&nbsp;\n");
			?>

			<input type='image' src='<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>' alt='Valider' name='go_valider' value='Valider'>
			</form>
		</div>

		<?php
			}
			db_close($dbr);
		?>

		<table border='0' cellpadding='2' align='center' style='padding-bottom:20px;'>
		<tr>
			<td align='justify' colspan='2' class='fond_menu2' style='padding:4px;'>
				<font class='Texte_menu2'><b>Les mod�les peuvent s'adapter aux informations de chaque candidat(e) gr�ce aux macros suivantes : </b></font>
			</td>
		</tr>
		<tr>
			<td align='justify'><font class='Texte'><b>%Civilit�%</b></font></td>
			<td align='justify'><font class='Texte'>Civilit� du candidat (Monsieur, Madame, Mademoiselle - sensible aux majuscules)</font></td>
		</tr>
		<tr>
			<td align='justify'><font class='Texte'><b>%Nom%</b></font></td>
			<td align='justify'><font class='Texte'>Nom du candidat (sensible aux majuscules : %NOM%, %Nom%, %nom%)</font></td>
		</tr>
		<tr>
			<td align='justify'><font class='Texte'><b>%Pr�nom%</b></font></td>
			<td align='justify'><font class='Texte'>Pr�nom du candidat (m�me remarque)</font></td>
		</tr>
		<tr>
			<td align='justify'><font class='Texte'><b>%naissance%</b></font></td>
			<td align='justify'><font class='Texte'>Date de naissance du candidat</font></td>
		</tr>
		<tr>
			<td align='justify'><font class='Texte'><b>%ville_naissance%</b></font></td>
			<td align='justify'><font class='Texte'>Ville de naissance du candidat</font></td>
		</tr>
		<tr>
			<td align='justify'><font class='Texte'><b>%pays_naissance%</b></font></td>
			<td align='justify'><font class='Texte'>Pays de naissance du candidat</font></td>
		</tr>
		<tr>
			<td align='justify'><font class='Texte'><b>%Responsable%</b></font></td>
			<td align='justify'><font class='Texte'>Responsable de la Formation (champ � compl�ter dans les propri�t�s de chaque formation)</font></td>
		</tr>
		<tr>
			<td align='justify'><font class='Texte'><b>%courriel_responsable%</b></font></td>
			<td align='justify'><font class='Texte'>Adresse �lectronique du responsable de la formation (m�me remarque)</font></td>
		</tr>
		<tr>
			<td align='justify'><font class='Texte'><b>%ann�e_universitaire%</b></font></td>
			<td align='justify'><font class='Texte'>Ann�e universitaire � venir <?php echo "(i.e \"$__PERIODE/" . ($__PERIODE+1) . "\")"; ?></font></td>
		</tr>
		<tr>
			<td align='justify'><font class='Texte'><b>%aaaa/bbbb%</b> (grammaire)</font></td>
			<td align='justify'><font class='Texte'>En fonction du candidat : affiche "aaaa" si c'est un homme, "bbbb" si c'est une femme (exemple : %admis/admise%)
			</font>
		</td>
	</tr>
	</table>

</div>

<?php
	pied_de_page();
?>
<script language="javascript">
	document.form1.modele_id.focus()
</script>

</body></html>
