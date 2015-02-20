<?php
/**
 * @package     Joomla.CMS
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;
$extension_data = $displayData['extension']; //print_r($extension_data);
$tags = $extension_data->includes->value;
$commercial = $extension_data->type->value != "free" ? true : false;
?>
<li class="item <?php echo $displayData['spanclass']; ?>">
<div class="thumbnail">
	<p class="rating center">
		<a target="_blank" href="<?php echo AppsHelper::getJEDUrl($extension_data) . '#reviews'; ?>">
		<?php echo JText::sprintf('COM_APPS_EXTENSION_VOTES_REVIEWS_LIST', $extension_data->score->value, $extension_data->num_reviews->value); ?>
		</a>
	</p>
	<div onclick="Joomla.loadweb(apps_base_url+'<?php echo AppsHelper::getAJAXUrl("view=extension&id={$extension_data->id->value}"); ?>');">
		<div class="center item-image">
			<!--<a class="transcode ajaxloaded" href="<?php echo AppsHelper::getAJAXUrl("view=extension&id={$extension_data->id->value}"); ?>">-->
				<img src="<?php echo $extension_data->image; ?>" class="img center" />
			<!--</a>-->
		</div>
		<ul class="item-type center">
			<?php if ($commercial) : ?>
			<span title="<?php echo $extension_data->type->value; ?>" class="label label-jcommercial">$</span> 
			<?php endif; ?>
			<?php if (in_array('com', $tags)) : ?>
			<span title="<?php echo JText::_('COM_APPS_COMPONENT'); ?>" class="label label-jcomponent">C</span> 
			<?php endif; ?>
			<?php if (in_array('lang', $tags)) : ?>
			<span title="<?php echo JText::_('COM_APPS_LANGUAGE'); ?>" class="label label-jlanguage">L</span>
			<?php endif; ?>
			<?php if (in_array('mod', $tags)) : ?>
			<span title="<?php echo JText::_('COM_APPS_MODULE'); ?>" class="label label-jmodule">M</span> 
			<?php endif; ?>
			<?php if (in_array('plugin', $tags)) : ?>
			<span title="<?php echo JText::_('COM_APPS_PLUGIN'); ?>" class="label label-jplugin">P</span> 
			<?php endif; ?>
			<?php if (in_array('esp', $tags)) : ?>
			<span title="<?php echo JText::_('COM_APPS_EXTENSION_SPECIFIC_ADDON'); ?>" class="label label-jspecial">S</span> 
			<?php endif; ?>
			<?php if (in_array('tool', $tags)) : ?>
			<span title="<?php echo JText::_('COM_APPS_TOOL'); ?>" class="label label-jtool">T</span> 
			<?php endif; ?>
		</ul>
		<h4 class="center muted">
			<a class="transcode ajaxloaded" href="<?php echo AppsHelper::getAJAXUrl("view=extension&id={$extension_data->id->value}"); ?>"><?php echo trim($extension_data->core_title->value); ?></a>
		</h4>
		<div class="item-description">
			<?php echo mb_strlen(trim($extension_data->core_body->value)) > 400 ? mb_substr(trim($extension_data->core_body->value), 0, mb_stripos(trim($extension_data->core_body->value), ' ', 400)) . '...' : trim($extension_data->core_body->value); ?>
			<div class="fader">&nbsp;</div>
		</div>
	</div>
</div>
</li>
