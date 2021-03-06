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
	if(isset($_GET["a"]) && isset($_GET["o"]) && isset($_SESSION["filtre_justif"]) && $_SESSION["filtre_justif"]!="-1") // Nouvel �l�ment
	{
		$_SESSION["ordre"]=$ordre=$_GET["o"];
		$_SESSION["ordre_max"]=$_SESSION["cbo"];

		// $_SESSION["ajout"]=1;
	}
	elseif(isset($_SESSION["ordre"]) && isset($_SESSION["ordre_max"])) // && isset($_SESSION["ajout"])
		$ordre=$_SESSION["ordre"];

	if(isset($_POST["go_valider"]) || isset ($_POST["go_valider_x"]))
	{
		$dbr=db_connect();

		$justificatif=$_POST['justif_id'];
		$cond_nationalite=$_POST["cond_nat"];

		// v�rification des champs
		if($justificatif=="")
			$justif_vide=1;

		if(!isset($justif_vide)) // on peut poursuivre
		{
			// Ajout unique : on prend l'ordre en compte
			if(isset($_SESSION["filtre_justif"]) && $_SESSION["filtre_justif"]!="-1")
			{
/*
				$result=db_query($dbr, "SELECT $_DBC_justifs_jf_justif_id, $_DBC_justifs_jf_ordre
													FROM $_DB_justif_jf
												WHERE $_DBC_justif_jf_propspec_id='$_SESSION[filtre_justif]'
												AND $_DBC_justifs_jf_ordre>='$_SESSION[ordre]'
													ORDER BY $_DBC_justifs_jf_ordre");

				$rows=db_num_rows($result);

				for($i=0; $i<$rows; $i++)
				{
					list($justif_id, $justif_ordre)=db_fetch_row($result, $i);

					$new_ordre=$justif_ordre+1;

					db_query($dbr, "UPDATE $_DB_justif_jf SET $_DBU_justifs_jf_ordre='$new_ordre'
										WHERE $_DBU_justifs_jf_justif_id='$justificatif'
										AND $_DBU_justif_jf_propspec_id='$_SESSION[filtre_justif]'
										AND $_DBU_justif_jf_ordre='$justif_ordre'");
				}

				db_free_result($result);
*/
				// D�calage des ordres pour faire une place
				db_query($dbr, "UPDATE $_DB_justifs_jf SET $_DBU_justifs_jf_ordre=$_DBU_justifs_jf_ordre+1
									 WHERE $_DBU_justifs_jf_propspec_id='$_SESSION[filtre_justif]'
									 AND $_DBU_justifs_jf_ordre>'$_SESSION[ordre]'");

				// Insertion
				db_query($dbr,"INSERT INTO $_DB_justif_jf VALUES ('$justificatif', '$_SESSION[filtre_justif]', '$ordre', '$cond_nationalite')");
			}
			else	// Rattachement multiple : ajout en queue de liste
			{
				// Formations associ�es
				if(isset($_POST["toutes_formations"]))
				{
					$result=db_query($dbr, "SELECT $_DBC_propspec_id,
														CASE WHEN $_DBC_propspec_id IN (SELECT distinct($_DBC_justifs_jf_propspec_id)
																								  FROM $_DB_justifs_jf)
															THEN (SELECT max($_DBC_justifs_jf_ordre) FROM $_DB_justifs_jf
																	WHERE $_DBC_justifs_jf_propspec_id=$_DBC_propspec_id)
															ELSE '0'END AS ordre
													 FROM $_DB_propspec WHERE $_DBC_propspec_comp_id ='$_SESSION[comp_id]'
													 ORDER BY $_DBC_propspec_id");

					$rows=db_num_rows($result);

					$requete="";

					for($i=0; $i<$rows; $i++)
					{
						list($propspec_id, $max_ordre)=db_fetch_row($result, $i);

						$insert_ordre=$max_ordre+1;

						$requete.="INSERT INTO $_DB_justifs_jf VALUES('$justificatif', '$propspec_id', '$insert_ordre', '$cond_nationalite');";
					}

					if(!empty($requete))
						db_query($dbr,"$requete");

					db_free_result($result);
				}
				else // S�lection individuelle 
				{
					$requete="";

					if(array_key_exists("formation", $_POST))
					{
					   foreach($_POST["formation"] as $formation_id)
						{
							// Ordre max pour la formation
							$res_ordre=db_query($dbr,"SELECT max($_DBC_justifs_jf_ordre) FROM $_DB_justifs_jf
														  	  WHERE $_DBC_justifs_jf_propspec_id='$formation_id'");

							$max_ordre=db_fetch_result($res_ordre, 0);

							$new_ordre=$max_ordre=="" ? 0 : $max_ordre+1;

							$requete.="INSERT INTO $_DB_justifs_jf VALUES('$justificatif', '$formation_id', '$new_ordre', '$cond_nationalite');";
						}
					}

					if(!empty($requete))
						db_query($dbr,"$requete");
				}
			}

			db_close($dbr);

			header("Location:index.php");
			exit;
		}
		else
			db_close($dbr);
	}
	
	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>

<div class='main'>
	<?php
		if(isset($_SESSION["filtre_justif"]) && $_SESSION["filtre_justif"]!="-1")
			titre_page_icone("Rattacher un justificatif � la formation \"$_SESSION[filtre_justif_nom]\"", "randr_32x32_fond.png", 30, "L");
		else
			titre_page_icone("Rattacher un justificatif � une ou plusieurs formations", "randr_32x32_fond.png", 30, "L");
	?>

	<form method='post' action='<?php echo $php_self; ?>'>

	<table align='center'>
	<tr>
		<td class='fond_menu2' colspan='2' style='padding:4px 20px 4px 20px;'>
			<font class='Texte_menu2'>
				<b>&#8226;&nbsp;&nbsp;Informations</b>
			</font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>El�ment � rattacher :</b></font>
		</td>
		<td class='td-droite fond_menu'>
			<?php
				$result=db_query($dbr,"SELECT $_DBC_justifs_id, $_DBC_justifs_intitule
													FROM $_DB_justifs
												WHERE $_DBC_justifs_id NOT IN (SELECT distinct($_DBC_justifs_jf_justif_id) FROM $_DB_justifs_jf)
												AND $_DBC_justifs_comp_id='$_SESSION[comp_id]'
													ORDER BY $_DBC_justifs_comp_id");
				$rows=db_num_rows($result);

				if($rows)
				{
					print("<select name='justif_id'>\n");

					for($i=0; $i<$rows; $i++)
					{
						list($justif_id, $justif_intitule)=db_fetch_row($result, $i);

						$val=htmlspecialchars($justif_intitule, ENT_QUOTES, $default_htmlspecialchars_encoding);

						print("<option value='$justif_id'>$val</option>\n");
					}

					print("</select>\n");
				}
				else
				{
					$no_element=1;
					message("Aucun justificatif modifiable ou aucun justificatif encore cr��", $__INFO);
				}
			?>
		</td>
	</tr>
	<?php
		if(!isset($no_element))
		{
	?>
	<tr>
		<td class='td-gauche fond_menu2' style='padding-bottom:20px;'>
			<font class='Texte_menu2'><b>Condition sur la nationalit� du candidat :</b></font>
		</td>
		<td class='td-droite fond_menu' style='padding-bottom:20px;'>
			<select name='cond_nat'>
				<option value='<?php echo $__COND_NAT_TOUS; ?>'>Nationalit� indiff�rente</option>
				<option value='<?php echo $__COND_NAT_FR; ?>'>Candidats Fran�ais uniquement</option>
				<option value='<?php echo $__COND_NAT_NON_FR; ?>'>Candidats Non Fran�ais uniquement</option>
				<option value='<?php echo $__COND_NAT_HORS_UE; ?>'>Candidats hors UE uniquement</option>
				<option value='<?php echo $__COND_NAT_UE; ?>'>Candidats intra-UE uniquement</option>
			</select>
		</td>
	</tr>
		<?php
			if(!isset($_SESSION["filtre_justif"]) || $_SESSION["filtre_justif"]=="-1")
			{
				$result=db_query($dbr,"SELECT $_DBC_propspec_id, $_DBC_annees_annee, $_DBC_specs_nom_court, $_DBC_propspec_finalite
													FROM $_DB_propspec, $_DB_annees, $_DB_specs
												WHERE $_DBC_propspec_annee=$_DBC_annees_id
												AND $_DBC_propspec_id_spec=$_DBC_specs_id
												AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
													ORDER BY $_DBC_annees_ordre, $_DBC_specs_nom_court, $_DBC_propspec_finalite");

				$rows=db_num_rows($result);

				$old_annee="===="; // on initialise � n'importe quoi (sauf vide)

				if($rows)
				{
					print("<tr>
								<td class='fond_menu2' align='center' colspan='2' style='padding:4px 20px 4px 20px;'>
									<font class='Texte_menu2'><b>Formations concern�es par ce justificatif</b></font>
								</td>
								<tr>
									<td class='fond_menu2' align='center' colspan='2' style='padding:4px 20px 4px 20px;'>
										<font class='Texte_menu2'><b>Options particuli�res</b></font>
									</td>
								</tr>
								<tr>
									<td class='fond_menu' colspan='2' style='padding:4px 20px 4px 20px;'>
										<input style='padding-right:10px;' type='checkbox' name='toutes_formations' value='1'>
										<font class='Texte_menu'>Toutes les formations</font>
									</td>
								</tr>\n");

					$count=0;

					for($i=0; $i<$rows; $i++)
					{
						list($propspec_id, $annee, $spec_nom, $finalite)=db_fetch_row($result, $i);

						$nom_finalite=$tab_finalite[$finalite];

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
											<td class='fond_menu' align='center' colspan='2' style='padding:4px 20px 4px 20px'>
												<font class='Texte_menu'><strong>$annee</strong></font>
											</td>
										</tr>\n");
						}

						if(!($count%2))
							print("<tr>");

						print("<td class='td-gauche fond_page'>
									<input style='padding-right:10px;' type='checkbox' name='formation[]' value='$propspec_id'>
									<font class='Texte'>$spec_nom $nom_finalite</font>
								 </td>\n");

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
			}
		}
	?>
	</table>
	
	<div class='centered_icons_box'>
		<a href='index.php' target='_self'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Annuler' border='0'></a>
		<?php
			if(!isset($no_element))
				print("<input type='image' src='$__ICON_DIR/button_ok_32x32_fond.png' alt='Valider' name='go_valider' value='Valider'>\n");
		?>
		</form>
	</div>
	
</div>
<?php
	pied_de_page();
?>
<script language="javascript">
	document.form1.comp_id.focus()
</script>

</body></html>
