<?php
/**
 * @version		2012.08.11
 * @package Hierarchical Category Filter for Joomla 2.5
 * @author  Fedik
 * @email	getthesite@gmail.com
 * @link    http://www.getsite.org.ua
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
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
//get module name
preg_match('|\Smodules\Smod_(.*?)\Sajax.php|si', __FILE__, $matches);
//get module
$module = JModuleHelper::getModule( 'mod_' . $matches[1] );
$module->ajax =  true;

//render module
//header('Content-type: application/json');

echo JModuleHelper::renderModule( $module, array('style' => 'none') );

$app->close();
