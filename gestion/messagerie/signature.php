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

	$dbr=db_connect();
	
	if(isset($_GET["succes"]))
		$succes=$_GET["succes"];

	if(isset($_POST["valider"]) || isset($_POST["valider_x"]))
	{
		$dbr=db_connect();

		$activation=array_key_exists("activation", $_POST) ? $_POST["activation"] : "f";
		$texte=$_POST['texte'];

		// Modification
		db_query($dbr,"UPDATE $_DB_acces SET $_DBU_acces_signature_txt='$texte',
														 $_DBU_acces_signature_active='$activation'
							WHERE $_DBU_acces_id='$_SESSION[auth_id]'");

		db_close($dbr);

		header("Location:$php_self?succes=1");
		exit;
	}
	
	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>

<div class='main'>
	<?php
		titre_page_icone("Signature automatique de vos messages", "abiword_32x32_fond.png", 15, "L");

		if(isset($succes))
			message("Signature enregistr�e avec succ�s", $__SUCCES);

		$dbr=db_connect();

		$result=db_query($dbr,"SELECT $_DBC_acces_signature_txt, $_DBC_acces_signature_active
											FROM $_DB_acces
										WHERE $_DBC_acces_id='$_SESSION[auth_id]'");

		list($cur_texte, $current_active)=db_fetch_row($result,0);

		db_free_result($result);

		print("<form name='form1' enctype='multipart/form-data' method='POST' action='$php_self'>\n");
	?>
	<table align='center'>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Activer la signature ?</b></font>
		</td>
		<td class='td-droite fond_menu'>
			<?php
				if(isset($activation))
					$cur_active=$activation;
				elseif(isset($current_active))
					$cur_active=$current_active;
				else
					$cur_active='f';

				if($cur_active=='t')
				{
					$yes_checked="checked";
					$no_checked="";
				}
				else
				{
					$yes_checked="";
					$no_checked="checked";
				}

				print("<input type='radio' name='activation' value='t' style='vertical-align:middle; padding-right:5px' $yes_checked><font class='Texte_menu'>Oui</font>
						&nbsp;&nbsp;<input type='radio' name='activation' value='f' style='vertical-align:middle; padding-right:5px' $no_checked><font class='Texte_menu'>Non</font>\n");
			?>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Votre signature</b></font>
		</td>
		<td class='td-droite fond_menu'>
			<textarea name='texte' rows='8' cols='80'><?php
				if(isset($texte))
					echo htmlspecialchars(stripslashes($texte), ENT_QUOTES, $default_htmlspecialchars_encoding);
				elseif(isset($cur_texte))
					echo htmlspecialchars(stripslashes($cur_texte), ENT_QUOTES, $default_htmlspecialchars_encoding);
			?></textarea>
		</td>
	</tr>
	</table>

	<div class='centered_icons_box'>
		<a href='index.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/back_32x32_fond.png"; ?>' alt='Retour' border='0'></a>
		<input type='image' src='<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>' alt='Valider' name='valider' value='Valider'>
		</form>
	</div>

</div>
<?php
	db_close($dbr);
	pied_de_page();
?>

<script language="javascript">
	document.form1.texte.focus()
</script>

</body></html>
