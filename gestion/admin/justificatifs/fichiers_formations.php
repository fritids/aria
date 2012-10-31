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

	verif_auth("$__GESTION_DIR/login.php");

	$dbr=db_connect();

	// r�cup�ration de variables
	if(isset($_GET["f"]) && is_numeric($_GET["f"]))
	{
		$fichier_id=$_GET["f"];

		if(!db_num_rows(db_query($dbr,"SELECT * FROM $_DB_justifs_fichiers
												 WHERE $_DBC_justifs_fichiers_id='$fichier_id'
												 AND $_DBC_justifs_fichiers_comp_id='$_SESSION[comp_id]'")))
		{
			db_close($dbr);
			header("Location:fichiers.php");
			exit;
		}
		else
			$_SESSION["fichier_id"]=$fichier_id;
	}
	elseif(isset($_SESSION["fichier_id"]))
		$fichier_id=$_SESSION["fichier_id"];
	else
	{
		db_close($dbr);
		header("Location:fichiers.php");
		exit;
	}

	if(isset($_POST["go_valider"]) || isset($_POST["go_valider_x"]))
	{
		// Nettoyage avant r�-insertion
			db_query($dbr, "DELETE FROM $_DB_justifs_ff
								 WHERE $_DBC_justifs_ff_fichier_id='$_SESSION[fichier_id]'");

		// Formations
		if(isset($_POST["toutes_formations"]))
		{
			$cond_nationalite_globale=$_POST["cond_nat_all"];

			// Condition de nationalit� � appliquer par d�faut si non parametr�e
			$cond_nationalite_globale=$cond_nationalite_globale=="" ? $__COND_NAT_TOUS : $cond_nationalite_globale;

			$result=db_query($dbr, "SELECT $_DBC_propspec_id FROM $_DB_propspec 
											WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]'
											AND $_DBC_propspec_active='1'");
			$rows=db_num_rows($result);

			$requete="";

			for($i=0; $i<$rows; $i++)
			{
				list($propspec_id)=db_fetch_row($result, $i);
				$requete.="INSERT INTO $_DB_justifs_ff VALUES('$_SESSION[fichier_id]', '$propspec_id', '$cond_nationalite_globale');";
			}

			if(!empty($requete))
				db_query($dbr,"$requete");

			db_free_result($result);
		}
		else
		{
			$cond_nationalite_globale=$_POST["cond_nat_all"];

			if($cond_nationalite_globale!="")
				$cond_nationalite=$cond_nationalite_globale;

			$requete="";

			foreach($_POST as $key => $propspec_id)
			{
				if($cond_nationalite_globale=="" && array_key_exists("nat_$propspec_id", $_POST) && $_POST["nat_$propspec_id"]!="")
					$cond_nationalite=$_POST["nat_$propspec_id"];
				elseif($cond_nationalite_globale=="")
					$cond_nationalite=$__COND_NAT_TOUS;

				if(!strncmp($key, "formation_", 8))
					$requete.="INSERT INTO $_DB_justifs_ff VALUES('$_SESSION[fichier_id]', '$propspec_id', '$cond_nationalite');";
			}

			if(!empty($requete))
				db_query($dbr,"$requete");
		}


		db_close($dbr);
		header("Location:fichiers.php");
		exit;
	}
	
	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>

<div class='main'>
	<?php
		titre_page_icone("Justificatifs : associer une pi�ce jointe � une formation", "document-export_32x32_fond.png", 30, "L");
	?>

	<form method='post' action='<?php echo $php_self; ?>'>
	<table align='center'>
	<tr>
		<td class='fond_menu2' colspan='2' align='left' style='padding:4px 20px 4px 20px;'>
			<font class='Texte_menu2'>
				<b>&#8226;&nbsp;&nbsp;Informations</b>
			</font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'>
				<b>S�lectionner toutes les formations</b>
			</font>
		</td>
		<td class='td-droite fond_menu'>
			<font class='Texte_menu'>
				<input type='checkbox' name='toutes_formations' value='1'>
				&nbsp;(<i>si coch�e, cette case est prioritaire sur la s�lection individuelle</i>)
			</font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' style='padding-bottom:20px;'>
			<font class='Texte_menu2'>
				<b>Appliquer cette condition de nationalit�
				<br>� toutes les formations rattach�es :</b>
			</font>
		</td>
		<td class='td-droite fond_menu' style='padding-bottom:20px;'>
			<select name='cond_nat_all'>
				<option value=""></option>
				<option <?php echo "value='$__COND_NAT_TOUS'"; ?>>Nationalit� indiff�rente</option>
				<option <?php echo "value='$__COND_NAT_FR'"; ?>>Candidats Fran�ais uniquement</option>
				<option <?php echo "value='$__COND_NAT_NON_FR'"; ?>>Candidats Non Fran�ais uniquement</option>
				<option <?php echo "value='$__COND_NAT_HORS_UE'"; ?>>Candidats hors UE</option>
				<option <?php echo "value='$__COND_NAT_UE'"; ?>>Candidats intra-UE uniquement</option>
			</select>
		</td>
	</tr>
	</table>
	<br>
	<table align='center'>
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
			print("<tr>
						<td class='fond_menu2' align='center' colspan='4' style='padding:4px 20px 4px 20px;'>
							<font class='Texte_menu2'><strong>Formations concern�es par ce fichier</strong></font>
						</td>\n");

			// On liste les formations actuellement rattach�es, avec la condition de nationalit�
			$res_actuels=db_query($dbr, "SELECT $_DBC_justifs_ff_propspec_id, $_DBC_justifs_ff_nationalite
													FROM $_DB_justifs_ff
												WHERE $_DBC_justifs_ff_fichier_id='$_SESSION[fichier_id]'");

			$rows_actuels=db_num_rows($res_actuels);

			$array_actuels=array();

			for($i=0; $i<$rows_actuels; $i++)
			{
				list($propspec_id, $nationalite)=db_fetch_row($res_actuels, $i);
				$array_actuels[$propspec_id]=$nationalite;
			}

			db_free_result($res_actuels);

			$count=0;

			for($i=0; $i<$rows; $i++)
			{
				list($propspec_id, $annee, $spec_nom, $finalite)=db_fetch_row($result, $i);

				$nom_finalite=$tab_finalite[$finalite];

				if(db_num_rows(db_query($dbr, "SELECT * FROM $_DB_justifs_ff
															WHERE $_DBC_justifs_ff_fichier_id='$_SESSION[fichier_id]'
															AND $_DBC_justifs_ff_propspec_id='$propspec_id'")))
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
									<td class='fond_menu2' align='center' colspan='4' style='padding:4px 20px 4px 20px;'>
										<font class='Texte_menu2'><b>$annee</b></font>
									</td>
								</tr>\n");
				}

				if(!($count%2))
					print("<tr>");

				print("<td class='td-gauche fond_page'>
							<input style='padding-right:10px;' type='checkbox' name='formation_$propspec_id' value='$propspec_id' $checked>
							<font class='Texte'>$spec_nom $nom_finalite</font>
						</td>
						<td class='td-gauche fond_page'>
							<select name='nat_$propspec_id'>
								<option value=''></option>\n");
			?>
								<option <?php echo "value='$__COND_NAT_TOUS'"; if(array_key_exists($propspec_id, $array_actuels) && $array_actuels[$propspec_id]==$__COND_NAT_TOUS) echo "selected=1"; ?>>Nationalit� indiff�rente</option>
								<option <?php echo "value='$__COND_NAT_FR'"; if(array_key_exists($propspec_id, $array_actuels) && $array_actuels[$propspec_id]==$__COND_NAT_FR) echo "selected=1"; ?>>Candidats Fran�ais uniquement</option>
								<option <?php echo "value='$__COND_NAT_NON_FR'"; if(array_key_exists($propspec_id, $array_actuels) && $array_actuels[$propspec_id]==$__COND_NAT_NON_FR) echo "selected=1"; ?>>Candidats Non Fran�ais uniquement</option>
								<option <?php echo "value='$__COND_NAT_HORS_UE'"; if(array_key_exists($propspec_id, $array_actuels) && $array_actuels[$propspec_id]==$__COND_NAT_HORS_UE) echo "selected=1"; ?>>Candidats hors UE</option>
								<option <?php echo "value='$__COND_NAT_UE'"; if(array_key_exists($propspec_id, $array_actuels) && $array_actuels[$propspec_id]==$__COND_NAT_UE) echo "selected=1"; ?>>Candidats intra-UE uniquement</option>
							</select>
						</td>
			<?php

				if($count%2)
					print("</tr>\n");

				$count++;
			}

			if($count%2)
				print("<td class='td-droite fond_page'></td>\n");

			db_free_result($result);
			db_close($dbr);

			print("</tr>\n");
		}
	?>
	</table>
	
	<div class='centered_icons_box'>
		<a href='fichiers.php' target='_self' class='lien_bleu_12'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Annuler' border='0'></a>
		<input type='image' src='<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>' alt='Valider' name='go_valider' value='Valider'>
		</form>
	</div>

</div>
<?php
	pied_de_page();
?>
</form>
</body></html>
