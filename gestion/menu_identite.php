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
	// V�rifications compl�mentaires au cas o� ce fichier serait appel� directement
	verif_auth();

	if(!isset($_SESSION["candidat_id"]))
	{
		header("Location:index.php");
		exit;
	}

	print("<div class='centered_box'>
				<font class='Texte_16'><strong>$_SESSION[onglet] - Identit�</strong></font>
			 </div>\n");

	if(isset($_GET["succes"]) && $_GET["succes"]==1)
		message("Informations enregistr�es avec succ�s", $__SUCCES);

	if((isset($_GET["wsb"]) && $_GET["wsb"]==1) || (isset($_GET["wab"]) && $_GET["wab"]==1))
	{
		$message="<strong>Attention :</strong><br>";

		if(isset($_GET["wab"]))
		{
			$message.="- l'ann�e d'obtention du baccalaur�at (ou �quivalent) est requise. Si ".$_SESSION["tab_candidat"]["etudiant_particule"]." ne l'a pas 
							 pas obtenu, indiquez l'ann�e du dernier dipl�me obtenu.";

			if(isset($_GET["wsb"]))
				$message.="<br>";
		}

		if(isset($_GET["wsb"]))
			$message.="- la s�rie du bac doit �tre pr�cis�e. Vous pouvez �ventuellement s�lectionner \"Sans bac\" si ".$_SESSION["tab_candidat"]["etudiant_particule"]." 
							 ne l'a pas obtenu.";

		message($message, $__WARNING);
	}

	if(in_array($_SESSION["niveau"], array("$__LVL_SCOL_MOINS","$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
	{
		print("<table cellpadding='2' cellspacing='0' border='0' align='center'>
				<tr>
					<td align='center' valign='middle' width='40'>
						<a href='edit_candidat.php' target='_self' class='lien2'>
							<img src='$__ICON_DIR/edit_22x22_fond.png' border='0' alt='Modifier' desc='Modifier'>
						</a>
					</td>
					<td align='left' valign='middle' nowrap='true'>
						<a href='edit_candidat.php' target='_self' class='lien2'>Modifier ces informations</a>
					</td>
				</tr>
				</table>
				<br>");
	}
?>
	<table align='center' style='padding-bottom:20px;'>
	<?php
		// Administrateur : affichage de l'identifiant numérique (ID dans la base de données)
		if($_SESSION["niveau"]==$__LVL_ADMIN || $_SESSION["niveau"]==$__LVL_SUPPORT)
		{			
	?>
	<tr>
      <td class='td-gauche fond_menu2'>
         <font class='Texte_menu2'><b>ID : </b></font>
      </td>
      <td class='td-droite fond_menu'>
         <font class='Texte_menu'><?php echo $_SESSION["candidat_id"]; ?></font>
      </td>
   </tr>
	<?php
		}
	?>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b><?php echo $_SESSION['tab_candidat']['etudiant']; ?> : </b></font>
		</td>
		<td class='td-droite fond_menu'>
			<font class='Texte_menu'>
			   <strong>
			   <?php 
			      echo $_SESSION["tab_candidat"]["civ_texte"] . " " . $_SESSION['tab_candidat']['nom'] . " " . $_SESSION['tab_candidat']['prenom']; 
			      
			      if($_SESSION["tab_candidat"]["prenom2"]!="")
			         echo " (".$_SESSION['tab_candidat']['prenom2'].")";
			   ?>
			   </strong>
			</font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Nom de naissance</b> : </font>
		</td>
		<td class='td-droite fond_menu'>
			<font class='Texte_menu'>
				<?php
						echo $_SESSION['tab_candidat']['nom_naissance'];;
				?>
			</font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Num�ros INE</b> : </font>
		</td>
		<td class='td-droite fond_menu'>
			<font class='Texte_menu'>
				<?php
					if(empty($_SESSION['tab_candidat']['numero_ine']))
						echo "Non renseign�";
					else
						echo $_SESSION['tab_candidat']['numero_ine'];;
				?>
			</font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b><?php echo $_SESSION['tab_candidat']['ne_le']; ?> : </b></font>
		</td>
		<td class='td-droite fond_menu'>
			<font class='Texte_menu'><?php echo $_SESSION['tab_candidat']['txt_naissance']; ?></font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Ville de naissance : </b></font>
		</td>
		<td class='td-droite fond_menu'>
			<font class='Texte_menu'><?php echo $_SESSION['tab_candidat']['lieu_naissance']; ?></font>
		</td>
	</tr>
	<?php
		if(isset($_SESSION["tab_candidat"]["dpt_naissance"]) && $_SESSION["tab_candidat"]["dpt_naissance"]!="" && isset($_SESSION["tab_candidat"]["nom_departement"]) && $_SESSION["tab_candidat"]["nom_departement"]!="")
		{
	?>
	<tr>
		<td nowrap='true' class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>D�partement de naissance : </b></font>
		</td>
		<td nowrap='true' class='td-droite fond_menu'>
			<font class='Texte_menu'><?php echo $_SESSION["tab_candidat"]["dpt_naissance"] . " - " . $_SESSION["tab_candidat"]["nom_departement"]; ?></font>
		</td>
	</tr>
	<?php	
		}
	?>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Pays de naissance : </b></font>
		</td>
		<td class='td-droite fond_menu'>
			<font class='Texte_menu'><?php echo stripslashes($_SESSION['tab_candidat']['pays_naissance']); ?></font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Nationalit�</b> : </font>
		</td>
		<td class='td-droite fond_menu'>
			<font class='Texte_menu'><?php echo preg_replace("/_/","",$_SESSION['tab_candidat']['nationalite']); ?></font>
		</td>
	</tr>
	
	<?php
		// Informations de contact uniquement affich�es pour la scolarit�

		if(in_array($_SESSION["niveau"], array("$__LVL_SUPPORT", "$__LVL_SCOL_MOINS","$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
		{
	?>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>T�l�phone fixe</b> : </font>
		</td>
		<td class='td-droite fond_menu'>
			<font class='Texte_menu'><?php echo $_SESSION['tab_candidat']['telephone']; ?></font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>T�l�phone portable</b> : </font>
		</td>
		<td class='td-droite fond_menu'>
			<font class='Texte_menu'><?php echo $_SESSION['tab_candidat']['telephone_portable']; ?></font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Adresse</b> : </font>
		</td>
		<td class='td-droite fond_menu'>
			<font class='Texte_menu'>
				<?php
					$adresse=$_SESSION['tab_candidat']['adresse'] . "\n" . $_SESSION['tab_candidat']['adresse_cp'] . " " . $_SESSION['tab_candidat']['adresse_ville'] . "\n" . $_SESSION['tab_candidat']['adresse_pays'];
					echo nl2br(stripslashes($adresse));
				?>
			</font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Courriel</b> : </font>
		</td>
		<td class='td-droite fond_menu'>
			<font class='Texte_menu'>
				<!-- <a href='mailetudiant.php' class='lien_menu_gauche'><?php echo $_SESSION['tab_candidat']['email']; ?></a> -->
				<?php
					$to=crypt_params("to=$candidat_id");
					print("<a href='messagerie/compose.php?p=$to' class='lien_menu_gauche'>Lui envoyer un message</a>\n");
				?>
			</font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Derni�re connexion</b> : </font>
		</td>
		<td class='td-droite fond_menu'>
			<font class='Texte_menu'><?php echo $_SESSION['tab_candidat']['derniere_connexion']; ?>&nbsp;</font>
		</td>
	</tr>
	<?php
		}
	?>
	<tr>
	   <td class='fond_page' colspan='2' height='10'></td>
	</tr>
	<tr>
		<td nowrap='true' class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Baccalaur�at (ou �quivalent)</b> : </font>
		</td>
		<td nowrap='true' class='td-droite fond_menu'>
			<font class='Texte_menu'>
				<?php  
					echo $_SESSION["tab_candidat"]["annee_bac"] . " - " . $_SESSION["tab_candidat"]["nom_serie_bac"];
				?>
			</font>
		</td>
	</tr>
	<tr>
	   <td nowrap='true' class='td-gauche fond_menu2'>
		   <font class='Texte_menu2'>
   		   <strong>
	   	      Inscription ant�rieure
		         <br />dans cette Universit� ? :
   			</strong>
   		</font>
   	</td>
	   <td nowrap='true' class='td-droite fond_menu'>
		   <font class='Texte_menu'>
			   <?php  
   			   if($_SESSION["tab_candidat"]["deja_inscrit"]==1)
	   		      print("Oui (". $_SESSION["tab_candidat"]["annee_premiere_inscr"].")");
		   		else
			   	   print("Non");
   			?>
   		</font>
   	</td>
   </tr>
	</table>
