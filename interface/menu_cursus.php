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
				<font class='Texte_16'><strong>$_SESSION[onglet] - Votre cursus scolaire complet (jusqu'� l'ann�e en cours incluse)</strong></font>
			</div>");

	message("- Chaque �tape du cursus devra �tre <b>justifi�e</b>.
				<br>- Apr�s verrouillage de chaque formation choisie, vous recevrez un message contenant la liste des pi�ces � fournir <b>par voie postale</b>.", $__WARNING);

	// Dans l'ordre : �tapes en cours puis diplome obtenus par ann�es d�croissantes
	$result=db_query($dbr,"(SELECT 	$_DBC_cursus_id, $_DBC_cursus_diplome, $_DBC_cursus_intitule, $_DBC_cursus_annee,
												$_DBC_cursus_ecole, $_DBC_cursus_ville, 
												CASE WHEN $_DBC_cursus_pays IN (SELECT $_DBC_pays_nat_ii_iso FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_cursus_pays) 
													THEN (SELECT $_DBC_pays_nat_ii_pays FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_cursus_pays)
													ELSE '' END as cursus_pays, 
												$_DBC_cursus_mention
									FROM $_DB_cursus
									WHERE $_DBC_cursus_candidat_id='$candidat_id'
									AND $_DBC_cursus_annee='0')
								UNION ALL
								  (SELECT 	$_DBC_cursus_id, $_DBC_cursus_diplome, $_DBC_cursus_intitule, $_DBC_cursus_annee,
												$_DBC_cursus_ecole, $_DBC_cursus_ville, 
												CASE WHEN $_DBC_cursus_pays IN (SELECT $_DBC_pays_nat_ii_iso FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_cursus_pays) 
													THEN (SELECT $_DBC_pays_nat_ii_pays FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_cursus_pays)
													ELSE '' END as cursus_pays,
												$_DBC_cursus_mention
									FROM $_DB_cursus
									WHERE $_DBC_cursus_candidat_id='$candidat_id'
									AND $_DBC_cursus_annee!='0'
										ORDER BY $_DBC_cursus_annee DESC)");
	$rows=db_num_rows($result);

	if($rows)
	{
		print("<table align='center'>
					<tr>
						<td colspan='4' class='td-gauche fond_menu2' style='vertical-align:top;'>
							<font class='Texte_menu2'><b>Dipl�me</font>
						</td>
					</tr>\n");

		for($i=0; $i<$rows; $i++)
		{
			list($cid, $dip, $int, $annee_obt,$ecole,$ville,$pays,$mention)=db_fetch_row($result,$i);

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
				$mention="- <b>Ajourn�</b>";
			else
				$mention="";

			// Etat des justificatifs

			$result2=db_query($dbr, "SELECT $_DBC_cursus_justif_statut, $_DBC_cursus_justif_precision
												FROM $_DB_cursus_justif
											 WHERE $_DBC_cursus_justif_cursus_id='$cid'
											 AND $_DBC_cursus_justif_comp_id='$_SESSION[comp_id]'
											 AND $_DBC_cursus_justif_periode='$__PERIODE'");

			if(!db_num_rows($result2))
			{
				$justifie=$__CURSUS_EN_ATTENTE;
				$precision="";
			}
			else
				list($justifie, $precision)=db_fetch_row($result2, 0);

			db_free_result($result2);


			if(!empty($precision))
				$precision="($precision)";

			switch($justifie)
			{
				case	$__CURSUS_NON_JUSTIFIE	:
									$justifie="<font class='Texte_important_menu'>Information non confirm�e</font>";
									break;

				case	$__CURSUS_VALIDE	:
									$justifie="<font class='Textevert_menu'>Justificatifs re�us</font>";
									break;

				case	$__CURSUS_PIECES	:
									$justifie="<font class='Texte_important_menu'>Pi�ces manquantes $precision</font>";
									break;

				case	$__CURSUS_EN_ATTENTE	:
									$justifie="<font class='Texte_menu'>En attente des justificatifs</font>";
									break;

				case $__CURSUS_DES_OBTENTION	:
									$justifie="<font class='Texte_important_menu'>Justificatif � fournir d�s l'obtention du dipl�me</font>";
									break;

				case $__CURSUS_NON_NECESSAIRE	:
									$justifie="<font class='Textevert_menu'>Justificatifs non n�cessaires</font>";
									break;
			}


			// Condition particuli�re pour le cursus:
			// Si une candidature est verrouill�e, le candidat ne peut plus le modifier (sauf en envoyant des pi�ces par courrier)

			if(!$_SESSION["lock"])
			{
				$crypt_params=crypt_params("cid=$cid");
				print("<tr>
							<td class='td-gauche fond_menu' style='vertical-align:middle;'>
								<a href='cursus.php?p=$crypt_params' class='lien_menu_gauche'>$annee_obt : </a>
							</td>
							<td class='td-milieu fond_menu' style='vertical-align:middle;'>
								<a href='cursus.php?p=$crypt_params' class='lien_menu_gauche'>$dip $int $mention <i>($ecole, $ville $pays)</i></a>
							</td>
							<td class='td-milieu fond_menu' style='vertical-align:middle; text-align:center;'>
								$justifie
							</td>
							<td class='td-droite fond_menu' style='vertical-align:middle; text-align:right;'>
								<a href='suppr_cursus.php?p=$crypt_params' target='_self' class='lien_menu_gauche'><img src='$__ICON_DIR/trashcan_full_22x22_slick_menu.png' alt='Supprimer' width='22' height='22' border='0'></a>
							</td>
						</tr>\n");
			}
			else
				print("<tr>
							<td class='td-gauche fond_menu' style='vertical-align:top;'>
								<font class='Texte_menu'>$annee_obt : </font>
							</td>
							<td class='td-milieu fond_menu' style='vertical-align:top;'>
								<font class='Texte_menu'>$dip $int <i>($ecole, $ville $pays)</i></font>
							</td>
							<td class='td-milieu fond_menu' style='vertical-align:middle; text-align:center;'>
								$justifie
							</td>
							<td class='td-droite fond_menu' style='vertical-align:middle; text-align:right;'></td>
						</tr>");
		}

		print("</table>
					<br>");
	}

	db_free_result($result);

	if(!isset($_SESSION["lock"]) || (isset($_SESSION["lock"]) && $_SESSION["lock"]==0))
		print("<div class='centered_box'>
					<a href='cursus.php' target='_self' class='lien2'><img class='icone' src='$__ICON_DIR/add_22x22_fond.png' border='0' alt='Ajouter' desc='Ajouter'></a>
					<a href='cursus.php' target='_self' class='lien2'>Ajouter une �tape � votre cursus</a>
				 </div>");
	else
		message("<center>Une composante a d�j� verrouill� l'un de vos voeux : vous ne pouvez plus modifier votre cursus en ligne
					<br><strong>Toute information compl�mentaire doit �tre envoy�e par courrier aux composantes concern�es</strong></center>", $__INFO);
?>
