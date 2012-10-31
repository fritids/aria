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


	if(!isset($_SESSION["authentifie"]))
	{
		session_write_close();
		header("Location:../index.php");
		exit();
	}

	if(!isset($_SESSION["propspec_id"]) || !ctype_digit($_SESSION["propspec_id"]))
	{
		session_write_close();
		header("Location:precandidatures.php");
		exit();
	}	

	$candidat_id=$_SESSION["authentifie"];

	// On peut arriver sur cette page pour deux raisons :
	// - soit des infos particuli�res sont attach�es � la formation
	// - soit des renseignements compl�mentaires sont demand�s, et on rappelle au candidat de les remplir

	$dbr=db_connect();

	// information
	$result=db_query($dbr, "SELECT $_DBC_propspec_info FROM $_DB_propspec WHERE $_DBC_propspec_id='$_SESSION[propspec_id]'");

	// Renseignements compl�mentaires
	$count=db_num_rows(db_query($dbr, "SELECT * FROM $_DB_dossiers_ef
												  WHERE $_DBC_dossiers_ef_propspec_id='$_SESSION[propspec_id]'"));

	if(db_num_rows($result))
	{
		list($info_formation)=db_fetch_row($result, 0);
		db_free_result($result);
/*
		// Si l'information est vide et qu'il n'y a aucun renseignements compl�mentaires : on n'affiche que le r�sum�
		if(trim($info_formation)=="" && !$count)
		{		
			db_close($dbr);
			
			session_write_close();
			header("Location:precandidatures.php");
			exit();
		}
	}
	else
	{
		db_free_result($result);
		db_close($dbr);
		
		session_write_close();
		header("Location:precandidatures.php");
		exit();
	}
*/
	}
	else
		$info_formation="";
	
	
	en_tete_candidat();
	menu_sup_candidat($__MENU_FICHE);
?>

<div class='main'>
	<?php
		titre_page_icone("R�sum� de votre voeu et informations sp�cifiques", "help2_32x32_fond.png", 30, "L");

		$message="";

		$result=db_query($dbr,"SELECT $_DBC_annees_annee, $_DBC_annees_annee_longue, $_DBC_specs_nom, $_DBC_propspec_frais, $_DBC_mentions_nom,
												$_DBC_propspec_finalite
											FROM $_DB_propspec, $_DB_annees, $_DB_specs, $_DB_mentions
										WHERE $_DBC_propspec_id_spec=$_DBC_specs_id
										AND $_DBC_propspec_annee=$_DBC_annees_id
										AND $_DBC_propspec_id='$_SESSION[propspec_id]'
										AND $_DBC_mentions_id=$_DBC_specs_mention_id");

		$rows=db_num_rows($result);

		if($rows)
		{
			list($annee, $annee_longue, $spec_nom, $frais_dossiers, $mention_nom, $finalite)=db_fetch_row($result,0);

			$nom_finalite=$tab_finalite[$finalite];

			$nom_formation=($annee=="") ? "$spec_nom $nom_finalite" : "$annee_longue - $spec_nom $nom_finalite";
		}
	?>

	<table align='center' style='padding:0px 30px 30px 30px;'>
	<tr>
		<td class='td-complet fond_menu2' colspan='2' style='padding:4px;'>
			<font class='Texte_menu2'><b>Vous avez s�lectionn� la formation suivante : </b></font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu'>
			<font class='Texte'><strong>Mention :</strong></font>
		</td>
		<td class='td-droite fond_menu'>
			<font class='Texte'><?php echo $mention_nom; ?></font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu'>
			<font class='Texte'><strong>Ann�e :</strong></font>
		</td>
		<td class='td-droite fond_menu'>
			<font class='Texte'><?php echo $nom_formation; ?></font>
		</td>
	</tr>
	<?php
	   if(array_key_exists("array_formations_groupe", $_SESSION) && is_array($_SESSION["array_formations_groupe"]) && count($_SESSION["array_formations_groupe"]))
	   {
	      $cnt=count($_SESSION["array_formations_groupe"]);
	      
	      if($cnt==1)
            $form_txt="la formation suivante a �t� automatiquement ajout�e";
			else
			   $form_txt="les formations suivantes ont �t� automatiquement ajout�es";
	?>
	<tr>
		<td class='td-gauche fond_menu'>
	      <font class='Texte'><strong>Choix multiples</strong></font>
	   </td>
	   <td class='td-droite fond_menu'>
         <font class='Texte'>
           <strong>S'agissant d'une formation � choix multiples, <?php echo $form_txt; ?> � votre fiche :</strong>
           
           <?php
              foreach($_SESSION["array_formations_groupe"] as $groupe_propspec_id => $groupe_formations_spec_nom)
                 print("<br>- $groupe_formations_spec_nom\n");
			  ?>
			  <br><br>
			  Vous pourrez supprimer ou r�ordonner ces voeux dans le menu Pr�candidatures.
     </td>
	</tr>
	<?php
   	}

		if($frais_dossiers)
		{
	?>

	<tr>
		<td class='td-gauche fond_menu'>
			<font class='Texte'><strong>Frais de dossiers :</strong></font>
		</td>
		<td class='td-droite fond_menu'>
			<font class='Texte'><?php echo $frais_dossiers; ?> Euros</font>
		</td>
	</tr>
	<?php
		}

		if(isset($_SESSION["info_lockdate"]))
		{
	?>

	<tr>
		<td class='td-gauche fond_menu'>
			<font class='Texte'><strong>Date de verrouillage :</strong></font>
		</td>
		<td class='td-droite fond_menu'>
			<font class='Texte'>
				<?php echo date_fr("j F", $_SESSION["info_lockdate"]); ?>
				<br>Si votre fiche est compl�te (v�rifiez bien tous les menus de votre fiche), vous recevrez la liste des justificatifs � cette date.
			</font>
		</td>
	</tr>
	<?php
		}

		if(trim($info_formation)!="")
		{
	?>
	<tr>
		<td class='td-gauche fond_menu'>
			<font class='Texte'><strong>Autres :</strong></font>
		</td>
		<td class='td-droite fond_menu' style='white-space:normal;'>
			<font class='Textebleu'><?php echo nl2br($info_formation); ?></font>
		</td>
	</tr>

	<?php
		}
	?>

	</table>

	<?php
		// Renseignements compl�mentaires demand�s : on affiche un rappel
		if($count)
		{
			if(isset($_SESSION["info_lockdate"]))
				$date_verrouillage=date_fr("j F", $_SESSION["info_lockdate"]) . " (date de verrouillage de ce voeu)";
			else
				$date_verrouillage="verrouillage de ce voeu";

			message("<center>
							Attention : des <b>renseignements compl�mentaires</b> sont demand�s pour cette formation.
							<br>N'oubliez pas de compl�ter les formulaires dans le <b>menu \"6 - Autres renseignements\" avant le $date_verrouillage</b> !
						</center>", $__WARNING);
		}

		db_close($dbr);
	?>

	<div class='centered_icons_box'>
		<a href='precandidatures.php' target='_self'><img src='<?php echo "$__ICON_DIR/forward_32x32_fond.png"; ?>' alt='Continuer' border='0'></a>
	</div>
</div>
<?php
	pied_de_page_candidat();
?>
</body></html>

