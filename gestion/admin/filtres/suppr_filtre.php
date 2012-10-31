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
	// SUPPRESSION D'UN FILTRE
	session_name("preinsc_gestion");
	session_start();

	include "../../../configuration/aria_config.php";
	include "$__INCLUDE_DIR_ABS/vars.php";
	include "$__INCLUDE_DIR_ABS/fonctions.php";
	include "$__INCLUDE_DIR_ABS/db.php";

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	verif_auth();

	if(!in_array($_SESSION['niveau'], array("$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
	{
		header("Location:$__MOD_DIR/gestion/noaccess.php");
		exit();
	}

	// param�tre chiffr� : identifiant du filtre en cas de modification
	if(isset($_GET["p"]) && -1!=($params=get_params($_GET['p'])))
	{
		if(isset($params["fid"]) && ctype_digit($params["fid"]))
			$fid=$params["fid"];
	}
	elseif(isset($_POST["fid"]) && ctype_digit($_POST["fid"]))
		$fid=$_POST["fid"];
	else
	{
		header("Location:index.php");
		exit;
	}

	if(isset($_POST["confirmer"]) || isset($_POST["confirmer_x"]))
	{
		$dbr=db_connect();

		db_query($dbr,"DELETE FROM $_DB_filtres WHERE $_DBC_filtres_id='$fid'");

		write_evt($dbr, $__EVT_ID_G_FILTRES, "Suppression filtre $fid", "", $fid);

		db_close($dbr);

		header("Location:index.php");
		exit;
	}
	else // r�cup�ration des donn�es actuelles
	{
		if(array_key_exists($fid, $_SESSION["tab_filtres"]))
			$nom_filtre=$_SESSION["tab_filtres"]["$fid"]["nom"];
		else
		{
			header("Location:index.php");
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
		titre_page_icone("Supprimer un filtre", "trashcan_full_32x32_slick_fond.png", 15, "L");

		print("<form action='$php_self' method='POST' name='form1'>
				 <input type='hidden' name='fid' value='$fid'>

				 <div class='centered_box'>
					<font class='Texte'>
						<strong>Filtre :</strong> \"$nom_filtre\"
					</font>
				 </div>\n");

		message("Souhaitez-vous r�ellement supprimer ce filtre ?", $__QUESTION);
	?>

	<div class='centered_icons_box'>
		<a href='index.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Annuler' border='0'></a>
		<input type="image" src="<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>" alt="Confirmer" name="confirmer" value="Confirmer">
		</form>
	</div>

</div>
<?php
	pied_de_page();
?>
</body></html>
