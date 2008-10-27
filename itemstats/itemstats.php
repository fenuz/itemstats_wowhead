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

include_once(dirname(__FILE__) . '/config.php');
include_once(dirname(__FILE__) . '/includes/itemcache.php');

include_once(dirname(__FILE__) . '/config_itemstats.php');

include_once(dirname(__FILE__) . '/includes/allakhazam.php');
include_once(dirname(__FILE__) . '/includes/thottbot.php');
include_once(dirname(__FILE__) . '/includes/wowdbu.php');
include_once(dirname(__FILE__) . '/includes/judgehype.php');
include_once(dirname(__FILE__) . '/includes/blasc.php');
include_once(dirname(__FILE__) . '/includes/wowhead.php');


define('ICON_LINK_PLACEHOLDER', '{ITEM_ICON_LINK}');
define('DEFAULT_ICON', 'inv_misc_questionmark');

function getStrCssStyle()
{
    if (defined('tooltip_css'))
    {
        return ("/templates/" . tooltip_css);
    }
    else
        return ("/templates/itemstats.css");
}

function getStrTooltipStyle()
{
    if (defined('tooltip_js'))
    {
        if (tooltip_js == 'overlib')
        {
            return ("/overlib/overlib.js");
        }
        else
        {
            return ("/tooltips_light/tooltips_light.js");
        }
    }
    else
        return ("/overlib/overlib.js");
}

function getViewitemLink($item_name, $type, $icon_lsize, $path_itemstats)
{
    if ($type == 'item')
        $add_type = '&type=item';
    else if ($type == 'itemico')
        $add_type = '&type=itemico';
    else
        $add_type = '';
        
    if ($icon_lsize != '')
        $add_icon_lsize = "&size=" . $icon_lsize;
    else
        $add_icon_lsize = '';

    $html = '<script src="' . $path_itemstats . '/viewitem.php?item=' . $item_name . $add_type . $add_icon_lsize . '" type="text/javascript"></script>';

    return ($html);   
}


// The main interface to the ItemStats module.
class ItemStats
{
	var $item_cache;
    var $info_site;
    var $connected;

    var $info_site_allakhazam;
    var $info_site_thottbot;
    var $info_site_judgehype;
    var $info_site_wowdbu;
    var $info_site_blasc;
    var $info_site_wowhead;

    // Constructor
	function ItemStats($bNewConnection = false, $openConnection = 0)
	{
        if (debug_mode == true)
        {
            echo "<br/><br/>Itemstats class initialized<br/>";
            echo "Preferences : ==============================================<br/>";
            echo "item_lang_default : " . item_lang_default . "<br/>";
            if (displayitemstatslink == true)
            	echo "displayitemstatslink : true<br/>";
            else
            	echo "displayitemstatslink : false<br/>";
            echo "path_cache : " . path_cache . "<br/>";
            if (automatic_search == true)
                echo "automatic_search : true<br/>";
            else
                echo "automatic_search : false<br/>";
            echo "Integration_mode : " . integration_mode . "<br/>";
			echo "openConnection : " . $openConnection . "<br/>";
            echo "Tooltip_css : " . tooltip_css . "<br/>";
            echo "Tooltip_js : " . tooltip_js . "<br/>";
            if (debug_mode == true)
                echo "debug_mode : true<br/>";
            else
                echo "debug_mode : false<br/>";            
            if (defined('path_itemstats'))
                echo "path_itemstats : " . path_itemstats . "<br/>";
            echo "priority list : <br/>";
            print_r($GLOBALS["prio"]);
            echo "<br/>============================================================<br/><br/>";            
        }
        
        if ($openConnection == 2 || ($openConnection == 0 && integration_mode == 'script'))
        {
            $this->connected = true;			
            return;
        }
        
		$this->item_cache = new ItemCache($bNewConnection);
        $this->connected = $this->item_cache->connected;
        if ($this->connected == false)
            return;

        if (debug_mode == true)
        {
            if ($bNewConnection == true)
                echo "Itemstats connected to database with NEW connection activated.<br/><br/>";
            else
                echo "Itemstats connected to database WITHOUT new connection activated.<br/><br/>";
        }
            
        // MODIFICATION, ItemStat http://itemstats.free.fr === by Yahourt / Thorkal == EU Elune / Horde =========
        $this->info_site_allakhazam = new ParseAllakhazam();
        $this->info_site_thottbot = new ParseThottbot();
        $this->info_site_judgehype = new ParseJudgehype();
        $this->info_site_wowdbu = new ParseWowdbu();
        $this->info_site_blasc = new ParseBlasc();
	$this->info_site_wowhead = new ParseWowhead();
        //========================================================================================================

		// Setup a ghetto destructor.
		register_shutdown_function(array(&$this, '_ItemStats'));
	}

