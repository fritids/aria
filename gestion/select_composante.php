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

	verif_auth("$__GESTION_DIR/login.php");
	
	if(!in_array($_SESSION['niveau'], array("$__LVL_SUPER_RESP","$__LVL_ADMIN")) && (!isset($_SESSION["multi_composantes"]) || (isset($_SESSION["multi_composantes"]) && $_SESSION["multi_composantes"]!=1)))
	{
		$dbr=db_connect();
		write_evt("", $__EVT_ID_COMP, "Composante : acc�s refus�");
		db_close($dbr);

		header("Location:$__MOD_DIR/gestion/noaccess.php");
		exit();
	}

	$dbr=db_connect();

	// Acc�s direct � une composante depuis une autre page : param�tre chiffr� "co=comp_id"

	if(isset($_GET["p"]) && -1!=($params=get_params($_GET['p'])))
	{
		if(isset($params["co"]) && ctype_digit($params["co"]))
			$selected_comp_id=$params["co"];

		// V�rification des droits d'acc�s
		// TODO : cr�er une fonction pour cette v�rification
		if(db_num_rows(db_query($dbr, "SELECT $_DBC_acces_id FROM $_DB_acces
													WHERE ($_DBC_acces_composante_id='$selected_comp_id'
															AND $_DBC_acces_id='$_SESSION[auth_id]')
													OR $_DBC_acces_id IN (SELECT $_DBC_acces_comp_acces_id FROM $_DB_acces_comp
																				WHERE $_DBC_acces_comp_composante_id='$selected_comp_id'
																				AND $_DBC_acces_comp_acces_id='$_SESSION[auth_id]')")))
			$new_comp_id=$selected_comp_id;
	}
	elseif(isset($_POST["go"]) || isset($_POST["go_x"]))
		$new_comp_id=$_POST["comp_id"];

	if(isset($new_comp_id) && ctype_digit($new_comp_id))
	{
		// R�cup�ration des param�tres propres � cette composante
		$result=db_query($dbr, "SELECT $_DBC_composantes_nom, $_DBC_universites_nom, $_DBC_universites_img_dir,
												 $_DBC_universites_id, $_DBC_universites_css,
												 $_DBC_composantes_gestion_motifs, $_DBC_composantes_scolarite, $_DBC_composantes_affichage_decisions, 
												 $_DBC_composantes_avertir_decision
											FROM $_DB_composantes, $_DB_universites
										WHERE $_DBC_composantes_univ_id=$_DBC_universites_id
										AND $_DBC_composantes_id='$new_comp_id'");

		if(db_num_rows($result)) // normalement toujours vrai, l'id provenant d'un "select" ou d'un param�tre chiffr�
		{
			$_SESSION['comp_id']=$new_comp_id;

			list($_SESSION["composante"],
					$_SESSION["universite"],
					$_SESSION["img_dir"],
					$_SESSION["univ_id"],
					$_SESSION["css"],
					$_SESSION["gestion_motifs"],
					$_SESSION["adr_scol"],
					$_SESSION["affichage_decisions"],
					$_SESSION["avertir_decision"])=db_fetch_row($result, 0);

			db_free_result($result);

			write_evt($dbr, $__EVT_ID_COMP, "Changement de composante : $_SESSION[composante]", "", $_SESSION["comp_id"]);

			// Les candidats de cette composante sont-ils soumis � des entretiens ? (utile pour le menu et la gestion du calendrier)
			if(db_num_rows(db_query($dbr, "SELECT * FROM $_DB_propspec WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]'
																							AND $_DBC_propspec_entretiens='1'")))
				$_SESSION["composante_entretiens"]=1;
			else
				$_SESSION["composante_entretiens"]=0;

			db_close($dbr);

			$_SESSION['spec_filtre_defaut']="-1";
			$_SESSION["filtre_propspec"]="-1";
			$_SESSION["filtre_justif"]="-1";

			header("Location:index.php");
			exit();
		}
		else
			write_evt($dbr, $__EVT_ID_COMP, "Echec du changement de composante (id demand� : $new_comp_id)", "", "");
	}
	
	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>
<div class='main'>
	<?php
		titre_page_icone("S�lection de la composante de travail", "composante_32x32_fond.png", 30, "L");

		// Si le niveau n'est pas "admin", on impose l'universit�
		if($_SESSION['niveau']==$__LVL_SUPER_RESP)
		{
			$condition_univ="AND $_DBC_universites_id='$_SESSION[univ_id]'";
			$condition_multi="";
		}
		elseif($_SESSION['niveau']==$__LVL_ADMIN)
			$condition_univ=$condition_multi="";
		else
		{
			$condition_univ="";

			if(isset($_SESSION["multi_composantes"]) && $_SESSION["multi_composantes"]==1)
				$condition_multi="AND $_DBC_composantes_id IN (SELECT distinct($_DBC_acces_comp_composante_id) FROM $_DB_acces_comp
																				WHERE $_DBC_acces_comp_acces_id='$_SESSION[auth_id]')";
			else
				$condition_multi="";
		}

		$result=db_query($dbr, "SELECT $_DBC_composantes_id, $_DBC_composantes_nom, $_DBC_composantes_univ_id, $_DBC_universites_nom
											FROM $_DB_composantes, $_DB_universites
										WHERE $_DBC_composantes_univ_id=$_DBC_universites_id
										$condition_univ
										$condition_multi
											ORDER BY $_DBC_composantes_univ_id, $_DBC_composantes_nom ASC");

		$rows=db_num_rows($result);

		print("<form action='$php_self' method='POST' name='form1'>
					<div class='centered_box'>
						<font class='Texte'>Composante : </font>
						<select name='comp_id' size='1'>\n");

		$old_univ="";

		for($i=0; $i<$rows; $i++)
		{
			list($comp_id, $comp_nom, $comp_univ_id, $univ_nom)=db_fetch_row($result,$i);

			if($comp_univ_id!=$old_univ)
			{
				if($i!=0)
					print("</optgroup>
							 <option value='' label='' disabled></option>\n");

				print("<optgroup label='".htmlspecialchars(stripslashes($univ_nom), ENT_QUOTES)."'>\n");
			}

			$value=htmlspecialchars($comp_nom, ENT_QUOTES);

			print("<option value='$comp_id' label=\"$value\">$value</option>\n");

			$old_univ=$comp_univ_id;
		}

		db_free_result($result);

		print("</optgroup>
				</select>\n
			</div>

			<div class='centered_icons_box'>
				<a href='index.php' target='_self'><img src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Retour' border='0'></a>
				<input type='image' src='$__ICON_DIR/forward_32x32_fond.png' alt='Suivant' name='go' value='Valider'>
				</form>
			</div>");

		db_close($dbr);
	?>
</div>
<?php
	pied_de_page();
?>
<script language="javascript">
	document.form1.comp_id.focus()
</script>
</body></html>
