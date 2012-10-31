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

// en fonction de l'entier en param�tre, donne l'alignement d'un objet

function get_align($int_align)
{
	if(!is_numeric($int_align))
		return "left";
		
	switch($int_align)
	{
		case 0 : 	return "left";
							break;
		case 1 : 	return "center";
							break;
		case 2 : 	return "right";
							break;
		case 3 : 	return "justify";
							break;
							
		default : return "left";
	}
}

// Pareil mais avec les param�tres de FPDF
function get_fpdf_align($int_align)
{
	if(!is_numeric($int_align))
		return "J";
		
	switch($int_align)
	{
		case 0 : 	return "L";
							break;
		case 1 : 	return "C";
							break;
		case 2 : 	return "R";
							break;
		case 3 : 	return "J";
							break;
							
		default : return "J";
	}
}

// GET_TABLE_NAME
// Determine le nom de la table en fonction du type d'un �l�ment
// ARGUMENT :
// - type d'�l�ment (entier)
// RETOUR
//- nom de la table correspondante et des colonnes utiles
function get_table_name($type)
{
	$return_array=array();

	switch($type)
	{
		case 2:	 	$return_array["table"]="$GLOBALS[_DB_comp_infos_encadre]";
							$return_array["id"]="$GLOBALS[_DBU_comp_infos_encadre_info_id]";
						 	$return_array["ordre"]="$GLOBALS[_DBU_comp_infos_encadre_ordre]";
							return $return_array;
							break;

		case 5:		$return_array["table"]="$GLOBALS[_DB_comp_infos_para]";
							$return_array["id"]="$GLOBALS[_DBU_comp_infos_para_info_id]";
						 	$return_array["ordre"]="$GLOBALS[_DBU_comp_infos_para_ordre]";
							return $return_array;
							break;

		case 6:		$return_array["table"]="$GLOBALS[_DB_comp_infos_fichiers]";
							$return_array["id"]="$GLOBALS[_DBU_comp_infos_fichiers_info_id]";
						 	$return_array["ordre"]="$GLOBALS[_DBU_comp_infos_fichiers_ordre]";
							return $return_array;
							break;

		case 8:		$return_array["table"]="$GLOBALS[_DB_comp_infos_sepa]";
							$return_array["id"]="$GLOBALS[_DBU_comp_infos_sepa_info_id]";
						 	$return_array["ordre"]="$GLOBALS[_DBU_comp_infos_sepa_ordre]";
							return $return_array;
							break;

		default: return FALSE; // normalement l'argument $type est v�rifi� AVANT l'appel � la fonction
	}

}


// d�cide si les boutons monter/descendre doivent �tre affich�s
function show_up_down2($i,$nb_elem,$element_type,$target_type,$target_type2)
{
	if($i!=0)
		print("<a href='move_element.php?co=$i&ct=$element_type&tt=$target_type&dir=0' target='_self'><img src='$GLOBALS[__ICON_DIR]/up_16x16.png' alt='Monter' border='0'></a> ");

	if($i!=($nb_elem-1))
		print("<a href='move_element.php?co=$i&&ct=$element_type&tt=$target_type2&dir=1' target='_self'><img src='$GLOBALS[__ICON_DIR]/down_16x16.png' alt='Descendre' border='0'></a> ");
}


