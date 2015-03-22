<?php
/**
 * @version		2012.08.11
 * @package Hierarchical Category Filter for Joomla 2.5
 * @author  Fedik
 * @email	getthesite@gmail.com
 * @link    http://www.getsite.org.ua
 * @license	GNU/GPL http://www.gnu.org/licenses/gpl.html
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
	 * load the full categories list
	 */
	public static function getCategories($params, $user_authorised_view_levels)
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		$query->from('#__categories as c');

		$query->select('c.id, c.parent_id, c.lft, c.level, c.title, c.alias');

		$query->where('c.published = 1');

		$query->where('(c.extension=' . $db->Quote('com_content') . ' OR c.extension=' . $db->Quote('system') . ')');
		$query->where('c.access IN (' . implode(',', $user_authorised_view_levels) . ')');
		$query->where('c.language in (' . $db->Quote(JFactory::getLanguage()->getTag()) . ',' . $db->Quote('*') . ')');

		$query->order('c.'. $params->get('ordering', 'lft') .' '. $params->get('ordering_dir', 'ASC'));//lft, title, created_time, modified_time, hits

		//echo $query->dump();
		$db->setQuery($query);

		if (!$categories = $db->loadObjectList('id')) {
			return array();
		}
		$i = 1;
		foreach ($categories as $cat) {
			//keep ordering
			$categories[$cat->id]->ordering = $i;

			if (empty($categories[$cat->id]->children)) {
				$categories[$cat->id]->children = array();
			}

			//keep children
			if (isset($categories[$cat->parent_id])) {
				if (empty($categories[$cat->parent_id]->children)) {
					$categories[$cat->parent_id]->children = array();
				}
				$categories[$cat->parent_id]->children[] = $cat;
			}
			$i++;
		}

		return $categories;
	}

	/**
	 * @return string full tree list
	 * { 1: { 3: "Option 3",  4: "Option 4" }, 3: {5: "Some 5", 6: "Some 6"} }
	 */
	public static function getCatsFullTree($items, $json = true, $order_pref = false)
	{
		$js_arr = array();

		foreach ($items as $cat){
			if(!empty($cat->children)){
				$js_arr[$cat->id] =  self::getCatsForOneLevel($cat->children, false, true);
			}
		}

		return $json ? json_encode($js_arr) : $js_arr;
	}

	/**
	 * @return string categories for one level
	 * { 1: "Option 1",  2: "Option 2" }
	 */
	public static function getCatsForOneLevel($items, $json = true, $order_pref = false)
	{
		$js_arr = array();

		foreach ($items as $cat){
			$js_arr[] = array(
				'title' => htmlspecialchars($cat->title, ENT_QUOTES),
				'value' => $cat->id,
				'parent'=> $cat->parent_id,
			);

		}
		return $json ? json_encode($js_arr) : $js_arr;
	}

	/**
	 * @return js array with active categories
	 */
	public static function getActivePath($items , $active_id, $json = true, $order_pref = false)
	{
		$parent_ids = array();

		if (empty($items[$active_id])) {
			return $json ? json_encode($parent_ids) : $parent_ids;
		}

		$curent = $items[$active_id];

		$parent_ids[] = $order_pref ? $curent->ordering .'_'. $curent->id : $curent->id;

		while (isset($items[$curent->parent_id])) {
			$curent = $items[$curent->parent_id];
			$parent_ids[] = $order_pref ? $curent->ordering .'_'. $curent->id : $curent->id;
		}

		return $json ? json_encode($parent_ids) : $parent_ids;
	}
}
