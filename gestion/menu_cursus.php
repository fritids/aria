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
	// V�rifications compl�mentaires au cas o� ce fichier serait appel� directement
	verif_auth();

	if(!isset($_SESSION["candidat_id"]))
	{
		header("Location:index.php");
		exit;
	}

	print("<div class='centered_box'>
				<font class='Texte_16'><strong>$_SESSION[onglet] - Son cursus scolaire</strong></font>
			</div>\n");

	if(isset($modifs) && $modifs>0)
		message("Succ�s de la mise � jour - message envoy� au candidat", $__SUCCES);
	elseif(isset($modifs) && $modifs==0)
		message("Aucune modification effectu�e, aucun message n'a �t� envoy�.", $__SUCCES);

	if(isset($precision_vide))
		message("Attention : le champ 'Pr�cisions' de certaines �tapes non justifi�es n'a pas �t� renseign� !", $__WARNING);

	if(in_array($_SESSION["niveau"], array("$__LVL_SCOL_MOINS","$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")) && ($_SESSION["tab_candidat"]["lock"]==1 || $_SESSION["tab_candidat"]["manuelle"]==1))
		print("<div class='centered_box'>
					<a href='cursus.php' target='_self' class='lien2'><img class='icone' src='$__ICON_DIR/add_22x22_fond.png' border='0' alt='Ajouter' desc='Ajouter'></a>
					<a href='cursus.php' target='_self' class='lien2'>Ajouter manuellement une �tape du cursus</a>
				</div>\n");
?>
	<table style='margin-left:auto; margin-right:auto; padding-bottom:20px;'>
	<tr>
		<td colspan='2' class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><strong>Dipl�me / Niveau d'�tudes</strong></font>
		</td>
		<td class='td-milieu fond_menu2'>
			<font class='Texte_menu2'><strong>Justification</strong></font>
		</td>
	<?php
		if($_SESSION["tab_candidat"]["lock"]==1 || $_SESSION["tab_candidat"]["manuelle"]==1)
			print("<td colspan='2' class='td-droite fond_menu2'>
						<font class='Texte_menu2'><strong>Pr�cisions</strong></font>
					 </td>\n");
		else
			print("<td class='td-droite fond_menu2'>
						<font class='Texte_menu2'><strong>Pr�cisions</strong></font>
					 </td>\n");
	?>
	</tr>
	<?php
		$result=db_query($dbr,"(SELECT 	$_DBC_cursus_id, $_DBC_cursus_diplome, $_DBC_cursus_intitule, $_DBC_cursus_annee,
													$_DBC_cursus_ecole, $_DBC_cursus_ville,
													CASE WHEN $_DBC_cursus_pays IN (SELECT $_DBC_pays_nat_ii_iso FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_cursus_pays) 
														THEN (SELECT $_DBC_pays_nat_ii_pays FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_cursus_pays)
														ELSE '' END as cursus_pays,
													$_DBC_cursus_mention, $_DBC_cursus_moyenne
											FROM $_DB_cursus
										WHERE $_DBC_cursus_candidat_id='$candidat_id'
										AND   $_DBC_cursus_annee='0')
									UNION ALL
										(SELECT 	$_DBC_cursus_id, $_DBC_cursus_diplome, $_DBC_cursus_intitule, $_DBC_cursus_annee,
													$_DBC_cursus_ecole, $_DBC_cursus_ville,
													CASE WHEN $_DBC_cursus_pays IN (SELECT $_DBC_pays_nat_ii_iso FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_cursus_pays) 
														THEN (SELECT $_DBC_pays_nat_ii_pays FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_cursus_pays)
														ELSE '' END as cursus_pays,
														$_DBC_cursus_mention, $_DBC_cursus_moyenne
											FROM $_DB_cursus
										WHERE $_DBC_cursus_candidat_id='$candidat_id'
										AND $_DBC_cursus_annee!='0'
											ORDER BY $_DBC_cursus_annee DESC)");
		$rows=db_num_rows($result);

		// compteur de cursus non justifi�s (sert � d�terminer si une fiche peut �tre transf�r�e vers la compeda)
		$cursus_non_justifies=0;

		// on a des informations sur le cursus, on initialise le tableau
		$_SESSION["tab_cursus"]=array();

		for($i=0; $i<$rows; $i++)
		{
			list($cu_id, $dip, $int, $annee_obt, $ecole, $ville, $pays, $mention, $moyenne)=db_fetch_row($result,$i);

			$dip=preg_replace("/_/","",$dip);
			$int=preg_replace("/_/","",$int);
			$ecole=preg_replace("/_/","",$ecole);
			$ville=preg_replace("/_/","",$ville);

			if($annee_obt==0)
				$annee_obt="En cours";

			if(!empty($pays))
				$pays="- ". preg_replace("/_/","",$pays);
			else
				$pays="";

			// si le candidat a �t� ajourn�, on le pr�cise (�a �vite de demander un justificatif)
			if(!empty($mention) && $mention=="Ajourn�")
				$mention="- <strong>Ajourn�</strong>";
			else
				$mention="";

			if(!empty($moyenne))
				$moyenne="- <strong>Moyenne : $moyenne</strong>";
			else
				$moyenne="";

			// Satut de l'�tape pour la composante concern�e
			$result2=db_query($dbr, "SELECT $_DBC_cursus_justif_statut, $_DBC_cursus_justif_precision
												FROM $_DB_cursus_justif
											 WHERE $_DBC_cursus_justif_cursus_id='$cu_id'
											 AND $_DBC_cursus_justif_comp_id='$_SESSION[comp_id]'
											 AND $_DBC_cursus_justif_periode='$__PERIODE'");

			$rows2=db_num_rows($result2);

			if(!$rows2) // insertion dans la base
			{
				db_query($dbr, "INSERT INTO $_DB_cursus_justif VALUES('$cu_id','$_SESSION[comp_id]','$__CURSUS_EN_ATTENTE','', '$__PERIODE')");
				$justifie=$__CURSUS_EN_ATTENTE;
				$precision="";
			}
			else
				list($justifie, $precision)=db_fetch_row($result2, 0);

			db_free_result($result2);

			// Justification des �l�ments du cursus (cf macros dans vars.php)
			// 0 = En attente des pi�ces (valeur par d�faut)
			// -2 = Pi�ces � fournir d�s l'obtention du dipl�me (pour les �tapes en cours)
			// -1 = Pi�ce(s) requise(s) manquante(s)
			// 1 = Etape du cursus valid�e
			// 2 = Etape ne n�cessitant aucune justification

			switch($justifie)
			{
				case	$__CURSUS_NON_JUSTIFIE:			$en_attente_selected=$a_fournir_des_obtention=$non_necessaire_selected=$oui_selected=$pieces_manquantes_selected="";
																$non_justifie='selected=1';
																$statut_cursus="Information non confirm�e";
																break;


				case	$__CURSUS_EN_ATTENTE	:			$non_justifie=$a_fournir_des_obtention=$non_necessaire_selected=$oui_selected=$pieces_manquantes_selected="";
																$en_attente_selected='selected=1';
																$cursus_non_justifies++;
																$statut_cursus="En attente des pi�ces";
																break;

				case	$__CURSUS_VALIDE	:				$oui_selected='selected=1';
																$non_justifie=$a_fournir_des_obtention=$non_necessaire_selected=$en_attente_selected=$pieces_manquantes_selected="";
																$statut_cursus="Information valid�e";
																break;

				case	$__CURSUS_PIECES	:				$non_justifie=$a_fournir_des_obtention=$non_necessaire_selected=$oui_selected=$en_attente_selected="";
																$pieces_manquantes_selected='selected=1';
																$cursus_non_justifies++;
																$statut_cursus="Pi�ces manquantes";
																break;

				case	$__CURSUS_NON_NECESSAIRE	:	$non_justifie=$oui_selected=$en_attente_selected=$a_fournir_des_obtention=$pieces_manquantes_selected="";
																$non_necessaire_selected='selected=1';
																$statut_cursus="Aucun justificatif n�cessaire";
																break;

				case	$__CURSUS_DES_OBTENTION	:		$non_justifie=$oui_selected=$non_necessaire_selected=$en_attente_selected=$pieces_manquantes_selected="";
																$a_fournir_des_obtention='selected=1';
																$statut_cursus="Justificatif � fournir d�s l'obtention";
																break;
			}

			// stockage des infos du cursus dans le tableau
			$_SESSION["tab_cursus"][$cu_id]=array();
			$_SESSION["tab_cursus"][$cu_id]["texte"]="$annee_obt : $dip $int";
			$_SESSION["tab_cursus"][$cu_id]["justifie"]=$justifie;
			$_SESSION["tab_cursus"][$cu_id]["precision"]=$precision;

			// Si la fiche est v�rrouill�e, on autorise la modification des �tapes entr�es
			if(in_array($_SESSION["niveau"], array("$__LVL_SCOL_MOINS","$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")) && ($_SESSION["tab_candidat"]["lock"]==1 || $_SESSION["tab_candidat"]["manuelle"]==1))
			{
				print("<tr>
							<td class='td-gauche fond_menu'>
								<a href='cursus.php?cu_id=$cu_id' class='lien_menu_gauche'>$annee_obt : </a>
							</td>
							<td class='td-milieu fond_menu'>
								<a href='cursus.php?cu_id=$cu_id' class='lien_menu_gauche'>$dip $int $mention $moyenne
									<br><i>$ecole, $ville $pays</i>
								</a>
							</td>\n");
			}
			else
				print("<tr>
							<td class='td-gauche fond_menu'>
								<font class='Texte_menu'>$annee_obt : </font>
							</td>
							<td class='td-milieu fond_menu'>
								<font class='Texte_menu'>$dip $int $mention $moyenne
									<br><i>$ecole, $ville $pays</i>
								</font>
							</td>\n");

			print("<td class='td-milieu fond_menu'>\n");

			if(in_array($_SESSION["niveau"], array("$__LVL_SAISIE", "$__LVL_SCOL_MOINS","$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")) && ($_SESSION["tab_candidat"]["lock"]==1 || $_SESSION["tab_candidat"]["manuelle"]==1))
				print("<select name='justification_$cu_id' size='1'>
							<option value='$__CURSUS_EN_ATTENTE' $en_attente_selected>En attente des pi�ces</option>
							<option value='$__CURSUS_VALIDE' $oui_selected>Information valid�e</option>
							<option value='$__CURSUS_PIECES' $pieces_manquantes_selected>Pi�ces manquantes</option>
							<option value='$__CURSUS_DES_OBTENTION' $a_fournir_des_obtention>Justificatif � fournir d�s l'obtention</option>
							<option value='$__CURSUS_NON_NECESSAIRE' $non_necessaire_selected>Aucun justificatif n�cessaire</option>
							<option value='$__CURSUS_NON_JUSTIFIE' $non_justifie>Information jamais confirm�e</option>
						</select>
					</td>
					<td class='td-milieu fond_menu'>
						<input type='text' name='precision_$cu_id' value=\"$precision\" size='30' maxlength='256'>
					</td>
					<td class='td-droite fond_menu' style='text-align:center; width:24px;'>
						<a href='suppr_cursus.php?cu_id=$cu_id' target='_self' class='lien2'><img src='$__ICON_DIR/trashcan_full_22x22_slick_menu.png' alt='Supprimer' width='22' height='22' border='0'></a>
					</td>
				</tr>\n");
			else
				print("	<font class='Texte_menu'>$statut_cursus</font></td>
						<td class='td-droite fond_menu'>
							<font class='Texte_menu'>$precision</font>
						</td>
					</tr>\n");
		}
		db_free_result($result);

		print("</table>\n");

		if(in_array($_SESSION["niveau"], array("$__LVL_SAISIE", "$__LVL_SCOL_MOINS","$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")) && ($_SESSION["tab_candidat"]["lock"]==1 || $_SESSION["tab_candidat"]["manuelle"]==1))
		{
			message("Toute modification valid�e entra�ne automatiquement l'envoi d'un message au candidat<br><center>(message global pour l'ensemble des �tapes du cursus)</center>", $__WARNING);

			print("<div class='centered_box'>
						<input type='image' src='$__ICON_DIR/bouton_valider_128x32_fond.png' alt='Valider les modifications' name='go_cursus' value='Valider les modifications'>
					</div>\n");
		}
?>
