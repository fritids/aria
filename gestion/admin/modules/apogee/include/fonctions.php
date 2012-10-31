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
// G�n�ration du code d'inscription administrative sur Apog�e (macro %code% sur les lettres)
// On a besoin de l'identifiant du candidat et de celui de la candidature

// Cette fonction �tend les macros disponibles dans l'�diteur de lettres
// Arguments en entr�e :
// - texte � traiter
// - tableau (array) du candidat
// - tableau (array) de la candidature
// - tableau (array) du cursus

// D�finition de code � "�valuer" tel quel : attention, le contexte lors de son ex�cution sera celui du
// fichier "gestion/admin/editeur/include/editeur_fonctions.php", pas celui lorsque ce fichier est inclus !
// => attention donc aux variables (i.e aux include n�cessaires) : elles doivent �tre connues par le code
// au moment de son ex�cution

$_SESSION["__MACROS_USERS"]["moduleapogee_macro_code_ia"]='
	include "$GLOBALS[__PLUGINS_DIR_ABS]/apogee/include/db.php";

	if(stristr($txt, "%code%") || stristr($txt, "%code_choix_multiples%"))
	{
		// Code apog�e de l\'universit�

		$res_code_univ=db_query($dbr, "SELECT $_module_apogee_DBC_config_code FROM  $_module_apogee_DB_config
												WHERE $_module_apogee_DBC_config_comp_id=\'$candidature_array[comp_id]\'");

		$rows=db_num_rows($res_code_univ);

		if($rows)	// Normalement, un seul r�sultat (un code par universit�)
			list($code_univ)=db_fetch_row($res_code_univ, 0);
		else
			$code_univ="";

		db_free_result($res_code_univ);
	}

	if(stristr($txt, "%code%"))
	{
		// Code apog�e de la formation
		$res_code_apogee=db_query($dbr, "SELECT $_module_apogee_DBC_formations_cet FROM  $_module_apogee_DB_formations
													WHERE $_module_apogee_DBC_formations_propspec_id=\'$candidature_array[propspec_id]\'");

		$rows=db_num_rows($res_code_apogee);

		if($rows)	// Normalement, un seul r�sultat (un code par formation)
			list($code_etape)=db_fetch_row($res_code_apogee, 0);
		else
			$code_etape="";

		db_free_result($res_code_apogee);

		// Question : doit-on quand m�me g�n�rer le code d\'inscription si le code_etape ou le code universit� ne sont pas renseign�s ?

		$code_annee=substr($candidature_array["periode"],-2); // deux derniers chiffres de l\'ann�e
		$code_nom=mb_strtoupper(substr(str_replace("\'","", str_replace(" ","",$cand_array["nom"])),0,2));
		$code_prenom=mb_strtoupper(substr(str_replace("\'","", str_replace(" ","",$cand_array["prenom"])),0,1));
		$code_annee_naiss=date_fr("y", $cand_array["naissance_unix"]);
		$code_mois_naiss=date_fr("m", $cand_array["naissance_unix"]);
		$code_jour_naiss=date_fr("d", $cand_array["naissance_unix"]);

		$code="$code_univ$code_annee$code_nom$code_prenom$code_annee_naiss$code_mois_naiss$code_jour_naiss$code_etape";

		$txt=str_ireplace("%code%", $code, $txt);
	}

	if(stristr($txt, "%code_choix_multiples%"))
	{
		$candidatures_multiples_array=__get_candidatures_multiples($dbr, $candidature_array["id"]);

		$ordre_dernier_choix=count($candidatures_multiples_array)-1;

		foreach($candidatures_multiples_array as $ordre_cand => $cand_m_array)
		{
			if($cand_m_array["decision"]==$GLOBALS["__DOSSIER_ADMIS"] || $cand_m_array["decision"]==$GLOBALS["__DOSSIER_ADMISSION_CONFIRMEE"] || $cand_m_array["decision"]==$GLOBALS["__DOSSIER_SOUS_RESERVE"])
			{
				// Code apog�e de la formation
				$res_code_apogee=db_query($dbr, "SELECT $_module_apogee_DBC_formations_cet FROM  $_module_apogee_DB_formations
																		WHERE $_module_apogee_DBC_formations_propspec_id=\'$cand_m_array[propspec_id]\'");

				$rows=db_num_rows($res_code_apogee);

				if($rows)	// Normalement, un seul r�sultat (un code par formation)
					list($code_etape)=db_fetch_row($res_code_apogee, 0);
				else
					$code_etape="";

				db_free_result($res_code_apogee);

				// Question : doit-on quand m�me g�n�rer le code d\'inscription si le code_etape ou le code universit� ne sont pas renseign�s ?

				$code_annee=substr($candidature_array["periode"],-2); // deux derniers chiffres de l\'ann�e
				$code_nom=mb_strtoupper(substr(str_replace("\'","", str_replace(" ","",$cand_array["nom"])),0,2));
				$code_prenom=mb_strtoupper(substr(str_replace("\'","", str_replace(" ","",$cand_array["prenom"])),0,1));
				$code_annee_naiss=date_fr("y", $cand_array["naissance_unix"]);
				$code_mois_naiss=date_fr("m", $cand_array["naissance_unix"]);
				$code_jour_naiss=date_fr("d", $cand_array["naissance_unix"]);
				$code="$code_univ$code_annee$code_nom$code_prenom$code_annee_naiss$code_mois_naiss$code_jour_naiss$code_etape";

				$txt=preg_replace("/%code_choix_multiples%/i", $code, $txt);

				break;
			}
		}
	}
	
	if(stristr($txt, "%ADR_PRIMO%"))
	{
		// Adresse du site pour l\'inscription des primo entrants
		$res_adr_primo=db_query($dbr, "SELECT $_module_apogee_DBC_config_adr_primo FROM  $_module_apogee_DB_config
                                     WHERE $_module_apogee_DBC_config_comp_id=\'$candidature_array[comp_id]\'");

		$rows=db_num_rows($res_adr_primo);

		if($rows)
			list($adr_primo)=db_fetch_row($res_adr_primo, 0);
		else
			$adr_primo="[Configuration incompl�te ; aucune adresse n\'a �t� valid�e]";

		db_free_result($res_adr_primo);

		$txt=str_ireplace("%ADR_PRIMO%", $adr_primo, $txt);
	}
	
	if(stristr($txt, "%ADR_REINS%"))
	{
		// Adresse du site pour les r�inscriptions
		$res_adr_reins=db_query($dbr, "SELECT $_module_apogee_DBC_config_adr_reins FROM  $_module_apogee_DB_config
                                     WHERE $_module_apogee_DBC_config_comp_id=\'$candidature_array[comp_id]\'");

		$rows=db_num_rows($res_adr_reins);

		if($rows)
			list($adr_reins)=db_fetch_row($res_adr_reins, 0);
		else
			$adr_reins="[Configuration incompl�te ; aucune adresse n\'a �t� valid�e]";

		db_free_result($res_adr_reins);

		$txt=str_ireplace("%ADR_REINS%", $adr_reins, $txt);
	}
	
	if(stristr($txt, "%ADR_RDV%"))
	{
		// Adresse du site pour les inscriptions avec prise de rendez-vous
		$res_adr_rdv=db_query($dbr, "SELECT $_module_apogee_DBC_config_adr_rdv FROM  $_module_apogee_DB_config
                                     WHERE $_module_apogee_DBC_config_comp_id=\'$candidature_array[comp_id]\'");

		$rows=db_num_rows($res_adr_rdv);

		if($rows)
			list($adr_rdv)=db_fetch_row($res_adr_rdv, 0);
		else
			$adr_rdv="[Configuration incompl�te ; aucune adresse n\'a �t� valid�e]";

		db_free_result($res_adr_rdv);

		$txt=str_ireplace("%ADR_RDV%", $adr_rdv, $txt);
	}
	
	if(stristr($txt, "%ADR_COND%"))
	{
		// Adresse du site pour les conditions d\'utilisation et autres consignes
		$res_adr_conditions=db_query($dbr, "SELECT $_module_apogee_DBC_config_adr_conditions FROM  $_module_apogee_DB_config
                                          WHERE $_module_apogee_DBC_config_comp_id=\'$candidature_array[comp_id]\'");

		$rows=db_num_rows($res_adr_conditions);

		if($rows)
			list($adr_conditions)=db_fetch_row($res_adr_conditions, 0);
		else
			$adr_conditions="[Configuration incompl�te ; aucune adresse n\'a �t� valid�e]";

		db_free_result($res_adr_conditions);

		$txt=str_ireplace("%ADR_COND%", $adr_conditions, $txt);
	}';

