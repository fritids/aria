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

	include "../../configuration/aria_config.php";
	include "$__INCLUDE_DIR_ABS/vars.php";
	include "$__INCLUDE_DIR_ABS/fonctions.php";
	include "$__INCLUDE_DIR_ABS/db.php";


	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	verif_auth("$__GESTION_DIR/login.php");

	if(!in_array($_SESSION['niveau'], array("$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
	{
		header("Location:$__GESTION_DIR/noaccess.php");
		exit();
	}

	// Donn�es du configurator

	// Titre principal de la page
	$titre_page="Candidatures - Gestion de la liste des sp�cialit�s";

	// V2
	$table=array("nom" => "$_DB_specs",
					 "pkey" => "$_DBU_specs_id",
					 "selection" => "$_DBU_specs_nom",
					 "order" => "$_DBU_specs_nom",
					 "separateur" => array("colonne" => "$_DBU_specs_mention_id",
												  "reference" => array(	"table" => "$_DB_mentions",
																				"key" => "$_DBU_mentions_id",
																				"texte" => "$_DBU_mentions_nom" )),

					 "colonnes" => array("$_DBU_specs_nom" => array("nom_complet" => "Intitul� de la sp�cialit�",	// colonne 1
																					"unique" => "0",
																					"not_null" => "1" ),

												"$_DBU_specs_nom_court" => array("nom_complet" => "Intitul� court",	// colonne 2
																							"unique" => "0",
																							"not_null" => "1" ),

												"$_DBU_specs_mention_id" => array("nom_complet" => "Type de sp�cialit�",	// colonne 3
																					 "unique" => "0",
																					 "not_null" => "1",
																					 "reference" => array("table" => "$_DB_mentions",
																												 "key" => "$_DBU_mentions_id",
																												 "description" => "$_DBU_mentions_nom")),

												"$_DBU_specs_comp_id" => array("nom_complet" => "Composante",
																						 "unique" => "0",
																						 "not_null" => "1",
																						 "order" => "$_DBU_composantes_univ_id, $_DBU_composantes_nom",
																						 "reference" => array("table" => "$_DB_composantes",
																													 "key" => "$_DBU_composantes_id",
																													 "description" => "$_DBU_composantes_nom"))
											  )
					);


	// ======================================================
	//	================	Fin des d�clarations	===============
	//	====================================================== 

	$warning="Attention : cette liste est <strong>GLOBALE</strong>, ne la modifiez que si vous �tes s�r de ce que vous faites.";

	include "configurator.php";
?>