	// Ghetto Destructor
	function _ItemStats()
	{
		if (isset($this->item_cache))
		{
			$this->item_cache->close();

	        $this->info_site_allakhazam->close();
	        $this->info_site_thottbot->close();
	        $this->info_site_judgehype->close();
	        $this->info_site_wowdbu->close();
	        $this->info_site_blasc->close();
		$this->info_site_wowhead->close();
		}
	}

    function    getItemForDisplay($item_name, $type, $icon_lsize, $search_objects, $force_integration_mode = 0)
    {
        if (debug_mode == true)
        {
            echo "New getItemForDisplay : <br/>";
            echo "item_name : " . $item_name . "<br/>";
            echo "type : " . $type . "<br/>";
            echo "icon_lsize : " . $icon_lsize . "<br/>";
            echo "search_objects : " . $search_objects . "<br/>";
			echo "force_integration_mode : " . $force_integration_mode . "<br/>";
        }        
        $item_name = cleanHTML($item_name);
        
        if ($force_integration_mode == 2 || ($force_integration_mode == 0 && integration_mode == 'script'))
        {
            $html = getViewitemLink($item_name, $type, $icon_lsize, "{PATH_ITEMSTATS}");
            return ($html);
        }
		
		if (!isset($this->item_cache))
		{
	        if (debug_mode == true)
	            echo "==> GROSSE ERREUR : Utilisation du Itemcache sans qu'il soit charg�, ni connect�<br/>" ;
		}

        // Get the proper name of this item.
		$item_name = $this->getItemName($item_name, $search_objects);

        if (debug_mode == true)
            echo "=> getItemName (real case name) : " . $item_name . "<br/>" ;
        
        // On regle la taille
        if ($icon_lsize == '')
            $icon_size = '40';
        else if ($icon_lsize == '=0')
            $icon_size = '10';
        else if ($icon_lsize == '=1')
            $icon_size = '20';
        else if ($icon_lsize == '=2')
            $icon_size = '30';
        else if ($icon_lsize == '=3')
            $icon_size = '40';
        else if ($icon_lsize == '=4')
            $icon_size = '50';
        else if ($icon_lsize == '=5')
            $icon_size = '60';
        else
            $icon_size = '40';

        if ($type == 'item')
        {
		    // Initialize the html.
		    $item_html = '[' . $item_name . ']';

		    // Get the color of this item and apply it to the html.
		    $item_color = $this->getItemColor($item_name);
		    if (!empty($item_color))
			    $item_html = "<span class='" . $item_color . "'>" . $item_html . "</span>";
        }
        else // Balise Itemico
        {
            // Recuperation du lien de l'image
            $item_html = $this->getItemIconLink($item_name);
            $item_html = "<img src='" . $item_html . "' width='" . $icon_size . "' height='" . $icon_size . "' border='0' />";
        }

		// Get the tooltip html for this item and apply it to the html.
		$item_tooltip_html = $this->getItemTooltipHtml($item_name);
		if (!empty($item_tooltip_html))
		{
			$item_html = "<span " . $item_tooltip_html . ">" . $item_html . "</span>";
		}

		// If this item has a link to the info site, add this link to the HTML.  If it doesn't have a link, it
		// means the item hasn't been found yet, so put up a link to the update page instead.
		$item_link = $this->getItemLink($item_name);
		if (!empty($item_link))
		{
			$item_html = "<a class='forumitemlink' target='_blank' href='" . $item_link . "'>" . $item_html . "</a>";
		}
		else
		{
			$item_link = '{PATH_ITEMSTATS}/updateitem.php?item=' . urlencode(urlencode($item_name));
			$item_html = "<a class='forumitemlink' href='$item_link'>" . $item_html . "</a>";
		}

        if (defined('displayitemstatslink') && displayitemstatslink == true)
            $item_html = str_replace("{ITEMSTATS_LINK}", "<br/><p class=\'textitemstats\'>itemstats.free.fr</p>", $item_html);
        else
            $item_html = str_replace("{ITEMSTATS_LINK}", "", $item_html);

        // For Guild Heberg :
        // $item_html = str_replace("''", "\\'", $item_html);

        if (debug_mode == true)
            echo "====== END getItemForDisplay ==================================================" ;
        
        return ($item_html);
    }

