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
	// Acc�s direct � une composante dont l'identifiant est pass� en param�tre
	// FICHIER A PLACER EN FONCTION DES LIENS DIRECTS QUE VOUS INDIQUEZ SUR VOS DIFFERENTS SITES
	// - le premier include est � modifier, le reste devrait �tre trouv� automatiquement.

	// Exemple si vous indiquez : https://aria.u-strasbg.fr/acces.php?co=101 pour la composante (id=101), le fichier devra
	// �tre plac� � la racine de votre serveur, m�me si l'interface est dans un sous r�pertoire /aria/ (par exemple).
	
	session_name("preinsc");
	session_start();

	include "configuration/aria_config.php";
	include "$__INCLUDE_DIR_ABS/vars.php";
	include "$__INCLUDE_DIR_ABS/fonctions.php";
	include "$__INCLUDE_DIR_ABS/db.php";

	if(isset($_GET["co"]) && is_numeric($_GET["co"]) && $_GET["co"]>0)
	{
		$comp=str_replace(" ", "", $_GET["co"]);

		$dbr=db_connect();

		if(db_num_rows($res_comp=db_query($dbr, "SELECT $_DBC_composantes_nom FROM $_DB_composantes WHERE $_DBC_composantes_id='$comp'")))
		{
			// R�cup�ration du nom de la composante (pour affichage si aucune session n'est ouverte)
			list($composante_nom)=db_fetch_row($res_comp, 0);
			db_free_result($res_comp);

			// Candidatures ouvertes pour cette composante ?
			$res_ouvertes=db_query($dbr, "SELECT min($_DBC_session_ouverture) FROM $_DB_session
													WHERE $_DBC_session_propspec_id IN (SELECT $_DBC_propspec_id FROM $_DB_propspec
																									WHERE $_DBC_propspec_comp_id='$comp')");

			if(db_num_rows($res_ouvertes))
			{
				list($ouverture)=db_fetch_row($res_ouvertes, 0);

				$limite_date_inf=maketime(1,0,0,$__MOIS_LIMITE_CANDIDATURE,1,$__PERIODE);

				if($ouverture!="" && $ouverture>$limite_date_inf && $ouverture<time())
				{
					$_SESSION["comp_id"]=$comp;
					$location="$__URL_CANDIDAT/index.php?co=$comp";
				}
			}

			db_free_result($res_ouvertes);
		}
		else
		 $location="$__URL_CANDIDAT/index.php";

		db_close($dbr);
	}
	else
		$location="$__URL_CANDIDAT/index.php";

	if(isset($location))
	{
		session_write_close();
		header("Location:$location");
		exit();
	}

	en_tete_index();
	menu_sup_simple();

?>

<div class='main'>
	<?php
		titre_page_icone("Dates d'ouverture des candidatures", "clock_32x32_fond.png", 15, "L");
			
		message("<center>
						Vous avez s�lectionn� la composante suivante : <strong>$composante_nom</strong>
						<br><br>Les candidatures pour cette composante ne sont <b>pas encore ouvertes</b>.
					</center>", $__WARNING);

		if($ouverture!="")
			$ouverture_txt=date_fr("j F Y", $ouverture);
		else
			$ouverture_txt="la date n'est pas encore d�termin�e";
	?>

	<br>
	<table cellpadding="4" cellspacing="0" border="0" align="center"><TR><TD>
	<tr>
		<td class='fond_menu' align='left' nowrap>
			<font class='Texte_menu'><b>Vous pouvez : </b></font>
		</td>
	</tr>
	<tr>
		<td align='justify' nowrap style='padding-bottom:25px;'>
			<font class='Texte'>
				- patienter jusqu'� l'ouverture des candidatures dans cette composante (<?php echo $ouverture_txt; ?>)
				<br>- vous connecter � l'interface principale pour d�poser un dossier dans une autre composante, si vous le d�sirez.
			</font>
		</td>
	</tr>
	<tr>
		<td class='fond_menu' align='left' nowrap>
			<font class='Texte_menu'><b>Liens utiles : </b></font>
		</td>
	</tr>
	<tr>
		<td align='justify' nowrap style='padding-bottom:25px;'>
			<font class='Texte'>
				- pour consulter l'ensemble des dates d'ouvertures : <a href='<?php echo "$__DOC_DIR/limites.php"; ?>' class='lien_bleu_12'><b>cliquez ici</b></a>,
				<br>- pour vous connecter � l'interface principale : <a href='<?php echo "$__MOD_DIR/index.php"; ?>' class='lien_bleu_12'><b>cliquez ici</b></a>.
			</font>
		</td>
	</tr>
	</table>
</div>
<?php
	pied_de_page_candidat();
?>
<br>

</body>
</html>
