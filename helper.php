<?php
/**
 * @version	2015.04.05
 * @package Hierarchical Category Filter
 * @author  Fedir Zinchuk
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
	 * @return array full tree list
	 */
	public static function getCatsFullTree($items)
	{
		$js_arr = array();

		foreach ($items as $cat){
			if(!empty($cat->children)){
				$js_arr[$cat->id] =  self::getCatsForOneLevel($cat->children);
			}
		}

		return $js_arr;
	}

	/**
	 * @return array categories for one level
	 */
	public static function getCatsForOneLevel($items)
	{
		$js_arr = array();

		foreach ($items as $cat){
			$js_arr[] = array(
				'title' => htmlspecialchars($cat->title, ENT_QUOTES),
				'value' => $cat->id,
				'parent'=> $cat->parent_id,
			);

		}
		return $js_arr;
	}

	/**
	 * @return array with active categories
	 */
	public static function getActivePath($items , $active_id)
	{
		$parent_ids = array();

		if (empty($items[$active_id])) {
			return $parent_ids;
		}

		$curent = $items[$active_id];

		$parent_ids[] = $curent->id;

		while (isset($items[$curent->parent_id])) {
			$curent = $items[$curent->parent_id];
			$parent_ids[] = $curent->id;
		}

		return $parent_ids;
	}

	/**
	 * Render module on ajax request
	 *
	 * @return void
	 */
	public static function getAjax()
	{
		$title  = JFactory::getApplication()->input->getString('title');
		$module = JModuleHelper::getModule('mod_hcatfilter', $title);

		if(!$module)
		{
			// TODO: show some error
			return;
		}

		$module->ajax = true;
		echo JModuleHelper::renderModule($module, array('style' => 'none'));
		JFactory::getApplication()->close();
	}
}