    // Returns the properly capitalized name for the specified item.  If the update flag is set and the item is
	// not in the cache, item data item will be fetched from an info site
	function    getItemName($name, $update = false)
	{        
        // Check if it is an id
        $id_object = substr($name, 0, strlen($name) - 2);
        $lang_object = substr($name, strlen($name) - 2, 2);

        if ($lang_object != 'fr' && $lang_object != 'en' && $lang_object != 'de' && $lang_object != 'es' && $lang_object != 'zh' && $lang_object != 'ko')
        {
            $id_object = $id_object . $lang_object;
            $lang_object = item_lang_default;
        }

        $objectid_to_check = $id_object;
        if (strstr($id_object, ',') !== false)
        	$objectid_to_check = str_replace(",", ".", $objectid_to_check);
        if (is_numeric($objectid_to_check))
        {
            if (debug_mode == true)
            {
                echo "We find a Blizzard Item Id, getItemNameFromId : <br/>";
                echo "id_object :" . $id_object . "<br/>";
                echo "lang_object :" . $lang_object . "<br/>";
                echo "update :" . $update . "<br/><br/>";
            }

            $proper_name = $this->getItemNameFromId($id_object, $lang_object, $update);
            if (!empty($proper_name))
                return ($proper_name);
        }

		$proper_name = $this->item_cache->getItemName($name);

		// If this item was not found and the update flag is set, try to fetch the item data from an info site.
		if (empty($proper_name) && $update)
		{
			$this->updateItemFromName($name);
			$proper_name = $this->item_cache->getItemName($name);
		}

		return empty($proper_name) ? $name : $proper_name;
	}

	// Returns the properly capitalized name for the specified item.  If the update flag is set and the item is
	// not in the cache, item data item will be fetched from an info site
	function    getItemNameFromId($id, $lang_object, $update = false)
	{
        if ($lang_object == '')
            $lang_object = item_lang_default;

        $proper_name = $this->item_cache->getItemNameFromId($id, $lang_object);

		// If this item was not found and the update flag is set, try to fetch the item data from an info site.
		if (empty($proper_name) && $update)
		{
			$this->updateItemFromId($id, $lang_object);
			$proper_name = $this->item_cache->getItemNameFromId($id, $lang_object);
		}

		return empty($proper_name) ? $id . $lang_object : $proper_name;
    }


	// Returns the link to the info site for the specified item.  If the update flag is set and the item is not in
	// the cache, item data will be fetched from an info site
	function    getItemLinkFromId($id, $lang_object, $update = false)
	{
        if ($lang_object == '')
            $lang_object = item_lang_default;

		$link = $this->item_cache->getItemLinkFromId($id, $lang_object);

		// If this item was not found and the update flag is set, try to fetch the item data from an info site.
		if (empty($link) && $update)
		{
			$this->updateItem($name);
			$link = $this->item_cache->getItemLinkFromId($name);
		}

		return $link;
	}

	// Returns the link to the info site for the specified item.  If the update flag is set and the item is not in
	// the cache, item data will be fetched from an info site
	function    getItemLink($name, $update = false)
	{
		$link = $this->item_cache->getItemLink($name);

		// If this item was not found and the update flag is set, try to fetch the item data from an info site.
		if (empty($link) && $update)
		{
			$this->updateItem($name);
			$link = $this->item_cache->getItemLink($name);
		}

		return $link;
	}

