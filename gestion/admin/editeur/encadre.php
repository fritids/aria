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

	// Ajouter / modifier un encadr�

	if(isset($_SESSION["lettre_id"]))
		$lettre_id=$_SESSION["lettre_id"];
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
	elseif(isset($_GET["o"])) // Modification
	{
		$_SESSION["ordre"]=$ordre=$_GET["o"];

		$action="Modifier";

		// R�cup�ration des infos actuelles
		$result=db_query($dbr,"SELECT $_DBC_encadre_texte, $_DBC_encadre_txt_align FROM $_DB_encadre
															WHERE $_DBC_encadre_lettre_id='$lettre_id'
															AND $_DBC_encadre_ordre='$ordre'");
		$rows=db_num_rows($result);
		if($rows)
		{
			list($texte,$alignement)=db_fetch_row($result,0);
			db_free_result($result);
		}
		else
		{
			db_close($dbr);
			header("Location:editeur.php");
			exit();
		}
	}

	if(isset($_SESSION["ajout"]) && $_SESSION["ajout"]==1)
		$action="Ajouter";
	else
		$action="Modifier";

	// section ex�cut�e lorsque le formulaire est valid�
	if(isset($_POST["go_valider"]) || isset($_POST["go_valider_x"]))
	{
		$texte=trim($_POST['new_encadre']);
		$alignement=$_POST['alignement'];

		// le nouveau texte est ok, on le modifie dans la table "encadre"
		// et on modifie la date de derni�re modif de l'article

		if(!isset($_SESSION["ajout"]))
			db_query($dbr,"UPDATE $_DB_encadre SET $_DBU_encadre_texte='$texte',
																$_DBU_encadre_txt_align='$alignement'
								WHERE $_DBU_encadre_lettre_id='$lettre_id'
								AND $_DBU_encadre_ordre='$_SESSION[ordre]'");
		else
		{
			if($_SESSION["ordre"]!=$_SESSION["ordre_max"]) // On n'ins�re pas l'�l�ment en dernier : d�callage
			{
				// 1 - Reconstruction des �l�ments (comme pour la suppression)
				$a=get_all_elements($dbr, $lettre_id);
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
			db_query($dbr,"INSERT INTO $_DB_encadre VALUES ('$lettre_id', '$_SESSION[ordre]', '$texte', $alignement)");
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
		titre_page_icone("$action un texte encadr�", "abiword_32x32_fond.png", 30, "L");

		if(isset($encadre_pas_clean))
			message("<center>Erreur : le texte contient des caract�res non autoris�s.
						<br>Les caract�res autoris�s sont : a-z A-Z 0-9 - ' ! ? _ : . / @ ( ) les caract�res accentu�s, la virgule et l'espace.</center>", $__ERREUR);

		if(isset($alignement))
		{
			switch($alignement)
			{
				case 0: 	$c0="checked";
									$c1=$c2=$c3="";
									break;

				case 1: 	$c1="checked";
									$c0=$c2=$c3="";
									break;

				case 2: 	$c2="checked";
									$c0=$c1=$c3="";
									break;

				case 3: 	$c3="checked";
									$c0=$c1=$c2="";
									break;

				default: 	$c0="checked";
									$c1=$c2=$c3="";
									break;
			}
		}
		else
		{
			$c0="checked";
			$c1=$c2=$c3="";
		}
	?>

	<form method='post' action='<?php echo $php_self; ?>'>

	<table align='center'>
	<tr>
		<td colspan='2' class='fond_menu2' style='padding:4px 20px 4px 20px;'>
			<font class='Texte_menu2'>
				<b>&#8226;&nbsp;&nbsp;Donn�es de l'encadr�</b>
			</font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Nouveau texte :</b></font>
		</td>
		<td class='td-droite fond_menu'>
			<textarea  name='new_encadre' rows='10' cols='60' class='input'><?php if(isset($texte)) echo htmlspecialchars($texte, ENT_QUOTES, $default_htmlspecialchars_encoding); ?></textarea>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Alignement du texte :</b></font>
		</td>
		<td class='td-droite fond_menu'>
			<font class='Texte_menu'>
				A gauche <input type='radio' name='alignement' value='0' <?php echo $c0; ?>>
				&nbsp;&nbsp;Centr� <input type='radio' name='alignement' value='1' <?php echo $c1; ?>>
				&nbsp;&nbsp;A droite <input type='radio' name='alignement' value='2' <?php echo $c2; ?>>
				&nbsp;&nbsp;Justifi� <input type='radio' name='alignement' value='3' <?php echo $c3; ?>>
			</font>
		</td>
	</tr>
	</table>

	<div class='centered_icons_box'>
		<a href='editeur.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Retour' border='0'></a>
		<input type='image' src='<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>' alt='Valider' name='go_valider' value='Valider'>
		</form>
	</div>

	<table cellpadding='2' align='center' style='padding-bottom:30px;'>
	<tr>
		<td class='fond_menu2' align='justify' colspan='2' style='padding:4px;'>
			<font class='Texte_menu2'><b>Les lettres peuvent s'adapter aux informations de chaque candidat(e) gr�ce aux macros suivantes : </b></font>
		</td>
	</tr>
	<tr>
		<td class='fond_menu' align='justify' colspan='2' style='padding:4px;'>
			<font class='Texte_menu'><b>Informations relatives au candidat : </b></font>
		</td>
	</tr>
	<tr>
		<td align='justify'><font class='Texte'><b>%Civilit�%</b></font></td>
		<td align='justify'><font class='Texte'>Civilit� du candidat (Monsieur, Madame, Mademoiselle - sensible aux majuscules)</font></td>
	</tr>
	<tr>
		<td align='justify'><font class='Texte'><b>%Civ%</b></font></td>
		<td align='justify'><font class='Texte'>Civilit� abbr�g�e (M., Mme., Mlle. - sensible aux majuscules)</font></td>
	</tr>
	<tr>
		<td align='justify'><font class='Texte'><b>%Nom%</b></font></td>
		<td align='justify'><font class='Texte'>Nom du candidat (sensible aux majuscules : %NOM%, %Nom%, %nom%)</font></td>
	</tr>
	<tr>
		<td align='justify'><font class='Texte'><b>%Pr�nom%</b></font></td>
		<td align='justify'><font class='Texte'>Pr�nom du candidat (m�me remarque)</font></td>
	</tr>
	<tr>
		<td align='justify'><font class='Texte'><b>%naissance%</b></font></td>
		<td align='justify'><font class='Texte'>Date de naissance du candidat</font></td>
	</tr>
	<tr>
		<td align='justify'><font class='Texte'><b>%ville_naissance%</b></font></td>
		<td align='justify'><font class='Texte'>Ville de naissance du candidat</font></td>
	</tr>
	<tr>
		<td align='justify'><font class='Texte'><b>%pays_naissance%</b></font></td>
		<td align='justify'><font class='Texte'>Pays de naissance du candidat</font></td>
	</tr>
	<tr>
		<td align='justify'><font class='Texte'><b>%cursus%</b></font></td>
		<td align='justify'><font class='Texte'>Cursus du candidat (limit� aux derni�res ann�es pour ne pas surcharger les lettres)</font></td>
	</tr>
	<tr>
		<td class='fond_menu' align='justify' colspan='2' style='padding:4px;'>
			<font class='Texte_menu'><b>Informations relatives � la formation : </b></font>
		</td>
	</tr>
	<tr>
		<td align='justify'><font class='Texte'><b>%ann�e_universitaire%</b></font></td>
		<td align='justify'><font class='Texte'>Ann�e universitaire concern�e par la lettre (par exemple : "<?php echo $__PERIODE . "/" . ($__PERIODE+1); ?>")</font></td>
	</tr>
	<tr>
		<td align='justify'><font class='Texte'><b>%Formation%</b></font></td>
		<td align='justify'><font class='Texte'>Formation demand�e par le candidat (sensible aux majuscules)</font></td>
	</tr>
	<tr>
		<td align='justify'><font class='Texte'><b>%Responsable%</b></font></td>
		<td align='justify'><font class='Texte'>Responsable de la Formation (champ compl�t� dans les propri�t�s de chaque formation)</font></td>
	</tr>
	<tr>
		<td align='justify'><font class='Texte'><b>%courriel_responsable%</b></font></td>
		<td align='justify'><font class='Texte'>Responsable de la Formation (champ compl�t� dans les propri�t�s de chaque formation)</font></td>
	</tr>
	<tr>
		<td class='fond_menu' align='justify' colspan='2' style='padding:4px;'>
			<font class='Texte_menu'><b>Informations relatives � la d�cision : </b></font>
		</td>
	</tr>
	<tr>
		<td align='justify'><font class='Texte'><b>%Transmission%</b></font></td>
		<td align='justify'><font class='Texte'>Nouvelle formation en cas de transfert de dossier (sensible aux majuscules)</font></td>
	</tr>
	<tr>
		<td align='justify'><font class='Texte'><b>%rang_attente%</b></font></td>
		<td align='justify'><font class='Texte'>Rang du candidat sur la liste compl�mentaire</font></td>
	</tr>
	<tr>
		<td align='justify'><font class='Texte'><b>%motifs%</b></font></td>
		<td align='justify'><font class='Texte'>Motifs de Refus, de Mise en Attente ou d'Admission sous R�serve</font></td>
	</tr>
	<tr>
		<td align='justify'><font class='Texte'><b>%entretien_date%</b></font></td>
		<td align='justify'><font class='Texte'>Date de la convocation � un entretien (compl�te, sans article, ex : "lundi 11 juillet <?php echo date("Y"); ?>")</font></td>
	</tr>
	<tr>
		<td align='justify'><font class='Texte'><b>%entretien_heure%</b></font></td>
		<td align='justify'><font class='Texte'>Heure de la convocation � un entretien (sans pr�position, ex : "11h30")</font></td>
	</tr>
	<tr>
		<td align='justify'><font class='Texte'><b>%entretien_salle%</b></font></td>
		<td align='justify'><font class='Texte'>Salle dans laquelle l'entretien aura lieu<br>(en fonction des valeurs entr�es dans les formulaires)</font></td>
	</tr>
	<tr>
		<td align='justify'><font class='Texte'><b>%entretien_lieu%</b></font></td>
		<td align='justify'><font class='Texte'>Adresse du b�timent (composante) dans lequel l'entretien aura lieu<br>(�galement en fonction des valeurs entr�es)</font></td>
	</tr>
	<tr>
		<td align='justify'><font class='Texte'><b>%code%</b></font></td>
		<td align='justify'><font class='Texte'>Code personnel pour l'inscription administrative (APOGEE)</font></td>
	</tr>
	<tr>
		<td class='fond_menu' align='justify' colspan='2' style='padding:4px;'>
			<font class='Texte_menu'><b>Autres : </b></font>
		</td>
	</tr>
	<tr>
		<td align='justify'><font class='Texte'><b>%signature%</b></font></td>
		<td align='justify'><font class='Texte'>Signature (d�pend de la configuration de la lettre et des donn�es par d�faut)</font></td>
	</tr>
	<tr>
		<td align='justify'><font class='Texte'><b>%date%</b></font></td>
		<td align='justify'><font class='Texte'>Date de g�n�ration de la lettre, en toutes lettres</font></td>
	</tr>
	<tr>
		<td align='justify'><font class='Texte'><b>%date_commission%</b></font></td>
		<td align='justify'><font class='Texte'>Date de la commission p�dagogique, en toutes lettres</font></td>
	</tr>
	<tr>
		<td align='justify'><font class='Texte'><b>%aaaa/bbbb%</b></font></td>
		<td align='justify'><font class='Texte'>En fonction du candidat : affiche "aaaa" si c'est un homme, "bbbb" si c'est une femme (exemple : %admis/admise%)</font>
	</tr>
	</table>
</div>
<?php
	pied_de_page();
	db_close($dbr);
?>
</body></html>
