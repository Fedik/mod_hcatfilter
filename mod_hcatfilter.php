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

// Include the syndicate functions only once
require_once dirname(__FILE__) . '/helper.php';

//already defined in the joomla application module helper:
//$module, $attribs, $app, $params, $scope, $path, $chrome, $lang

$doc = JFactory::getDocument();
$user = JFactory::getUser();
//container for js options
$options = array();

//get settings
$class_sfx	= htmlspecialchars($params->get('class_sfx'));
$use_ajax = $params->get('use_ajax', 0);
//check whether ajax call
$is_ajax = !empty($module->ajax) && $use_ajax;
$root_catid = $params->get('root_catid', 1);
//check whether ROOT selected
$root_catid = $root_catid == '0' ? '1' : $root_catid;
//for AJAX use sended id instead of selected in configuration
$root_catid = !$is_ajax ? $root_catid: substr(strrchr($app->input->get('id', '', 'string'), '_'), 1);

//use caching
$cache = JFactory::getCache('mod_hcatfilter', 'callback');
//load categories
$categories = $cache->call( array( 'modHcatFilterHelper', 'getCategories' ), $params, $user->getAuthorisedViewLevels());

if (empty($categories) || empty($categories[$root_catid])) {
	echo JText::_('JLIB_DATABASE_ERROR_EMPTY_ROW_RETURNED');
	return;
}

//get categories sorted by their parents
if (!$use_ajax) {
	$cat_tree = $cache->call( array( 'modHcatFilterHelper', 'getCatsFullTree' ), $categories, false, true);
	$cat_first_lvl = $cat_tree[$root_catid];
} else {
	$cat_first_lvl = modHcatFilterHelper::getCatsForOneLevel($categories[$root_catid]->children, false, true);
	if ($is_ajax) {
		echo json_encode($cat_first_lvl);
		return;
	}
	$cat_tree = new stdClass();
	$options['request_url'] = JURI::root(true).'/modules/mod_hcatfilter/ajax.php?mid=' . $module->id;
	$options['loading_image'] = JURI::root(true) . '/media/system/images/mootree_loader.gif';
}

//curent category id
$active_catid = 0;
if ($app->input->get('option') == 'com_content' && $app->input->get('view') == 'category') {
	$active_catid = $app->input->get('id', 0, 'string');
}
elseif ($app->input->get('option') == 'com_content' && $app->input->get('view') == 'article') {
	$active_catid = $app->input->get('catid', 0, 'string');
}

$options['preselect'] = ($active_catid) ? modHcatFilterHelper::getActivePath($categories, $active_catid, false, true) : array();

//for assign result for menu item
$Itemid = $params->get('assign_menu') ? $params->get('menu_item') : JRequest::getInt('Itemid');

$block_id = 'mod-hcatfilter-' . $module->id;
//get labels
$labels = trim(htmlspecialchars($params->get('labels'), ENT_QUOTES));
if($labels){
	$options['labels'] = explode(',', $labels);
}

$options['root'] = $root_catid;
$options['tree'] = $cat_tree;
$options['choose'] = JText::_('MOD_HCATFILTER_MAKE_CHOOSE');

var_dump($cat_first_lvl, $cat_tree);
$js_config = '
try{
 hCatFilterItems.push({
  element: \'#'. $block_id .'\',
  options: '. json_encode($options) .'
 });
}catch(e){console.error(e)};
';
$doc->addScriptDeclaration($js_config);

//load js and css
if ($params->get('use_def_css', 1))
{
	JHtml::_('stylesheet', 'mod_hcatfilter/hcatfilter.css', array(), true);
}
JHtml::_('jquery.framework');
//JHtml::_('behavior.framework');
//JHtml::_('script', 'mod_hcatfilter/mooOptionTree.js', false, true);

JHtml::_('script', 'mod_hcatfilter/jquery.relatedSelect.js', false, true);
JHtml::_('script', 'mod_hcatfilter/hcatfilter.js', false, true);


//get template
require JModuleHelper::getLayoutPath('mod_hcatfilter', $params->get('layout', 'default'));
