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
	session_name("preinsc");
	session_start();

	include "../configuration/aria_config.php";
	include "$__INCLUDE_DIR_ABS/vars.php";
	include "$__INCLUDE_DIR_ABS/fonctions.php";
	include "$__INCLUDE_DIR_ABS/db.php";

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	if(!isset($_SESSION["lock"]) || $_SESSION["lock"]==1)
	{
		session_write_close();
		header("Location:precandidatures.php");
		exit();
	}

	if(!isset($_SESSION["authentifie"]))
	{
		session_write_close();
		header("Location:../index.php");
		exit();
	}

	$dbr=db_connect();

	$candidat_id=$_SESSION["authentifie"];

	if(isset($_GET["p"]) && -1!=($params=get_params($_GET['p']))) // modification d'un �l�ment existant : l'identifiant est en param�tre
	{
		if(isset($params["la_id"]) && is_numeric($params["la_id"]))
			$_SESSION["la_id"]=$la_id=$params["la_id"];
		elseif(isset($params["suppr"]) && is_numeric($params["suppr"]))
		{
			$la_id=$params["suppr"];

			if(db_num_rows(db_query($dbr,"SELECT * FROM $_DB_langues WHERE $_DBC_langues_id='$la_id' AND $_DBC_langues_candidat_id='$candidat_id'")))
				db_query($dbr,"DELETE FROM $_DB_langues WHERE $_DBC_langues_id='$la_id' AND $_DBC_langues_candidat_id='$candidat_id'");

			session_write_close();
			header("Location:precandidatures.php");
			exit();
		}
		else
			$la_id=0;
	}
	elseif(isset($_SESSION["la_id"]))
		$la_id=$_SESSION["la_id"];
	else // pas de param�tre : ajout
		$la_id=0;

	if(isset($_POST["valider"]) || isset($_POST["valider_x"])) // validation du formulaire
	{
		$langue=$_POST["langue"];
		$langue_libre=ucwords(mb_strtolower(trim($_POST["langue_libre"])));
	
		if(empty($langue) && !empty($langue_libre)) // on v�rifie que ce qui a �t� saisi dans le champ libre n'est pas dans la liste
		{
			$result=db_query($dbr,"SELECT $_DBC_liste_langues_langue FROM $_DB_liste_langues
																	WHERE $_DBC_liste_langues_langue ILIKE '$langue_libre'");

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
				$new_id=db_locked_query($dbr, $_DB_langues, "INSERT INTO $_DB_langues VALUES('##NEW_ID##','$candidat_id','$langue','$niveau','$nb_annees')");
				db_close($dbr);
	
				session_write_close();
				header("Location:precandidatures.php");
				exit();
			}
			else	// mise � jour d'une valeur existante
			{
				db_query($dbr,"UPDATE $_DB_langues SET	$_DBU_langues_langue='$langue',
																	$_DBU_langues_niveau='$niveau',
																	$_DBU_langues_annees='$nb_annees'
									WHERE $_DBU_langues_id='$la_id' AND $_DBU_langues_candidat_id='$candidat_id'");

				db_close($dbr);
	
				session_write_close();
				header("Location:precandidatures.php");
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
			
			session_write_close();
			header("Location:../index.php");
			exit();
		}
		else
		{
			list($cur_langue,$cur_niveau, $cur_nb_annees)=db_fetch_row($result,0);

			$niveau_array=explode("|",$cur_niveau);
			$lu=$niveau_array[0];
			$ecrit=$niveau_array[1];
			$parle=$niveau_array[2];

			// Compatibilit�
			if(isset($niveau_array[3]))
				$maternelle=$niveau_array[3];
			else
				$maternelle=0;

			db_free_result($result);
		}
	}
	else // nouvelle langue : initialisation des valeurs
		$cur_langue=$lu=$ecrit=$parle=$maternelle=$cur_nb_annees="";

	en_tete_candidat();
	menu_sup_candidat($__MENU_FICHE);
?>

<div class='main'>
	<?php
		titre_page_icone("Votre niveau en langues", "edu_languages_32x32_fond.png", 30, "L");

		if(isset($champ_vide))
			message("Formulaire incomplet : tous les champs sont <u>obligatoires</u>", $__ERREUR);
		else
			message("Tous les champs sont obligatoires", $__WARNING);

		print("<form action='$php_self' method='POST' name='form1'>\n");
	?>
	
	<table style="margin-left:auto; margin-right:auto;">
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Langue</b></font>
		</td>
		<td class='td-droite fond_menu'>
			<select name='langue' size='1'>
			<?php
				$result=db_query($dbr,"SELECT $_DBC_liste_langues_langue FROM $_DB_liste_langues
																		ORDER BY lower($_DBC_liste_langues_langue) ASC");

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
			<input type='text' name='langue_libre' value='<?php if(isset($cur_langue) && !isset($langue_liste)) echo htmlspecialchars(preg_replace("/_/","",stripslashes($cur_langue)),ENT_QUOTES); ?>' size="25" maxlength="128">&nbsp;&nbsp;
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte'><b>Votre niveau dans cette langue</b></font>
		</td>
		<td class='td-droite fond_menu' style="text-align:left;">
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
		
				print("<div style='display:inline; margin: 0px; padding:0px'>
							<input type='checkbox' name='lu' value='1' $lu_checked>Lu
							&nbsp;&nbsp;<input type='checkbox' name='ecrit' value='1' $ecrit_checked>Ecrit
							&nbsp;&nbsp;<input type='checkbox' name='parle' value='1' $parle_checked>Parl�
							&nbsp;&nbsp;<input type='checkbox' name='maternelle' value='1' $maternelle_checked>Langue Maternelle
						</div>");
			?>
			</font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte'><b>Combien d'ann�es l'avez vous �tudi�e ?</b></font>
		</td>
		<td class='td-droite fond_menu'>
			<input type='text' name='nb_annees' value='<?php if(isset($cur_nb_annees)) echo htmlspecialchars(stripslashes($cur_nb_annees),ENT_QUOTES); ?>' size="25" maxlength="128">
		</td>
	</tr>
	</table>	
	
	<div class='centered_icons_box'>
		<a href='precandidatures.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Retour' border='0'></a>
		<input type="image" src="<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>" alt="Valider" name="valider" value="Valider">
		</form>
	</div>

</div>

<?php
	db_close($dbr);
	pied_de_page_candidat();
?>

<script language="javascript">
	document.form1.langue.focus()
</script>

</body></html>
