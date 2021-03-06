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
	include "include/editeur_fonctions.php";

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	verif_auth("../../login.php");

	// Modification des propri�t�s
	if(isset($_SESSION["lettre_id"]))
		$lettre_id=$_SESSION["lettre_id"];
	else
	{
		header("Location:index.php");
		exit;
	}

	// section ex�cut�e lorsque le formulaire est valid�
	if(isset($_POST["go"]) || isset($_POST["go_x"]))
	{
		$dbr=db_connect();

		// D�cisions associ�es

		// Nettoyage pr�alable (�conomise des v�rifications)
		db_query($dbr, "DELETE FROM $_DB_lettres_dec WHERE $_DBC_lettres_dec_lettre_id='$lettre_id'");

		if(array_key_exists("decision", $_POST))
		{
			foreach($_POST["decision"] as $dec_id)
				db_query($dbr, "INSERT INTO $_DB_lettres_dec VALUES ('$lettre_id', '$dec_id')");
		}
		
		// Menu des options particuli�res

		if(isset($_POST["options_particulieres"]) && $_POST["options_particulieres"]!="")
		{
			if($_POST["options_particulieres"]=="toutes_formations")
			{
				// Suppression avant insertion
				db_query($dbr, "DELETE FROM $_DB_lettres_propspec WHERE $_DBC_lettres_propspec_lettre_id='$lettre_id'");

				db_query($dbr, "UPDATE $_DB_lettres SET $_DBU_lettres_choix_multiples='0' WHERE $_DBC_lettres_id='$lettre_id'");

				db_query($dbr,"INSERT INTO $_DB_lettres_propspec (SELECT '$lettre_id', $_DBC_propspec_id FROM $_DB_propspec
																					WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]')");
			}
			elseif($_POST["options_particulieres"]=="aucune_formation") // Suppression
			{
				db_query($dbr, "DELETE FROM $_DB_lettres_propspec WHERE $_DBC_lettres_propspec_lettre_id='$lettre_id'");
				db_query($dbr, "UPDATE $_DB_lettres SET $_DBU_lettres_choix_multiples='0' WHERE $_DBC_lettres_id='$lettre_id'");
			}
			elseif($_POST["options_particulieres"]=="choix_multiples")
			{
				db_query($dbr, "DELETE FROM $_DB_lettres_propspec WHERE $_DBC_lettres_propspec_lettre_id='$lettre_id'");
				db_query($dbr, "UPDATE $_DB_lettres SET $_DBU_lettres_choix_multiples='1' WHERE $_DBC_lettres_id='$lettre_id'");
			}
		}
		else
		{
			db_query($dbr, "UPDATE $_DB_lettres SET $_DBU_lettres_choix_multiples='0' WHERE $_DBC_lettres_id='$lettre_id'");

			db_query($dbr, "DELETE FROM $_DB_lettres_propspec WHERE $_DBC_lettres_propspec_lettre_id='$lettre_id'");

			$requete="";

			if(array_key_exists("propspec", $_POST))
         {
            foreach($_POST["propspec"] as $propspec_id)
               $requete.="INSERT INTO $_DB_lettres_propspec VALUES ('$lettre_id', '$propspec_id');";
         }
			
			if(!empty($requete))
				db_query($dbr,"$requete");
		}

		db_close($dbr);

		header("Location:editeur.php");
		exit;
	}

	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_simple();
?>

