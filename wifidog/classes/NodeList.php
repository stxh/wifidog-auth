<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

// +-------------------------------------------------------------------+
// | WiFiDog Authentication Server                                     |
// | =============================                                     |
// |                                                                   |
// | The WiFiDog Authentication Server is part of the WiFiDog captive  |
// | portal suite.                                                     |
// +-------------------------------------------------------------------+
// | PHP version 5 required.                                           |
// +-------------------------------------------------------------------+
// | Homepage:     http://www.wifidog.org/                             |
// | Source Forge: http://sourceforge.net/projects/wifidog/            |
// +-------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or     |
// | modify it under the terms of the GNU General Public License as    |
// | published by the Free Software Foundation; either version 2 of    |
// | the License, or (at your option) any later version.               |
// |                                                                   |
// | This program is distributed in the hope that it will be useful,   |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of    |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the     |
// | GNU General Public License for more details.                      |
// |                                                                   |
// | You should have received a copy of the GNU General Public License |
// | along with this program; if not, contact:                         |
// |                                                                   |
// | Free Software Foundation           Voice:  +1-617-542-5942        |
// | 59 Temple Place - Suite 330        Fax:    +1-617-542-2652        |
// | Boston, MA  02111-1307,  USA       gnu@gnu.org                    |
// |                                                                   |
// +-------------------------------------------------------------------+

/**
 * @package    WiFiDogAuthServer
 * @subpackage NodeLists
 * @author     Max Horváth <max.horvath@freenet.de>
 * @copyright  2006 Max Horváth, Horvath Web Consulting
 * @version    Subversion $Id: Content.php 974 2006-02-25 15:08:12Z max-horvath $
 * @link       http://www.wifidog.org/
 */

/**
 * Load required classes
 */
require_once('classes/Cache.php');

/**
 * Defines and loads any type of node list
 *
 * @package    WiFiDogAuthServer
 * @subpackage NodeLists
 * @author     Max Horváth <max.horvath@freenet.de>
 * @copyright  2006 Max Horváth, Horvath Web Consulting
 */
/**
 * Directory of NodeList classes
 */   define ('NODE_LIST_CLASSES_DIR', WIFIDOG_ABS_FILE_PATH . "classes/NodeLists");
class NodeList {
    /**
     * Directory of NodeList classes
     *
     * @var string
     *

     */


    /**
     * NodeList being used
     *
     * @var object
     */
    public $nodeList;

    /**
     * Constructor
     *
     * @param string $nodeListType Type of node list
     * @param object $network      Object of network to generate list from
     *
     * @return void
     */
    public function __construct($nodeListType, &$network)
    {
        // Check if node list type exists
        if (in_array($nodeListType, self::getAvailableNodeListTypes())) {
            require_once(NODE_LIST_CLASSES_DIR . "/NodeList" . $nodeListType . ".php");
        } else {
            throw new Exception(_("The node list type '$nodeListType' is not supported!"));
        }

        $_nodeListClass = "NodeList" . $nodeListType;
        $this->nodeList = new $_nodeListClass($network);

        // Set header of node list if node list class supports it
        if (method_exists($this->nodeList, "setHeader")) {
            $this->nodeList->setHeader();
        }
    }

    /**
     * Get the list of available node list types on the system
     *
     * @return array An array of class names

     */
    public static function getAvailableNodeListTypes()
    {
        // Init values
        $_nodeListTypes = array();
        $_useCache = false;
        $_cachedData = null;

        // Create new cache object with a lifetime of one week
        $_cache = new Cache("NodeListClasses", "ClassFileCaches", 604800);

        // Check if caching has been enabled.
        if ($_cache->isCachingEnabled) {
            $_cachedData = $_cache->getCachedData("mixed");

            if ($_cachedData) {
                // Return cached data.
                $_useCache = true;
                $_nodeListTypes = $_cachedData;
            }
        }

        if (!$_useCache) {
            $_dir = NODE_LIST_CLASSES_DIR;
            $_dirHandle = @opendir($_dir);

            if ($_dirHandle) {
                // Loop over the directory
                while (false !== ($_filename = readdir($_dirHandle))) {
                    // Loop through sub-directories of Content
                    if ($_filename != '.' && $_filename != '..') {
                        $_matches = null;

                        if (preg_match("/^NodeList(.*)\.php$/", $_filename, $_matches) > 0) {
                            // Only add files containing a corresponding NodeList class
                            if (is_file("{$_dir}/{$_matches[0]}")) {
                                $_nodeListTypes[] = $_matches[1];
                            }
                        }
                    }
                }

                closedir($_dirHandle);
            } else {
                throw new Exception(_('Unable to open directory ') . $_dir);
            }

            // Sort the result array
            sort($_nodeListTypes);

            // Check if caching has been enabled.
            if ($_cache->isCachingEnabled) {
                // Save results into cache, because it wasn't saved into cache before.
                $_cache->saveCachedData($_nodeListTypes, "mixed");
            }
        }

        return $_nodeListTypes;
    }
    /** Menu hook function */
    static public function hookMenu() {
        $items = array();

        $items[] = array('path' => 'node_lists/map',
        'title' => _("Deployed HotSpots map"),
        'url' => BASE_URL_PATH."hotspots_map.php"
		);

        $listTypes=self::getAvailableNodeListTypes();
        //pretty_print_r($listTypes);
        foreach ($listTypes as $type) {
            $items[] = array('path' => 'node_lists/'.$type,
            'title' => sprintf(_("List in %s format"), $type),
            'url' => BASE_URL_PATH."hotspot_status.php?format=$type"
            );
        }
        $items[] = array('path' => 'node_lists/technical_status',
        'title' => _("Full node technical status (includes non-deployed nodes)"),
        'url' => BASE_URL_PATH."node_list.php"
		);
        $items[] = array('path' => 'node_lists',
        'title' => _('Node lists'),
        'type' => MENU_ITEM_GROUPING);




        return $items;
    }

}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */

