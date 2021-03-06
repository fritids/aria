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
	// TODO novembre 2008 : ces dates sont-elles obsol�tes ... ?
	// OUI
	
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

	// r�cup�ration des donn�es actuelles
	$dbr=db_connect()	;
	$result=db_query($dbr,"SELECT date_ouverture, date_fermeture FROM configuration");

	if(db_num_rows($result)) // normalement on a un seul r�sultat dans cette table
	{
		list($cur_date_ouverture, $cur_date_fermeture)=db_fetch_row($result,0);
		db_free_result($result);
		$cur_date_ouv=date_fr("dmY",$cur_date_ouverture);
		$cur_date_fer=date_fr("dmY",$cur_date_fermeture);

		$jour_ouverture=date("j", $cur_date_ouverture);
		$mois_ouverture=date("n", $cur_date_ouverture);
		$annee_ouverture=date("Y", $cur_date_ouverture);

		$jour_fermeture=date("j", $cur_date_fermeture);
		$mois_fermeture=date("n", $cur_date_fermeture);
		$annee_fermeture=date("Y", $cur_date_fermeture);
	}
	else
	{
		db_free_result($result);
		db_close($dbr);
		header("Location:../login.php");
		exit;
	}
	
	if(isset($_POST["valider"]) || isset($_POST["valider_x"]))
	{

		// Ouverture
		$jour_ouverture=$_POST["jour_ouverture"];
		$mois_ouverture=$_POST["mois_ouverture"];
		$annee_ouverture=trim($_POST["annee_ouverture"]);

		if(!is_numeric($annee_ouverture) || $annee_ouverture<date("Y"))
			$annee_ouverture=date("Y");

		$new_date_ouverture=MakeTime(2,15,0,$mois_ouverture, $jour_ouverture, $annee_ouverture); // date au format unix

		// Fermeture
		$jour_fermeture=$_POST["jour_fermeture"];
		$mois_fermeture=$_POST["mois_fermeture"];
		$annee_fermeture=trim($_POST["annee_fermeture"]);

		if(!is_numeric($annee_fermeture) || $annee_fermeture<date("Y"))
			$annee_fermeture=date("Y");

		$new_date_fermeture=MakeTime(23,59,59,$mois_fermeture, $jour_fermeture, $annee_fermeture); // date au format unix

		if($new_date_ouverture>$new_date_fermeture)
			$date_ouverture_apres_fermeture=1;

		if(!isset($date_ouverture_apres_fermeture))
		{
			db_query($dbr, "UPDATE configuration SET 	date_ouverture='$new_date_ouverture',
																												date_fermeture='$new_date_fermeture'");
			db_close($dbr);

			$succes=1;
		}
	}		

	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>

<div class='main'>
	<?php
		titre_page_icone("Dates limites", "clock_32x32_fond.png", 12, "L");

		if(isset($date_ouverture_apres_fermeture))
			message("Erreur : la date d'ouverture des pr�candidatures est post�rieure � la date de fermeture.", $__ERREUR);

		if(isset($succes))
			message("Informations mises � jour avec succ�s.", $__SUCCES);

		message("Les champs en gras sont obligatoires.", $__INFO);

		print("<form action='$php_self' method='POST' name='form1'>\n");
	?>

	<table align='center'>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Date d'ouverture des pr�candidatures</b></font>
		</td>
		<td class='td-droite fond_menu'>
			<select name='jour_ouverture'>
				<?php
					for($i=1; $i<=31; $i++)
					{
						if(isset($jour_ouverture) && $jour_ouverture==$i)
							$selected="selected";
						else
							$selected="";

						print("<option value='$i' $selected>$i</option>\n");
					}
				?>
			</select>
			&nbsp;
			<select name='mois_ouverture'>
				<option value='1' <?php if(isset($mois_ouverture) && $mois_ouverture==1) echo "selected"; ?>>Janvier</option>
				<option value='2' <?php if(isset($mois_ouverture) && $mois_ouverture==2) echo "selected"; ?>>Fevrier</option>
				<option value='3' <?php if(isset($mois_ouverture) && $mois_ouverture==3) echo "selected"; ?>>Mars</option>
				<option value='4' <?php if(isset($mois_ouverture) && $mois_ouverture==4) echo "selected"; ?>>Avril</option>
				<option value='5' <?php if(isset($mois_ouverture) && $mois_ouverture==5) echo "selected"; ?>>Mai</option>
				<option value='6' <?php if(isset($mois_ouverture) && $mois_ouverture==6) echo "selected"; ?>>Juin</option>
				<option value='7' <?php if(isset($mois_ouverture) && $mois_ouverture==7) echo "selected"; ?>>Juillet</option>
				<option value='8' <?php if(isset($mois_ouverture) && $mois_ouverture==8) echo "selected"; ?>>Ao�t</option>
				<option value='9' <?php if(isset($mois_ouverture) && $mois_ouverture==9) echo "selected"; ?>>Septembre</option>
				<option value='10' <?php if(isset($mois_ouverture) && $mois_ouverture==10) echo "selected"; ?>>Octobre</option>
				<option value='11' <?php if(isset($mois_ouverture) && $mois_ouverture==11) echo "selected"; ?>>Novembre</option>
				<option value='12' <?php if(isset($mois_ouverture) && $mois_ouverture==12) echo "selected"; ?>>D�cembre</option>
			</select>
			<input type='text' name='annee_ouverture' maxlength="4" size="6" value='<?php if(isset($annee_ouverture)) echo $annee_ouverture; else echo date("Y"); ?>'>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Date de fermeture des pr�candidatures</b></font>
		</td>
		<td class='td-droite fond_menu'>
			<select name='jour_fermeture'>
				<?php
					for($i=1; $i<=31; $i++)
					{
						if(isset($jour_fermeture) && $jour_fermeture==$i)
							$selected="selected";
						else
							$selected="";

						print("<option value='$i' $selected>$i</option>\n");
					}
				?>
			</select>
			&nbsp;
			<select name='mois_fermeture'>
				<option value='1' <?php if(isset($mois_fermeture) && $mois_fermeture==1) echo "selected"; ?>>Janvier</option>
				<option value='2' <?php if(isset($mois_fermeture) && $mois_fermeture==2) echo "selected"; ?>>Fevrier</option>
				<option value='3' <?php if(isset($mois_fermeture) && $mois_fermeture==3) echo "selected"; ?>>Mars</option>
				<option value='4' <?php if(isset($mois_fermeture) && $mois_fermeture==4) echo "selected"; ?>>Avril</option>
				<option value='5' <?php if(isset($mois_fermeture) && $mois_fermeture==5) echo "selected"; ?>>Mai</option>
				<option value='6' <?php if(isset($mois_fermeture) && $mois_fermeture==6) echo "selected"; ?>>Juin</option>
				<option value='7' <?php if(isset($mois_fermeture) && $mois_fermeture==7) echo "selected"; ?>>Juillet</option>
				<option value='8' <?php if(isset($mois_fermeture) && $mois_fermeture==8) echo "selected"; ?>>Ao�t</option>
				<option value='9' <?php if(isset($mois_fermeture) && $mois_fermeture==9) echo "selected"; ?>>Septembre</option>
				<option value='10' <?php if(isset($mois_fermeture) && $mois_fermeture==10) echo "selected"; ?>>Octobre</option>
				<option value='11' <?php if(isset($mois_fermeture) && $mois_fermeture==11) echo "selected"; ?>>Novembre</option>
				<option value='12' <?php if(isset($mois_fermeture) && $mois_fermeture==12) echo "selected"; ?>>D�cembre</option>
			</select>
			<input type='text' name='annee_fermeture' maxlength="4" size="6" value='<?php if(isset($annee_fermeture)) echo $annee_fermeture; else echo date("Y"); ?>'>
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

	<script language="javascript">
		document.form1.jour_ouverture.focus()
	</script>
</div>
<?php
	pied_de_page();
?>
</body></html>
