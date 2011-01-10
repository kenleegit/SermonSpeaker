<?php

// No direct access
defined('_JEXEC') or die;

class SermonspeakerHelper
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param	string	The name of the active view.
	 * @since	1.6
	 */
	public static function addSubmenu($vName = 'main')
	{
		JSubMenuHelper::addEntry(
			JText::_('COM_SERMONSPEAKER_MENU_SERIES'),
			'index.php?option=com_sermonspeaker&view=series',
			$vName == 'series'
		);
		JSubMenuHelper::addEntry(
			JText::_('COM_SERMONSPEAKER_MENU_SPEAKERS'),
			'index.php?option=com_sermonspeaker&view=speakers',
			$vName == 'speakers'
		);
		JSubMenuHelper::addEntry(
			JText::_('COM_SERMONSPEAKER_MENU_SERMONS'),
			'index.php?option=com_sermonspeaker&view=sermons',
			$vName == 'sermons'
		);
		JSubMenuHelper::addEntry(
			JText::_('COM_SERMONSPEAKER_MENU_CATEGORY'),
			'index.php?option=com_categories&extension=com_sermonspeaker',
			$vName == 'categories'
		);
		if ($vName=='categories') {
			JToolBarHelper::title(
				JText::sprintf('COM_CATEGORIES_CATEGORIES_TITLE',JText::_('com_sermonspeaker')),
				'sermonspeaker-categories');
		}
		JSubMenuHelper::addEntry(
			JText::_('COM_SERMONSPEAKER_MENU_STATISTICS'),
			'index.php?option=com_sermonspeaker&view=statistics',
			$vName == 'statistics'
		);
		JSubMenuHelper::addEntry(
			JText::_('COM_SERMONSPEAKER_MENU_HELP'),
			'index.php?option=com_sermonspeaker&view=help',
			$vName == 'help'
		);

		$document = JFactory::getDocument();
		if ($vName == 'categories'){
			$document->addStyleDeclaration('.icon-48-sermonspeaker-categories {
				background-image: url(components/com_sermonspeaker/images/category.png); }');
			JToolBarHelper::preferences('com_sermonspeaker');
		}
	}
	
	/**
	 * Get the actions for ACL
	 */
	public static function getActions($categoryId = 0)
	{
		$user  	= JFactory::getUser();
		$result	= new JObject;

		if (empty($categoryId)) {
			$assetName = 'com_sermonspeaker';
		} else {
			$assetName = 'com_sermonspeaker.category.'.(int) $categoryId;
		}

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete'
		);

		foreach ($actions as $action) {
			$result->set($action, $user->authorise($action, $assetName));
		}

		return $result;
	}
}