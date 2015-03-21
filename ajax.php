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

// Set flag that this is a parent file.
define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', preg_replace('|\Smodules\Smod_.*?\Sajax.php|i', '', __FILE__));

//init joomla app
require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';

// Instantiate the application.
$app = JFactory::getApplication('site');

// Initialise the application.
$app->initialise();

//import module helper
jimport( 'joomla.application.module.helper' );

//check module id
if (!$mid = $app->input->get('mid', 0 , 'int')) {
	$app->close();
}
//load module
$db = JFactory::getDbo();
$query = $db->getQuery(true);
$query->from('#__modules AS m');
$query->select('m.id, m.title, m.module, m.position, m.content, m.showtitle, m.params');
$query->where('m.published = 1');
$query->where('m.id = ' . (int) $mid);
$query->join('LEFT', '#__extensions AS e ON e.element = m.module AND e.client_id = m.client_id');
$query->where('e.enabled = 1');
$query->where('e.name = ' . $db->Quote('mod_hcatfilter'));
//echo $query->dump();

// Set the query
$db->setQuery($query);
if(!$module = $db->loadObject()) {
	$app->close();
}

$module->ajax =  true;

//render module
header('Content-type: application/json');
echo JModuleHelper::renderModule( $module, array('style' => 'none') );

$app->close();
