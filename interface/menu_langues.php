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

	if(isset($_GET["err_langue"]) && $_GET["err_langue"]==1)
		message("<center>
						Erreur : la langue pour laquelle vous tentez d'ajouter un dipl�me n'existe pas ou plus.
						<br>Pour regler ce probl�me, veuillez ajouter de nouveau cette langue, puis retenter l'ajout du dipl�me.
					</center>", $__ERREUR);
	

	print("<div class='centered_box'>
				<font class='Texte_16'><strong>$_SESSION[onglet] - Langues : niveau et dipl�mes obtenus</strong></font>
			</div>");

	message("<center>
					Pour les candidats �trangers : vous devez <b>imp�rativement</b> renseigner votre niveau en langue fran�aise
					<br>ainsi que les dipl�mes obtenus (parmi : DELF A1, DELF A2, DELF B1, DELF B2, DALF C1 et DALF C2)
				</center>", $__WARNING);

	$result=db_query($dbr,"SELECT $_DBC_langues_id, $_DBC_langues_langue, $_DBC_langues_niveau, $_DBC_langues_annees
										FROM $_DB_langues
									WHERE $_DBC_langues_candidat_id='$candidat_id'
									ORDER BY $_DBC_langues_langue ASC");

	$rows=db_num_rows($result);

	if($rows)
	{
		print("<table align='center'>");

		for($i=0; $i<$rows; $i++)
		{
			list($la_id, $langue,$niveau, $nb_annees)=db_fetch_row($result,$i);

			// Dipl�mes obtenus dans cette langue
			$result2=db_query($dbr,"SELECT $_DBC_langues_dip_id, $_DBC_langues_dip_nom, $_DBC_langues_dip_annee, $_DBC_langues_dip_resultat
												FROM $_DB_langues_dip WHERE $_DBC_langues_dip_langue_id='$la_id'
											ORDER BY $_DBC_langues_dip_annee");

			$rows2=db_num_rows($result2);

			$langue=str_replace("_","",$langue);

			$niveau_langue=explode("|",$niveau);
			$niveau_txt="";

			if($niveau_langue[0])
				$niveau_txt="Lu";

			if($niveau_langue[1])
			{
				if(!empty($niveau_txt))
					$niveau_txt.=", ";

				$niveau_txt.="Ecrit";
			}
			if($niveau_langue[2])
			{
				if(!empty($niveau_txt))
					$niveau_txt.=", ";

				$niveau_txt.="Parl�";
			}

			if(isset($niveau_langue[3]) && $niveau_langue[3])
			{
				if(!empty($niveau_txt))
					$niveau_txt.=", ";

				$niveau_txt.="Langue Maternelle";
			}

			if(!empty($nb_annees))
				$nb_annees="Nombre d'ann�es : $nb_annees";

			if($_SESSION["lock"]!=1)
			{
				$crypt_params=crypt_params("la_id=$la_id");
				$crypt_params2=crypt_params("suppr=$la_id");
				print("<tr>
							<td class='td-gauche fond_menu2' style='text-align:left; vertical-align:middle;'>
								<a href='langues.php?p=$crypt_params2' target='_self' class='lien_menu_gauche'><img src='$__ICON_DIR/trashcan_full_16x16_slick_menu2.png' alt='Supprimer' border='0'></a>
							</td>
							<td class='td-milieu fond_menu2' style='vertical-align:middle;'>
								<a href='langues.php?p=$crypt_params' class='lien_menu_gauche'><b>$langue</b></a>
							</td>
							<td class='td-milieu fond_menu2' style='vertical-align:middle;'>
								<a href='langues.php?p=$crypt_params' class='lien_menu_gauche'>$niveau_txt</a>
							</td>
							<td class='td-droite fond_menu2' style='vertical-align:middle;'>
								<a href='langues.php?p=$crypt_params' class='lien_menu_gauche'>$nb_annees</a>
							</td>
						</tr>\n");

				if($rows2)
				{
					for($j=0; $j<$rows2; $j++)
					{
						list($langue_dip_id, $langue_dip, $langue_dip_annee, $langue_dip_resultat)=db_fetch_row($result2, $j);

						if(!empty($langue_dip_annee) && $langue_dip_annee!=0)
							$langue_diplome_txt="$langue_dip_annee : $langue_dip";
						else
							$langue_diplome_txt="$langue_dip";

						if(!empty($langue_dip_resultat))
							$langue_diplome_txt.="&nbsp;&nbsp;&nbsp;<b>R�sultat / Mention :</b> $langue_dip_resultat";

						$crypt_params=crypt_params("la_id=$la_id&suppr=$langue_dip_id");

						print("<tr>
									<td colspan='3' class='td-gauche fond_menu'>
										<font class='Texte_menu'>$langue_diplome_txt</font>
									</td>
									<td class='td-droite fond_menu' style='text-align:right;'>
										<a href='langues_diplomes.php?p=$crypt_params' class='lien_menu_gauche'><img src='$__ICON_DIR/trashcan_full_16x16_slick_menu.png' alt='Supprimer' border='0'></a>
									</td>
								</tr>\n");
					}
				}

				$crypt_params=crypt_params("la_id=$la_id&la_nom=" . addslashes($langue));

				print("<tr>
							<td colspan='4' align='center' class='td-gauche fond_menu'>
								<a href='langues_diplomes.php?p=$crypt_params' class='lien_menu_gauche'>Cliquer ici pour ajouter un dipl�me dans cette langue</a>
							</td>
						</tr>
						<tr>
							<td colspan='4' height='20' class='td-separation fond_page'></td>
						</tr>\n");
			}
			else
			{
				print("<tr>
							<td class='td-gauche fond_menu' style='vertical-align:middle;'>
								<font class='Texte_menu'><b>$langue</b></font>
							</td>
							<td class='td-milieu fond_menu' style='vertical-align:middle;'>
								<font class='Texte_menu'>$niveau_txt</font>
							</td>
							<td class='td-droite fond_menu' style='vertical-align:middle;'>
								<font class='Texte_menu'>$nb_annees</font>
							</td>
						</tr>\n");

				if($rows2)
				{
					$langue_diplome_txt="";

					for($j=0; $j<$rows2; $j++)
					{
						list($langue_dip_id, $langue_dip, $langue_dip_annee, $langue_dip_resultat)=db_fetch_row($result2, $j);

						if(!empty($langue_diplome_txt))
							$langue_diplome_txt.="<br>";

						if(!empty($langue_dip_annee) && $langue_dip_annee!=0)
							$langue_diplome_txt.="$langue_dip_annee : $langue_dip";
						else
							$langue_diplome_txt.="$langue_dip";

						if(!empty($langue_dip_resultat))
							$langue_diplome_txt.="&nbsp;&nbsp;&nbsp;<b>R�sultat / Mention :</b> $langue_dip_resultat";
					}

					print("<tr>
								<td colspan='3' class='td-gauche fond_menu'>
									<font class='Texte_menu'>$langue_diplome_txt</font>
								</td>
							</tr>\n");
				}

				print("<tr>
							<td colspan='3' height='20' class='td-separation fond_page'></td>
						</tr>\n");
			}

			db_free_result($result2);
		}

		print("</table>\n");
	}

	db_free_result($result);

	if($_SESSION["lock"]!=1)
		print("<div class='centered_box'>
					<a href='langues.php' target='_self' class='lien2'><img class='icone' src='$__ICON_DIR/add_22x22_fond.png' border='0' alt='Ajouter' desc='Ajouter'></a>
					<a href='langues.php' target='_self' class='lien2'>Ajouter une langue</a>
				</div>");
	else
		message("<center>Une composante a d�j� verrouill� l'un de vos voeux : vous ne pouvez plus modifier ces informations en ligne.
					<br><strong>Toute information compl�mentaire doit �tre envoy�e par courrier aux composantes concern�es</strong></center>", $__ERREUR);
?>