	// Returns the color class for the specified item.  If the update flag is set and the item is not in the cache, item
	// data will be fetched from an info site
	function getItemColor($name, $update = false)
	{
		$color = $this->item_cache->getItemColor($name);

		// If this item was not found and the update flag is set, try to fetch the item data from an info site.
		if (empty($color) && $update)
		{
			$this->updateItem($name);
			$color = $this->item_cache->getItemColor($name);
		}

		return $color;
	}

	// Returns the icon link for the specified item.  If the update flag is set and the item is not in the cache, item
	// data will be fetched from an info site
	function getItemIconLink($name, $update = false)
	{
		$icon = $this->item_cache->getItemIcon($name);

		// If this item was not found and the update flag is set, try to fetch the item data from an info site.
		if (empty($icon) && $update)
		{
			$this->updateItem($name);
			$icon = $this->item_cache->getItemIcon($name);
		}

		// If the icon was found, create a link by merging it with the icon path and extension.
		if (!empty($icon))
		{
			$icon_link = ICON_STORE_LOCATION . $icon . ICON_EXTENSION;
		}
        else
            $icon_link = ICON_STORE_LOCATION . DEFAULT_ICON . ICON_EXTENSION;

		return $icon_link;
	}

	// Returns the html for the specified item.  If the update flag is set and the item is not in the cache, the
	// item will be fetched from an info site
	function getItemHtml($name, $update = false)
	{
		$html = $this->item_cache->getItemHtml($name);

		// If this item was not found and the update flag is set, try to fetch the item data from an info site.
		if (empty($html) && $update)
		{
			$this->updateItem($name);
			$html = $this->item_cache->getItemHtml($name);
		}

		// If the item was found, update the icon path in the HTML.
		if (!empty($html))
		{
			$html = str_replace(ICON_LINK_PLACEHOLDER, $this->getItemIconLink($name), $html);
		}

		return $html;
	}

	// Returns the overlib tooltip html for the specified item.  If the update flag is set and the item is not in
	// the cache, the item will be fetched from an info site
	function getItemTooltipHtml($name, $update = false)
	{
		// Retrieve the item data from the cache.
		$html = $this->getItemHtml($name, $update);
		if (empty($html))
		{
			return null;
		}

		// Warp the data around the HTML data that invokes the tooltip.
		if (!empty($html))
		{
			// Format the HTML to be compatible with Overlib.
			$html = str_replace(array("\n", "\r"), '', $html);
			$html = addslashes($html);

            if (defined('tooltip_js') && tooltip_js == 'light')
                $html = 'onmouseover="return doTooltip(event,\'' . $html . '\')" onmouseout="hideTip()"';
            else
                $html = 'onmouseover="return overlib(' . "'" . $html . "'" . ',VAUTO,HAUTO,FULLHTML);" onmouseout="return nd();"';
		}

		return $html;
	}



    function updateItem($object_str)
    {
        // Check if it is an id
        $id_object = substr($object_str, 0, strlen($object_str) - 2);
        $lang_object = substr($object_str, strlen($object_str) - 2, 2);

        if ($lang_object != 'fr' && $lang_object != 'en' && $lang_object != 'de' && $lang_object != 'es' && $lang_object != 'zh' && $lang_object != 'ko')
        {
            $id_object = $id_object . $lang_object;
            $lang_object = item_lang_default;
        }

        $objectid_to_check = $id_object;
        if (strstr($id_object, ',') !== false)
        	$objectid_to_check = str_replace(",", ".", $objectid_to_check);
        if (is_numeric($objectid_to_check))
            $result = $this->updateItemFromId($id_object, $lang_object);
        else
            $result = $this->updateItemFromName($object_str);

        return ($result);
    }

