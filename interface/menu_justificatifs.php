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
	else
		$candidat_id=$_SESSION['authentifie'];

	if(!isset($_SESSION["comp_id"]) || (isset($_SESSION["comp_id"]) && $_SESSION["comp_id"]==""))
	{
		session_write_close();
		header("Location:composantes.php");
		exit();
	}

	print("<div class='centered_box'>
				<font class='Texte_16'><strong>R�capitulatifs et Justificatifs</strong></font>
			</div>");

	$result=db_query($dbr,"SELECT $_DBC_cand_id, $_DBC_propspec_id, $_DBC_annees_annee, $_DBC_specs_nom, $_DBC_propspec_finalite,
											$_DBC_cand_statut
											FROM $_DB_propspec, $_DB_annees, $_DB_specs, $_DB_cand
										WHERE $_DBC_propspec_annee=$_DBC_annees_id
										AND $_DBC_propspec_id_spec=$_DBC_specs_id
										AND $_DBC_cand_propspec_id=$_DBC_propspec_id
										AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
										AND $_DBC_cand_periode='$__PERIODE'
										AND $_DBC_cand_candidat_id='$candidat_id'
										AND $_DBC_cand_lock='1'");

	$rows=db_num_rows($result);
	
	if($rows)
	{
		message("Les documents <strong>PDF</strong> suivants sont g�n�r�s lorsque vous cliquez sur les liens propos�s. Cette op�ration peut prendre quelques secondes.
					<br>Le programme <a href='http://www.adobe.com/fr/' class='lien_bleu' target='_blank' style='vertical-align:top;'><strong>Adobe Acrobat Reader</strong></a> 
					peut �tre utilis� pour ouvrir les fichiers PDF", $__INFO);

		print("<table cellpadding='4' cellspacing='0' align='center' border='0'>
				 <tr>
					<td align='left' nowrap='true' width='40' valign='top' style='padding-bottom:20px;'>
						<a href='gen_recapitulatif.php' class='lien_bleu_10' target='_blank'><img src='$__ICON_DIR/pdf_32x32_fond.png' alt='PDF' desc='PDF' border='0'></a>
					</td>
					<td align='left' nowrap='true' valign='middle' style='padding-bottom:20px;'>
						<a href='gen_recapitulatif.php' class='lien_bleu_10' target='_blank'>R�capitulatif des informations que vous avez saisies</a>
					</td>
				</tr>
				<tr>
					<td align='left' nowrap='true' width='40' valign='top' style='padding-bottom:20px;'>
						<img src='$__ICON_DIR/pdf_32x32_fond.png' alt='PDF' desc='PDF' border='0'>
					</td>
					<td align='left' nowrap='true' valign='middle' style='padding-bottom:20px;'>
						<font class='Texte'>
							<strong>Justificatifs � nous fournir pour vos voeux dans l'�tablissement \"$_SESSION[composante]\"</strong>\n");

		for($i=0; $i<$rows; $i++)
		{
			list($cand_id, $propspec_id, $annee, $spec, $finalite, $statut)=db_fetch_row($result, $i);

			if($annee=="")
				print("<br>- <a href='gen_justificatifs.php?cand_id=$cand_id' class='lien_bleu_10' target='_blank'>$spec $tab_finalite[$finalite]</a>\n");
			else
				print("<br>- <a href='gen_justificatifs.php?cand_id=$cand_id' class='lien_bleu_10' target='_blank'>$annee - $spec $tab_finalite[$finalite]</a>\n");
		}
		
		print("</font>
			   </td>
		    </tr>
		    </table>
		    <br>\n");
		    
		message("<center>
                  Attention : cette fonctionnalit� peut poser probl�me avec certaines version du navigateur Internet Explorer.
                  <br>Le navigateur <a href='http://www.mozilla-europe.org/fr/products/firefox/' target='_blank' class='lien_bleu' style='vertical-align:top;'>Mozilla Firefox</a> (gratuit) est en revanche totalement compatible.
				   </center>", $__WARNING);
	}
	else
		message("Ces documents ne sont pas disponibles car :
		         <br>- soit vous n'avez s�lectionn� aucune formation dans cette composante (menu <strong>5 - Pr�candidatures</strong>),
		         <br>- soit aucun de vos voeux n'est encore verrouill� (date �galement visible dans le menu 5).", $__ERREUR);

	db_free_result($result);
?>
