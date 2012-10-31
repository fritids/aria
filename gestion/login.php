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

	// Conservation de variables pour les acc�s directs
	if(isset($_SESSION["fiche_id"]))	$fiche_id=$_SESSION["fiche_id"];
	if(isset($_SESSION["dco"])) $co=$_SESSION["dco"];

	// Nettoyage
	session_unset();

	if(isset($fiche_id))	$_SESSION["fiche_id"]=$fiche_id;
	if(isset($co)) $_SESSION["dco"]=$co;

   if(is_file("../include/vars.php")) include "../include/vars.php";
   else die("Fichier \"include/vars.php\" non trouv�");

   if(is_file("../include/fonctions.php")) include "../include/fonctions.php";
   else die("Fichier \"include/fonctions.php\" non trouv�");

   if(is_file("../include/db.php")) include "../include/db.php";
   else die("Fichier \"include/db.php\" non trouv�");

   if(is_file("../include/access_functions.php")) include "../include/access_functions.php";
   else die("Fichier \"include/access_functions.php\" non trouv�");

   if(is_file("../include/fonctions_ldap.php")) include "../include/fonctions_ldap.php";
   else die("Fichier \"include/fonctions_ldap\" non trouv�");
   
   $dbr=db_connect();

   // Chargement de la configuration
   $load_config=__get_config($dbr);

   if($load_config===FALSE) // config absente : erreur
      $erreur_config=1;
   elseif($load_config==-1) // param�tre(s) manquant(s) : avertissement
      $warn_config=1;

   // Blocage si le fichier gestion/admin/config.php existe encore
   if(is_file("admin/config.php") && is_readable("admin/config.php"))
      die("Le fichier gestion/admin/config.php doit imp�rativement �tre supprim� ou d�plac� pour utiliser l'application (vous pouvez �galement modifier les droits de lecture).\n");

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	if(isset($_POST["Valider"]) || isset($_POST["Valider_x"]))
	{
		$_SESSION["auth_id"]="-1";
		$_SESSION["auth_prenom"]="";
		$_SESSION["auth_email"]="";
		$_SESSION["comp_id"]="-1";
		$_SESSION["niveau"]="$__LVL_DESACTIVE";

		$_SESSION["auth_ip"]=$_SERVER['REMOTE_ADDR'];
		$_SESSION["auth_host"]=@gethostbyaddr($_SERVER['REMOTE_ADDR']);

		$_SESSION["auth_nom"]=$user=strtolower($_POST["user"]);

      // Le mot de passe ne sera utilis� que si le compte est local (LDAP sinon)
		$pass=$_POST["pass"];

		// Acc�s direct � une composante ?
		if(isset($_SESSION["dco"]))
			$result=db_query($dbr,"SELECT $_DBC_acces_id, $_DBC_acces_nom, $_DBC_acces_prenom, $_DBC_acces_courriel, $_DBC_acces_pass,
													$_DBC_acces_filtre, $_DBC_composantes_nom, $_DBC_universites_nom,
													$_DBC_universites_img_dir, $_DBC_universites_id, $_DBC_composantes_id,
													$_DBC_universites_css, $_DBC_acces_niveau, $_DBC_composantes_gestion_motifs,
													$_DBC_composantes_scolarite, $_DBC_composantes_courriel_scol, $_DBC_composantes_affichage_decisions,
													$_DBC_composantes_avertir_decision, $_DBC_acces_source
												FROM $_DB_acces, $_DB_composantes, $_DB_universites
											WHERE $_DBC_acces_composante_id=$_DBC_composantes_id
											AND ($_DBC_acces_id IN (SELECT $_DBC_acces_comp_acces_id FROM $_DB_acces_comp
																			WHERE $_DBC_acces_comp_composante_id='$_SESSION[dco]')
														OR $_DBC_acces_niveau='$__LVL_ADMIN')
											AND $_DBC_composantes_univ_id=$_DBC_universites_id
											AND $_DBC_acces_login='$user'");
		else
			$result=db_query($dbr,"SELECT $_DBC_acces_id, $_DBC_acces_nom, $_DBC_acces_prenom, $_DBC_acces_courriel, $_DBC_acces_pass,
													$_DBC_acces_filtre, $_DBC_composantes_nom, $_DBC_universites_nom,
													$_DBC_universites_img_dir, $_DBC_universites_id, $_DBC_composantes_id,
													$_DBC_universites_css, $_DBC_acces_niveau, $_DBC_composantes_gestion_motifs,
													$_DBC_composantes_scolarite, $_DBC_composantes_courriel_scol, $_DBC_composantes_affichage_decisions,
													$_DBC_composantes_avertir_decision, $_DBC_acces_source
												FROM $_DB_acces, $_DB_composantes, $_DB_universites
											WHERE $_DBC_acces_composante_id=$_DBC_composantes_id
											AND $_DBC_composantes_univ_id=$_DBC_universites_id
											AND $_DBC_acces_login='$user'");
			$rows=db_num_rows($result);

		if($rows)
		{
			list($_SESSION["auth_id"],
					$_SESSION["auth_nom"],
					$_SESSION["auth_prenom"],
					$_SESSION["auth_email"],
					$bpass,
					$_SESSION['spec_filtre_defaut'],
					$_SESSION["composante"],
					$_SESSION["universite"],
					$_SESSION["img_dir"],
					$_SESSION["univ_id"],
					$_SESSION["comp_id"],
					$_SESSION["css"],
					$_SESSION["niveau"],
					$_SESSION["gestion_motifs"],
					$_SESSION["adr_scol"],
					$_SESSION["courriel_scol"],
					$_SESSION["affichage_decisions"],
					$_SESSION["avertir_decision"],
					$_SESSION["auth_source"])=db_fetch_row($result,0);

         // Niveau suppl�mentaire dans l'arborescence des messages
         $_SESSION["MSG_SOUS_REP"]=sous_rep_msg($_SESSION["auth_id"]);

			// L'acc�s direct a r�ussi : on met cette composante par d�faut
			// TODO : requ�te doublon : � corriger
			if(isset($_SESSION["dco"]) && isset($_SESSION["fiche_id"]))
			{
				if($_SESSION["comp_id"]!=$_SESSION["dco"])
				{
					$result2=db_query($dbr, "SELECT $_DBC_composantes_nom, $_DBC_universites_nom, $_DBC_universites_img_dir,
															  $_DBC_universites_id, $_DBC_universites_css,
															  $_DBC_composantes_gestion_motifs, $_DBC_composantes_scolarite, $_DBC_composantes_courriel_scol,
															  $_DBC_composantes_affichage_decisions, $_DBC_composantes_avertir_decision
														FROM $_DB_composantes, $_DB_universites
													WHERE $_DBC_universites_id=$_DBC_composantes_univ_id
													AND $_DBC_composantes_id=$_SESSION[dco]");

					if(db_num_rows($result2))
						list($_SESSION["composante"], $_SESSION["universite"], $_SESSION["img_dir"], $_SESSION["univ_id"],
								$_SESSION["css"], $_SESSION["gestion_motifs"], $_SESSION["adr_scol"],
								$_SESSION["courriel_scol"], $_SESSION["affichage_decisions"], $_SESSION["avertir_decision"])=db_fetch_row($result2,0);

					db_free_result($result2);

					$_SESSION["comp_id"]=$_SESSION["dco"];
				}
			
				$_SESSION["candidat_id"]=$_SESSION["fiche_id"];
			}

         if(isset($GLOBALS["__LDAP_ACTIF"]) && $GLOBALS["__LDAP_ACTIF"]=="t" && $_SESSION["auth_source"]==$GLOBALS["__COMPTE_LDAP"])
         {
            if(aria_ldap_auth($user, $pass)==TRUE)
               $auth_ok=1;
         }
         elseif($bpass!="" && md5($pass)=="$bpass")
            $auth_ok=1;
            
         if(isset($auth_ok) && $auth_ok==1)
			{
				// le pass est correct, on v�rifie les droits (consultation de la table appropri�e dans l'annuaire)

				// Compte d�sactiv� ?
				if($_SESSION["niveau"]!="$__LVL_DESACTIVE")
				{
					$_SESSION['auth_user']=$user;

					if(empty($_SESSION["auth_email"])) // normalement, ne devrait JAMAIS �tre possible, mais par pr�caution ...
						$_SESSION['auth_email']=$_SESSION["courriel_scol"];

					$_SESSION['tri']=0;
					$_SESSION["onglet"]=1; // onglet par d�faut : identite du candidat

					// Mode par d�faut : commission pour le mode consultation (enseignants), pr�candidatures pour les autres
					if(in_array($_SESSION["niveau"], array("$__LVL_SAISIE","$__LVL_SCOL_MOINS","$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
						$_SESSION["mode"]=$__MODE_PREC;
					else
						$_SESSION["mode"]=$__MODE_COMPEDA;

					// Filtre par d�faut
					if($_SESSION['spec_filtre_defaut']=="")
						$_SESSION['spec_filtre_defaut']="-1";

					// cr�ation du vecteur d'encryption (utilis� pour chiffrer les param�tres)
					$td=mcrypt_module_open("tripledes", "", "cbc", "");
					$_SESSION["iv"]=mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
					mcrypt_module_close($td);

					// Log de la connexion dans la BDD
					write_evt($dbr, $__EVT_ID_LOGIN, "Connexion r�ussie", "", $_SESSION["auth_id"]);

					// Multi-composantes ?
					
					$_SESSION["auth_droits"]=array();
					
					$res_droits_composantes=db_query($dbr,"SELECT distinct($_DBC_composantes_id) FROM $_DB_composantes 
																			WHERE $_DBC_composantes_id IN (SELECT $_DBC_acces_composante_id FROM $_DB_acces WHERE $_DBC_acces_id='$_SESSION[auth_id]')
																			OR $_DBC_composantes_id IN (SELECT distinct($_DBC_acces_comp_composante_id) FROM $_DB_acces_comp WHERE $_DBC_acces_comp_acces_id='$_SESSION[auth_id]')");
																					
					$nb_droits_composantes=db_num_rows($res_droits_composantes);
					
					$_SESSION["multi_composantes"]=$nb_droits_composantes>1 ? "1" : "0";
					
					// Pour chaque composante � laquelle l'utilisateur a acc�s, on cr�� un tableau qui contiendra (peut �tre) le d�tail des formations accessibles
					// Si le tableau reste vide, l'utilisateur aura acc�s � toutes les formations de la composante
					for($d=0; $d<$nb_droits_composantes; $d++)
					{
						list($droits_comp_id)=db_fetch_row($res_droits_composantes, $d);
						
						$_SESSION["auth_droits"]["$droits_comp_id"]=array();
					}
						
					db_free_result($res_droits_composantes);
						
					// Droits sur les formations
					$res_droits_formations=db_query($dbr,"SELECT $_DBC_propspec_comp_id,$_DBC_droits_formations_propspec_id 
																	     FROM $_DB_propspec, $_DB_droits_formations
																	  WHERE $_DBC_propspec_id=$_DBC_droits_formations_propspec_id
																	  AND $_DBC_droits_formations_acces_id='$_SESSION[auth_id]'
																	     ORDER BY $_DBC_propspec_comp_id, $_DBC_droits_formations_propspec_id");
																	
					$nb_droits_formations=db_num_rows($res_droits_formations);
					
					for($d=0; $d<$nb_droits_formations; $d++)
					{
						list($droits_comp_id, $droits_propspec_id)=db_fetch_row($res_droits_formations, $d);
						
						// Si la composante n'existait pas dans le tableau, on l'ajoute
						if(!array_key_exists($droits_comp_id, $_SESSION["auth_droits"]))
							$_SESSION["auth_droits"]["$droits_comp_id"]=array($droits_propspec_id);
						else
							$_SESSION["auth_droits"]["$droits_comp_id"]=array_merge($_SESSION["auth_droits"]["$droits_comp_id"], array($droits_propspec_id));
					}
											
					db_free_result($res_droits_formations);
																	

					// Les candidats de cette composante sont-ils soumis � des entretiens ? (utile pour le menu et la gestion du calendrier)
					$_SESSION["composante_entretiens"]=db_num_rows(db_query($dbr, "SELECT * FROM $_DB_propspec WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]' AND $_DBC_propspec_entretiens='1'")) ? "1" : "0";

					

					// Chargement des plugins
					if(function_exists("add_modules"))
						add_modules();

					// on sort.
					// Pour les admins et le support => page de recherche imm�diatement 
					// Sinon, si la composante / un candidat est d�j� param�tr�, on va directement � la page correspondante
               if(in_array($_SESSION["niveau"], array("$__LVL_ADMIN", "$__LVL_SUPPORT")))
                  header("Location:recherche.php");
					elseif(($_SESSION["niveau"]=="$__LVL_SUPER_RESP" || $_SESSION["multi_composantes"]) && !isset($_SESSION["dco"]))
						header("Location:select_composante.php");
					elseif(isset($_SESSION["comp_id"]) && isset($_SESSION["candidat_id"]))
						header("Location:edit_candidature.php");
					else
						header("Location:index.php");

					db_close($dbr);

					exit();
				}
				else
					$auth_error=3;
			}
			else
				$auth_error=1;
		}
		else
			$auth_error=2;

		if(isset($auth_error))
		{
			switch($auth_error)
			{
				case 1	:	$evenement="Echec : '" . str_replace("'","''", $user) . "' : Mot de passe incorrect";
									break;

				case 2	: $evenement="Echec : '" . str_replace("'","''", $user) . "' : Identifiant inconnu";
									break;

				case 3	: $evenement="Echec : '" . str_replace("'","''", $user) . "' : Compte d�sactiv�";
									break;
			}

			write_evt($dbr, $__EVT_ID_LOGIN, "$evenement");
		}

		db_free_result($result);
	}

	en_tete_simple();
	menu_sup_simple();
?>

<div class='main' style='padding-top:20px; padding-bottom:100px;'>
	<?php
		// titre_page_icone("Authentification", "password_32x32_fond.png", 15, "C");
		titre_page_icone("Authentification", "password_32x32_fond.png", 15, "C");

		print("<form action='$php_self' method='POST' name='form1'>\n");

      if(isset($erreur_config))
         message("La configuration de l'interface n'a pu �tre charg�e. Merci de contacter rapidement l'administrateur de l'application.", $__ERREUR);

      if(isset($warn_config))
         message("La configuration de l'interface est incompl�te (param�tres manquants).
                  <br>- si vous �tes l'administrateur, identifiez vous et compl�tez la configuration,
                  <br>- dans le cas contraire, merci de contacter rapidement l'administrateur de l'application.", $__WARNING);

		if(isset($auth_error))
		{
			if($auth_error==1)
				message("Identifiant ou mot de passe incorrect", $__ERREUR);

			if($auth_error==2)
				message("Acc�s non autoris� � cette application", $__ERREUR);

			if($auth_error==3)
				message("<center>
								Votre compte a �t� d�sactiv�.
								<br>Si vous pensez qu'il s'agit d'une erreur, merci de contacter l'administrateur de l'application.
							</center>", $__ERREUR);
		}
	?>
	<table border="0" cellpadding="4" align='center' valign="top">
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'>Identifiant :&nbsp;&nbsp;</font>
		</td>
		<td class='td-droite fond_menu'>
			<input name="user" value="" type="text" size=20 maxlength='30' autocomplete='off'>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'>Mot de passe :&nbsp;&nbsp;</font>
		</td>
		<td class='td-droite fond_menu'>
			<input name="pass" value="" type="password" size=20 maxlength='15' autocomplete='off'>
		</td>
	</tr>
	</table>

	<div class='centered_box' style='padding-top:20px;'>
		<input type="image" src="<?php echo "$__ICON_DIR/forward_32x32_fond.png"; ?>" alt="Valider" name="Valider" value="Valider">
		</form>
	</div>

	<div class='centered_box'>
		<a style='padding-right:10px;' href='oubli_pass.php' class='lien2'>Oubli du mot de passe ?</a>
		<a style='padding-left:10px;' href='change_pass.php' class='lien2'>Changer son mot de passe</a>
	</div>

	<script language="javascript">
		document.form1.user.focus()
	</script>
</div>

<?php
   db_close($dbr);
	pied_de_page();
?>

</body>
</html>

