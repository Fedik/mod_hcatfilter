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

// Include the syndicate functions only once
require_once dirname(__FILE__).'/helper.php';


//already defined in the joomla application module helper:
//$module, $attribs, $app, $params, $scope, $path, $chrome, $lang

$doc = JFactory::getDocument();
$user = JFactory::getUser();
//$menu = $app->getMenu();
//$menu_active = $menu->getActive();

//get settings
$class_sfx	= htmlspecialchars($params->get('class_sfx'));
$text_before = $params->get('text_before');
$text_after	= $params->get('text_after');
$root_catid = $params->get('root_catid', 1);
//get labels
$labels = trim(htmlspecialchars($params->get('labels'), ENT_QUOTES));
if($labels){
	$labels = explode(',', $labels);
	$labels = json_encode($labels);// '\'' . implode('\',\'', $labels) . '\'';
}
//for assign result for menu item
$Itemid = $params->get('assign_menu') ? $params->get('menu_item') : JRequest::getInt('Itemid');

//curent category
$active_catid = 0;
if (JRequest::getCmd('option') == 'com_content' && JRequest::getCmd('view') == 'category') {
//if ($menu_active->query['option'] == 'com_content' && $menu_active->query['view'] == 'category') {
	$active_catid = $app->input->get('id', null, 'string'); //JRequest::getInt('id');
	//$active_catid = $menu_active->query['id'];
}

//use caching
$cache = JFactory::getCache('mod_hcatfilter', 'callback');
//load categories
//$categories = modHcatFilterHelper::getCategories($params, $user->getAuthorisedViewLevels());
$categories = $cache->call( array( 'modHcatFilterHelper', 'getCategories' ), $params, $user->getAuthorisedViewLevels());

if (empty($categories) || empty($categories[$root_catid])) {
	echo JText::_('JLIB_DATABASE_ERROR_EMPTY_ROW_RETURNED');
	return;
}
$root_id_with_order = $categories[$root_catid]->ordering . '_' . $root_catid;

//get categories soreted by their parents
//$cat_first_lvl_js = modHcatFilterHelper::getCatsForOneLevel($categories[$root_catid]->children, true, true);
//$cat_tree = modHcatFilterHelper::getCatsFullTree($categories, false, true);
$cat_tree = $cache->call( array( 'modHcatFilterHelper', 'getCatsFullTree' ), $categories, false, true);
$cat_first_lvl = $cat_tree[$root_id_with_order];

$active_categories = ($active_catid) ? modHcatFilterHelper::getActivePath($categories, $active_catid, true, true) : '[]';

$block_id = 'mod-hcatfilter-' . $module->id;
$select_text = JText::_('MOD_HCATFILTER_MAKE_CHOOSE');

$js_config = "
try{
 hCatFilterItems.push({
  treeRoot: " . json_encode($cat_first_lvl) .",
  tree: " . json_encode($cat_tree) .",
  element: '{$block_id}',
  options: {choose:'{$select_text}',". (($labels) ? 'labels:'. $labels . ',' : '' ) ." preselect:{$active_categories}}
 });
}catch(e){console.error(e)};
";

//load js and css
if ($params->get('use_def_css', 1)){
	$doc->addStyleSheet(JURI::root(true).'/modules/mod_hcatfilter/css/hcatfilter.css');
}
$doc->addScript(JURI::root(true).'/modules/mod_hcatfilter/js/mooOptionTree.js');
$doc->addScript(JURI::root(true).'/modules/mod_hcatfilter/js/hcatfilter.js');
$doc->addScriptDeclaration($js_config);
//get template
require JModuleHelper::getLayoutPath('mod_hcatfilter', $params->get('layout', 'default'));

