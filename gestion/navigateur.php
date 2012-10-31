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

	if(isset($_SESSION["comp_id"]) && !isset($_GET["d"]) && !isset($_GET["co"]))
		$temp_comp_id=$_SESSION["comp_id"];

	session_unset();

	include "../configuration/aria_config.php";
	include "$__INCLUDE_DIR_ABS/vars.php";
	include "$__INCLUDE_DIR_ABS/fonctions.php";
   include "$__INCLUDE_DIR_ABS/db.php";

   $dbr=db_connect();

	if(isset($temp_comp_id))
		$_SESSION["comp_id"]=$temp_comp_id;
	elseif(isset($_GET["co"]))
	{
		$_GET["co"]=str_replace(" ", "", $_GET["co"]);
		
		if(db_num_rows(db_query($dbr, "SELECT  * FROM $_DB_composantes  WHERE $_DBC_composantes_id='$_GET[co]'")))
			$_SESSION["comp_id"]=$_GET["co"];
	}

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;
	$_SESSION["auth"]=0;

	unset($_SESSION["conditions_ok"]);
	
	en_tete_simple();
	menu_sup_simple();
?>

<div class='main'>
	<?php
		titre_page_icone("Votre navigateur semble poser probl�me", "button_cancel_32x32_fond.png", 30, "L");
	?>

	<table cellpadding="0" cellspacing="0" border='0' align='center'>
	<tr>
		<td align='left' width='40' nowrap='true'>
			<a href='http://www.mozilla.com/firefox/' target='_blank'><img src='images/product-firefox.png' border='0'></a>
		</td>
		<td align='left' nowrap='true'>
			<font class='Texte'>
				Nous recommandons l'utilisation du navigateur <b>Mozilla Firefox</b> (disponible <a href='http://www.mozilla.com/firefox/' class='lien_bleu_14' target='_blank'>sur cette page</a>) ou du navigateur <b>Internet Explorer</b> version 6 ou sup�rieure.
			</font>
		</td>
	</tr>
	<tr>
		<td align='justify' colspan='2' style='padding-top:20px;'>
			<font class='Texte'>
				<b>Proc�dure � suivre :</b>
				<br>1 - Cliquez <a href='http://www.mozilla.com/firefox/' class='lien_bleu_14' target='_blank'>sur ce lien</a> et t�l�chargez la derni�re version propos�e pour votre syst�me d'exploitation.
				<br>
				<br>2 - Une fois le programme d'installation enregistr� sur votre ordinateur, ex�cutez-le et laissez-vous guider.
				<br>
				<br>3 - A la fin de l'installation, fermez le navigateur courant, ex�cutez Mozilla Firefox et reconnectez-vous � l'Interface de Pr�candidatures en Ligne.
			</font>
		</td>
	</tr>
	</table>
</div>
<?php
	pied_de_page();
?>
</body>
</html>

