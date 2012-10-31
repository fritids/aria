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

   include "../../../../configuration/aria_config.php";
   include "$__INCLUDE_DIR_ABS/vars.php";
   include "$__INCLUDE_DIR_ABS/fonctions.php";
   include "$__INCLUDE_DIR_ABS/db.php";

   // includes sp�cifiques au module
   include "include/db.php"; // db.php appellera �galement update_db.php pour la mise � jour du sch�ma

   $php_self=$_SERVER['PHP_SELF'];
   $_SESSION['CURRENT_FILE']=$php_self;

   verif_auth("$__GESTION_DIR/login.php");

	if(!in_array($_SESSION['niveau'], array("$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
   {
      session_write_close();
      header("Location:$__MOD_DIR/gestion/noaccess.php");
      exit();
   }

	$dbr=db_connect();

	if(isset($_POST["modifier"]) || isset($_POST["modifier_x"]))
	{
		$user_id=$_POST["user_id"];
		$resultat=1;
	}

	if((isset($_POST["valider"]) || isset($_POST["valider_x"])) && isset($_SESSION["array_comp"]))
	{
      if(isset($_POST["pe"]) || isset($_POST["lp"]))
      {
         foreach($_SESSION["array_comp"] as $comp_id => $comp_array)
         {
            $_SESSION["array_comp"][$comp_id]["pe"]=isset($_POST["pe"]) && array_key_exists($comp_id, $_POST["pe"]) ? "t" : "f";
            $_SESSION["array_comp"][$comp_id]["lp"]=isset($_POST["lp"]) && array_key_exists($comp_id, $_POST["lp"]) ? "t" : "f";
         }
      }

      // Les champs "toutes" sont prioritaires et �crasent les choix pr�c�dents
      if(isset($_POST["all_pe"]))
      {
         foreach($_POST["all_pe"] as $univ_id => $val)
         {
            if($val!="")
            {
               foreach($_SESSION["array_comp"] as $comp_id => $comp_array)
               {
                  if($comp_array["univ_id"]==$univ_id)
                     $_SESSION["array_comp"][$comp_id]["pe"]="$val";
               }
            }
         }
      }

      if(isset($_POST["all_lp"]))
      {
         foreach($_POST["all_lp"] as $univ_id => $val)
         {
            if($val!="")
            {
               foreach($_SESSION["array_comp"] as $comp_id => $comp_array)
               {
                  if($comp_array["univ_id"]==$univ_id)
                     $_SESSION["array_comp"][$comp_id]["lp"]="$val";
               }
            }
         }
      }

      // Le tableau est complet, on met � jour la base de donn�es
      foreach($_SESSION["array_comp"] as $comp_id => $comp_array)
      {
         $extr_pe=$comp_array["pe"];
         $extr_lp=$comp_array["lp"];

         if(db_num_rows(db_query($dbr,"SELECT * FROM $_module_apogee_DB_activ WHERE $_module_apogee_DBC_activ_comp_id='$comp_id'")))
            db_query($dbr,"UPDATE $_module_apogee_DB_activ SET $_module_apogee_DBU_activ_pe='$extr_pe',
                                                               $_module_apogee_DBU_activ_lp='$extr_lp'
                            WHERE $_module_apogee_DBC_activ_comp_id='$comp_id'");
         else
            db_query($dbr,"INSERT INTO $_module_apogee_DB_activ VALUES ('$comp_id','$extr_pe','$extr_lp')");
      }

      $succes=1;
	}
	
	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>

<div class='main'>
	<?php
      titre_page_icone("Activation des extractions (OPI Primo-entrants et Laisser-Passer) par composante", "randr_32x32_fond.png", 30, "L");

		if(isset($id_existe_pas))
			message("Erreur : l'identifiant demand� est incorrect (probl�me de coh�rence de la base)", $__ERREUR);

		if(isset($succes) && $succes==1)
			message("Param�tres d'activation enregistr�s avec succ�s.", $__SUCCES);

		print("<form action='$php_self' method='POST' name='form1'>\n");
	?>
	<table align='center'>
	<?php
	   // Droits
	   
	   if($_SESSION["niveau"]==$__LVL_ADMIN || $_SESSION["niveau"]==$__LVL_SUPER_RESP)
         $condition_droits="";
      else
         $condition_droits="AND ($_DBC_composantes_id IN (SELECT $_DBC_acces_composante_id FROM $_DB_acces WHERE $_DBC_acces_id='$_SESSION[auth_id]')
                                OR $_DBC_composantes_id IN (SELECT $_DBC_acces_comp_composante_id FROM $_DB_acces_comp WHERE $_DBC_acces_comp_acces_id='$_SESSION[auth_id]'))";
         
      $result=db_query($dbr,"SELECT $_DBC_composantes_univ_id, $_DBC_universites_nom, $_DBC_composantes_id, $_DBC_composantes_nom,
                                CASE WHEN $_DBC_composantes_id IN (SELECT $_module_apogee_DBC_activ_comp_id FROM $_module_apogee_DB_activ)
                                  THEN (SELECT $_module_apogee_DBC_activ_pe FROM $_module_apogee_DB_activ
                                          WHERE $_module_apogee_DBC_activ_comp_id=$_DBC_composantes_id)
                                  ELSE 'f' END AS pe,
                                CASE WHEN $_DBC_composantes_id IN (SELECT $_module_apogee_DBC_activ_comp_id FROM $_module_apogee_DB_activ)
                                  THEN (SELECT $_module_apogee_DBC_activ_lp FROM $_module_apogee_DB_activ
                                          WHERE $_module_apogee_DBC_activ_comp_id=$_DBC_composantes_id)
                                  ELSE 'f' END AS lp
                             FROM $_DB_composantes, $_DB_universites
                             WHERE $_DBC_composantes_univ_id=$_DBC_universites_id
                             $condition_droits
                                ORDER BY $_DBC_universites_nom, $_DBC_composantes_nom");

		$rows=db_num_rows($result);

		$old_univ="";

      $_SESSION["array_comp"]=array();

		for($i=0; $i<$rows; $i++)
		{
			list($univ_id, $univ_nom, $comp_id, $comp_nom, $activ_pe, $activ_lp)=db_fetch_row($result, $i);

         $_SESSION["array_comp"]["$comp_id"]=array("univ_id" => "$univ_id", "pe" => "$activ_pe", "lp" => "$activ_lp");

			if($univ_id!=$old_univ)
			{
				print("<tr>
							<td class='fond_menu2' colspan='3' style='padding:6px 20px 6px 20px;'>
								<font class='Texte_menu2'><strong>$univ_nom</strong></font>
							</td>
						</tr>                 
                  <tr>
                     <td class='fond_menu2 td-gauche'><font class='Texte_menu2'><strong>Composante</strong></font></td>
                     <td class='fond_menu2 td-milieu'><font class='Texte_menu2'><strong>Primo Entrants</strong></font></td>
                     <td class='fond_menu2 td-milieu'><font class='Texte_menu2'><strong>Laisser Passer</strong></font></td>
                  </tr>
                  <tr>
                     <td class='fond_menu2 td-gauche'></td>
                     <td class='fond_menu2 td-milieu'>
<!--
                        <input type='checkbox' name='all_pe[]' value='$univ_id' style='vertical-align:middle;'>
                        <font class='Texte_menu2'><strong>Toutes</strong></font>
-->
                        <select name='all_pe[$univ_id]'>
                           <option value=''></option>
                           <option value='t'>Tout activer</option>
                           <option value='f'>Tout d�sactiver</option>
                        </select>
                     </td>
                     <td class='fond_menu2 td-milieu'>
<!--
                        <input type='checkbox' name='all_lp[]' value='$univ_id' style='vertical-align:middle;'>
                        <font class='Texte_menu2'><strong>Toutes</strong></font>
-->
                        <select name='all_lp[$univ_id]'>
                           <option value=''></option>
                           <option value='t'>Tout activer</option>
                           <option value='f'>Tout d�sactiver</option>
                        </select>
                     </td>
                  </tr>\n");

				$old_univ=$univ_id;
			}

         $checked_pe=$activ_pe=="t" ? "checked" : "";
         $checked_lp=$activ_lp=="t" ? "checked" : "";

			print("<tr>
						<td class='fond_menu td-gauche'>
                     <font class='Texte_menu'>$comp_nom</font>
                  </td>
                  <td class='fond_menu td-milieu'>
							<input type='checkbox' name='pe[$comp_id]' value='t' style='vertical-align:middle;' $checked_pe>
						</td>
                  <td class='fond_menu td-milieu'>
                     <input type='checkbox' name='lp[$comp_id]' value='t' style='vertical-align:middle;' $checked_lp>
                  </td>
					</tr>\n");
		}

		db_free_result($result);
      db_close($dbr);
	?>
	</table>

	<div class='centered_icons_box'>
		<a href='../../index.php' target='_self' class='lien_bleu_12'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Annuler' border='0'></a>
		<input type="image" src="<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>" alt="Valider" name="valider" value="Valider">
	</div>
   </form>
</div>
<?php
	pied_de_page();
?>
</body></html>

