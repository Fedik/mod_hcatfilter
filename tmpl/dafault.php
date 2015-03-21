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
//don't allow other scripts to grab and execute our file
defined('_JEXEC') or die('Get lost?');
?>
<div class="hcatfilter<?php echo $class_sfx;?>">
	<?php // for save request in browser hisroty better use GET instead of POST ?>
	<form action="<?php  echo JRoute::_('index.php');?>" method="get" id="<?php echo $block_id; ?>-form" class="hcatfilter-form">
		<input type="hidden" name="option" value="com_content" />
		<input type="hidden" name="view" value="category" />
		<input type="hidden" name="id" value="<?php echo $root_catid; ?>" />
		<input type="hidden" name="Itemid" value="<?php echo $Itemid; ?>" />
		<div class="hcatfilter-select">
			<div id="<?php echo $block_id; ?>"></div>
		</div>
		<div class="hcatfilter-button">
			<input type="submit" class="button btn" value="<?php echo JText::_('JGLOBAL_FILTER_BUTTON'); ?>"/>
			<input type="button" class="button clear btn" value="<?php echo JText::_('JLIB_FORM_BUTTON_CLEAR'); ?>"/>
		</div>
	</form>
</div>
