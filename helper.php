<?php
/**
 * @version		2012.04.11
 * @package Hierarchical Category Filter for Joomla 2.5
 * @author  Fedik
 * @email	getthesite@gmail.com
 * @link    http://www.getsite.org.ua
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 *
 * mod_hcatfilter
 */

//don't allow other scripts to grab and execute our file
defined('_JEXEC') or die('Get lost?');

require_once JPATH_SITE.'/components/com_content/helpers/route.php';
jimport('joomla.application.categories');

// module class helper
abstract class modHcatFilterHelper
{

	/**
	* return full tree list
	* { 1: { 3: "Option 3",  4: "Option 4" }, 3: {5: "Some 5", 6: "Some 6"} }
	*/
	public static function getJSONTree($items){

		$js_arr = array();
		foreach ($items as $cat){
			if($cat->hasChildren()){
				$children = $cat->getChildren();
				$js_arr[] = $cat->id . ':' . self::getJSONLevel($children);
			}
		}
		return '{' . implode(',', $js_arr) . '}';
	}

	/**
	* return categories for one level
	* { 1: "Option 1",  2: "Option 2" }
	*/
	public static function getJSONLevel($items){

		$js_arr = array();
		foreach ($items as $cat){
			$js_arr[] = $cat->id. ':"' . htmlspecialchars($cat->title, ENT_QUOTES) . '"';
		}
		return '{' . implode(',', $js_arr) . '}';

	}

	/**
	* return js array with active categories
	*/
	public static function getStringPathActive($item) {

		$par_ids = array();
		//add also curent item
		$par_ids[] = ($item->id != 'root') ? '\'' . $item->id . '\'' : '\'0\'';

		while ($item->hasParent()) {
			$item = $item->getParent();
			$par_ids[] = ($item->id != 'root') ? '\'' . $item->id . '\'' : '\'0\'';
		}

		return implode(',', $par_ids);

	}

}