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

// Remplacement des macros pr�d�finies dans l'�diteur de lettres
// Entr�e :
// - texte � traiter
// - informations du candidat (tableau)
// - fili�re
// - cursus (tableau)
// Note sur les macros : les majuscules sont importantes !!

function traitement_macros($txt, $cand_array, $cursus_array)
{
	// Civilit�
	$txt=preg_replace("/%Civilit.%/u", ucfirst(strtolower($cand_array["civ_texte"])), $txt);
	$txt=preg_replace("/%civilit.%/u", strtolower($cand_array["civ_texte"]), $txt);
	$txt=preg_replace("/%CIVILIT.%/u", strtoupper($cand_array["civ_texte"]), $txt);

	// Nom
	$txt=preg_replace("/%Nom%/", ucfirst(mb_strtolower($cand_array["nom"])), $txt);
	$txt=preg_replace("/%nom%/", mb_strtolower($cand_array["nom"]), $txt);
	$txt=preg_replace("/%NOM%/", mb_strtoupper($cand_array["nom"]), $txt);

	// Pr�nom
	$txt=preg_replace("/%Pr.nom%/u", ucfirst(mb_strtolower($cand_array["prenom"])), $txt);
	$txt=preg_replace("/%pr.nom%/u", mb_strtolower($cand_array["prenom"]), $txt);
	$txt=preg_replace("/%PR.NOM%/u", mb_strtoupper($cand_array["prenom"]), $txt);

	// Date de naissance
	$txt=str_ireplace("%naissance%", $cand_array["naissance"], $txt);

	// Ville de naissance
	$txt=str_ireplace("%ville_naissance%", $cand_array["lieu_naissance"], $txt);

	// Pays de naissance
	$txt=str_ireplace("%pays_naissance%", $cand_array["pays_naissance"], $txt);

	// Ann�e universitaire
	$Y=date("Y");
	$Z=$Y+1;
	$annee_txt="$Y-$Z";
	$txt=preg_replace("/%ann.e_universitaire%/u", $annee_txt, $txt);

	// Cursus
	$count_cursus=count($cursus_array);

	if($count_cursus)
	{
		// on ne prend que les 2 derniers diplomes obtenus
		// TODO : � v�rifier
		$texte_cursus="";

		if($count_cursus>2)
			$i=$count_cursus-2;
		else
			$i=0;

		for(; $i<$count_cursus; $i++)
		{
			if(isset($cursus_array[$i]["lieu"]))
				$texte_cursus .=$cursus_array[$i]["cursus"] . " " . $cursus_array[$i]["lieu"] . " (". $cursus_array[$i]["date"] . ")\n";
			else
				$texte_cursus .=$cursus_array[$i]["cursus"] . " (". $cursus_array[$i]["date"] . ")\n";
		}

		$txt=str_ireplace("%cursus%", $texte_cursus, $txt);
	}
	else
		$txt=str_ireplace("%cursus%", "- N�ant", $txt);

	// Grammaire : %masculin/f�minin%

	if(preg_match_all("/%[a-zA-Z����������������������������]+\/[a-zA-Z����������������������������]+%/", $txt, $resultats))
   {
      foreach($resultats[0] as $valeur)
      {
         $vals=explode("/", $valeur);

         $masculin=str_replace("%","", $vals[0]);
         $feminin=str_replace("%","", $vals[1]);

         if($cand_array["civilite"]=="M")
            $txt=str_replace($valeur, $masculin, $txt);
         else
            $txt=str_replace($valeur, $feminin, $txt);
      }
   }

	// Date

	$txt=str_ireplace("%date%", date_fr("j F Y", time()), $txt);

	return $txt;
}

?>
