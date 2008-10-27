<?php
/*
+---------------------------------------------------------------+
|       Itemstats FR Core
|
|       Yahourt
|       http://itemstats.free.fr
|       itemstats@free.fr
|
|       Thorkal
|       EU Elune / Horde
|       www.elune-imperium.com
+---------------------------------------------------------------+
*/

//[en]
// Order search
// Actually it search in this order : judgehype, allakhazam, buffed, wowdbu and thottbot
// --
// [fr]
// Permet de d�finir la priorit� de la recherche
// Actuellement il va chercher dans cet ordre :  judgehype, allakhazam, buffed, wowdbu puis thottbot

$GLOBALS["prio"][] = 'wowhead';
//$GLOBALS["prio"][] = 'allakhazam';
//$GLOBALS["prio"][] = 'judgehype';
//$GLOBALS["prio"][] = 'buffed';
//$GLOBALS["prio"][] = 'wowdbu';
//$GLOBALS["prio"][] = 'thottbot';

//[en]
// Allakhazam languages search
// Actually it search in this order : fr, en, de, es, ko, zh
// --
// [fr]
// Langues de recherche dans Allakhazam
// Actuellement il va chercher dans cet ordre :  fr, en, de, es, ko, zh
$GLOBALS["allakhazam_lang"][] = 'enUS';
$GLOBALS["allakhazam_lang"][] = 'frFR';
$GLOBALS["allakhazam_lang"][] = 'deDE';
$GLOBALS["allakhazam_lang"][] = 'esES';
$GLOBALS["allakhazam_lang"][] = 'koKR';
$GLOBALS["allakhazam_lang"][] = 'zhCN';
$GLOBALS["allakhazam_lang"][] = 'zhTW';

//[en]
// Language default for Item's Id when not specified
// Example : [item]17182[/item] will choose this language
// It can be : 'en', 'fr', 'de'
// --
// [fr]
// Langage par d�faut pour les num�ros d'objet si la langue n'est pas sp�cifi�
// Par exemple : [item]17182[/item] choisira le langage s�lectionn� en dessous
// Vous pouvez mettre : 'fr', 'en' ou 'de'
define('item_lang_default', 'en');


//[en]
// The path for custom item, it's based on Itemstats directory path.
// --
// [fr]
// Le chemin pour les objets personnalis�s, c'est par rapport au dossier Itemstats.
define('path_cache', './xml_cache/');


//[en]
// Display the text "itemstats.free.fr" in the tooltips
// It can be : 'true' or 'false'
// --
// [fr]
// Afficher le texte "itemstats.free.fr" dans les infobulles
// Cela peut �tre : 'true' (activ�) ou 'false' (d�sactiv�)
define('displayitemstatslink', false);


//[en]
// Choose the comportement of Itemstats :
// - true : If the object is not on the cache, Itemstats will search it on data website (Allakhazam, etc.)
// - false : The object is displayed only if it is on cache, otherwise it stays grey and you have to click one time on it (to search the object and fill the cache)
// --
// [fr]
// D�finir le comportement d'Itemstats :
// - true : Si l'objet n'est pas dans le cache, Itemstats le cherchera automatiquement sur les sites d'objets (Allakhazam, etc.)
// - false : Les objets ne sont affich�s seulement si ils sont en cache, autrement ils restent gris et il faudra cliquer dessus pour lancer la rechercher
define('automatic_search', false);

//[en]
// Choose the integration mode
// - normal : Use the normal method, it scans the text and inject the tooltips directly in the HTML code
// - script : Use alternative method, it scans the text and put <script> tag (that asks the tooltip by the navigator)
// [fr]
// Choisir le mode d'int�gration
// - normal : Utilisation de la m�thode normale, il scanne le texte et inject le code des infobulles directement dans l'HTML
// - script : Utilisation de la m�thode alternative, il scanne le texte et pose des balises <script> (ce sera le navigateur qui fera la demande du code de l'infobulle)
define('integration_mode', 'normal');


//[en]
// Choose the tooltip style.
// - path to the CSS style based on the itemstats/templates directory
// - by default : "itemstats.css"
// [fr]
// Choisir le style d'infobulle
// - le chemin vers le fichier CSS, bas� sur le dossier itemstats/templates
// - par d�faut : "itemstats.css"
define('tooltip_css', 'wowhead.css');


//[en]
// Choose the tooltip displayer
// - overlib : Overlib (big tooltip library with very good compatibility) ~20ko+
// - light : Light Tooltip (very light tooltip script that works) ~3ko
// [fr]
// Choisir le systeme d'affichage d'infobulle
// - overlib : Overlib (une grosse librairie d'affiche d'infobulle avec une tres forte compatibilit�) ~20ko+
// - light : Light Tooltip (librairie de petite taille qui fonctionne bien) ~3ko
define('tooltip_js', 'overlib');


//[en]
// Activate or not the DEBUG MODE (more information if there is a problem)
// It can be : 'true' or 'false'
// --
// [fr]
// Activer ou non le MODE DE DEBOGAGE (cela done plus d'information si il y a un probleme)
// Cela peut �tre : 'true' (activ�) ou 'false' (d�sactiv�)
define('debug_mode', false);

//[en]
// Sockets images path (only for Allakhazam objects)
// Example : http://wow.allakhazam.com/images/
// --
// [fr]
// Chemin des images des "ch�sses" (rouge, bleu, etc.), seulement pour les objets d'Allakhazam
// Par exemple : http://wow.allakhazam.com/images/
define('path_sockets_image', 'http://wow.allakhazam.com/images/');

?>