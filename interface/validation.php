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

	if(isset($_SESSION['email']))
	{
		$email=$_SESSION["email"];
		$_SESSION["auth"]=1; // pour ne pas revenir automatiquement � l'index
	}
	else
	{
		session_write_close();
		header("Location:../session.php"); // page d'erreur standard : session expir�e
		exit();
	}
	
	en_tete_candidat();
	menu_sup_simple();
?>

<div class='main'>
	<?php
		titre_page_icone("Identifiants envoy�s !", "idea_32x32_fond.png", 30, "L");

		message("<center>
						<p>Un courriel vient de vous �tre envoy� � l'adresse \"<strong>$email</strong>\".</p>
						<p>Une fois vos identifiants re�us, vous pourrez vous authentifier via le formulaire de la page pr�c�dente.</p>
				 		<p><strong>Conservez bien vos identifiants</strong> car ils vous seront utiles tout au long de la proc�dure.</p>				 						 		
					</center>", $__INFO);
					
		message("<strong>Rappels</strong> :
		         <br>- n'oubliez pas de v�rifier le contenu du dossier <strong>\"Spams\"</strong> (ou <strong>\"Courriers ind�sirables\"</strong>) de votre messagerie,
		         <br>- les filtres de votre messagerie doivent autoriser les courriels provenant de l'adresse \"$__EMAIL_ADMIN\".", $__WARNING);		         
	?>

	<div class='centered_icons_box'>
		<a href='identification.php' target='_self' class='lien2'>
			<img src='<?php echo "$__ICON_DIR/back_32x32.png"; ?>' alt='Retour' border='0'>
		</a>
	</div>
</div>

<?php
	pied_de_page_candidat();
?>

</body>
</html>

