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

// Include the syndicate functions only once
require_once dirname(__FILE__) . '/helper.php';

$app  = JFactory::getApplication();
$doc  = JFactory::getDocument();
$user = JFactory::getUser();

//get settings
$class_sfx = htmlspecialchars($params->get('class_sfx'));
$block_id  = 'mod-hcatfilter-' . $module->id;
$use_ajax  = $params->get('use_ajax', 0);
$Itemid    = $params->get('assign_menu') ? $params->get('menu_item') : $app->input->getInt('Itemid');
$options   = array();

//check whether ajax call
$is_ajax = !empty($module->ajax) && $use_ajax;
$root_catid = $params->get('root_catid', 1);

//check whether ROOT selected
$root_catid = $root_catid == '0' ? '1' : $root_catid;

//for AJAX use sended id instead of selected in configuration
$root_catid = !$is_ajax ? $root_catid : $app->input->get('id', '', 'string');

//load categories, use caching
$cache = JFactory::getCache('mod_hcatfilter', 'callback');
$categories = $cache->call( array( 'modHcatFilterHelper', 'getCategories' ), $params, $user->getAuthorisedViewLevels());

if (empty($categories) || empty($categories[$root_catid]))
{
	echo JText::_('JLIB_DATABASE_ERROR_EMPTY_ROW_RETURNED');
	return;
}

//get categories sorted by their parents
if (!$use_ajax)
{
	$cat_tree = $cache->call( array( 'modHcatFilterHelper', 'getCatsFullTree' ), $categories);
}
else
{
	// @TODO: load also preselected
	$cat_tree = array($root_catid => modHcatFilterHelper::getCatsForOneLevel($categories[$root_catid]->children));
	if ($is_ajax)
	{
		echo json_encode($cat_tree[$root_catid]);
		return;
	}

	$options['requestUrl'] = JUri::base(true) . '/index.php?option=com_ajax&format=json&module=hcatfilter&title=' . urlencode($module->title) . '&Itemid=' . $app->input->getInt('Itemid');
	$options['loadingImage'] = JHtml::_('image', 'system/mootree_loader.gif', '', null, true, true);
}

//curent category id
$active_catid = 0;
if ($app->input->get('option') == 'com_content' && $app->input->get('view') == 'category')
{
	$active_catid = $app->input->get('id', 0, 'string');
}
elseif ($app->input->get('option') == 'com_content' && $app->input->get('view') == 'article')
{
	$active_catid = $app->input->get('catid', 0, 'string');
}

$options['preselect'] = $active_catid ? modHcatFilterHelper::getActivePath($categories, $active_catid) : array();
if($use_ajax)
{
	// Preload selected
	foreach($options['preselect'] as $s){
		if($s == $root_catid)
		{
			// It alredy loaded
			continue;
		}
		$cat_tree[$s] = modHcatFilterHelper::getCatsForOneLevel($categories[$s]->children);
	}
}

//get labels
$labels = trim(htmlspecialchars($params->get('labels'), ENT_QUOTES));
if ($labels)
{
	$options['labels'] = explode(',', $labels);
}

$options['root'] = $root_catid;
$options['tree'] = $cat_tree;
$options['choose'] = JText::_('MOD_HCATFILTER_MAKE_CHOOSE');

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
JHtml::_('script', 'mod_hcatfilter/jquery.relatedSelect.min.js', false, true);
JHtml::_('script', 'mod_hcatfilter/hcatfilter.min.js', false, true);

//get template
require JModuleHelper::getLayoutPath('mod_hcatfilter', $params->get('layout', 'default'));
