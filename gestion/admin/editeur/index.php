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

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	verif_auth("$__GESTION_DIR/login.php");

	if(!in_array($_SESSION["niveau"], array("$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
	{
		header("Location:$__GESTION_DIR/noaccess.php");
		exit();
	}

	unset($_SESSION["lettre_id"]);
	unset($_SESSION["cbo"]);

	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>

<div class='main'>
	<div class='menu_haut_2'>
		<a href='tableau.php' target='_self'><img class='icone_menu_haut_2' border='0' src='<?php echo "$__ICON_DIR/kdeprint_report_16x16_menu2.png"; ?>'></a>
		<a href='tableau.php' target='_self' class='lien_menu_haut_2'>Tableau r�capitulatif</a>
		<?php
			if(in_array($_SESSION["niveau"], array("$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
			{
		?>

			<a href='parametres.php' target='_self'><img class='icone_menu_haut_2' border='0' src='<?php echo "$__ICON_DIR/preferences_16x16_menu2.png"; ?>' alt='parametres'></a>
			<a href='parametres.php' target='_self' class='lien_menu_haut_2'>Param�tres par d�faut</a>
		<?php
			}
		?>
			<a href='editeur.php?lettre_id=-1'  target='_self'><img class='icone_menu_haut_2' border='0' src='<?php echo "$__ICON_DIR/add_16x16_menu2.png"; ?>' alt='+'></a>
			<a href='editeur.php?lettre_id=-1'  target='_self' class='lien_menu_haut_2'>Cr�er une nouvelle lettre</a>
		<?php
			if(in_array($_SESSION["niveau"], array("$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
			{
		?>
			<a href='copie_lettre.php'  target='_self'><img class='icone_menu_haut_2' border='0' src='<?php echo "$__ICON_DIR/editcopy_16x16_menu2.png"; ?>' alt='+'></a>
			<a href='copie_lettre.php'  target='_self' class='lien_menu_haut_2'>Dupliquer une lettre</a>
		<?php
			}
		?>
	</div>

	<?php
		titre_page_icone("Mod�les de lettres", "abiword_32x32_fond.png", 15, "L");

		if(isset($_GET["succes"]) && $_GET["succes"]==1)
			message("Informations mises � jour avec succ�s", $__SUCCES);
	?>

	<?php
		$dbr=db_connect();

		// Lettres non associ�es

		$result=db_query($dbr,"SELECT $_DBC_lettres_id, $_DBC_lettres_titre FROM $_DB_lettres
										WHERE $_DBC_lettres_comp_id=$_SESSION[comp_id]
										AND $_DBC_lettres_id NOT IN (SELECT distinct($_DBC_lettres_dec_lettre_id) FROM $_DB_lettres_dec)
									  ORDER BY $_DBC_lettres_titre ASC");
		$rows=db_num_rows($result);

		if($rows)
		{
			print("<table cellpadding='2' align='center' width='98%' style='margin-bottom:30px;'>
						<tr>
							<td class='fond_menu2'>
								<font class='Texte_menu2'><b>Lettres non associ�es � une d�cision</b></font>
							</td>
						</tr>\n");

			for($i=0; $i<$rows; $i++)
			{
				list($lettre_id, $lettre_titre)=db_fetch_row($result,$i);
				$date_creation=date_fr("j M Y", id_to_date($lettre_id));

				print("<tr>
							<td class='fond_page'>
								<a href='editeur.php?lettre_id=$lettre_id' target='_self' class='lien_bleu_10'>$lettre_titre ($date_creation)</a>
							</td>
						</tr>\n");
			}

			print("</table>\n");
		}

		print("<table cellpadding='2' align='center' width='98%' style='margin-bottom:30px;'>
					<tr>
						<td class='fond_menu2'>
							<font class='Texte_menu2'><b>Lettres associ�es, tri�es par d�cision</b></font>
						</td>
					</tr>\n");

		// lettres associ�es � une d�cision

		$result=db_query($dbr,"SELECT $_DBC_lettres_id, $_DBC_lettres_titre, $_DBC_decisions_texte
											FROM $_DB_lettres, $_DB_decisions, $_DB_lettres_dec
											WHERE $_DBC_lettres_comp_id=$_SESSION[comp_id]
											AND $_DBC_lettres_id=$_DBC_lettres_dec_lettre_id
											AND $_DBC_decisions_id=$_DBC_lettres_dec_dec_id
										ORDER BY $_DBC_decisions_texte ASC");
		$rows=db_num_rows($result);

		if(!$rows)
			print("<tr>
						<td class='fond_page'>
							<font class='Texte'><i>Aucune</i></font>
						</td>
					</tr>
					</table>\n");
		else
		{
			$old_decision="";

			for($i=0; $i<$rows; $i++)
			{
				list($lettre_id, $lettre_titre, $decision)=db_fetch_row($result,$i);
				$date_creation=date_fr("j F Y", id_to_date($lettre_id));

				if($old_decision!=$decision)
				{
					$old_decision=$decision;

					if($i!=0)
						print("<tr>
									<td class='fond_page' height='20'></td>
								</tr>\n");

					print("<tr>
								<td class='fond_menu'>
									<font class='Texte_menu'><b>$decision</b></font>
								</td>
							</tr>\n");
				}

				print("<tr>
							<td class='fond_page' align='left'>
								<a href='editeur.php?lettre_id=$lettre_id' target='_self' class='lien_bleu_10'>$lettre_titre ($date_creation)</a>
							</td>
						</tr>\n");
			}

			print("</table>\n");
		}

		db_free_result($result);
		db_close($dbr);
	?>
</div>
<?php
	pied_de_page();
?>

</body></html>