function menu_editeur_3($chemin)
{
	print("<table border='0' cellpadding='2' cellspacing='0' width='100%' align='center'>
					<tr>
						<td height='24' background='$GLOBALS[__IMG_DIR]/fond_menu_haut.jpg' align='left' valign='middle' nowrap='true'>");

	$cnt_path=count($chemin);

	while($cnt_path=count($chemin))
	{
		$nom=key($chemin);
		$lien=current($chemin);

		if(!empty($lien))		
			print("<a href='$lien' target='_self' class='lien_blanc'><b>$nom</b></a>");
		else
			print("<font class='Texteblanc'>$nom</font>");

		// ! dernier �l�ment
		if($cnt_path!=1)
			print("<font class='Texteblanc'>&nbsp;<b>></b>&nbsp;</font>");
		array_shift($chemin);
	}

	print("</td>
					<td height='24' background='$GLOBALS[__IMG_DIR]/fond_menu_haut.jpg' align='right' valign='middle' nowrap='true'>
						<font class='Texteblanc'>
							<a href='$GLOBALS[__GESTION_DIR]/login.php' class='lien_blanc'>D�connecter</a>&nbsp;&nbsp;
						</font>
					</td>
				</tr>
				</table>");
}

// GET_ALL_ELEMENTS
// Construction d'un tableau contenant les �l�ments composant une lettre
// ARGUMENTS :
// - db : ressource correspondant � une connexion � une bdd
// - info_id : identifiant de la lettre concern�e
// RETOUR
// - array contenant les �l�ments (cl�s=ordre des �l�ments)

function get_all_elements($db, $info_id)
{
	// fonction qui recherche tous les �l�ments d'un article et qui retourne un tableau contenant ces �l�ments tri�s

	// initialisation du tableau d'�l�ments
	$elements=array();

	// ENCADRES (type_element = 2)
	$result=db_query($db,"SELECT $GLOBALS[_DBC_comp_infos_encadre_info_id], $GLOBALS[_DBC_comp_infos_encadre_texte], $GLOBALS[_DBC_comp_infos_encadre_txt_align], 
										  $GLOBALS[_DBC_comp_infos_encadre_ordre]
									FROM $GLOBALS[_DB_comp_infos_encadre]
								 WHERE $GLOBALS[_DBC_comp_infos_encadre_info_id]='$info_id'
									ORDER BY $GLOBALS[_DBC_comp_infos_encadre_ordre] ASC");

	$rows=db_num_rows($result);

	// on met chaque encadr� dans le tableau
	for($i=0; $i<$rows ; $i++)
	{
		list($id,$texte,$txt_align,$ordre)=db_fetch_row($result, $i);
		if(array_key_exists("$ordre",$elements)) // l'ordre existe deja : erreur
		{
			$err_file=realpath(__FILE__);
			$line=__LINE__;
			
			if(array_key_exists("__EMAIL_ADMIN", $GLOBALS) && trim($GLOBALS["__EMAIL_ADMIN"])!="")
			{
				mail($GLOBALS["__EMAIL_ADMIN"],$GLOBALS["__ERREUR_SUJET"], "Erreur dans $err_file, ligne $line\n'Base de donn�es incoh�rente'\nIdentifiant : $_SESSION[auth_user]");
				die("Erreur : base de donn�es incoh�rente. Un courriel a �t� envoy� � l'administrateur.");
			}
			else
				die("Erreur : base de donn�es incoh�rente. Aucun courriel n'a pu �tre envoy� � l'administrateur car aucune adresse �lectronique n'a �t� configur�e.");
		}
		else
			$elements["$ordre"]=array("type" => 2, "id" => $id, "texte" => $texte, "txt_align" => $txt_align);
	}
	db_free_result($result);

	// PARAGRAPHES (type_element = 5)
	$result=db_query($db,"SELECT $GLOBALS[_DBC_comp_infos_para_info_id], $GLOBALS[_DBC_comp_infos_para_texte], $GLOBALS[_DBC_comp_infos_para_align], $GLOBALS[_DBC_comp_infos_para_ordre], 
										  $GLOBALS[_DBC_comp_infos_para_gras], $GLOBALS[_DBC_comp_infos_para_italique], $GLOBALS[_DBC_comp_infos_para_taille]
									FROM $GLOBALS[_DB_comp_infos_para] 
								 WHERE $GLOBALS[_DBC_comp_infos_para_info_id]='$info_id'
									ORDER BY $GLOBALS[_DBC_comp_infos_para_ordre] ASC");

	$rows=db_num_rows($result);

	// on met chaque paragraphe dans le tableau
	for($i=0; $i<$rows ; $i++)
	{
		list($id,$texte,$txt_align,$ordre, $gras, $italique, $taille)=db_fetch_row($result, $i);
		if(array_key_exists("$ordre",$elements)) // l'ordre existe deja : erreur
		{
			$err_file=realpath(__FILE__);
			$line=__LINE__;
			
			if(array_key_exists("__EMAIL_ADMIN", $GLOBALS) && trim($GLOBALS["__EMAIL_ADMIN"])!="")
			{
				mail($GLOBALS["__EMAIL_ADMIN"],$GLOBALS["__ERREUR_SUJET"], "Erreur dans $err_file, ligne $line\n'Base de donn�es incoh�rente'\nIdentifiant : $_SESSION[auth_user]");
				die("Erreur : base de donn�es incoh�rente. Un courriel a �t� envoy� � l'administrateur.");
			}
			else
				die("Erreur : base de donn�es incoh�rente. Aucun courriel n'a pu �tre envoy� � l'administrateur car aucune adresse �lectronique n'a �t� configur�e.");
		}
		else
			$elements["$ordre"]=array("type" => 5, "id" => $id, "texte" => $texte, "txt_align" => $txt_align, "gras" => $gras, "italique" => $italique, "taille" => $taille);
	}
	db_free_result($result);

	// FICHIERS (type_element = 6)
	$result=db_query($db,"SELECT $GLOBALS[_DBC_comp_infos_fichiers_info_id], $GLOBALS[_DBC_comp_infos_fichiers_texte], $GLOBALS[_DBC_comp_infos_fichiers_fichier],
										  $GLOBALS[_DBC_comp_infos_fichiers_ordre]
									FROM	 $GLOBALS[_DB_comp_infos_fichiers]
								 WHERE $GLOBALS[_DBC_comp_infos_fichiers_info_id]='$info_id'
									ORDER BY $GLOBALS[_DBC_comp_infos_fichiers_ordre] ASC");

	$rows=db_num_rows($result);

	// on met chaque encadr� dans le tableau
	for($i=0; $i<$rows ; $i++)
	{
		list($id,$texte,$fichier,$ordre)=db_fetch_row($result, $i);
		if(array_key_exists("$ordre",$elements)) // l'ordre existe deja : erreur
		{
			$err_file=realpath(__FILE__);
			$line=__LINE__;
			
			if(array_key_exists("__EMAIL_ADMIN", $GLOBALS) && trim($GLOBALS["__EMAIL_ADMIN"])!="")
			{
				mail($GLOBALS["__EMAIL_ADMIN"],$GLOBALS["__ERREUR_SUJET"], "Erreur dans $err_file, ligne $line\n'Base de donn�es incoh�rente'\nIdentifiant : $_SESSION[auth_user]");
				die("Erreur : base de donn�es incoh�rente. Un courriel a �t� envoy� � l'administrateur.");
			}
			else
				die("Erreur : base de donn�es incoh�rente. Aucun courriel n'a pu �tre envoy� � l'administrateur car aucune adresse �lectronique n'a �t� configur�e.");
		}
		else
			$elements["$ordre"]=array("type" => 6, "id" => $id, "texte" => $texte, "fichier" => $fichier);
	}
	db_free_result($result);

	// S�parateurs (type 8)
	$result=db_query($db,"SELECT $GLOBALS[_DBC_comp_infos_sepa_info_id], $GLOBALS[_DBC_comp_infos_sepa_ordre] FROM $GLOBALS[_DB_comp_infos_sepa]	
									WHERE $GLOBALS[_DBC_comp_infos_sepa_info_id]='$info_id'
								 ORDER BY $GLOBALS[_DBC_comp_infos_sepa_ordre] ASC");

	$rows=db_num_rows($result);

	// on met chaque s�parateur dans le tableau
	for($i=0; $i<$rows ; $i++)
	{
		list($id,$ordre)=db_fetch_row($result, $i);
		if(array_key_exists("$ordre",$elements)) // l'ordre existe deja : erreur
		{
			$err_file=realpath(__FILE__);
			$line=__LINE__;
			
			if(array_key_exists("__EMAIL_ADMIN", $GLOBALS) && trim($GLOBALS["__EMAIL_ADMIN"])!="")
			{
				mail($GLOBALS["__EMAIL_ADMIN"],$GLOBALS["__ERREUR_SUJET"], "Erreur dans $err_file, ligne $line\n'Base de donn�es incoh�rente'\nIdentifiant : $_SESSION[auth_user]");
				die("Erreur : base de donn�es incoh�rente. Un courriel a �t� envoy� � l'administrateur.");
			}
			else
				die("Erreur : base de donn�es incoh�rente. Aucun courriel n'a pu �tre envoy� � l'administrateur car aucune adresse �lectronique n'a �t� configur�e.");
		}
		else
			$elements["$ordre"]=array("type" => 8, "id" => $id);
	}
	db_free_result($result);
	
	return($elements);
}


?>