<div class='main'>
	<?php
		titre_page_icone("Modifier les liens de la lettre", "randr_32x32_fond.png", 15, "L");

		$dbr=db_connect();
		$result=db_query($dbr,"SELECT $_DBC_lettres_titre, $_DBC_lettres_choix_multiples
										FROM $_DB_lettres WHERE $_DBC_lettres_id='$lettre_id'");

		$rows=db_num_rows($result);

		if($rows) // si != 1 : probleme...
		{
			list($current_titre, $current_choix_multiples)=db_fetch_row($result,0);
			db_free_result($result);

			// on doit r�cup�rer les d�cisions associ�es � cette lettre

			$result=db_query($dbr, "SELECT $_DBC_lettres_dec_dec_id FROM $_DB_lettres_dec WHERE $_DBC_lettres_dec_lettre_id='$lettre_id'");
			$rows=db_num_rows($result);

			if($rows)
			{
				$decisions_id_array=array();

				for($i=0; $i<$rows; $i++)
				{
					list($dec_id)=db_fetch_row($result, $i);
					$decisions_id_array[$dec_id]=$dec_id;
				}
			}

			print("<div class='centered_box'>
						<font class='TitrePage2' style='font-size:16px'><b>'$current_titre'</b></font>
					 </div>
							
					 <form method='post' action='$php_self'>\n");
		?>

		<table style='margin-left:auto; margin-right:auto; padding-bottom:20px;'>
		<tr>
			<td class='fond_menu2' colspan='2' style='padding:4px 20px 4px 20px;'>
				<font class='Texte_menu2'>
					<b>&#8226;&nbsp;&nbsp;D�cisions pour lesquelles imprimer cette lettre :</b>
				</font>
			</td>
		</tr>
			<?php
				$result2=db_query($dbr,"SELECT $_DBC_decisions_id, $_DBC_decisions_texte FROM $_DB_decisions
															WHERE $_DBC_decisions_id IN (SELECT distinct($_DBC_decisions_comp_dec_id) FROM $_DB_decisions_comp
																									WHERE $_DBC_decisions_comp_comp_id='$_SESSION[comp_id]')
														ORDER BY $_DBC_decisions_texte");

				$rows2=db_num_rows($result2);

				for($j=0; $j<$rows2; $j++)
				{
					list($decision_id, $decision_texte)=db_fetch_row($result2, $j);

					if(isset($decisions_id_array) && array_key_exists($decision_id, $decisions_id_array))
						$checked="checked=1";
					else
						$checked="";

					// print("<option value='$decision_id' $selected>$decision_texte</option>\n");

					if(!($j%2))
						print("<tr>\n");

					print("<td class='td-gauche fond_menu'>
								<font class='Texte_menu'>
									<input type='checkbox' name='decision[]' value='$decision_id' $checked>&nbsp;&nbsp;$decision_texte
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

		<?php
			$result=db_query($dbr,"SELECT $_DBC_propspec_id, $_DBC_annees_annee, $_DBC_specs_nom_court, $_DBC_propspec_finalite
												FROM $_DB_propspec, $_DB_annees, $_DB_specs
											WHERE $_DBC_propspec_annee=$_DBC_annees_id
											AND $_DBC_propspec_id_spec=$_DBC_specs_id
											AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
											AND $_DBC_propspec_active='1'
												ORDER BY $_DBC_annees_ordre, $_DBC_specs_nom_court, $_DBC_propspec_finalite");

			$rows=db_num_rows($result);

			$old_annee="===="; // on initialise � n'importe quoi (sauf vide)

			if($rows)
			{
				if($current_choix_multiples==1)
				{
					$multiples_selected="selected";
					$none_selected="";
				}
				else
				{
					$multiples_selected="";
					$none_selected="selected";
				}

				print("<table align='center'>
							<tr>
								<td class='fond_menu2' align='center' colspan='2' style='padding:4px 20px 4px 20px;'>
									<font class='Texte_menu2'><b>Formations concern�es par cette lettre</b></font>
								</td>
								<tr>
									<td class='fond_menu2' align='center' colspan='2' style='padding:4px 20px 4px 20px;'>
										<font class='Texte_menu2'><b>Options particuli�res</b></font>
									</td>
								</tr>
								<tr>
									<td class='fond_page' align='left' colspan='2' style='padding:4px 20px 4px 20px;'>
										<select name='options_particulieres'>
											<option value='' $none_selected></option>
											<option value='toutes_formations'>S�lectionner toutes les formations</option>
											<option value='aucune_formation'>Ne s�lectionner aucune formation</option>
											<option value='choix_multiples' $multiples_selected>Ne s'applique qu'aux formations � choix multiples</option>
										</select>
										<br>
										<font class='Texte'>
											<b>Si vous utilisez ce menu, il est inutile de modifier les s�lections ci-dessous.</b>
										</font>
										<br><br>
									</td>
								</tr>\n");

				$count=0;

				for($i=0; $i<$rows; $i++)
				{
					list($propspec_id, $annee, $spec_nom, $finalite)=db_fetch_row($result, $i);

					$nom_finalite=$tab_finalite[$finalite];

					if(db_num_rows(db_query($dbr, "SELECT * FROM $_DB_lettres_propspec
															WHERE $_DBC_lettres_propspec_lettre_id='$_SESSION[lettre_id]'
															AND $_DBC_lettres_propspec_propspec_id='$propspec_id'")))
						$checked="checked";
					else
						$checked="";

					if($annee=="")
						$annee="Ann�es particuli�res";

					if($annee!=$old_annee)
					{
						if($count%2)
							print("<td class='td-droite fond_page'></td>\n");

						$count=0;

						$old_annee=$annee;

						print("</tr>
									<tr>
										<td class='fond_menu' align='center' colspan='2' style='padding:4px 20px 4px 20px;'>
											<font class='Texte_menu'><b>$annee</b></font>
										</td>
									</tr>\n");
					}

					if(!($count%2))
						print("<tr>");

					print("<td class='td-gauche fond_page'>
									<input style='padding-right:10px;' type='checkbox' name='propspec[]' value='$propspec_id' $checked>
									<font class='Texte'>$spec_nom $nom_finalite</font>
								</td>\n");

					if($count%2)
						print("</tr>\n");

					$count++;
				}

				db_free_result($result);

				if($count%2)
					print("<td class='td-droite fond_page'></td>");

				print("</tr>
						 </table>\n");
			}
		?>

		<div class='centered_icons_box'>
			<a href='editeur.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Annuler' border='0'></a>
			<input type="image" src="<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>" alt="Valider" name="go" value="Valider">
			</form>
		</div>
	<?php
		}
	?>
</div>
<?php
	pied_de_page();
	db_close($dbr);
?>
</body></html>

