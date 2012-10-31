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
	include "$__INCLUDE_DIR_ABS/db.php";
	include "$__INCLUDE_DIR_ABS/fonctions.php";

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	en_tete_candidat_simple();
	menu_sup_simple();
?>

<div class='main'>
	<div class='centered_box'>
		<font class='Texte3'>
			<b>D�p�t de dossiers de pr�candidature
			<br><br>II - Composantes accessibles pour un d�p�t de pr�candidature en ligne</b>
			<br>
			<br><a href='documentation.php' class='lien2a'><img src='<?php echo "$__ICON_DIR/back_32x32_fond.png"; ?>' alt='Retour' border='0'></a>
		</font>
	</div>

	<div style='width:80%; text-align:justify; margin:0px auto 0px auto; padding-bottom:30px;'>
		<?php
			$dbr=db_connect();

			$result=db_query($dbr, "SELECT $_DBC_composantes_id, $_DBC_composantes_nom, $_DBC_composantes_univ_id, $_DBC_universites_nom,
														$_DBC_composantes_courriel_scol, $_DBC_composantes_scolarite
												FROM $_DB_composantes, $_DB_universites
											WHERE $_DBC_composantes_univ_id=$_DBC_universites_id
											AND $_DBC_composantes_id IN (SELECT distinct($_DBC_propspec_comp_id) FROM $_DB_propspec
																					WHERE $_DBC_propspec_active='1')
												ORDER BY $_DBC_composantes_univ_id, $_DBC_composantes_nom ASC");

			$rows=db_num_rows($result);

			$old_univ="";
			$count=0;

			for($i=0; $i<$rows; $i++)
			{
				list($comp_id, $comp_nom, $comp_univ_id, $univ_nom, $courriel_scol, $scolarite)=db_fetch_row($result,$i);

				$scolarite=nl2br($scolarite);

				if($comp_univ_id!=$old_univ)
				{
					if($i)
					{
						if($count==1)
							print("<td bgcolor='#DDEEFF'></td>\n");

						print("</tr>
								</table>\n");
					}

					print("<table cellspacing='0' cellpadding='4' align='center' width='85%'>
								<tr>
									<td colspan='2' align='left' bgcolor='#CCDDEE'>
										<font class='Texte3'><b>$univ_nom</b></font>
									</td>
								</tr>\n");

					$old_univ=$comp_univ_id;
					$count=0;
				}

				if($count==0)
					print("<tr>\n");

				print("<td align='left' bgcolor='#DDEEFF' valign='top'>
							<font class='Texte'>
								<b>&#8226;&nbsp;&nbsp;$comp_nom</b>\n");

				if(!empty($scolarite))
					print("<br>$scolarite
								<br>\n");
	/*
				if(!empty($courriel_scol))
					print("<a href='mailto:$courriel_scol' class='lien_bleu_12'>- Contacter cette scolarit� par courriel</a>
									<br>\n");
	*/
				print("<br>
						</font>
					</td>\n");

				if($count==1)
				{
					print("</tr>\n");
					$count=0;
				}
				else
					$count=1;
			}

			if($count==1)
				print("<td bgcolor='#DDEEFF'></td>\n");

			print("</tr>
						</table>\n");

			db_free_result($result);
			db_close($dbr);
		?>
	</div>
</div>
<?php
	pied_de_page_candidat();
?>

</body>
</html>

