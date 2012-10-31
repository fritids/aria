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

	include "../../configuration/aria_config.php";
	include "$__INCLUDE_DIR_ABS/vars.php";
	include "$__INCLUDE_DIR_ABS/fonctions.php";
	include "$__INCLUDE_DIR_ABS/db.php";

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	if(!isset($_SESSION["authentifie"]))
	{
		session_write_close();
		header("Location:../../index.php");
		exit();
	}

	if(!isset($_SESSION["comp_id"]) || (isset($_SESSION["comp_id"]) && $_SESSION["comp_id"]==""))
	{
		session_write_close();
		header("Location:../composantes.php");
		exit();
	}

	if(!isset($_SESSION["current_dossier"]))
	{
		session_write_close();
		header("Location:index.php");
		exit();
	}
	else
		$current_dossier=$_SESSION["current_dossier"];

	$dbr=db_connect();

	if(isset($_GET["p"]) && -1!=($params=get_params($_GET['p']))) // chemin complet du message, chiffr�
	{
		if(isset($params["dir"]) && $params["dir"]==1)
			$flag_pj=1;

		if(isset($params["msg"]))
		{
			$fichier=$params["msg"];

			// On v�rifie que le message existe et qu'il appartient bien � l'utilisateur
			// $fichier="$__CAND_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[authentifie]/$_SESSION[current_dossier]/$_SESSION[msg]";

			// Test d'ouverture du fichier
			if(($array_file=@file("$fichier"))==FALSE)
			{
				// On tente en modifiant la fin du nom du fichier (flag read)
				if(substr($fichier, -1)=="0")
					$fichier=preg_replace("/\.0$/", ".1", $fichier);
				else
					$fichier=preg_replace("/\.1$/", ".0", $fichier);

				// $fichier="$__CAND_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[authentifie]/$_SESSION[current_dossier]/$new_file";

				if(($array_file=@file("$fichier"))==FALSE)
					$location="index.php";
/*
				else
					$_SESSION["msg"]=$new_file;
*/
			}

			if(!isset($location))
			{
				// Nom du fichier sans le r�pertoire
				$complete_path=explode("/", $fichier);
				$rang_fichier=count($complete_path)-1;
				
				// Nom du fichier
				$_SESSION["msg"]=$complete_path[$rang_fichier];
				
				// R�pertoire
				unset($complete_path[$rang_fichier]);
				$_SESSION["msg_dir"]=implode("/", $complete_path);

				if(strlen($_SESSION["msg"])==18) // Ann�e sur un caract�re (16 pour l'identifiant + ".0" ou ".1" pour le flag "read")
				{
					$date_offset=0;
					$annee_len=1;
					$leading_zero="0";
					$_SESSION["msg_id"]=$msg_id=substr($_SESSION["msg"], 0, 16);
					$msg_read=substr($_SESSION["msg"], 17, 1);
				}
				else // Ann�e sur 2 caract�res (chaine : 19 caract�res)
				{
					$date_offset=1;
					$annee_len=2;
					$leading_zero="";
					$_SESSION["msg_id"]=$msg_id=substr($_SESSION["msg"], 0, 17);
					$msg_read=substr($_SESSION["msg"], 18, 1);
				}

				$_SESSION['msg_exp_id']=trim($array_file["0"]);
				$_SESSION['msg_exp']=trim($array_file["1"]);
				$_SESSION['msg_to_id']=trim($array_file["2"]);
				$_SESSION['msg_to']=trim($array_file["3"]);
				$_SESSION['msg_sujet']=stripslashes(trim($array_file["4"]));

				$_SESSION['msg_message']=array_slice($array_file, 5);
				$_SESSION['msg_message_txt']=stripslashes(implode($_SESSION['msg_message']));
			}
		}
		else
			$location="index.php";
	}
	elseif(!isset($_SESSION["msg"]) || !isset($_SESSION["msg_id"]) || !isset($_SESSION['msg_sujet']) || !isset($_SESSION['msg_exp_id'])
				|| !isset($_SESSION['msg_exp']) || !isset($_SESSION['msg_message']) || !isset($_SESSION["msg_message_txt"]))
		$location="index.php";

	if(isset($location)) // param�tre manquant : retour � l'index
	{
		db_close($dbr);
		
		session_write_close();
		header("Location:$location");
		exit();
	}

	en_tete_candidat();
	menu_sup_candidat($__MENU_MSG);