	// Retrieves the data for the specified item from an info site and caches it.
	function updateItemFromName($name)
	{
        if ($name == '')
            return ($name);
        // Retrives the data from an information site.
        // On init la chose :)
        $item['html'] = '';

        if (debug_mode == true)
        {
            echo "updateItemFromName : <br/>";
            echo "name :" . $name . "<br/>";
        }
        
        
        //=============== DEBUT XML_CACHE =============================================================
        // On v�rifie qu'il y a pas un fichier dans xml_cache
        // POUR LA RECHERCHE DE FICHIER CACHE, il faut encod� le nom en UTF8 sinon la recherche est mauvaise quand le nom comporte des accents.
        $search_name = utf8_encode($name);
        // On fait attention aux failles de s�curit�
        $search_name = str_replace("..", ".", $search_name);
        $search_name = str_replace("/", "", $search_name);
        $search_name = str_replace("\\", "", $search_name);

        if (debug_mode == true)
        {
            echo "Check on cache : <br/>";
            echo "search in :" . path_cache . $search_name . "<br/>";
        }
        
        // On v�rifie si il y a pas un fichier cache pour cet objet, ca permet de cr�er les objets qu'on a envie.
        if (file_exists(path_cache . $search_name))
        {
            if (debug_mode == true)
                echo "Object found !<br/><br/>";

            //echo "Fichier cache trouv� !<br/>";
	        if ($fp = fopen(dirname(__FILE__) . '/' . path_cache . '/' . $search_name, "r"))
            {
                $data = "";
                while (!feof($fp))
                    $data .= fread($fp, 4096);
                $value = explode("|", $data);
                if (count($value) > 3)
                {
					$item['name'] = $name;
                    $item['id'] = 0;
                    $item['lang'] = 'fr';
                    $item['link'] = $value[0];
                    $item['color'] = $value[1];
                    $item['icon'] = $value[2];
                    $item['html'] = $value[3];

                    $item['html'] = trim($item['html']);
                    if (substr($item['html'], strlen($item['html']) - 6, 6) == '</div>')
                        $item['html'] = substr($item['html'], 0, strlen($item['html']) - 6) . '{ITEMSTATS_LINK}</div>';

                    // Build the final HTML by merging the template and the data we just prepared.
                    $template_html = trim(file_get_contents(dirname(__FILE__) . '/templates/popup.tpl'));
			        $item['html'] = str_replace('{ITEM_HTML}', $item['html'], $template_html);
                }
                //echo "--> Erreur dans le fichier <br/>";
            }
	    }
        if (debug_mode == true)
        {
            if ($item['html'] == '')
                echo "Cache Object not found !<br/><br/>";
        }
        //=============== FIN XML_CACHE ===============================================================



        //===  SEARCH OBJECT IN DATABASE  ========================================================================
        if ($item['html'] == '')
        {
            for ($ct = 0; isset($GLOBALS["prio"][$ct]); $ct++)
            {
                if (debug_mode == true)
                    echo "Search on the site : " . $GLOBALS["prio"][$ct] . "<br/>";

                if ($GLOBALS["prio"][$ct] == 'allakhazam')
                    $item = $this->info_site_allakhazam->getItem($name);
                else if ($GLOBALS["prio"][$ct] == 'wowdbu')
                    $item = $this->info_site_wowdbu->getItem($name);
                else if ($GLOBALS["prio"][$ct] == 'judgehype')
                    $item = $this->info_site_judgehype->getItem($name);
                else if ($GLOBALS["prio"][$ct] == 'thottbot')
                    $item = $this->info_site_thottbot->getItem($name);
                else if ($GLOBALS["prio"][$ct] == 'blasc')
                    $item = $this->info_site_blasc->getItem($name);
                else if ($GLOBALS["prio"][$ct] == 'buffed')
                    $item = $this->info_site_blasc->getItem($name);
		else if ($GLOBALS["prio"][$ct] == 'wowhead')
		    $item = $this->info_site_wowhead->getItem($name);
                if (!empty($item['link']))
                    break;
            }
        }
        //========================================================================================================

        if (empty($item['link']))
        {
            if (debug_mode == true)
                echo "Item not found, getEmptyItem on " . $name . "<br/>";
            $item = $this->item_cache->getEmptyItem($name);
        }

		// If the item wasn't found, and we have something cached already, don't overwrite with lesser data.
		$cached_link = $this->getItemLink($name);
		if (!empty($item['link']) || empty($cached_link))
		{
            if (debug_mode == true)
                echo "Save item data on the Item_cache<br/>";
			// If the data was loaded succesfully, save it to the cache.
			$result = $this->item_cache->saveItem($item);
            if ($result == false)
            {
                $item = $this->item_cache->getEmptyItem($name);
                $result = $this->item_cache->saveItem($item);
            }
		}
        else
            $result = 0;

		return $result;
	}

