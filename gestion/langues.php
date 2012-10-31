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

	if(!in_array($_SESSION["niveau"], array("$__LVL_SCOL_MOINS","$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
	{
		header("Location:$__MOD_DIR/gestion/noaccess.php");
		exit();
	}

	// Condition : la fiche doit �tre verrouill�e ou �tre une fiche manuelle
	if((!isset($_SESSION["tab_candidat"]["lock"]) || $_SESSION["tab_candidat"]["lock"]!=1) && $_SESSION["tab_candidat"]["manuelle"]!=1)
	{
		header("Location:edit_candidature.php");
		exit;
	}

	// identifiant de l'�tudiant
	$candidat_id=$_SESSION["candidat_id"];

	$dbr=db_connect();

	// Verrouillage exclusif
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

	if(isset($_GET["la_id"]) && is_numeric($_GET["la_id"])) // modification d'un �l�ment existant : l'identifiant est en param�tre
		$_SESSION["la_id"]=$la_id=$_GET["la_id"];
	elseif(isset($_GET["suppr"]) && is_numeric($_GET["suppr"]))
	{
		$la_id=$_GET["suppr"];
		$result=db_query($dbr,"SELECT * FROM $_DB_langues WHERE $_DBC_langues_id='$la_id' AND $_DBC_langues_candidat_id='$candidat_id'");
		if(db_num_rows($result))
			db_query($dbr,"DELETE FROM $_DB_langues WHERE $_DBC_langues_id='$la_id' AND $_DBC_langues_candidat_id='$candidat_id'");

		db_free_result($result);

		write_evt($dbr, $__EVT_ID_G_LANG, "Suppression langue", $candidat_id, $la_id);

		db_close($dbr);

		header("Location:edit_candidature.php");
		exit();
	}
	elseif(isset($_SESSION["la_id"]))
		$la_id=$_SESSION["la_id"];
	else // pas de param�tre : ajout d'une candidature ext�rieure
		$la_id=0;

	if(isset($_POST["go"]) || isset($_POST["go_x"])) // validation du formulaire
	{
		$langue=str_replace("'","''", stripslashes(trim($_POST["langue"])));
		$langue_libre=str_replace("'","''", stripslashes(ucwords(mb_strtolower(trim($_POST["langue_libre"])))));

		if(empty($langue) && !empty($langue_libre)) // on v�rifie que ce qui a �t� saisi dans le champ libre n'est pas dans la liste
		{
			$result=db_query($dbr,"SELECT $_DBC_liste_langues_langue FROM $_DB_liste_langues WHERE $_DBC_liste_langues_langue ILIKE '$langue_libre'");

			if(db_num_rows($result)) // 1 seul r�sultat si �a donne quelque chose
				list($langue)=db_fetch_row($result,0);
			else
				$langue=$langue_libre;

			db_free_result($result);
		}
		
		// niveau
		if(isset($_POST["lu"])) $lu=1; else $lu=0;
		if(isset($_POST["ecrit"])) $ecrit=1; else $ecrit=0;
		if(isset($_POST["parle"])) $parle=1; else $parle=0;
		if(isset($_POST["maternelle"])) $maternelle=1; else $maternelle=0;

		$niveau="$lu|$ecrit|$parle|$maternelle";

		if(empty($langue) || $niveau=="0|0|0|0")
			$champ_vide=1;

		$nb_annees=trim($_POST["nb_annees"]);

		if(!isset($champ_vide))
		{
			if($la_id==0) // nouvelle langue
			{
				$new_id=db_locked_query($dbr, $_DB_langues, "INSERT INTO $_DB_langues VALUES('##NEW_ID##','$candidat_id','$langue','$niveau', '$nb_annees')");

				write_evt($dbr, $__EVT_ID_G_LANG, "Ajout langue '$langue'", $candidat_id, $la_id, "INSERT INTO $_DB_langues VALUES('$new_id','$candidat_id','$langue','$niveau', '$nb_annees')");

				db_close($dbr);
	
				header("Location:edit_candidature.php");
				exit();
			}
			else	// mise � jour d'une candidature ext�rieure existante
			{
				$req="UPDATE $_DB_langues SET 	$_DBU_langues_langue='$langue',
															$_DBU_langues_niveau='$niveau',
															$_DBU_langues_annees='$nb_annees'
						WHERE $_DBU_langues_id='$la_id'
						AND $_DBU_langues_candidat_id='$candidat_id'";

				db_query($dbr, $req);

				write_evt($dbr, $__EVT_ID_G_LANG, "Modification langue : $langue", $candidat_id, $la_id, $req);

				db_close($dbr);
	
				header("Location:edit_candidature.php");
				exit();
			}
		}
	}

	if($la_id!=0)
	{
		// r�cup�ration des valeurs courantes
		$result=db_query($dbr,"SELECT $_DBC_langues_langue, $_DBC_langues_niveau, $_DBC_langues_annees
										FROM $_DB_langues WHERE $_DBC_langues_id='$la_id'");
		$rows=db_num_rows($result);

		if(!$rows) // erreur
		{
			db_free_result($result);
			db_close($dbr);
			header("Location:login.php");
			exit();
		}
		else
		{
			list($cur_langue, $cur_niveau, $cur_nb_annees)=db_fetch_row($result,0);

			$niveau_array=explode("|",$cur_niveau);
			$lu=$niveau_array[0];
			$ecrit=$niveau_array[1];
			$parle=$niveau_array[2];

			// Compatibilit�
			if(isset($niveau_array[3]))
				$maternelle=$niveau_array[3];
			else
				$maternelle=0;

			if($cur_nb_annees=="0")
				$cur_nb_annees="";

			db_free_result($result);
		}
	}
	else // nouvelle langue : initialisation des valeurs
		$cur_langue=$lu=$ecrit=$parle=$maternelle=$cur_nb_annees="";

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

				<form action='$php_self' method='POST' name='form1'>\n");

		titre_page_icone("Niveau en langues", "edu_languages_32x32_fond.png", 15, "L");

		if(isset($champ_vide))
			message("Formulaire incomplet : les champs en gras sont <u>obligatoires</u>", $__ERREUR);
		else
			message("Les champs en gras sont <u>obligatoires</u>", $__WARNING);
	?>

	<table align='center'>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Langue</b></font>
		</td>
		<td class='td-droite fond_menu'>
			<select name='langue' size='1'>
			<?php
				$result=db_query($dbr,"SELECT $_DBC_liste_langues_langue FROM $_DB_liste_langues ORDER BY lower($_DBC_liste_langues_langue) ASC");
				$rows=db_num_rows($result);

				if(isset($langue_libre) && $langue_libre!="")
					$cur_langue=$langue_libre;
				elseif(isset($langue))
					$cur_langue=$langue;

				if(empty($cur_langue))
					print("<option value='' selected=1></option>");
				else
					print("<option value=''></option>");

				$value2=preg_replace("/_/","",htmlspecialchars(stripslashes($cur_langue), ENT_QUOTES));

				for($i=0; $i<$rows; $i++)
				{
					list($langue)=db_fetch_row($result,$i);
					$value=htmlspecialchars($langue, ENT_QUOTES);

					if(isset($value2) && !strcasecmp($value,$value2))
					{
						$selected="selected=1";
						$langue_liste=1; // permet de court-circuiter le champ libre
					}
					else
						$selected="";
					print("<option value='$value' $selected>$value</option>\n");
				}
				db_free_result($result);
			?>
			</select>
			<font class='Texte_menu'>&nbsp;&nbsp;Si la langue n'est pas dans la liste : </font>
			<input type='text' name='langue_libre' value='<?php if(isset($cur_langue) && !isset($langue_liste)) echo htmlspecialchars(preg_replace("/_/","",stripslashes($cur_langue)),ENT_QUOTES); ?>' size="25" maxlength="128">
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Niveau dans cette langue</b></font>
		</td>
		<td class='td-droite fond_menu'>
			<font class='Texte_menu'>
			<?php
				if(isset($lu) && $lu==1)
					$lu_checked="checked='1'";
				else
					$lu_checked="";

				if(isset($ecrit) && $ecrit==1)
					$ecrit_checked="checked='1'";
				else
					$ecrit_checked="";

				if(isset($parle) && $parle==1)
					$parle_checked="checked='1'";
				else
					$parle_checked="";

				if(isset($maternelle) && $maternelle==1)
					$maternelle_checked="checked='1'";
				else
					$maternelle_checked="";

				print("<input type='checkbox' name='lu' value='1' $lu_checked>Lu
							&nbsp;&nbsp;<input type='checkbox' name='ecrit' value='1' $ecrit_checked>Ecrit
							&nbsp;&nbsp;<input type='checkbox' name='parle' value='1' $parle_checked>Parl�
							&nbsp;&nbsp;<input type='checkbox' name='maternelle' value='1' $maternelle_checked>Langue Maternelle");
			?>
			</font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_menu2'><b>Combien d'ann�es la langue a-t'elle �t� �tudi�e ?</b></font>
		</td>
		<td class='td-droite fond_menu'>
			<input type='text' name='nb_annees' value='<?php if(isset($cur_nb_annees)) echo htmlspecialchars(stripslashes($cur_nb_annees),ENT_QUOTES); ?>' size="25" maxlength="128">
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
	document.form1.langue.focus()
</script>
</body></html>
