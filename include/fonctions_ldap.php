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

function aria_ldap_connect()
{
   $ldap=@ldap_connect($GLOBALS["__LDAP_HOST"], $GLOBALS["__LDAP_PORT"]); 
   
   if(!$ldap)
      return -1;

   ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, $GLOBALS["__LDAP_PROTO"]);

   if(!@ldap_bind($ldap, $GLOBALS["__LDAP_ID"], $GLOBALS["__LDAP_PASS"]))
      return -1;

   return $ldap;
}

function aria_ldap_auth($login, $pass)
{
   if(!isset($GLOBALS["__LDAP_ATTR_LOGIN"]) || $GLOBALS["__LDAP_ATTR_LOGIN"]=="")
      return -1;
      
   $ldap=@ldap_connect($GLOBALS["__LDAP_HOST"], $GLOBALS["__LDAP_PORT"]); 
   
   if(!$ldap)
      return -1;

   ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, $GLOBALS["__LDAP_PROTO"]);

   $ret=@ldap_bind($ldap, "$GLOBALS[__LDAP_ATTR_LOGIN]=$login,$GLOBALS[__LDAP_BASEDN]", "$pass");
   
   aria_ldap_close($ldap);
   
   return $ret;
}

function recherche_individu_ldap($cnx_ldap, $nom_ou_login)
{
   if(empty($nom_ou_login) || $cnx_ldap==-1)
      return FALSE;

   // || !isset($GLOBALS["__LDAP_ATTR_PASS"]) || $GLOBALS["__LDAP_ATTR_PASS"]==""

   if(!isset($GLOBALS["__LDAP_ATTR_LOGIN"]) || $GLOBALS["__LDAP_ATTR_LOGIN"]=="" 
      || !isset($GLOBALS["__LDAP_ATTR_PRENOM"]) || $GLOBALS["__LDAP_ATTR_PRENOM"]=="" 
      || !isset($GLOBALS["__LDAP_ATTR_NOM"]) || $GLOBALS["__LDAP_ATTR_NOM"]=="" 
      || !isset($GLOBALS["__LDAP_ATTR_MAIL"]) || $GLOBALS["__LDAP_ATTR_MAIL"]=="")
      return FALSE;

   // Construction du filtre
   $filtre="(|($GLOBALS[__LDAP_ATTR_LOGIN]=$nom_ou_login)($GLOBALS[__LDAP_ATTR_NOM]=$nom_ou_login*))";

   $filtre_tri=array($GLOBALS["__LDAP_ATTR_PRENOM"],$GLOBALS["__LDAP_ATTR_NOM"]);

   if(FALSE!==strstr($GLOBALS["__LDAP_ATTR_MAIL"], ","))
      $attr_mails_array=explode(",", $GLOBALS["__LDAP_ATTR_MAIL"]);
   else
      $attr_mails_array=array($GLOBALS["__LDAP_ATTR_MAIL"]);
      
   $attributs_tmp=array($GLOBALS["__LDAP_ATTR_LOGIN"],$GLOBALS["__LDAP_ATTR_NOM"],$GLOBALS["__LDAP_ATTR_PRENOM"]);
   
   // Fusion des attributs simple et des potentielles valeurs multiples pour le mail
   $attributs=array_merge($attributs_tmp, $attr_mails_array);

   $result_ldap=ldap_search($cnx_ldap, $GLOBALS["__LDAP_BASEDN"], $filtre, $attributs);

   if($result_ldap==FALSE)
      return array();

   // Tri
   foreach($filtre_tri as $tri_attr)
      ldap_sort($cnx_ldap, $result_ldap, $tri_attr);
      
   // R�cup�ration du r�sultat tri�
   $entries_ldap=ldap_get_entries($cnx_ldap, $result_ldap);

   // traitement
   if($entries_ldap["count"])
   {
      $all_users=array();
      $i=0;

      foreach($entries_ldap as $num => $entry)
      {
         if(is_array($entry))
         {
            $current_user=array();
            
            if(array_key_exists($GLOBALS["__LDAP_ATTR_LOGIN"], $entry))
            {
               foreach($entry[$GLOBALS["__LDAP_ATTR_LOGIN"]] as $current_login)
               {
                  $current_user["login"]=$current_login;
                  
               }
	         }

            if(array_key_exists($GLOBALS["__LDAP_ATTR_PRENOM"], $entry))
            {
               foreach($entry[$GLOBALS["__LDAP_ATTR_PRENOM"]] as $current_prenom)
               {
                  $current_user["prenom"]=$current_prenom;
                  
               }
            }
            
            if(array_key_exists($GLOBALS["__LDAP_ATTR_NOM"], $entry))
            {
               foreach($entry[$GLOBALS["__LDAP_ATTR_NOM"]] as $current_nom)
               {
                  $current_user["nom"]=$current_nom;
                  
               }
            }

            $current_user["mail"]=array();
            $m=0;
            
            foreach($attr_mails_array as $current_attr_mail)
            {
               if(array_key_exists($current_attr_mail, $entry))
               {
                  foreach($entry[$current_attr_mail] as $current_mail)
                  {                     
                     if(FALSE!==strstr($current_mail, "@"))
                     {
                        $current_user["mail"][$m]=$current_mail;
                        $m++;
                     }
                  }
               }
            }
/*
            if(array_key_exists($GLOBALS["__LDAP_ATTR_PASS"], $entry))
               $current_user["pass"]=$entry["$attr_ldap_pass"];
*/
            if(count($current_user))
            {            
               $all_users[$i]=$current_user;
               $i++;
            }
         }
      }

      return $all_users;
   }
   else
      return array();
}

function aria_ldap_close($ldap)
{
   return ldap_unbind($ldap);
}


?>