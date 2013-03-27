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
	session_name("preinsc");
	session_start();

	include "../configuration/aria_config.php";
	include "$__INCLUDE_DIR_ABS/vars.php";
	include "$__INCLUDE_DIR_ABS/fonctions.php";
	include "$__INCLUDE_DIR_ABS/db.php";

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	if(isset($_POST["go_valider"]) || isset($_POST["go_valider_x"])) // validation du formulaire
	{
		// v�rification des valeurs entr�es dans le formulaire
		// TODO : v�rifications pouss�es ?

		$nom=mb_strtoupper(trim($_POST["nom"]));

		$jour=trim($_POST["jour"]);
		$mois=trim($_POST["mois"]);
		$annee=trim($_POST["annee"]);

		$email=mb_strtolower(trim($_POST["email"]));

		$champs_obligatoires=array($nom,$jour,$mois,$annee,$email);
		$cnt_obl=count($champs_obligatoires);

		for($i=0; $i<$cnt_obl; $i++) // v�rification des champs obligatoires
		{
			if($champs_obligatoires[$i]=="")
			{
				$champ_vide=1;
				$i=$cnt_obl;
			}
		}

		if(!ctype_digit($jour) || !ctype_digit($mois) || !ctype_digit($annee) || $jour<1 || $jour>31 || $mois<1 || $mois>12 || $annee<1900 | $annee>3000)
			$bad_date=1;
		else
			$date_naissance=MakeTime(12,0,0,$mois,$jour,$annee);

		// $date_naissance=MakeTime(12,0,0,$mois,$jour,$annee);

		if(!isset($champ_vide) && !isset($bad_date))
		{
			// V�rification de pr�sence dans la base

			$dbr=db_connect();
			$result=db_query($dbr,"SELECT $_DBC_candidat_id, $_DBC_candidat_civilite, $_DBC_candidat_prenom, $_DBC_candidat_identifiant,
													$_DBC_candidat_code_acces
												FROM $_DB_candidat
											WHERE $_DBC_candidat_nom ILIKE '$nom'
											AND $_DBC_candidat_date_naissance='$date_naissance'
											AND $_DBC_candidat_email ILIKE '$email'");
			$rows=db_num_rows($result);

			if(!$rows)
				$not_found=1;
			else // si le r�sultat est positif, on ne devrait en n'avoir qu'un seul
			{
				list($cand_id,$cand_civilite,$cand_prenom,$cand_identifiant,$cand_code)=db_fetch_row($result,0);

				db_free_result($result);

				// g�n�ration du Code Personnel
				srand((double)microtime()*1000000);
				$code_conf=mb_strtoupper(md5(rand(0,9999)));
				$new_code=substr($code_conf, 17, 8);
				// on supprime le chiffre 1, le z�ro et la lettre O : portent � confusion - on les remplace par d'autres caract�res
				$new_code=str_replace("0","A", $new_code);
				$new_code=str_replace("O","H", $new_code);
				$new_code=str_replace("1","P", $new_code);

				db_query($dbr,"UPDATE $_DB_candidat SET $_DBU_candidat_code_acces='$new_code' WHERE $_DBU_candidat_id='$cand_id'");

				// envoi du mail de confirmation
				$headers = "From: $__EMAIL_ADMIN" . "\r\n" . "Reply-To: $__EMAIL_ADMIN";

				$corps_message="============================================================\nCeci est un message automatique, merci de ne pas y r�pondre.\n============================================================\n\n
Bonjour $cand_civilite ". stripslashes($nom) . ",\n\n
Les nouvelles informations vous permettant d'acc�der � l'interface de pr�candidatures sont les suivantes:
- Identifiant : ". stripslashes($cand_identifiant) . "
- Code Personnel : $new_code   (respectez bien les majuscules !)\n
Ne perdez surtout pas votre identifiant car vous devrez le joindre aux �ventuels justificatifs de dipl�mes � envoyer � la scolarit�.\n\n
Cordialement,\n\n\n--
$__SIGNATURE_COURRIELS";

				$ret=mail($email,"[Pr�candidatures] - Nouveaux identifiants", $corps_message, $headers);

				// Debug : envoi d'un courriel � l'administrateur
				if($GLOBALS["__DEBUG"]=="t" && $GLOBALS["__DEBUG_RAPPEL_IDENTIFIANTS"]=="t" && $GLOBALS["__EMAIL_ADMIN"]!="")
					mail($GLOBALS["__EMAIL_ADMIN"], "$GLOBALS[__DEBUG_SUJET] - Nouveaux identifiants - $cand_civilite $nom $cand_prenom", "ID : $cand_id\nCandidat : $cand_civilite $nom $cand_prenom\nAdresse �lectronique : $email\n\n" . $corps_message, $headers);

            write_evt($dbr, $__EVT_ID_C_RECUP, "Demande de renvoi de nouveaux identifiants", $cand_id, $cand_id);

				db_close($dbr);

				if($ret==TRUE)
				{
					$_SESSION["email"]=$email;

					session_write_close();
					header("Location:validation.php");
					exit();
				}
			}
		}
	}
	
	en_tete_candidat();
	menu_sup_simple();
?>

<div class='main'>
	<?php titre_page_icone("R�cup�ration de vos identifiants", "password_32x32_fond.png", 15, "L"); ?>

	<form name='form1' action="<?php print("$php_self"); ?>" method="POST">
	
	<?php
		$prev_periode=$__PERIODE-1 . "-$__PERIODE";

		if(isset($champ_vide))
			message("Formulaire incomplet: les champs en gras sont <u>obligatoires</u>", $__ERREUR);

		if(isset($bad_date))
			message("Erreur : le format de votre date de naissance est incorrect (JJ / MM / AAAA)", $__ERREUR);

		if(isset($not_found))
			message("<b>Erreur : ces donn�es ne se trouvent pas dans notre base.</b>
						<br><br><b>1/</b> V�rifiez que les donn�es que vous avez entr�es sont rigoureusement identiques � celles entr�es lors de votre enregistrement
						<br><br><b>2/</b> Si vous n'avez pas effectu� la proc�dure d'enregistrement, merci de vous rendre <a href='enregistrement.php' class='lien2a'>� cette adresse</a>.
						<br><br><b>3/</b> Si vous vous �tes tromp� d'adresse e-mail lors de l'enregistrement, merci <a href='mailto:$__EMAIL_SUPPORT?subject=Enregistrement : adresse �lectronique erron�e' class='lien2a'>d'envoyer un courriel � cette adresse</a>.
						<br><br><b>4/</b> Si tout le reste a �chou�, merci <a href='mailto:$__EMAIL_SUPPORT?subject=Identifiants : dernier recours' class='lien2a'>d'envoyer un courriel � cette adresse</a>.
						avec toutes les donn�es du formulaire.", $__ERREUR);

		if(!isset($not_found) && !isset($bad_date) && !isset($champ_vide))
			message("Veuillez compl�ter le formulaire suivant.
						<br>- Les donn�es � entrer sont </font><font class='Texte_important_14'><b>celles que vous avez entr�es lors de votre premier enregistrement</b></font>
						<font class='Textebleu'>
						<br>- Si vous �tiez d�j� enregistr� en $prev_periode et que vous avez chang� d'adresse �lectronique, retournez � l'�cran pr�c�dent et utilisez le lien \"Signaler un probl�me technique\" en pr�cisant votre identit� compl�te.
						<br>- Vous recevrez une nouvelle fois, par courriel, les codes d'acc�s qui vous permettront d'acc�der aux pr�candidatures en ligne", $__INFO);
	?>

	<table style="margin-left:auto; margin-right:auto;">
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_menu2'><b>Nom : </b></font>
		</td>
		<td class='td-droite fond_menu'>
			<input type='text' name='nom' value='<?php if(isset($nom)) echo htmlspecialchars($nom,ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size="25" maxlength="30">
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_menu2'><b>Date de naissance (JJ/MM/AAAA) : </b></font>
		</td>
		<td class='td-droite fond_menu'>
			<input type='text' name='jour' value='<?php if(isset($jour)) echo htmlspecialchars($jour,ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size="2" maxlength="2">/
			<input type='text' name='mois' value='<?php if(isset($mois)) echo htmlspecialchars($mois,ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size="2" maxlength="2">/
			<input type='text' name='annee' value='<?php if(isset($annee)) echo htmlspecialchars($annee,ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size="4" maxlength="4">
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2' style="text-align:right;">
			<font class='Texte_menu2'><b>Adresse �lectronique (<i>e-mail</i>) : </b></font>
		</td>
		<td class='td-droite fond_menu'>
			<input type='text' name='email' value='<?php if(isset($email)) echo htmlspecialchars($email,ENT_QUOTES, $default_htmlspecialchars_encoding); ?>' size="25" maxlength="255">
		</td>
	</tr>
	</table>
	
	<div class='centered_icons_box'>
		<a href='identification.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Annuler' border='0'></a>
		<input type="image" src="<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>" alt="Valider" name="go_valider" value="Valider">
		</form>
	</div>
</div>

<?php
	pied_de_page_candidat();
?>

<script language="javascript">
	document.form1.nom.focus()
</script>

</body></html>