    // Retrieves the data for the specified item from an info site and caches it.
	function updateItemFromId($id, $lang)
	{
        // Retrives the data from an information site.
        // On init la chose :)
        $item['html'] = '';

        if (debug_mode == true)
        {
            echo "updateItemFromId : <br/>";
            echo "id :" . $id . "<br/>";
            echo "lang :" . $lang . "<br/>";
        }
                
        for ($ct = 0; isset($GLOBALS["prio"][$ct]); $ct++)
        {
            if (debug_mode == true)
                echo "Search on the site : " . $GLOBALS["prio"][$ct] . "<br/>";
            
            if ($GLOBALS["prio"][$ct] == 'allakhazam')
			{
				for ($ct2 = 0; isset($GLOBALS["allakhazam_lang"][$ct2]); $ct2++)
				{
					if (substr($GLOBALS["allakhazam_lang"][$ct2], 0, 2) == $lang)
					{
						$item = $this->info_site_allakhazam->getItemId($id, $GLOBALS["allakhazam_lang"][$ct2]);
						break;
					}
				}
			}
            else if ($GLOBALS["prio"][$ct] == 'wowdbu' && $lang == 'fr')
                $item = $this->info_site_wowdbu->getItemId($id);
            else if ($GLOBALS["prio"][$ct] == 'judgehype' && $lang == 'fr')
                $item = $this->info_site_judgehype->getItemId($id);
            else if ($GLOBALS["prio"][$ct] == 'thottbot' && $lang == 'en')
                $item = $this->info_site_thottbot->getItemId($id);
            else if ($GLOBALS["prio"][$ct] == 'blasc' && $lang == 'de')
                $item = $this->info_site_blasc->getItemId($id);
            else if ($GLOBALS["prio"][$ct] == 'buffed' && $lang == 'de')
                $item = $this->info_site_blasc->getItemId($id);
	    else if ($GLOBALS["prio"][$ct] == 'wowhead' && $lang == 'en')
                $item = $this->info_site_wowhead->getItemId($id);
            if (!empty($item['link']))
                break;
        }
        //========================================================================================================

        if (empty($item['link']))
        {
            if (debug_mode == true)
                echo "Item not foud, getEmptyItem on " . $id . $lang . "<br/>";        
            $item = $this->item_cache->getEmptyItem($id . $lang);
        }

		// If the item wasn't found, and we have something cached already, don't overwrite with lesser data.
		$cached_link = $this->getItemLinkFromId($id, $lang);
		if (!empty($item['link']) || empty($cached_link))
		{
            if (debug_mode == true)
                echo "Save item data on the Item_cache<br/>";
			// If the data was loaded succesfully, save it to the cache.
			$result = $this->item_cache->saveItem($item);
            if ($result == false)
            {
                $item = $this->item_cache->getEmptyItem($name);
                $result = $this->item_cache->saveItem($item);
            }
                        
            //if (isset($item))
			// If the data was loaded succesfully, save it to the cache.
		}
        else
            $result = 0;
		return $result;
    }

}

function cleanHTML($string)
{
    if (function_exists("mb_convert_encoding"))
        $string = mb_convert_encoding($string, "ISO-8859-1", "HTML-ENTITIES");
    else
    {
       $conv_table = get_html_translation_table(HTML_ENTITIES);
       $conv_table = array_flip($conv_table);
       $string = strtr ($string, $conv_table);
       //$string = preg_replace('/\&\#([0-9]+)\;/me', "chr('\\1')", $string);
       $string = preg_replace('/&#(\d+);/me', "chr('\\1')", $string);
    }
    return ($string);
}

function cleanRegExp($string)
{
    $string = quotemeta($string);
    $string = str_replace("#", "\#", $string);
    return ($string);
}

?>