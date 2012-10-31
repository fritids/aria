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

	verif_auth("$__GESTION_DIR/login.php");

	if(!in_array($_SESSION['niveau'], array("$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
	{
		header("Location:$__GESTION_DIR/noaccess.php");
		exit();
	}

	$dbr=db_connect();

	if(isset($_POST["suivant"]) || isset($_POST["suivant_x"]))
		$_SESSION["new_date_periode"]=$_POST["periode"];
	if(isset($_POST["valider"]) || isset($_POST["valider_x"]))
	{
		$new_id=$_POST["new_id"];
/*
		if(array_key_exists("toutes", $_POST))
		{
			$new_toutes_formations=$_POST["toutes"];

			if($new_toutes_formations!=0 && $new_toutes_formations!=1)
				$new_toutes_formations=1;
		}
		else
			$new_toutes_formations=1;
*/
		$new_toutes_formations=1;

		if($new_toutes_formations)
		{
			if(isset($_POST["jour_commission"]) && !empty($_POST["jour_commission"]) && isset($_POST["mois_commission"]) && !empty($_POST["mois_commission"]) && isset($_POST["annee_commission"]) && !empty($_POST["annee_commission"]))
			{
				$jour_commission=$_POST["jour_commission"];
				$mois_commission=$_POST["mois_commission"];
				$annee_commission=$_POST["annee_commission"];

				if(!ctype_digit($annee_commission) || $annee_commission<date("Y"))
					$annee_commission=date("Y");

				$new_date_commission=MakeTime(0,30,0,$mois_commission, $jour_commission, $annee_commission);

				// Insertion en masse dans la base, avec les valeurs saisies dans le formulaire
				db_query($dbr,"INSERT INTO $_DB_commissions (SELECT $_DBC_propspec_id, '$new_id', '$new_date_commission', '$_SESSION[new_date_periode]'
																	 		FROM $_DB_propspec WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]' AND $_DBC_propspec_active='1')");

				write_evt($dbr, $__EVT_ID_G_SESSION, "Cr�ation Commission $new_id");

				db_close($dbr);

				header("Location:index.php?succes=1");
				exit();
			}
			else
				$dates_manquantes=1;
		}
		else // Redirection vers le choix des formations (� �crire)
		{

		}
	}

	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>

<div class='main'>
	<?php
		// TODO
		// S�lection de la p�riode : temporaire pour les commissions ?
		// Ne faut-il pas pr�f�rer un syst�me de s�lection de p�riode plus global ? (mais alors comment emp�cher de param�trer
		// des dates 2009-2010 alors que __PERIODE=2008 ???)

		print("<form action='$php_self' method='POST' name='form1'>\n");

		if(!isset($_SESSION["new_date_periode"]))
		{
			titre_page_icone("Ajouter une date de Commission P�dagogique : s�lection de l'ann�e", "clock_32x32_fond.png", 15, "L");

			message("S�lectionnez l'ann�e universitaire pour laquelle la commission sera valide.", $__WARNING);
	?>
		<table align='center'>
		<tr>
			<td class='td-gauche fond_menu2'>
				<font class='Texte_menu2'><b>Ann�e universitaire concern�e par la Commission : </b></font>
			</td>
			<td class='td-droite fond_menu'>
				<select name='periode'>
					<?php
						if(isset($current_periode) && $current_periode==($__PERIODE+1))
						{
							$selected="";
							$selected_suivante="selected";
						}
						else
						{
							$selected_suivante="";
							$selected="selected";
						}

						print("<option value='$__PERIODE'>Ann�e actuelle ($__PERIODE-" . ($__PERIODE+1) . ")</option>
								 <option value='".($__PERIODE+1)."'>Ann�e suivante (".($__PERIODE+1). "-" . ($__PERIODE+2) . ")</option>\n");
					?>
				</select>
			</td>
		</tr>
		</table>

		<div class='centered_icons_box'>
			<a href='index.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Retour' border='0'></a>
			<input type="image" src="<?php echo "$__ICON_DIR/forward_32x32_fond.png"; ?>" alt="Suivant" name="suivant" value="Suivant">
			</form>
		</div>
	<?php
		}
		else
		{
			titre_page_icone("Ajouter une date de Commission P�dagogique pour l'ann�e $_SESSION[new_date_periode]-".($_SESSION["new_date_periode"]+1), "add_32x32_fond.png", 30, "L");

			$result=db_query($dbr, "SELECT max($_DBC_commissions_id) FROM $_DB_commissions, $_DB_propspec
											WHERE $_DBC_propspec_id=$_DBC_commissions_propspec_id
											AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
											AND $_DBC_commissions_periode='$_SESSION[new_date_periode]'");

			list($max_id)=db_fetch_row($result, 0);

			if($max_id=="")
				$new_id=1;
			else
				$new_id=$max_id+1;

			print("<input type=hidden name='new_id' value='$new_id'>\n");
	?>

	<table align='center'>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'>
				<b>Date globale pour la nouvelle Commission</b>
				<br><i>Vous pourrez ajuster cette date pour chaque formation par la suite</i>
			</font>
		</td>
	</tr>
	<tr>
		<td class='td-droite fond_menu'>
			<font class='Texte_menu'>
				Jour :&nbsp;
				<select name='jour_commission'>
					<option value=''></option>
						<?php
							for($i=1; $i<=31; $i++)
								print("<option value='$i'>$i</option>\n");
						?>
				</select>
				&nbsp;Mois : &nbsp;
				<select name='mois_commission'>
					<option value=''></option><option value='1'>Janvier</option><option value='2'>Fevrier</option>
					<option value='3'>Mars</option><option value='4'>Avril</option><option value='5'>Mai</option>
					<option value='6'>Juin</option><option value='7'>Juillet</option><option value='8'>Ao�t</option>
					<option value='9'>Septembre</option><option value='10'>Octobre</option><option value='11'>Novembre</option>
					<option value='12'>D�cembre</option>
				</select>
				&nbsp;Ann�e : &nbsp;
				<input type='text' name='annee_commission' maxlength="4" size="6" value='<?php echo $_SESSION["new_date_periode"]; ?>'>
			</font>
		</td>
	</tr>
	</table>

	<div class='centered_icons_box'>
		<?php
			if(isset($succes))
				print("<a href='index.php' target='_self' class='lien2'><img src='$__ICON_DIR/back_32x32_fond.png' alt='Retour' border='0'></a>\n");
			else
				print("<a href='index.php' target='_self' class='lien2'><img src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Retour' border='0'></a>\n");
		?>
		<input type="image" src="<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>" alt="Valider" name="valider" value="Valider">
		</form>
	</div>
	<?php
		}
	?>
</div>
<?php
	db_close($dbr);
	pied_de_page();
?>

</body></html>
