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

	verif_auth("../login.php");
	
	if(!in_array($_SESSION['niveau'], array("$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
	{
		header("Location:$__MOD_DIR/gestion/noaccess.php");
		exit();
	}

	$dbr=db_connect();

	if(isset($_POST["go"]) || isset($_POST["go_x"]))
	{
		// R�cup�ration de toutes les d�cisions
		$result2=db_query($dbr, "SELECT $_DBC_decisions_id FROM $_DB_decisions");

		if(db_num_rows($result2))
			$all_decs=db_fetch_all($result2);
		else
			$all_decs=array();

		db_free_result($result2);

		// R�cup�ration des d�cisions actives
		$result2=db_query($dbr, "SELECT $_DBC_decisions_comp_dec_id FROM $_DB_decisions_comp
																WHERE $_DBC_decisions_comp_comp_id='$_SESSION[comp_id]'");

		if(db_num_rows($result2))
			$active_decs=db_fetch_all($result2);
		else
			$active_decs=array();

		db_free_result($result2);

		// R�cup�ration des d�cisions utilis�es
		$result2=db_query($dbr, "SELECT distinct($_DBC_cand_decision) FROM $_DB_cand, $_DB_propspec
																WHERE $_DBC_cand_propspec_id=$_DBC_propspec_id
																AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'");

		if(db_num_rows($result2))
			$all_used_decs=db_fetch_all($result2);
		else
			$all_used_decs=array();

		db_free_result($result2);

		$selected_decs=array();
		$i=0;

		if(array_key_exists("decision_id", $_POST))
		{
		   foreach($_POST["decision_id"] as $dec_id)
		   {
				$selected_decs[$i]=$dec_id;
				$i++;
	
			/*
				// Si pas encore dedans
				if(!in_array(array("$_DBU_decisions_comp_dec_id" => $decision_id), $all_decs))
					db_query($dbr, "INSERT INTO $_DBC_decisions_comp VALUES('$_SESSION[comp_id]', '$dec_id')");
			*/
			}
		}

		foreach($all_decs as $index => $array_dec)
		{
			$dec_id=$array_dec[$_DBU_decisions_id];

			// Pas dans la liste des d�cisions s�lectionn�e ET d�cision supprimable : suppression
			if(in_array(array("$_DBU_decisions_comp_dec_id" => $dec_id), $active_decs) && !in_array($dec_id, $selected_decs) && !in_array(array("$_DBU_cand_decision" => $dec_id), $all_used_decs))
				db_query($dbr, "DELETE FROM $_DB_decisions_comp
														WHERE $_DBC_decisions_comp_dec_id='$dec_id'  AND $_DBC_decisions_comp_comp_id='$_SESSION[comp_id]'");
			elseif(in_array($dec_id, $selected_decs) && !in_array(array("$_DBU_decisions_comp_dec_id" => $dec_id), $active_decs)) // Insertion
				db_query($dbr, "INSERT INTO $_DB_decisions_comp VALUES('$_SESSION[comp_id]', '$dec_id')");
		}

		$succes=1;
	}

	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>

<div class='main'>
	<?php
		titre_page_icone("D�cisions de Commissions P�dagogiques utilis�es dans cette composante", "decisions_32x32_fond.png", 30, "L");

		if(isset($succes))
			message("Param�tres enregistr�s", $__SUCCES);

		message("<center>Les cases gris�es correspondent � des d�cisions d�j� rendues<br>Elles ne peuvent pas �tre d�sactiv�es</center>", $__WARNING);

		$result=db_query($dbr,"SELECT $_DBC_decisions_id,$_DBC_decisions_texte FROM $_DB_decisions ORDER BY $_DBC_decisions_texte");
		$rows=db_num_rows($result);

		if($rows)
		{
			// R�cup�ration des d�cisions actives
			$result2=db_query($dbr, "SELECT $_DBC_decisions_comp_dec_id FROM $_DB_decisions_comp
												WHERE $_DBC_decisions_comp_comp_id='$_SESSION[comp_id]'");

			if(db_num_rows($result2))
				$active_decs=db_fetch_all($result2);
			else
				$active_decs=array();

			db_free_result($result2);

			// R�cup�ration des d�cisions utilis�es
			$result2=db_query($dbr, "SELECT distinct($_DBC_cand_decision) FROM $_DB_cand, $_DB_propspec
												WHERE $_DBC_cand_propspec_id=$_DBC_propspec_id
												AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'");

			if(db_num_rows($result2))
				$all_used_decs=db_fetch_all($result2);
			else
				$all_used_decs=array();

			db_free_result($result2);

			print("<form action='$php_self' method='POST' name='form1'>

						<table align='center'>
						<tr>
							<td class='td-gauche fond_menu2'>
								<font class='Texte_menu2'><b>S�lection<br>des D�cisions :</b></font>
							</td>
							<td class='td-droite fond_menu'>
								<table border='0' width='100%' cellpadding='2'>\n");

			for($i=0; $i<$rows; $i++)
			{
				list($decision_id, $decision_texte)=db_fetch_row($result, $i);

				if(!($i%2))
					print("<tr>\n");

				// Si la d�cision est activ�e ... (attention � la fonction in_array : on cherche bien un 'array' dans $all_decs et $all_used_decs
				if(in_array(array("$_DBU_decisions_comp_dec_id" => $decision_id), $active_decs))
				{
					// On regarde si la d�cision courante est d�sactivable ou non
					if(in_array(array("$_DBU_cand_decision" => $decision_id), $all_used_decs)) // pas d�sactivable car utilis�e
						print("<td align='left'>
										<font class='Texte_menu'>
											<input type='checkbox' name='decision_id[]' value='$decision_id' checked='1' disabled='1'>&nbsp;&nbsp;<i>$decision_texte</i>
										</font>
									</td>\n");
					else
						print("<td align='left'>
										<font class='Texte_menu'>
											<input type='checkbox' name='decision_id[]' value='$decision_id' checked='1'>&nbsp;&nbsp;$decision_texte
										</font>
									</td>\n");
				}
				else
					print("<td align='left'>
									<font class='Texte_menu'>
										<input type='checkbox' name='decision_id[]' value='$decision_id'>&nbsp;&nbsp;$decision_texte
									</font>
								</td>\n");

				if($i%2)
					print("</tr>\n");
			}

			if($i%2)
				print("<td></td>
						</tr>\n");

			print("</table>
					</td>
				</tr>
				</table>\n");
		}

		db_free_result($result);
		db_close($dbr);
	?>
	<div class='centered_icons_box'>
		<a href='index.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/back_32x32_fond.png"; ?>' alt='Annuler' border='0'></a>
		<input type="image" src="<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>" alt="Valider" name="go" value="Valider">
		</form>
	</div>

</div>
<?php
	pied_de_page();
?>

</body></html>
