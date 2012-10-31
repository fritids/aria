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
				<font class='Texte_16'><strong>$_SESSION[onglet] - Informations compl�mentaires et exp�riences professionnelles</strong></font>
			 </div>\n");

	if(in_array($_SESSION["niveau"], array("$__LVL_SCOL_MOINS","$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")) && ($_SESSION["tab_candidat"]["lock"]==1 || $_SESSION["tab_candidat"]["manuelle"]==1))
		print("<div class='centered_box'>
					<a href='info.php' target='_self' class='lien2'><img class='icone' src='$__ICON_DIR/add_22x22_fond.png' border='0' alt='Ajouter' desc='Ajouter'></a>
					<a href='info.php' target='_self' class='lien2'>Ajouter manuellement une information compl�mentaire</a>
				</div>\n");

	// informations compl�mentaires et candidatures ext�rieures

	$result=db_query($dbr,"SELECT $_DBC_infos_comp_id, $_DBC_infos_comp_texte, $_DBC_infos_comp_annee, $_DBC_infos_comp_duree
										FROM $_DB_infos_comp
									WHERE $_DBC_infos_comp_candidat_id='$candidat_id'
										ORDER BY $_DBC_infos_comp_annee DESC");

	$rows=db_num_rows($result);

	if($rows)
	{
		print("<table style='margin-left:auto; margin-right:auto; padding-bottom:20px;'>\n");

		for($i=0; $i<$rows; $i++)
		{
			list($iid, $info,$annee,$duree)=db_fetch_row($result,$i);
			// $info=str_replace("\n"," - ",$info);
			$info=preg_replace("/[\n]+/","<br>",$info);

			if($duree=="")
				$dur="";
			else
				$dur="($duree)";

			// Si la fiche est v�rrouill�e, on autorise la modification et la suppression
			if(in_array($_SESSION["niveau"], array("$__LVL_SCOL_MOINS","$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")) && ($_SESSION["tab_candidat"]["lock"]==1 || $_SESSION["tab_candidat"]["manuelle"]==1))
			{
				print("<tr>
							<td class='td-gauche fond_menu2' style='white-space:normal'>
								<a href='info.php?iid=$iid' class='lien_menu_gauche'>$annee $dur</a></td>
							</td>
							<td class='td-milieu fond_menu' style='white-space:normal'>
								<a href='info.php?iid=$iid' class='lien_menu_gauche'>$info</a></td>
							</td>
							<td class='td-droite fond_menu' style='text-align:center; width:24px'>
								<a href='suppr_info.php?iid=$iid' target='_self' class='lien2'><img src='$__ICON_DIR/trashcan_full_22x22_slick_menu.png' alt='Supprimer' width='22' height='22' border='0'></a>
							</td>
						</tr>
						<tr>
							<td colspan='3' height='20' class='fond_page'></td>
						</tr>\n");
			}
			else
				print("<tr>
							<td class='td-gauche fond_menu2' style='padding:6px 20px 6px 20px; white-space:normal'>
								<font class='Texte_menu2'>$annee $dur</font>
							</td>
							<td class='td-droite fond_menu' style='padding:6px 20px 6px 20px; white-space:normal'>
								<font class='Texte_menu'>$info</font>
							</td>
						</tr>
						<tr>
							<td colspan='2' height='20' class='fond_page'></td>
						</tr>\n");
		}
		print("</table>\n");
	}

	db_free_result($result);
?>
