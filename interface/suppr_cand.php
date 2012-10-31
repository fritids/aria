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

	// TODO QUESTION 13 mars 2006 : permettre la suppression apr�s verrouillage ?

/*
	if(!isset($_SESSION["lock"]) || $_SESSION["lock"]==1)
	{
		session_write_close();
		header("Location:precandidatures.php");
		exit();
	}
*/

	if(!isset($_SESSION["authentifie"]))
	{
		session_write_close();
		header("Location:../index.php");
		exit();
	}
	else
		$candidat_id=$_SESSION["authentifie"];
	
	// identifiant de la candidature
	if(isset($_GET['p']) && -1!=($params=get_params($_GET['p'])))
	{
		// cand_id
		if(isset($params["cand_id"]) && ctype_digit($params["cand_id"]))
			$_SESSION["cand_id"]=$cand_id=$params["cand_id"];
		else
		{
			session_write_close();
			header("Location:index.php");
			exit;
		}

		// suppression ou annulation ?

		if(isset($params["suppr"]) && ctype_digit($params["suppr"]))
			$_SESSION["suppr"]=$suppr=$params["suppr"];
		elseif(isset($params["annuler"]) && ctype_digit($params["annuler"]))
			$_SESSION["annuler"]=$annuler=$params["annuler"];
		else
		{
			session_write_close();
			header("Location:index.php");
			exit;
		}

		// parametre de groupe pour les candidatures � choix multiples
		if(isset($params["groupe"]) && $params["groupe"]!=-1)
			$_SESSION["groupe"]=$params["groupe"];
		else
			unset($_SESSION["groupe"]);

		if(isset($params["ordre_spec"]) && $params["ordre_spec"]!=-1)
			$_SESSION["ordre_spec"]=$params["ordre_spec"];
		else
			unset($_SESSION["ordre_spec"]);
	}
	elseif(isset($_SESSION["cand_id"]))
		$cand_id=$_SESSION["cand_id"];
	else
	{
		session_write_close();
		header("Location:precandidatures.php");
		exit;
	}
	
	// validation du formulaire
	if(isset($_POST["go"]) || isset($_POST["go_x"]))
	{
		$o=$_POST["o"];

		$dbr=db_connect();

		if(isset($_SESSION["suppr"]))
		{
			// pour la recherche sur l'id des inscriptions
			if(isset($_SESSION["groupe"]))
			{
				$groupe=$_SESSION["groupe"];
				$ordre_spec=$_SESSION["ordre_spec"];
				$cond_groupe="AND $_DBC_cand_groupe_spec='$groupe' AND $_DBC_cand_ordre_spec>'$ordre_spec'";
			}
			else
				$cond_groupe="AND $_DBC_cand_ordre>'$o'";

			// On r�ordonne les candidatures restantes

			if(isset($groupe)) // suppression d'une formation au sein d'une candidature � choix multiples, on ne d�cale que l'ordre des sp�cialit�s
			{
				db_query($dbr,"UPDATE $_DB_cand SET $_DBU_cand_ordre_spec=$_DBU_cand_ordre_spec-1
										WHERE $_DBU_cand_candidat_id='$candidat_id'
										AND	$_DBU_cand_ordre_spec>'$ordre_spec'
										AND   $_DBU_cand_groupe_spec='$groupe'
										AND	$_DBU_cand_propspec_id IN (SELECT $_DBC_propspec_id FROM $_DB_propspec
																			  WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]')
										AND 	$_DBU_cand_periode='$__PERIODE'");

				// au cas o� on supprimerait ici le dernier �l�ment d'un groupe de sp�cialit�s, il faut d�caler
				// les candidatures suivantes
				if(1==db_num_rows(db_query($dbr,"SELECT * FROM $_DB_cand WHERE $_DBC_cand_candidat_id='$candidat_id'
																AND $_DBC_cand_groupe_spec='$groupe'
																AND $_DBC_cand_propspec_id IN (SELECT $_DBC_propspec_id FROM $_DB_propspec
																									WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]')
																AND $_DBC_cand_periode='$__PERIODE'")))
				{
					db_query($dbr,"UPDATE $_DB_cand SET $_DBU_cand_ordre=$_DBU_cand_ordre-1
										WHERE $_DBU_cand_candidat_id='$candidat_id'
										AND	$_DBU_cand_ordre>'$o'
										AND	$_DBU_cand_propspec_id IN (SELECT $_DBC_propspec_id FROM $_DB_propspec
																			  WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]')
										AND 	$_DBU_cand_periode='$__PERIODE'");
				}
			}
			else
				db_query($dbr,"UPDATE $_DB_cand SET $_DBU_cand_ordre=$_DBU_cand_ordre-1
										WHERE $_DBU_cand_candidat_id='$candidat_id'
										AND	$_DBU_cand_ordre>'$o'
										AND	$_DBU_cand_propspec_id IN (SELECT $_DBC_propspec_id FROM $_DB_propspec
																			   WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]')
										AND 	$_DBU_cand_periode='$__PERIODE'");

			// Nom de la formation, pour l'historique
			$res_formation=db_query($dbr,"SELECT $_DBC_annees_annee, $_DBC_specs_nom_court, $_DBC_propspec_finalite
														FROM $_DB_propspec, $_DB_annees, $_DB_specs, $_DB_cand
													WHERE $_DBC_cand_propspec_id=$_DBC_propspec_id 
													AND $_DBC_cand_id='$cand_id'
													AND $_DBC_annees_id=$_DBC_propspec_annee
													AND $_DBC_specs_id=$_DBC_propspec_id_spec");

			if(db_num_rows($res_formation)) // inqui�tant si Faux
			{
				list($nom_annee, $nom_spec, $finalite)=db_fetch_row($res_formation, 0);

				$formation=$nom_annee=="" ? "$nom_spec" : "$nom_annee $nom_spec";
				$formation=$tab_finalite[$finalite]=="" ? $formation : "$formation $tab_finalite[$finalite]";

				write_evt("", $__EVT_ID_C_PREC, "Suppression candidature : $formation", $candidat_id, $cand_id);
			}

			db_free_result($res_formation);

			// suppression de la candidature
			db_query($dbr,"DELETE FROM $_DB_cand WHERE $_DBC_cand_id='$cand_id'");
			unset($_SESSION["array_lock"][$cand_id]);
		}
		elseif(isset($_SESSION["annuler"]))
		{
			// Nom de la formation, pour l'historique
			$res_formation=db_query($dbr,"SELECT $_DBC_annees_annee, $_DBC_specs_nom_court, $_DBC_propspec_finalite
														FROM $_DB_propspec, $_DB_annees, $_DB_specs, $_DB_cand
													WHERE $_DBC_cand_propspec_id=$_DBC_propspec_id 
													AND $_DBC_cand_id='$cand_id'
													AND $_DBC_annees_id=$_DBC_propspec_annee
													AND $_DBC_specs_id=$_DBC_propspec_id_spec");

			if(db_num_rows($res_formation)) // inqui�tant si Faux
			{
				list($nom_annee, $nom_spec, $finalite)=db_fetch_row($res_formation, 0);

				$formation=$nom_annee=="" ? "$nom_spec" : "$nom_annee $nom_spec";
				$formation=$tab_finalite[$finalite]=="" ? $formation : "$formation $tab_finalite[$finalite]";

				write_evt("", $__EVT_ID_C_PREC, "Annulation candidature : $formation", $candidat_id, $cand_id);
			}

			db_free_result($res_formation);

			// changement de statut
			// TODO : envoyer les mails � la scol et au candidat
			db_query($dbr,"UPDATE $_DB_cand SET $_DBU_cand_statut='$__PREC_ANNULEE' WHERE $_DBU_cand_id='$cand_id'");
		}

		db_close($dbr);

		unset($_SESSION["cand_id"]);
		
		session_write_close();
		header("Location:precandidatures.php");
		exit;
	}
	else // v�rification de l'id pass�e en param�tre
	{
		$dbr=db_connect()	;
		
		$result=db_query($dbr,"SELECT $_DBC_cand_ordre, $_DBC_annees_annee, $_DBC_specs_nom, $_DBC_propspec_finalite
											FROM $_DB_cand, $_DB_annees, $_DB_specs, $_DB_propspec
										WHERE $_DBC_cand_propspec_id=$_DBC_propspec_id
										AND $_DBC_propspec_annee=$_DBC_annees_id
										AND $_DBC_propspec_id_spec=$_DBC_specs_id
										AND $_DBC_cand_id='$cand_id'");
		$rows=db_num_rows($result);
		
		if($rows)				
		{
			list($ordre, $nom_annee, $nom_specialite, $finalite)=db_fetch_row($result,0);
			db_free_result($result);
		}
		else
		{
			db_free_result($result);
			db_close($dbr);
			unset($_SESSION["cand_id"]);

			session_write_close();
			header("Location:index.php");
			exit;
		}
		
		db_close($dbr);
	}
	
	en_tete_candidat();
	menu_sup_candidat($__MENU_FICHE);
?>

<div class='main'>
	<?php
		if(isset($suppr))
		{
			$action="supprimer";
			titre_page_icone(ucfirst($action) . " une pr�candidature", "trashcan_full_32x32_slick_fond.png", 30, "L");
		}
		else
		{
			$action="annuler";
			titre_page_icone(ucfirst($action) . " une pr�candidature", "trashcan_full_32x32_slick_fond.png", 30, "L");
		}

		print("<form action='$php_self' method='POST' name='form1'>
					<input type='hidden' name='o' value='$ordre'>

					<div class='centered_box'>
						<font class='Texte3'>Pr�candidature : $nom_annee $nom_specialite $tab_finalite[$finalite]</font>
					</div>");

		if(isset($annuler))
			message("<center>Une annulation sera automatiquement notifi�e � la Scolarit�.
						<br>Une pi�ce sera �galement jointe � votre dossier.</center>", $__WARNING);

		message("Etes-vous s�r de vouloir $action cette pr�candidature ?", $__QUESTION);
	?>

	<div class='centered_icons_box'>
		<a href='precandidatures.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Annuler' border='0'></a>
		<input type="image" src="<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>" alt="Confirmer" name="go" value="Confirmer">
		</form>
	</div>

</div>
<?php
		pied_de_page_candidat();
?>
</body></html>
