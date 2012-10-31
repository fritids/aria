<?php
$MODULE=array(

// Nom du module
"MOD_NAME"      =>   "Module Apog�e",

// Sous r�pertoire de gestion/admin/modules/
"MOD_DIR"       =>   "apogee",

// Page(s) utilis�e(s) pour acc�der au module via la page d'administration
// MOD_CONFIG doit TOUJOURS �tre un tableau (array) contenant lui m�me un ou plusieurs arrays
// => Rien n'emp�che de rediriger vers une page de configuration plus compl�te du module
// => la gestion du niveau d'acc�s requis doit �tre g�r�e directement dans les pages

"MOD_CONFIG"   =>   array(
                          array("MOD_CONFIG_TITLE" => "Configuration et messages par d�faut",
                                "MOD_CONFIG_PAGE"  => "configuration.php",
                                "MOD_NIVEAU_MIN"   => "$GLOBALS[__LVL_RESP]"),
/*                                
                          array("MOD_CONFIG_TITLE" => "Messages sp�cifiques",
                                "MOD_CONFIG_PAGE"  => "messages_formations.php",
                                "MOD_NIVEAU_MIN"   => "$GLOBALS[__LVL_RESP]"),      
*/
                          array("MOD_CONFIG_TITLE" => "Activation par composante",
                                "MOD_CONFIG_PAGE"  => "activation.php",
                                "MOD_NIVEAU_MIN"   => "$GLOBALS[__LVL_RESP]"),

                          array("MOD_CONFIG_SEP"   => "Pour la composante courante :"),

                          array("MOD_CONFIG_TITLE" => "Centres de gestion",
                                "MOD_CONFIG_PAGE"  => "centres_gestion.php",
                                "MOD_NIVEAU_MIN"   => "$GLOBALS[__LVL_RESP]"),

                          array("MOD_CONFIG_TITLE" => "Codes et versions d'�tape",
                                "MOD_CONFIG_PAGE"  => "codes_formations.php",
                                "MOD_NIVEAU_MIN"   => "$GLOBALS[__LVL_SCOL_PLUS]")
                         ),

"MOD_INCLUDE"   => array("include/db.php",
                        "include/fonctions.php")
);

?>