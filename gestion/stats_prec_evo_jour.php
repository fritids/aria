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

	verif_auth();

	$dbr=db_connect();

	// D�verrouillage, au cas o�
	if(isset($_SESSION["candidat_id"]))
		cand_unlock($dbr, $_SESSION["candidat_id"]);


	function cmp_formations($a, $b)
	{
		if($a["annee"]<$b["annee"])
			return -1;
		elseif($a["annee"]>$b["annee"])
			return 1;
		elseif($a["spec"]<$b["spec"])
			return -1;
		elseif($a["spec"]>$b["spec"])
			return 1;
		elseif($a["finalite"]<$b["finalite"])
			return -1;
		elseif($a["finalite"]>$b["finalite"])
			return 1;
		else
			return 0;
	}

	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>

<div class='main'>
	<?php
		titre_page_icone("Evolution journali�re du nombre de pr�candidatures", "kpercentage_32x32_fond.png", 15, "L");

		// Construction de la liste des formations
		$result=db_query($dbr, "SELECT $_DBC_propspec_id, $_DBC_annees_annee, $_DBC_specs_nom, $_DBC_propspec_finalite
											FROM $_DB_annees, $_DB_specs, $_DB_propspec
										WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]'
										AND $_DBC_propspec_annee=$_DBC_annees_id
										AND $_DBC_propspec_id_spec=$_DBC_specs_id
										AND $_DBC_propspec_active='1'
											ORDER BY $_DBC_annees_ordre, $_DBC_specs_nom, $_DBC_propspec_finalite");

		$rows=db_num_rows($result);

		$formations_array=array();

		for($i=0; $i<$rows; $i++)
		{
			list($propspec_id, $annee, $spec, $finalite)=db_fetch_row($result, $i);

			$nom_finalite=$tab_finalite[$finalite];

			$formations_array[$propspec_id]=array("annee" => "$annee", "spec" => "$spec", "finalite" => "$nom_finalite", "nb" => 0);
		}

		db_free_result($result);

		// Boucle sur les pr�candidatures dans cette composante
		$result=db_query($dbr, "SELECT $_DBC_cand_id, $_DBC_cand_candidat_id, $_DBC_cand_propspec_id, $_DBC_annees_annee, $_DBC_specs_nom,
													$_DBC_propspec_finalite
											FROM $_DB_cand, $_DB_annees, $_DB_specs, $_DB_propspec
										WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]'
										AND $_DBC_propspec_annee=$_DBC_annees_id
										AND $_DBC_propspec_id_spec=$_DBC_specs_id
										AND $_DBC_cand_propspec_id=$_DBC_propspec_id
										AND $_DBC_cand_periode='$__PERIODE'
											ORDER BY $_DBC_cand_id");

		$rows=db_num_rows($result);

		if($rows)
		{
			print("<table cellpadding='0' cellspacing='0' align='center'>
					<tr>
						<td align='top'>\n");

			$propspec_jour_array=$formations_array;
			$propspec_glob_array=$formations_array;

			$candidat_array=array();

			// R�cup�ration du premier jour, comme r�f�rence
			list($cand_id, $fiche_candidat_id, $propspec_id, $annee, $spec, $finalite)=db_fetch_row($result, 0);

			// Sur la date, cette partie sert � savoir si l'ann�e est cod�e sur un ou deux chiffres (2007 => 7 | 2010 => 10)
			if(substr(date("y"), 0,1) == "0")
			{
				$debut_date="200";
				$sub_annee_len=1;
			}
			else
			{
				$debut_date="20";
				$sub_annee_len=2;
			}

			// Codage de l'identifiant : (Y)YMMJJHHMMSSms (ms sur 5 chiffres)
			$old_mois=$__MOIS[substr($cand_id, $sub_annee_len, 2)];
			$old_jour=substr($cand_id, $sub_annee_len+2, 2);

			$nb_candidats=0;

			for($i=0; $i<$rows; $i++)
			{
				list($cand_id, $fiche_candidat_id, $propspec_id, $annee, $spec, $finalite)=db_fetch_row($result, $i);

				$nom_finalite=$tab_finalite[$finalite];

				$mois=$__MOIS[substr($cand_id, $sub_annee_len, 2)];
				$jour=substr($cand_id, $sub_annee_len+2, 2);

				// Stats journali�res
				if($old_jour==$jour && $old_mois==$mois && $i!=($rows-1)) // si $i=($rows-1), c'est la derni�re pr�candidature
				{
					$propspec_jour_array[$propspec_id]["nb"]++;

					// Pour le nombre de fiches diff�rentes (si le candidat �tait d�j� dedans, il n'est pas ajout� une nouvelle fois)
					$candidat_array[$fiche_candidat_id]=1;
				}
				else // On passe au jour suivant ou on traite la derni�re pr�candidature : on affiche le contenu du tableau et on le r�initialise
				{
					if($i==($rows-1)) // derniere pr�candidature  : il faut l'ajouter aux tableaux
					{
						$candidat_array[$fiche_candidat_id]=1;
						$propspec_jour_array[$propspec_id]["nb"]++;
						$propspec_glob_array[$propspec_id]["nb"]++;
					}

					$nb_fiches=count($candidat_array);

					print("<table width='100%'>
							<tr>
								<td class='td-gauche fond_menu2'>
									<font class='Texte_menu2'><b>$old_jour $old_mois</b></font>
								</td>
								<td class='td-gauche fond_menu2' colspan='2'>
									<font class='Texte_menu2'><b>$nb_fiches fiche(s) distinctes</b></font>
								</td>
							</tr>
							<tr>
								<td class='td-gauche fond_menu2'>
									<font class='Texte_menu2'><b>Formations</b></font>
								</td>
								<td class='td-milieu fond_menu2' style='text-align:center;'>
									<font class='Texte_menu2'><b>Ajout�es<br>ce jour</b></font>
								</td>
								<td class='td-droite fond_menu2' style='text-align:center;'>
									<font class='Texte_menu2'><b>Total depuis<br>ouverture</b></font>
								</td>
							</tr>\n");

					// Tri du tableau en fonction des formations
					// ksort($propspec_jour_array);

					if(!uasort($propspec_jour_array,"cmp_formations"))
					{
						if(array_key_exists("__EMAIL_ADMIN", $GLOBALS) && trim($GLOBALS["__EMAIL_ADMIN"])!="")
						{
							mail($GLOBALS["__EMAIL_ADMIN"],$GLOBALS["__ERREUR_SUJET"], "Erreur de la fonction de tri - \Fichier : $_SESSION[CURRENT_FILE]\nFonction cmp_formations\nIdentifiant : $_SESSION[auth_user]");
							die("Erreur de la fonction de tri. Un courriel a �t� envoy� � l'administrateur.");
						}
						else
							die("Erreur de la fonction de tri. Aucun courriel n'a pu �tre envoy� � l'administrateur car aucune adresse �lectronique n'a �t� configur�e.");
					}

					foreach($propspec_jour_array as $current_propspec_id => $form_array)
					{
						if($form_array["annee"]=="")
							$formation=$form_array["spec"] . " " . $form_array["finalite"];
						else
							$formation=$form_array["annee"] . " " . $form_array["spec"] . " " . $form_array["finalite"];

						$nb_global=$propspec_glob_array[$current_propspec_id]["nb"];

						if($nb_global)
							print("<tr>
										<td class='td-gauche fond_menu'>
											<font class='Texte_menu'>$formation</font>
										</td>
										<td class='td-milieu fond_menu' style='text-align:center;'>
											<font class='Texte_menu'>$form_array[nb]</font>
										</td>
										<td class='td-droite fond_menu' style='text-align:center;'>
											<font class='Texte_menu'>$nb_global</font>
										</td>
									</td>\n");
					}

					// Remise � 0 des stats journali�res
					$propspec_jour_array=$formations_array;
					$old_jour=$jour;
					$old_mois=$mois;

					// On n'oublie pas d'ajouter la pr�candidature courante : 1�re du "nouveau jour"
					$candidat_array[$fiche_candidat_id]=1;
					$propspec_jour_array[$propspec_id]["nb"]++;
					
					print("</table>
								<br><br>\n");
				}

				// Stats globales
				$propspec_glob_array[$propspec_id]["nb"]++;
			}

			print("</td>
					</tr>
					</table>\n");
		}

		db_free_result($result);
		db_close($dbr);
	?>
</div>
<?php
	pied_de_page();
?>
</body></html>
