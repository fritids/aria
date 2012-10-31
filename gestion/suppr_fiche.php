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
	
	if(!in_array($_SESSION['niveau'], array("$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
	{
		header("Location:$__MOD_DIR/gestion/noaccess.php");
		exit();
	}

	if(isset($_POST["go"]) || isset($_POST["go_x"])) // validation du formulaire
	{
		$candidat_id=$_SESSION["candidat_id"];

		if($candidat_id!="")
		{
			$dbr=db_connect();
			db_query($dbr,"DELETE FROM $_DB_candidat WHERE $_DBC_candidat_id='$candidat_id'");

			//On supprime aussi les messages
         $sous_rep=sous_rep_msg($candidat_id);
			
			if(is_dir("$__CAND_MSG_STOCKAGE_DIR_ABS/$sous_rep/$candidat_id"))
				@deltree("$__CAND_MSG_STOCKAGE_DIR_ABS/$sous_rep/$candidat_id");

			write_evt($dbr, $__EVT_ID_G_MAN, "Suppression fiche $candidat_id", $candidat_id, $candidat_id);

			unset($_SESSION["tab_candidat"]);
			unset($_SESSION["candidat_id"]);

			db_close($dbr);
		}

		header("Location:index.php");
		exit();
	}

	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>

<div class='main'>
	<?php
		titre_page_icone("Supprimer une fiche candidat", "trashcan_full_32x32_slick_fond.png", 30, "L");

		message("<center>
						Attention : toute suppression est <strong>irr�versible</strong> sur cette interface.
						<br>Souhaitez-vous r�ellement supprimer cette fiche ?
					</center>", $__WARNING);
	?>

	<form action="<?php print("$php_self"); ?>" method="POST">

	<table align='center'>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b><?php echo $_SESSION['tab_candidat']['etudiant']; ?> : </b></font>
		</td>
		<td class='td-droite fond_menu'>
			<font class='Texte_menu'><b><?php echo $_SESSION["tab_candidat"]["civ_texte"] . " " . $_SESSION['tab_candidat']['nom'] . " " . $_SESSION['tab_candidat']['prenom']; ?></b></font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b><?php echo $_SESSION['tab_candidat']['ne_le']; ?> : </b></font>
		</td>
		<td class='td-droite fond_menu'>
			<font class='Texte_menu'><?php echo $_SESSION['tab_candidat']['txt_naissance']; ?></font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Lieu de naissance : </b></font>
		</td>
		<td class='td-droite fond_menu'>
			<font class='Texte_menu'><?php echo $_SESSION['tab_candidat']['lieu_naissance']; ?></font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Nationalit�</b> : </font>
		</td>
		<td class='td-droite fond_menu'>
			<font class='Texte_menu'><?php echo preg_replace("/_/","",$_SESSION['tab_candidat']['nationalite']); ?></font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Courriel</b> : </font>
		</td>
		<td class='td-droite fond_menu'>
			<font class='Texte_menu'><?php echo $_SESSION['tab_candidat']['email']; ?>&nbsp;</font>
		</td>
	</tr>
	</table>
		
	<div class='centered_icons_box'>		
		<a href='edit_candidature.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Annuler' border='0'></a>
		<input type="image" src="<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>" alt="Confirmer" name="go" value="Confirmer">
		</form>
	</div>		
	
</div>
<?php
	pied_de_page();
?>
</body></html>

