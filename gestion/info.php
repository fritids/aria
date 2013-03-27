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

	include "../configuration/aria_config.php";
	include "$__INCLUDE_DIR_ABS/vars.php";
	include "$__INCLUDE_DIR_ABS/fonctions.php";
	include "$__INCLUDE_DIR_ABS/db.php";
	

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	verif_auth();

	if(!in_array($_SESSION['niveau'], array("$__LVL_SCOL_MOINS","$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
	{
		header("Location:$__MOD_DIR/gestion/noaccess.php");
		exit();
	}

	// identifiant de l'�tudiant
	$candidat_id=$_SESSION["candidat_id"];

	$dbr=db_connect();

	// Seconde condition : on doit avoir le verrouillage exclusif
	$res=cand_lock($dbr, $candidat_id);

	if($res>0)
	{
		db_close($dbr);
		header("Location:fiche_verrouillee.php");
		exit;
	}
	elseif($res==-1)
	{
		db_close($dbr);
		header("Location:edit_candidature.php");
		exit;
	}

	// Ajout ou modification ?

	if(isset($_GET["iid"]) && is_numeric($_GET["iid"])) // modification d'un �l�ment existant : l'identifiant est en param�tre
		$_SESSION["iid"]=$iid=$_GET["iid"];
	elseif(isset($_SESSION["iid"]))
		$iid=$_SESSION["iid"];

	if(isset($iid))
	{
		$result=db_query($dbr,"SELECT $_DBC_infos_comp_texte, $_DBC_infos_comp_annee, $_DBC_infos_comp_duree
																FROM $_DB_infos_comp
															WHERE $_DBC_infos_comp_id='$iid'");
		if(!db_num_rows($result))
		{
			db_free_result($result);
			db_close($dbr);
			header("Location:edit_candidature.php");
			exit();
		}
		else
		{
			list($cur_texte,$cur_annee,$cur_duree)=db_fetch_row($result,0); // normalement un seul r�sultat
			db_free_result($result);
		}
	}

	if(isset($_POST["go"]) || isset($_POST["go_x"])) // validation du formulaire
	{
		$annee=trim($_POST["annee"]);

		if($annee!=0 && (!is_numeric($annee) || strlen($annee)!=4))
			$annee_format=1;

		$information=trim($_POST["information"]);
		$information=clean_word_str($information);
		$information=preg_replace("/[']+/", "''", stripslashes($information));
		
		$duree=trim($_POST["duree"]);

		if(empty($annee) || empty($information) || empty($duree))
			$champ_vide=1;

		if(!isset($champ_vide) && !isset($annee_format))
		{
			if(!isset($iid))
			{
				$info_id=db_locked_query($dbr, $_DB_infos_comp, "INSERT INTO $_DB_infos_comp VALUES('##NEW_ID##','$candidat_id','$information','$annee','$duree')");

				write_evt($dbr, $__EVT_ID_G_INFO, "Ajout information $info_id", $candidat_id, $info_id, "INSERT INTO $_DB_infos_comp VALUES('$info_id','$candidat_id','$information','$annee','$duree')");
			}
			else // mise � jour
			{
				$req="UPDATE $_DB_infos_comp SET 	$_DBU_infos_comp_texte='$information',
																$_DBU_infos_comp_annee='$annee',
																$_DBU_infos_comp_duree='$duree'
						WHERE $_DBU_infos_comp_id='$iid'
						AND $_DBU_infos_comp_candidat_id='$candidat_id'";

				db_query($dbr, $req);

				write_evt($dbr, $__EVT_ID_G_INFO, "Modification information $iid", $candidat_id, $iid, $req);
			}
			db_close($dbr);
			header("Location:edit_candidature.php");
			exit();
		}
	}

	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>

<div class='main'>
	<?php
		print("<div class='infos_candidat Texte'>
					<strong>" . $_SESSION["tab_candidat"]["etudiant"] ." : " . $_SESSION["tab_candidat"]["civ_texte"] . " " . $_SESSION["tab_candidat"]["nom"] . " " . $_SESSION["tab_candidat"]["prenom"] .", " . $_SESSION["tab_candidat"]["ne_le"] . " " . $_SESSION["tab_candidat"]["txt_naissance"] ."</strong>
				 </div>

				<form action='$php_self' method='POST' name='form1'>");

		titre_page_icone("Informations compl�mentaires (stages, emplois, formations, ...)", "abiword_32x32_fond.png", 2, "L");

		message("Dans le cas d'une exp�rience <u>professionnelle</u>, n'oubliez pas de pr�ciser le nom de l'entreprise ainsi que la nature du poste occup�.", $__WARNING);

		if(isset($champ_vide))
			message("Formulaire incomplet : les champs en gras sont <u>obligatoires</u>", $__ERREUR);
		elseif(isset($annee_format))
			message("Erreur : le champ 'ann�e' doit �tre une valeur num�rique � 4 chiffres", $__ERREUR);
		else
			message("Les champs en gras sont <u>obligatoires</u>", $__WARNING);
	?>

	<table align='center'>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Ann�e (YYYY)</b></font>
		</td>
		<td class='td-droite fond_menu'>
			<input type='text' name='annee' value='<?php if(isset($annee)) echo htmlspecialchars(stripslashes($annee), ENT_QUOTES, $default_htmlspecialchars_encoding); elseif(isset($cur_annee)) echo htmlspecialchars(stripslashes($cur_annee), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' maxlength='4' size='15'>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Information</b></font>
		</td>
		<td class='td-droite fond_menu'>
			<textarea name='information' rows='6' cols='70' class='input'><?php if(isset($information)) echo htmlspecialchars(stripslashes($information), ENT_QUOTES, $default_htmlspecialchars_encoding); elseif(isset($cur_texte)) echo htmlspecialchars(stripslashes($cur_texte), ENT_QUOTES, $default_htmlspecialchars_encoding);?></textarea>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Dur�e</b></font>
		</td>
		<td class='td-droite fond_menu'>
			<input type='text' name='duree' value='<?php if(isset($duree)) echo htmlspecialchars(stripslashes($duree), ENT_QUOTES, $default_htmlspecialchars_encoding); elseif(isset($cur_duree)) echo htmlspecialchars(stripslashes($cur_duree), ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' maxlength='20' size='30'>&nbsp;&nbsp;<font class='Texte_menu'><i>exemple : 1 mois, 2 ans, ...</i></font>
		</td>
	</tr>
	</table>

	<div class='centered_icons_box'>
		<a href='edit_candidature.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Retour' border='0'></a>
		<input type="image" src="<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>" alt="Valider" name="go" value="Valider">
		</form>
	</div>

</div>
<?php
	db_close($dbr);
	pied_de_page();
?>

<script language="javascript">
	document.form1.annee.focus()
</script>
</body></html>