?>
<div class='main'>
	<div class='menu_gauche'>
		<ul class='menu_gauche'>
			<?php
				dossiers_messagerie();
			?>
		</ul>
	</div>
	<div class='corps'>
		<?php
			titre_page_icone("Messagerie interne : message de $_SESSION[msg_exp]", "email_32x32_fond.png", 15, "L");

			$date_today=date("ymd") . "00000000000"; // on s'aligne sur le format des identifiants

			// Identifiant du message = date
			// Format : AA(1 ou 2) MM JJ HH Mn SS �S(5)

			if(strlen($_SESSION["msg_id"])==16) // Ann�e sur un caract�re
			{
				$date_offset=0;
				$annee_len=1;
				$leading_zero="0";
			}
			else
			{
				$date_offset=1;
				$annee_len=2;
				$leading_zero="";
			}

			// Flag lu/non lu
			if(!$msg_read)
			{
				$_SESSION["msg"]=$_SESSION["msg_id"] . ".1";
				// rename("$fichier", "$__CAND_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[authentifie]/$_SESSION[current_dossier]/$_SESSION[msg]");
				rename("$fichier", "$_SESSION[msg_dir]/$_SESSION[msg]");
			}

			// On convertit la date en temps Unix : plus simple ensuite pour l'affichage et les conversions
			$unix_date=mktime(substr($_SESSION["msg_id"], 5+$date_offset, 2), substr($_SESSION["msg_id"], 7+$date_offset, 2), substr($_SESSION["msg_id"], 9+$date_offset, 2),
									substr($_SESSION["msg_id"], 1+$date_offset, 2), substr($_SESSION["msg_id"], 3+$date_offset, 2), $leading_zero . substr($_SESSION["msg_id"], 0, $annee_len));

			$date_txt=ucfirst(date_fr("l d F Y - H\hi", $unix_date));

			$crypt_params_to=crypt_params("to=$_SESSION[msg_exp_id]&r=1");
			$crypt_params_suppr=crypt_params("msg=$_SESSION[msg_id]");
			// $crypt_params=crypt_params("msg=$_SESSION[msg_id]");

			print("<table class='encadre_messagerie' width='95%' align='center' style='margin-bottom:30px;'>
						<tr>
							<td class='td-msg-titre fond_menu2' style='padding:4px 2px 4px 2px;'>
								<a href='index.php' class='lien_menu_gauche' style='font-size:14px;'><b>$__MSG_DOSSIERS[$current_dossier]</b>
							</td>
						</tr>
						<tr>
							<td class='td-msg-menu fond_menu' style='white-space:normal; padding:4px 2px 4px 2px;'>
								<font class='Texte_menu'>
									<b>$date_txt - Sujet : $_SESSION[msg_sujet]</b>
								</font>
							</td>
						</tr>\n");

			if($_SESSION['msg_exp_id']!=$__USER_SYSTEME_ID)
				print("<tr>
							<td class='td-msg-tools fond_gris_B' style='vertical-align:top;' height='20'>
								<font class='Texte_menu'>
									<a href='suppr_msg.php?p=$crypt_params_suppr' class='lien_bleu_12'>Supprimer</a>&nbsp;|&nbsp;<a href='compose.php?p=$crypt_params_to' class='lien_bleu_12'>R�pondre</a>
								</font>
							</td>
						</tr>\n");

			print("<tr>
						<td class='td-msg-titre fond_page' style='border-right:0px; border-left:0px; height:10px;'></td>
					 </tr>
					 <tr>
						<td class='td-msg' style='white-space:normal; vertical-align:top; background-color:white; padding-bottom:20px;'>
							<font class='Texte'><br>\n");

			// Pi�ces jointes ?
			if(isset($flag_pj) && $flag_pj==1 && is_dir("$_SESSION[msg_dir]/files"))
			{
				$array_pj=scandir("$_SESSION[msg_dir]/files");
				// 4 �l�ments � ne pas inclure dans la recherche : ".", "..", le message et "index.php"

				if(FALSE!==($key=array_search("$_SESSION[msg]", $array_pj)))
					unset($array_pj[$key]);

				if(FALSE!==($key=array_search(".", $array_pj)))
					unset($array_pj[$key]);

				if(FALSE!==($key=array_search("..", $array_pj)))
					unset($array_pj[$key]);

				if(FALSE!==($key=array_search("index.php", $array_pj)))
					unset($array_pj[$key]);
				// **************** //

				if(count($array_pj))
					print("Pi�ce(s) jointe(s) : <br>\n");

				foreach($array_pj as $pj_name)
				{
					$crypt_params_pj=crypt_params("pj=$pj_name");
					print("- <a href='view.php?p=$crypt_params_pj' class='lien_bleu_12' target='_blank'>$pj_name</a><br>\n");
				}
			}

			// print(nl2br(msg_macros($_SESSION["msg_message_txt"])) . "</font>
         print(nl2br(parse_macros($_SESSION["msg_message_txt"])) . "</font>
							</td>
						</tr>
						</table>\n");

			db_close($dbr);
		?>
	</div>
</div>
<?php
	pied_de_page_candidat();
?>
</body></html>
