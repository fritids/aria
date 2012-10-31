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
	
	if(!in_array($_SESSION['niveau'], array("$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
	{
		header("Location:$__MOD_DIR/gestion/noaccess.php");
		exit();
	}

	$dbr=db_connect();

	if(isset($_POST["go"]) || isset($_POST["go_x"]))
	{
		$propspec_id=$_POST["propspec_id"];

		if($propspec_id!="")
		{
			$result=db_query($dbr,"SELECT * FROM $_DB_propspec WHERE $_DBC_propspec_id='$propspec_id'");
			$rows=db_num_rows($result);
			db_free_result($result);

			if(!$rows)
				$id_existe_pas=1;
			else
			{
				db_query($dbr,"DELETE FROM $_DB_propspec WHERE $_DBC_propspec_id='$propspec_id'");
				db_close($dbr);

				header("Location:$php_self?succes=1");
				exit;
			}
		}
	}

	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>

<div class='main'>
	<?php
		titre_page_icone("Supprimer une formation", "trashcan_full_32x32_slick_fond.png", 30, "L");

		if(isset($id_existe_pas))
			message("La composante demand�e est incorrecte (probl�me de coh�rence de la base)", $__ERREUR);

		if(array_key_exists("succes",$_GET) && $_GET["succes"]==1)
			message("Sp�cialit� supprim�e avec succ�s", $__SUCCES);

		message("La suppression entrainera automatiquement l'effacement de <b>toutes les candidatures correspondantes</b>.", $__WARNING);

		print("<br>
				 <form action='$php_self' method='POST' name='form1'>
				 <input type='hidden' name='act' value='1'>\n");

		$result=db_query($dbr,"SELECT $_DBC_propspec_id, $_DBC_specs_nom, $_DBC_annees_annee, $_DBC_propspec_finalite,
												$_DBC_annees_id, $_DBC_propspec_manuelle
											FROM $_DB_specs, $_DB_propspec, $_DB_annees
										WHERE $_DBC_specs_comp_id='$_SESSION[comp_id]'
										AND $_DBC_propspec_annee=$_DBC_annees_id
										AND $_DBC_propspec_id_spec=$_DBC_specs_id
											ORDER BY $_DBC_annees_ordre, $_DBC_specs_nom, $_DBC_specs_mention_id, $_DBC_propspec_finalite ASC");
		$rows=db_num_rows($result);

		print("<div class='centered_box'>
					<font class='Texte'>Sp�cialit� : </font>
					<select name='propspec_id' size='1'>\n
						<option value=''></option>\n");

		$old_annee="-1";

		for($i=0; $i<$rows; $i++)
		{
			list($propspec_id, $form_spec_nom, $form_annee_nom, $form_finalite, $form_annee_id, $manuelle) =db_fetch_row($result,$i);

			$finalite_txt=$tab_finalite[$form_finalite];

			if($form_annee_id!=$old_annee)
			{
				if($i!=0)
					print("</optgroup>
								<option value='' label='' disabled></option>\n");

				if($form_annee_nom=="")
					$form_annee_nom="Ann�es particuli�res";

				print("<optgroup label='$form_annee_nom'>\n");

				$new_sep_annee=1;

				$old_annee=$form_annee_id;
				$old_mention="-1";
			}
			else
				$new_sep_annee=0;

			if($manuelle)
				$manuelle_txt=" (M)";
			else
				$manuelle_txt="";

			print("<option value='$propspec_id' label=\"$form_spec_nom $finalite_txt $manuelle_txt\">$form_spec_nom $finalite_txt $manuelle_txt</option>\n");
		}

		print("</select>
				</div>\n");

		db_free_result($result);
		db_close($dbr);
	?>

	<div class='centered_icons_box'>
		<?php
			if(isset($_GET["succes"]))
				print("<a href='index.php' target='_self'><img src='$__ICON_DIR/back_32x32_fond.png' alt='Retour' border='0'></a>\n");
			else
				print("<a href='index.php' target='_self'><img src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Annuler' border='0'></a>\n");
		?>
		<input type="image" src="<?php echo "$__ICON_DIR/trashcan_full_34x34_slick_fond.png"; ?>" alt="Supprimer" name="go" value="Supprimer">
	</div>

	<script language='javascript'>
		document.form1.propspec_id.focus()
	</script>

	</form>
</div>
<?php
	pied_de_page();
?>
</body></html>

