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
	if(!isset($_SESSION["authentifie"]))
	{
		session_write_close();
		header("Location:../index.php");
		exit();
	}

	if(!isset($_SESSION["comp_id"]) || (isset($_SESSION["comp_id"]) && $_SESSION["comp_id"]==""))
	{
		session_write_close();
		header("Location:composantes.php");
		exit();
	}

	print("<div class='centered_box'>
				<font class='TitrePage_16'>$_SESSION[onglet] - Informations compl�mentaires et exp�riences professionnelles</font>
			 </div>");

	message("Cette section sert � ajouter d'�ventuelles informations sur votre parcours professionnel (stages, emplois, formations, service national, ...).
				<br>Si vous avez arr�t�, puis repris vos �tudes, vous pouvez �galement le mentionner ici.", $__INFO);


	$result=db_query($dbr,"SELECT $_DBC_infos_comp_id, $_DBC_infos_comp_texte, $_DBC_infos_comp_annee, $_DBC_infos_comp_duree
										FROM $_DB_infos_comp
									WHERE $_DBC_infos_comp_candidat_id='$candidat_id'
										ORDER BY $_DBC_infos_comp_annee DESC");
	$rows=db_num_rows($result);

	if($rows)
	{
		print("<table style='margin:0px auto 0px auto;'>\n");

		for($i=0; $i<$rows; $i++)
		{
			list($iid, $info,$annee,$duree)=db_fetch_row($result,$i);
			$info=str_replace("\n"," - ",$info);

			if($duree=="")
				$dur="";
			else
				$dur="($duree)";

			if($_SESSION["lock"]!=1)
			{
				$crypt_params=crypt_params("iid=$iid");
				print("<tr>
							<td class='td-gauche fond_menu' style='white-space: normal;'>
								<a href='info.php?p=$crypt_params' class='lien_menu_gauche'><b>$annee</b> : $info $dur</a>
							</td>
							<td class='td-droite fond_menu' style='text-align:right;'>
								<a href='suppr_info.php?p=$crypt_params' target='_self' class='lien_menu_gauche'><img src='$__ICON_DIR/trashcan_full_16x16_slick_menu.png' alt='Supprimer' border='0'></a>
							</td>
						</tr>
						<tr>
							<td colspan='2' height='20' class='td-separation fond_page'></td>
						</tr>\n");
			}
			else
				print("<tr>
							<td class='td-gauche fond_menu' style='white-space: normal;'>
								<font class='Texte_menu'><b>$annee</b> : $info $dur</font>
							</td>
						</tr>
						<tr>
							<td height='20' class='td-separation fond_page'></td>
						</tr>\n");
		}

		print("</table>");
	}

	db_free_result($result);

	if($_SESSION["lock"]!=1)
		print("<div class='centered_box'>
					<a href='info.php' target='_self' class='lien2'><img class='icone' src='$__ICON_DIR/add_22x22_fond.png' border='0' alt='Ajouter' desc='Ajouter'></a>
					<a href='info.php' target='_self' class='lien2'>Ajouter une information</a>
				</div>");
	else
		message("<center>Une composante a d�j� verrouill� l'un de vos voeux : vous ne pouvez plus modifier ces informations en ligne.
					<br><strong>Toute information compl�mentaire doit �tre envoy�e par courrier aux composantes concern�es</strong></center>", $__ERREUR);
?>
