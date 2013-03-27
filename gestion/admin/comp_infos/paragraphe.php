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

	$dbr=db_connect();

	// edition d'un paragraphe

	// article et filiere
	if(isset($_SESSION["info_doc_id"]))
		$info_doc_id=$_SESSION["info_doc_id"];
	else
	{
		header("Location:index.php");
		exit;
	}

	if(isset($_GET["a"]) && isset($_GET["o"])) // Nouvel �l�ment
	{
		$_SESSION["ordre"]=$ordre=$_GET["o"];
		$_SESSION["ordre_max"]=$_SESSION["cbo"];

		$_SESSION["ajout"]=1;
		$action="Ajouter";
	}
	elseif(isset($_SESSION["ordre"]) && isset($_SESSION["ordre_max"]) && isset($_SESSION["ajout"]))
	{
		$ordre=$_SESSION["ordre"];
		$action="Ajouter";
	}
	elseif(isset($_GET["o"])) // Modification
	{
		$_SESSION["ordre"]=$ordre=$_GET["o"];

		$action="Modifier";

		// R�cup�ration des infos actuelles
		$result=db_query($dbr,"SELECT $_DBC_comp_infos_para_texte, $_DBC_comp_infos_para_align, $_DBC_comp_infos_para_gras,
																			$_DBC_comp_infos_para_italique, $_DBC_comp_infos_para_taille
															 FROM $_DB_comp_infos_para
															WHERE $_DBC_comp_infos_para_info_id='$info_doc_id'
															AND $_DBC_comp_infos_para_ordre='$ordre'");
		$rows=db_num_rows($result);
		if($rows)
		{
			list($texte,$alignement, $gras, $italique, $taille)=db_fetch_row($result,0);
			db_free_result($result);
		}
		else
		{
			db_close($dbr);
			header("Location:index.php");
			exit();
		}
	}
	elseif(isset($_SESSION["ordre"]))
	{
		$ordre=$_SESSION["ordre"];
		$action="Modifier";
	}

	if(isset($_SESSION["ajout"]) && $_SESSION["ajout"]==1)
		$action="Ajouter";
	else
		$action="Modifier";

	// section ex�cut�e lorsque le formulaire est valid�
	if(isset($_POST["valider"]) || isset($_POST["valider_x"]))
	{
		$texte=trim($_POST['new_para']);
		$alignement=$_POST['alignement'];
		$gras=$_POST["gras"];
		$italique=$_POST["italique"];
		$taille=trim($_POST["taille"]);

		if($taille=="" || !ctype_digit($taille) || $taille<4)
			$taille=10;
			
		if($texte=="")
			$para_vide=1;
		else
		{
			if(!isset($_SESSION["ajout"]))
				db_query($dbr,"UPDATE $_DB_comp_infos_para SET	$_DBU_comp_infos_para_texte='$texte',
																				$_DBU_comp_infos_para_align='$alignement',
																				$_DBU_comp_infos_para_gras='$gras',
																				$_DBU_comp_infos_para_italique='$italique',
																				$_DBU_comp_infos_para_taille='$taille'
									WHERE $_DBU_comp_infos_para_info_id='$info_doc_id'
									AND $_DBU_comp_infos_para_ordre='$_SESSION[ordre]'");
			else
			{
				if($_SESSION["ordre"]!=$_SESSION["ordre_max"]) // On n'ins�re pas l'�l�ment en dernier : d�callage
				{
					// 1 - Reconstruction des �l�ments (comme pour la suppression)
					$a=get_all_elements($dbr, $info_doc_id);
					$nb_elements=count($a);

					for($i=$nb_elements; $i>$_SESSION["ordre"]; $i--)
					{
						$current_ordre=$i-1;
						$new_ordre=$i;
						$current_type=$a["$current_ordre"]["type"]; // le type sert juste � savoir dans quelle table on doit modifier l'�l�ment courant
						$current_id=$a["$current_ordre"]["id"];

						$current_table_name=get_table_name($current_type);
						$col_ordre=$current_table_name["ordre"];
						$col_id=$current_table_name["id"];
						$table=$current_table_name["table"];

						db_query($dbr,"UPDATE $table SET $col_ordre='$new_ordre'
											WHERE $col_id='$current_id'
											AND $col_ordre='$current_ordre'");
					}
				}

				// Insertion du nouvel �l�ment
				db_query($dbr,"INSERT INTO $_DB_comp_infos_para VALUES ('$info_doc_id', '$_SESSION[ordre]','$texte', '$gras', '$italique', '$alignement', '$taille')");
			}

			db_close($dbr);

			header("Location:index.php");
			exit;
		}
	}

	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_simple();
?>

<div class='main'>
	<?php
		titre_page_icone("$action un paragraphe", "edit_32x32_fond.png", 15, "L");

		if(isset($para_pas_clean))
			message("<center>Erreur : le texte contient des caract�res non autoris�s.
						<br>Les caract�res autoris�s sont : a-z A-Z 0-9 - ' ! ? _ : . / @ ( ) les caract�res accentu�s, la virgule et l'espace.</center>", $__ERREUR);

		if(isset($para_vide))
			message("Erreur : le texte du paragraphe ne doit pas �tre vide", $__ERREUR);

		if(isset($texte))
			$texte=htmlspecialchars(stripslashes($texte),ENT_QUOTES, $default_htmlspecialchars_encoding);
		else
			$texte="";

		if(isset($taille))
			$current_taille=$taille;
		else
			$current_taille=10;

		if(isset($alignement))
		{
			switch($alignement)
			{
				case 0: 	$c0="checked"; $c1=$c2=$c3="";
									break;
				case 1: 	$c1="checked"; $c0=$c2=$c3="";
									break;
				case 2: 	$c2="checked"; $c0=$c1=$c3="";
									break;
				case 3: 	$c3="checked"; $c0=$c1=$c2="";
									break;
			}
		}
		else
		{
			$c3="checked";
			$c1=$c2=$c0="";
		}

		if(isset($gras) && $gras)
		{
			$gras_1="checked"; $gras_0="";
		}
		else
		{
			$gras_1=""; $gras_0="checked";
		}

		if(isset($italique) && $italique)
		{
			$italique_1="checked"; $italique_0="";
		}
		else
		{
			$italique_1=""; $italique_0="checked";
		}

		print("<form method='post' action='$php_self'>

				<table align='center'>
				<tr>
					<td class='fond_menu2' colspan='2' style='padding:4px 20px 4px 20px;'>
						<font class='Texte_menu2'>
							<b>&#8226;&nbsp;&nbsp;Donn�es du paragraphe</b>
						</font>
					</td>
				</tr>
				<tr>
					<td class='td-gauche fond_menu2'>
						<font class='Texte_menu2'><b>Texte du paragraphe :</b></font>
					</td>
					<td class='td-droite fond_menu'>
						<font class='Texte_menu'><i>Les adresses http(s):// seront automatiquement transform�es en liens HTML</i></font>
						<br><textarea  name='new_para' rows='10' cols='85' class='input'>$texte</textarea>
					</td>
				</tr>
				<tr>
					<td class='td-gauche fond_menu2'>
						<font class='Texte_menu2'><b>Taille du texte (en points) : </b></font>
					</td>
					<td class='td-droite fond_menu'>
						<input type='text' name='taille' size='10' malength='2' value='$current_taille'>
					</td>
				</tr>
				<tr>
					<td class='td-gauche fond_menu2'>
						<font class='Texte_menu2'><b>Alignement du texte :</b></font>
					</td>
					<td class='td-droite fond_menu'>
						<font class='Texte_menu'>
							A gauche <input type='radio' name='alignement' value='0' $c0>
							&nbsp;&nbsp;Centr&eacute <input type='radio' name='alignement' value='1' $c1>
							&nbsp;&nbsp;A droite <input type='radio' name='alignement' value='2' $c2>
							&nbsp;&nbsp;Justifi� <input type='radio' name='alignement' value='3' $c3>
						</font>
					</td>
				</tr>
				<tr>
					<td class='td-gauche fond_menu2'>
						<font class='Texte_menu2'><b>Afficher en gras ?</b></font>
					</td>
					<td class='td-droite fond_menu'>
						<font class='Texte_menu'>
							Oui <input type='radio' name='gras' value='1' $gras_1>
							&nbsp;&nbsp;Non <input type='radio' name='gras' value='0' $gras_0>
						</font>
					</td>
				</tr>
				<tr>
					<td class='td-gauche fond_menu2'>
						<font class='Texte_menu2'><b>Afficher en italique ?</b></font>
					</td>
					<td class='td-droite fond_menu'>
						<font class='Texte_menu'>
							Oui <input type='radio' name='italique' value='1' $italique_1>
							&nbsp;&nbsp;Non <input type='radio' name='italique' value='0' $italique_0>
						</font>
					</td>
				</tr>
				</table>

				<div class='centered_icons_box'>
					<a href='index.php' target='_self' class='lien2'><img src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Retour' border='0'></a>
					<input type='image' src='$__ICON_DIR/button_ok_32x32_fond.png' alt='Valider' name='valider' value='Valider'>
					<form>
				</div>\n");

		db_close($dbr);
	?>

</div>
<?php
	pied_de_page();
?>
</body></html>
