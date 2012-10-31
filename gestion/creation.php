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

	if(!in_array($_SESSION['niveau'], array("$__LVL_SCOL_MOINS","$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
	{
		header("Location:$__MOD_DIR/gestion/noaccess.php");
		exit();
	}

	$dbr=db_connect();

	// D�verrouillage, au cas o�
	if(isset($_SESSION["candidat_id"]))
		cand_unlock($dbr, $_SESSION["candidat_id"]);

	if(isset($_POST["valider"]) || isset($_POST["valider_x"]))
	{
		// v�rification des valeurs entr�es dans le formulaire
		// TODO : v�rifications pouss�es ?
		$civilite=$_POST["civilite"];

		// $nom=mb_strtoupper(trim($_POST["nom"]));
		$nom=str_replace("'","''", str_replace("\\","", (mb_strtoupper(trim($_POST["nom"])))));
		$nom_naissance=str_replace("'","''", str_replace("\\","", (mb_strtoupper(trim($_POST["nom_naissance"])))));
		
		if($nom_naissance=="")
		   $nom_naissance=$nom;
		
		// $prenom=ucwords(strtolower(trim($_POST["prenom"])));
		// $deuxieme_prenom=ucwords(strtolower(trim($_POST["prenom2"])));
		$prenom=str_replace("'","''", str_replace("\\","", (ucwords(mb_strtolower(trim($_POST["prenom"]))))));
		// $deuxieme_prenom=ucwords(strtolower(trim($_POST["prenom2"])));
		$deuxieme_prenom=str_replace("'","''", str_replace("\\","", (ucwords(mb_strtolower(trim($_POST["prenom2"]))))));

		$jour=trim($_POST["jour"]);
		$mois=trim($_POST["mois"]);
		$annee=trim($_POST["annee"]);

		$adresse_1=str_replace("'","''", str_replace("\\","", (mb_strtolower(trim($_POST["adresse_1"])))));
		$adresse_2=str_replace("'","''", str_replace("\\","", (mb_strtolower(trim($_POST["adresse_2"])))));
		$adresse_3=str_replace("'","''", str_replace("\\","", (mb_strtolower(trim($_POST["adresse_3"])))));
		$adr_cp=str_replace("'","''", str_replace("\\","", ($_POST["adr_cp"])));
		$adr_ville=str_replace("'","''", str_replace("\\","", ($_POST["adr_ville"])));
		$adr_pays_code=$_POST["adr_pays"];      

		$lieu_naissance=str_replace("'","''", str_replace("\\","", (ucwords(strtolower(trim($_POST["lieu_naissance"]))))));
		$dpt_naissance=$_POST["dpt_naissance"];
		$pays_naissance_code=$_POST["pays_naissance"];

		$email=trim($_POST["email"]);
		$telephone=trim($_POST["telephone"]);
 		$telephone_portable=trim($_POST["telephone_portable"]);

		$nationalite_code=$_POST["nationalite"];

		$deja_inscrit=$_POST["deja_inscrit"];

		if($deja_inscrit!="0" && $deja_inscrit!="1")
			$err_deja_inscrit="1";

      $num_ine=str_replace(" ", "", $_POST["num_ine"]);

		if($num_ine!="" && check_ine_bea($num_ine))
			$erreur_ine_bea=1;

      if($deja_inscrit==1 && $num_ine=="")
         $erreur_ine_obligatoire=1;

		$annee_premiere_inscr=$_POST["annee_premiere_inscr"];

		if($deja_inscrit==0)
			$annee_premiere_inscr="";
		elseif(!ctype_digit($annee_premiere_inscr) || strlen($annee_premiere_inscr)!=4 || $annee_premiere_inscr<1900 || $annee_premiere_inscr>"$__PERIODE")
			$err_annee_premiere_inscr=1;

		$serie_bac=$_POST["serie_bac"];

		if($serie_bac=="")
			$err_serie_bac=1;

		// Ajouter le cas "sans bac"
		$annee_bac=$_POST["annee_bac"];

		if(!ctype_digit($annee_bac) || strlen($annee_bac)!=4 || $annee_bac<1900 || $annee_bac>"$__PERIODE")
			$err_annee_bac=1;
	
		$champs_obligatoires=array($nom,$prenom,$jour,$mois,$annee,$lieu_naissance,$pays_naissance_code,$adresse_1,$nationalite_code, $adr_cp, $adr_ville, $adr_pays_code,$annee_bac,$serie_bac,$deja_inscrit);
		$cnt_obl=count($champs_obligatoires);

		for($i=0; $i<$cnt_obl; $i++) // v�rification des champs obligatoires
		{
			if($champs_obligatoires[$i]=="")
			{
				$champ_vide=1;
				$i=$cnt_obl;
			}
		}

		// Le d�partement de naissance est obligatoire pour ceux n�s en France
		if($pays_naissance_code=="FR" && $dpt_naissance!="2A" && $dpt_naissance!="2B" && (!ctype_digit($dpt_naissance) || $dpt_naissance<1 || ($dpt_naissance>95 && ($dpt_naissance<971 || $dpt_naissance>987))))
			$bad_dpt_naissance=1;

		if(!ctype_digit($mois) || $mois<=0 || $mois >12 || !ctype_digit($jour) || $jour<=0 || $jour > 31 || !ctype_digit($annee) || $annee<=0 || $annee>=date('Y'))
			$erreur_date_naissance=1;
		else
		{
			$date_naissance=MakeTime(12,0,0,$mois,$jour,$annee); // heure : midi (pour �viter les probl�mes de d�callages horaires)

			// V�rification d'unicit� - On se base sur le nom, le pr�nom et la date de naissance
			// TODO : v�rifier si ces crit�res sont suffisants

			$result=db_query($dbr,"SELECT $_DBC_candidat_nom, $_DBC_candidat_prenom, $_DBC_candidat_date_naissance
												FROM $_DB_candidat
											WHERE $_DBC_candidat_nom ILIKE '$nom'
											AND $_DBC_candidat_prenom ILIKE '$prenom'
											AND $_DBC_candidat_date_naissance='$date_naissance'");
			$rows=db_num_rows($result);

			if($rows)
				$id_existe=1;

			db_free_result($result);
		}

		if(!isset($champ_vide) && !isset($id_existe) && !isset($erreur_date_naissance) && !isset($erreur_ine_bea) && !isset($err_deja_inscrit)
			&& !isset($err_annee_premiere_inscr) && !isset($err_serie_bac) && !isset($err_annee_bac) && !isset($erreur_ine_obligatoire))
		{
			// Les donn�es du nouvel utilisateur sont compl�tes (pas forc�ment bonnes, mais �a le p�nalisera)
			// On peut cr�er l'identifiant et le code, l'ins�rer dans la base et envoyer le mail

			// Cr�ation de l'identifiant
			$new_identifiant=str_replace(" ","",mb_strtolower($nom)); // base de l'identifiant
			$new_identifiant=str_replace("-","",$new_identifiant);

			// initialisation de la boucle
			$prenom2=mb_strtolower(str_replace(" ","",$prenom));
			$nb_lettres_prenom=1;
			$iteration=0;
			$len_prenom=strlen($prenom);
/*
			$result=db_query($dbr,"SELECT $_DBC_candidat_id FROM $_DB_candidat WHERE $_DBC_candidat_identifiant='$new_identifiant'");
			$rows=db_num_rows($result);
*/
			while(db_num_rows(db_query($dbr,"SELECT $_DBC_candidat_id FROM $_DB_candidat WHERE $_DBC_candidat_identifiant='$new_identifiant'")))
			{
				if($nb_lettres_prenom<=$len_prenom) // si on peut encore utiliser le pr�nom
				{
					$new_identifiant= substr($prenom2,0,$nb_lettres_prenom) . "." . mb_strtolower($nom);
					$nb_lettres_prenom++;
				}
				else
				{
					$iteration++;
					$new_identifiant=$prenom2 . "." . mb_strtolower($nom) . $iteration;
				}
			}

			// g�n�ration du Code Personnel
			srand((double)microtime()*1000000);
			$code_conf=mb_strtoupper(md5(rand(0,9999)));
			$new_code=substr($code_conf, 17, 8);

			$fiche_manuelle=1;
			$candidat_lock=$candidat_lockdate=$derniere_connexion=$cursus_en_cours=0;
         $derniere_ip=$dernier_host=$dernier_user_agent=$derniere_erreur_code="";
         
			$new_id=db_locked_query($dbr, $_DB_candidat, "INSERT INTO $_DB_candidat ($_DBU_candidat_id,
                                                                                  $_DBU_candidat_civilite, 
                                                                                  $_DBU_candidat_nom, 
                                                                                  $_DBU_candidat_nom_naissance, 
                                                                                  $_DBU_candidat_prenom, 
                                                                                  $_DBU_candidat_prenom2, 
                                                                                  $_DBU_candidat_date_naissance, 
                                                                                  $_DBU_candidat_lieu_naissance,
                                                                                  $_DBU_candidat_dpt_naissance, 
                                                                                  $_DBU_candidat_pays_naissance, 
                                                                                  $_DBU_candidat_nationalite, 
                                                                                  $_DBU_candidat_telephone, 
                                                                                  $_DBU_candidat_telephone_portable, 
                                                                                  $_DBU_candidat_adresse_1, 
                                                                                  $_DBU_candidat_adresse_2, 
                                                                                  $_DBU_candidat_adresse_3, 
                                                                                  $_DBU_candidat_adresse_cp, 
                                                                                  $_DBU_candidat_adresse_ville, 
                                                                                  $_DBU_candidat_adresse_pays, 
                                                                                  $_DBU_candidat_numero_ine, 
                                                                                  $_DBU_candidat_email, 
                                                                                  $_DBU_candidat_identifiant, 
                                                                                  $_DBU_candidat_code_acces, 
                                                                                  $_DBU_candidat_connexion, 
                                                                                  $_DBU_candidat_derniere_ip, 
                                                                                  $_DBU_candidat_dernier_host, 
                                                                                  $_DBU_candidat_dernier_user_agent, 
                                                                                  $_DBU_candidat_derniere_erreur_code, 
                                                                                  $_DBU_candidat_manuelle, 
                                                                                  $_DBU_candidat_cursus_en_cours, 
                                                                                  $_DBU_candidat_lock, 
                                                                                  $_DBU_candidat_lockdate, 
                                                                                  $_DBU_candidat_deja_inscrit, 
                                                                                  $_DBU_candidat_annee_premiere_inscr, 
                                                                                  $_DBU_candidat_annee_bac,
                                                                                  $_DBU_candidat_serie_bac)
                                                                                  
                                                                           VALUES('##NEW_ID##',
                                                                                 '$civilite',
                                                                                 '$nom',
                                                                                 '$nom_naissance',
                                                                                 '$prenom',
                                                                                 '$deuxieme_prenom',
                                                                                 '$date_naissance',
                                                                                 '$lieu_naissance',
                                                                                 '$dpt_naissance',
                                                                                 '$pays_naissance_code',
                                                                                 '$nationalite_code',
                                                                                 '$telephone',
                                                                                 '$telephone_portable',
                                                                                 '$adresse_1',
                                                                                 '$adresse_2',
                                                                                 '$adresse_3',
                                                                                 '$adr_cp',
                                                                                 '$adr_ville',
                                                                                 '$adr_pays_code',
                                                                                 '$num_ine',
                                                                                 '$email',
                                                                                 '$new_identifiant',
                                                                                 '$new_code',
                                                                                 '$derniere_connexion',
                                                                                 '$derniere_ip',
                                                                                 '$dernier_host',
                                                                                 '$dernier_user_agent',
                                                                                 '$derniere_erreur_code',
                                                                                 '$fiche_manuelle',
                                                                                 '$cursus_en_cours',
                                                                                 '$candidat_lock',
                                                                                 '$candidat_lockdate',
                                                                                 '$deja_inscrit',
                                                                                 '$annee_premiere_inscr',
                                                                                 '$annee_bac',
                                                                                 '$serie_bac')");

			// renseignements minimum pour l'historique			
			$_SESSION['tab_candidat']=array("nom" => $nom, "prenom" => $prenom, "email" => $email);

			write_evt($dbr, $__EVT_ID_G_ID, "Cr�ation fiche manuelle $nom $prenom", $new_id, $new_id, "INSERT INTO $_DB_candidat VALUES('$new_id','$civilite','$nom','$prenom','$date_naissance','$nationalite_code','$telephone','".$adresse_1." ".$adresse_2." ".$adresse_3."','$num_ine','$email','$new_identifiant','$new_code','0','$lieu_naissance', '$deuxieme_prenom','','','$pays_naissance_code','$adr_cp','$adr_ville','$adr_pays_code','','','$fiche_manuelle', '$candidat_lock','$candidat_lockdate','$dpt_naissance','$nom_naissance','$telephone_portable')");

			// db_query($dbr, "INSERT INTO $_DB_verrouillage VALUES('$new_id', '$_SESSION[comp_id]', '$lock', '$new_lockdate', '$__PERIODE')");

			// Message au candidat
         $corps_message="Bonjour $civilite ". preg_replace("/[']+/", "'", $nom) .",

Bienvenue sur l'Interface de Candidatures !

Cette interface vous permet de d�poser un ou plusieurs dossiers de candidatures dans les composantes enregistr�es.

<strong><u>Quelques conseils pour d�buter :</u></strong>

&#8226;&nbsp;&nbsp;<a href='$__DOC_DIR/documentation.php' target='_blank' class='lien_bleu_12'><b>la documentation</b> : elle r�sume toute la proc�dure</a>

&#8226;&nbsp;&nbsp;<b>le menu sup�rieur</b> : il vous permet d'acc�der aux fonctionnalit�s de l'interface :
&nbsp;&nbsp;- \"Choisir une autre composante\" pour d�poser un dossier dans un autre �tablissement,
&nbsp;&nbsp;- \"Votre fiche\" pour compl�ter vos informations (menu par d�faut),
&nbsp;&nbsp;- \"Rechercher une formation\" pour trouver la composante proposant la formation que vous cherchez,
&nbsp;&nbsp;- \"Messagerie\" : l'application vous enverra automatiquement des messages (avec notification de r�ception sur votre adresse �lectronique), et vous pourrez �galement l'utiliser pour contacter la scolarit�,
&nbsp;&nbsp;- \"Mode d'emploi\" : un lien permanent vers la documentation.

N'h�sitez pas � explorer cette interface !


Vous pouvez maintenant cliquer sur <strong>\"Votre fiche\"</strong> et commencer � compl�ter les informations demand�es.


Bien cordialement,


$__SIGNATURE_COURRIELS";

         $dest_array=array("0" => array("id"       => "$new_id",
                                        "civ"      => "$civilite",
                                        "nom"       => preg_replace("/[']+/", "'", $nom),
                                        "prenom"    => preg_replace("/[']+/", "'", $prenom),
                                        "email"      => "$email"));

         write_msg("", array("id" => "0", "nom" => "Syst�me", "prenom" => "", "composante" => "", "universite" => "$__SIGNATURE_COURRIELS"),
                   $dest_array, "Bienvenue !", $corps_message, "$nom $prenom", $__FLAG_MSG_NO_NOTIFICATION);


         // Enregistrement : identifiants
         
         if(FALSE!==strpos($email, "@"))
         {
            $headers = "MIME-Version: 1.0\r\nFrom: $__EMAIL_ADMIN\r\nReply-To: $__EMAIL_ADMIN\r\nContent-Type: text/plain; charset=ISO-8859-15\r\nContent-transfer-encoding: 8bit\r\n\r\n";
            
            $corps_message="============================================================\nCeci est un message automatique, merci de ne pas y r�pondre.\n============================================================\n\n
Bonjour $civilite ". preg_replace("/[']+/", "'", $nom) . ",\n
Les informations vous permettant d'acc�der � l'interface de pr�candidatures sont les suivantes:
- Adresse : $__URL_CANDIDAT
- Identifiant : ". stripslashes($new_identifiant) . "
- Code Personnel : $new_code\n
Attention : respectez bien les minuscules et majuscules lorsque vous entrez ces codes !\n
Ne perdez surtout pas ces informations : elles vous serviront � consulter certains documents et r�sultats par la suite.\n\n
Cordialement,\n\n
--
$__SIGNATURE_COURRIELS";

            $ret=mail($email,"[Pr�candidatures] - Enregistrement", $corps_message, $headers);
         }
         
		   db_close($dbr);

   		header("Location:" . base_url($php_self) . "edit_candidature.php?cid=$new_id");
	   	exit();
		}
	}

	// Construction de la liste des pays et nationalit�s (codes ISO) pour son utilisation dans le formulaire
	$_SESSION["liste_pays_nat_iso"]=array();
	
	$res_pays_nat=db_query($dbr, "SELECT $_DBC_pays_nat_ii_iso, $_DBC_pays_nat_ii_insee, $_DBC_pays_nat_ii_pays, $_DBC_pays_nat_ii_nat
											FROM $_DB_pays_nat_ii
											ORDER BY to_ascii($_DBC_pays_nat_ii_pays)");
											
	$rows_pays_nat=db_num_rows($res_pays_nat);
	
	for($p=0; $p<$rows_pays_nat; $p++)
	{
		list($code_iso, $code_insee, $table_pays, $table_nationalite)=db_fetch_row($res_pays_nat, $p);
		
		// Construction uniquement si le code insee est pr�sent (pour les exports APOGEE ou autres)
		if($code_insee!="")
			$_SESSION["liste_pays_nat_iso"]["$code_iso"]=array("pays" => "$table_pays", "nationalite" => $table_nationalite);
/*		
		if($code_insee!="")
			$_SESSION["liste_pays_nat_insee"]["$code_insee"]=array("pays" => "$table_pays", "nationalite" => $table_nationalite);
*/
	}

	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>

<div class='main'>
	<?php
		titre_page_icone("Cr�ation manuelle d'une fiche candidat", "add_32x32_fond.png", 12, "L");

		if(isset($id_existe))
			message("<strong>Erreur</strong> : ces donn�es existent d�j� dans la base (m�mes nom, pr�nom(s) et date de naissance)", $__ERREUR);
		else
		{
			$message_erreur="";

			if(isset($bad_dpt_naissance))
				$message_erreur.="- si le candidat / la candidate est n�(e) en France, le d�partement de naissance est obligatoire";

			if(isset($erreur_date_naissance))
			{
				$message_erreur.=$message_erreur!="" ? "\n<br>" : "";
				$message_erreur.="- le format de la date de naissance est incorrect (JJ / MM / AAAA)";
			}

			if(isset($erreur_ine_bea))
			{
				$message_erreur.=$message_erreur!="" ? "\n<br>" : "";
				$message_erreur.="- le numero INE ou BEA est incorrect";
			}
			
			if(isset($erreur_ine_obligatoire))
         {
            $message_erreur.=$message_erreur!="" ? "\n<br>" : "";
            $message_erreur.="- Si l'�tudiant(e) d�j� �t� inscrit(e) dans cette Universit� : le numero INE ou BEA est <strong>obligatoire</strong>";
         }

			if(isset($err_deja_inscrit))
			{
				$message_erreur.=$message_erreur!="" ? "\n<br>" : "";
				$message_erreur.="- vous devez indiquer si le candidat / la candidate a d�j� �t� inscrit(e) ou non dans cette Universit�";
			}

			if(isset($err_annee_premiere_inscr))
			{
				$message_erreur.=$message_erreur!="" ? "\n<br>" : "";
				$message_erreur.="- le format de l'ann�e de premi�re inscription dans cette Universit� est incorrect (AAAA)";
			}

			if(isset($err_annee_bac))
			{
				$message_erreur.=$message_erreur!="" ? "\n<br>" : "";
				$message_erreur.="- le format de l'ann�e d'obtention du baccalaur�at est incorrect (AAAA)";
			}

			if(isset($err_serie_bac))
			{
				$message_erreur.=$message_erreur!="" ? "\n<br>" : "";
				$message_erreur.="- vous devez s�lectionner la s�rie du baccalaur�at (ou �quivalence). Si le candidat / la candidate n'a pas obtenu le baccalaur�at, s�lectionnez \"Sans bac\" dans le menu d�roulant.";
			}

			if($message_erreur!="")
			{
				$message_erreur="<strong>Erreur(s)</strong> :\n<br>$message_erreur";
				message("$message_erreur", $__ERREUR);
			}
		}

		if(isset($champ_vide))
			message("<strong>Formulaire incomplet</strong> : les champs en gras sont <u>obligatoires</u>", $__ERREUR);
		else
			message("<strong>Les champs en gras sont <u>obligatoires</u></strong>. Aucun courriel n'est envoy� lors de la cr�ation manuelle d'une Fiche Candidat.", $__WARNING);
	?>

	<form name="form1" action="<?php print("$php_self"); ?>" method="POST">

	<table align='center'>
	<tr>
		<td class='td-complet fond_menu2' colspan='2'>
			<font class='Texte_menu2' style="font-size:14px"><strong>Identit�</strong></font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_important_menu2'><b>Civilit� : </b></font>
		</td>
		<td class='td-droite fond_menu'>
			<?php
			if(isset($civilite))
			{
				if($civilite=="M")
				{
					$selected_M="selected='1'";
					$selected_Mlle="";
					$selected_Mme="";
				}
				else
				{
					if($civilite=="Mme")
					{
						$selected_Mme="selected='1'";
						$selected_M="";
						$selected_Mlle="";
					}
					else
					{
						$selected_Mlle="selected='1'";
						$selected_M="";
						$selected_Mme="";
					}
				}
			}
			else
				$selected_M=$selected_Mlle=$selected_Mme="";

			print("
			<select name='civilite' size='1'>
				<option value='Mme' $selected_Mme>Madame</option>
				<option value='Mlle' $selected_Mlle>Mademoiselle</option>
				<option value='M' $selected_M>Monsieur</option>
			</select>");
			?>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_important_menu2'><b>Nom usuel : </b></font>
		</td>
		<td class='td-droite fond_menu'>
			<input type='text' name='nom' value='<?php if(isset($nom)) echo htmlspecialchars(preg_replace("/[']+/", "'", stripslashes($nom)), ENT_QUOTES); ?>' size="25" maxlength="30">
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_important_menu2'><b>Nom de naissance (si diff�rent) : </b></font>
		</td>
		<td class='td-droite fond_menu'>
			<input type='text' name='nom_naissance' value='<?php if(isset($nom_naissance)) echo htmlspecialchars(preg_replace("/[']+/", "'", stripslashes($nom_naissance)), ENT_QUOTES); ?>' size="25" maxlength="30">
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_important_menu2'><b>Pr�nom : </b></font>
		</td>
		<td class='td-droite fond_menu'>
			<input type='text' name='prenom' value='<?php if(isset($prenom)) echo htmlspecialchars(preg_replace("/[']+/", "'", stripslashes($prenom)),ENT_QUOTES); ?>' size="25" maxlength="30">
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_menu2'>Deuxi�me pr�nom (facultatif) : </font>
		</td>
		<td class='td-droite fond_menu'>
			<input type='text' name='prenom2' value='<?php if(isset($deuxieme_prenom)) echo htmlspecialchars(preg_replace("/[']+/", "'", stripslashes($deuxieme_prenom)),ENT_QUOTES); ?>' size="25" maxlength="30">
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_important_menu2'><b>Date de naissance (JJ/MM/AAAA) : </b></font>
		</td>
		<td class='td-droite fond_menu'>
			<input type='text' name='jour' value='<?php if(isset($jour)) echo htmlspecialchars($jour,ENT_QUOTES); ?>' size="2" maxlength="2">/
			<input type='text' name='mois' value='<?php if(isset($mois)) echo htmlspecialchars($mois,ENT_QUOTES); ?>' size="2" maxlength="2">/
			<input type='text' name='annee' value='<?php if(isset($annee)) echo htmlspecialchars($annee,ENT_QUOTES); ?>' size="4" maxlength="4">
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_important_menu2'><b>Ville de naissance : </b></font>
		</td>
		<td class='td-droite fond_menu'>
			<input type='text' name='lieu_naissance' value='<?php if(isset($lieu_naissance)) echo htmlspecialchars(preg_replace("/[']+/", "'", stripslashes($lieu_naissance)),ENT_QUOTES); ?>' size="25" maxlength="60">
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_important_menu2'><b>Si n�(e) en France<br>N� du d�partement de naissance: </b></font>
		</td>
		<td class='td-droite fond_menu'>
			<select name='dpt_naissance'>
				<option value=''></option>
				<?php
					$res_departements=db_query($dbr, "SELECT $_DBC_departements_fr_numero, $_DBC_departements_fr_nom
																 FROM $_DB_departements_fr
																 ORDER BY $_DBC_departements_fr_numero");

					$nb_dpts_fr=db_num_rows($res_departements);

					for($dpt=0; $dpt<$nb_dpts_fr; $dpt++)
					{
						list($dpt_num, $dpt_nom)=db_fetch_row($res_departements, $dpt);

						$selected=(isset($dpt_naissance) && $dpt_naissance==$dpt_num) || (isset($_SESSION["dpt_naissance"]) && $_SESSION["dpt_naissance"]==$dpt_num) ? "selected='1'" : "";
						
						print("<option value='$dpt_num' $selected>$dpt_num - $dpt_nom</option>\n");
					}

					db_free_result($res_departements);
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_important_menu2'><b>Pays de naissance : </b></font>
		</td>
		<td class='td-droite fond_menu'>
			<select name='pays_naissance' size='1'>
			 	<option value=''></option>
				<?php
					foreach($_SESSION["liste_pays_nat_iso"] as $code_iso => $array_pays_nat)
					{
						if($array_pays_nat["pays"]!="")
						{
							$selected=(isset($pays_naissance_code) && $pays_naissance_code==$code_iso) ? "selected='1'" : "";
							
							print("<option value='$code_iso' $selected>$array_pays_nat[pays]</option>\n");
						}
					}
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_important_menu2'><b>Nationalit� : </b></font>
		</td>
		<td class='td-droite fond_menu'>
			<select name='nationalite' size='1'>
			 	<option value=''></option>
				<?php
					foreach($_SESSION["liste_pays_nat_iso"] as $code_iso => $array_pays_nat)
					{
						if($array_pays_nat["nationalite"]!="")
						{
							$selected=(isset($nationalite_code) && $nationalite_code==$code_iso) ? "selected='1'" : "";
							
							print("<option value='$code_iso' $selected>$array_pays_nat[nationalite]</option>\n");
						}
					}
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_menu2'>Adresse �lectronique valide : </font>
		</td>
		<td class='td-droite fond_menu'>
			<input type='text' name='email' value='<?php if(isset($email)) echo htmlspecialchars(preg_replace("/[']+/", "'", stripslashes($email)), ENT_QUOTES); ?>' size="25" maxlength="255">
		</td>
	</tr>
	<tr>
		<td colspan='2' style='height:10px;'></td>
	</tr>
	<tr>
		<td class='td-complet fond_menu2' colspan='2'>
			<font class='Texte_menu2' style="font-size:14px"><strong>Adresse postale pour la r�ception des courriers</strong></font>
		</td>
	</tr>
	<tr>
      <td class='td-gauche fond_menu2' style="text-align:right;">
         <font class='Texte_important_menu2'><b>Adresse : <br></b></font>
      </td>
      <td class='td-droite fond_menu' style="text-align:left;">
         <input name='adresse_1' value="<?php if(isset($adresse_1)) echo htmlspecialchars(stripslashes($adresse_1), ENT_QUOTES); ?>" size='40' maxlength="30">
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2' style="text-align:right;">
         <font class='Texte_important_menu2'><b>Adresse (suite) : <br></b></font>
      </td>
      <td class='td-droite fond_menu' style="text-align:left;">
         <input name='adresse_2' value="<?php if(isset($adresse_2)) echo htmlspecialchars(stripslashes($adresse_2), ENT_QUOTES); ?>" size='40' maxlength="30">
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2' style="text-align:right;">
         <font class='Texte_important_menu2'><b>Adresse (suite) : <br></b></font>
      </td>
      <td class='td-droite fond_menu' style="text-align:left;">
         <input name='adresse_3' value="<?php if(isset($adresse_3)) echo htmlspecialchars(stripslashes($adresse_3), ENT_QUOTES); ?>" size='40' maxlength="30">
      </td>
   </tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_important_menu2'><b>Code Postal :</b></font>
		</td>
		<td class='td-droite fond_menu'>
			<input type='text' name='adr_cp' value='<?php if(isset($adr_cp)) echo htmlspecialchars($adr_cp,ENT_QUOTES); ?>' size="25" maxlength="15">
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_important_menu2'><b>Ville :</b></font>
		</td>
		<td class='td-droite fond_menu'>
			<input type='text' name='adr_ville' value='<?php if(isset($adr_ville)) echo htmlspecialchars(preg_replace("/[']+/", "'", stripslashes($adr_ville)),ENT_QUOTES); ?>' size="25" maxlength="60">
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_important_menu2'><b>Pays :</b></font>
		</td>
		<td class='td-droite fond_menu'>
			<select name='adr_pays' size='1'>
			 	<option value=''></option>
				<?php
					foreach($_SESSION["liste_pays_nat_iso"] as $code_iso => $array_pays_nat)
					{
						if($array_pays_nat["pays"]!="")
						{
							$selected=(isset($adr_pays_code) && $adr_pays_code==$code_iso) ? "selected='1'" : "";
							
							print("<option value='$code_iso' $selected>$array_pays_nat[pays]</option>\n");
						}
					}
				?>
			</select>
			<!-- <input type='text' name='adr_pays' value='<?php if(isset($adr_pays)) echo htmlspecialchars($adr_pays,ENT_QUOTES); ?>' size="25" maxlength="60"> -->
		</td>
	</tr>
	<tr>
		<td colspan='2' style='height:10px;'></td>
	</tr>
	<tr>
		<td class='td-complet fond_menu2' colspan='2'>
			<font class='Texte_menu2' style="font-size:14px"><strong>Baccalaur�at (ou �quivalent) : pr�cisions</strong></font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_important_menu2'>
				<b>Ann�e d'obtention du baccalaur�at<br>(ou �quivalent) ?</b>
			</font>
		</td>
		<td class='td-droite fond_menu'>
			<input type='text' name='annee_bac' value='<?php if(isset($annee_bac)) echo "$annee_bac"; ?>' size="25" maxlength="4"><font class='Texte'><i>(Format : YYYY)</i></font>
			<br><font class='Texte_menu_10'><i>Si le candidat n'a pas le baccalaur�at (et qu'il n'est pas en cours de pr�paration), s�lectionnez "Sans bac" dans<br>la liste et indiquez l'ann�e du dernier dipl�me obtenu</i></font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_important_menu2'><b>S�rie du baccalaur�at (ou �quivalent) :</b></font>
		</td>
		<td class='td-droite fond_menu'>
			<select name='serie_bac' size='1'>
				<option value=''></option>
				<?php
					$result=db_query($dbr,"SELECT $_DBC_diplomes_bac_code, $_DBC_diplomes_bac_intitule
												FROM $_DB_diplomes_bac ORDER BY $_DBC_diplomes_bac_intitule");
					$rows=db_num_rows($result);

					if(isset($serie_bac))
						$cur_serie_bac=$serie_bac;

					for($i=0; $i<$rows; $i++)
					{
						list($serie_bac, $intitule_bac)=db_fetch_row($result,$i);

						$selected=isset($cur_serie_bac) && $cur_serie_bac==$serie_bac ? "selected=1" : "";

						print("<option value='$serie_bac' $selected>$intitule_bac</option>\n");
					}
				?>
			</select>
		</td>
	</tr>
	<tr>
      <td colspan='2' style='height:10px;'></td>
   </tr>
   <tr>
      <td class='td-complet fond_menu2' colspan='2'>
         <font class='Texte_menu2' style="font-size:14px"><strong>Inscriptions ant�rieures</strong></font>
      </td>
   </tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_important_menu2'><b>Le candidat/la candidate a-t'il/elle d�j� �t� inscrit(e) dans cette Universit� ?</b></font>
		</td>
		<td class='td-droite fond_menu'>
			<?php
				if(isset($deja_inscrit) && $deja_inscrit==1)
				{
					$oui_checked="checked";
					$non_checked="";
				}
				else
				{
					$oui_checked="";
					$non_checked="checked";
				}

				print("<input type='radio' name='deja_inscrit' value='1' $oui_checked><font class='Texte_menu'>&nbsp;Oui&nbsp;&nbsp;</font><input type='radio' name='deja_inscrit' value='0' $non_checked><font class='Texte'>&nbsp;Non</font>\n");
			?>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_important_menu2'><b><u>Si oui</u>, indiquez l'ann�e de premi�re inscription :</b></font>
		</td>
		<td class='td-droite fond_menu'>
			<input type='text' name='annee_premiere_inscr' value='<?php if(isset($annee_premiere_inscr)) echo "$annee_premiere_inscr"; ?>' size="25" maxlength="4"><font class='Texte'><i>(Format : YYYY)</i></font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_menu2'>Num�ro INE <b>ou</b> BEA : </font>
		</td>
		<td class='td-droite fond_menu'>
			<input type='text' name='num_ine' value='<?php if(isset($num_ine)) echo htmlspecialchars($num_ine,ENT_QUOTES); ?>' size="25" maxlength="11"> <font class='Texte_menu'>(<b>obligatoire</b> en cas d'inscription ant�rieure dans cette Universit�)</font>
		</td>
	</tr>
	<tr>
      <td colspan='2' style='height:10px;'></td>
   </tr>
   <tr>
      <td class='td-complet fond_menu2' colspan='2'>
         <font class='Texte_menu2' style="font-size:14px"><strong>Autres informations</strong></font>
      </td>
   </tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_menu2'>T�l�phone fixe : </font>
		</td>
		<td class='td-droite fond_menu'>
			<input type='text' name='telephone' value='<?php if(isset($telephone)) echo htmlspecialchars($telephone,ENT_QUOTES); ?>' size="25" maxlength="15">
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_menu2'>T�l�phone portable : </font>
		</td>
		<td class='td-droite fond_menu'>
			<input type='text' name='telephone_portable' value='<?php if(isset($telephone_portable)) echo htmlspecialchars($telephone_portable,ENT_QUOTES); ?>' size="25" maxlength="15">
		</td>
	</tr>
	</table>

	<div class='centered_icons_box'>
		<a href='index.php' target='_self'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Retour' border='0'></a>
		<input type="image" src="<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>" alt="Valider" name="valider" value="Valider">
		</form>
	</div>

</div>
<?php
	db_close($dbr);
	pied_de_page();
?>

<script language="javascript">
	document.form1.civilite.focus()
</script>

</body>
</html>

