<?php
/**
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * Speakerlist Field class for the SermonSpeaker.
 * Based on the Bannerlist field from com_banners
 *
 * @package		SermonSpeaker
 * @since		4.0
 */
class JFormFieldSpeakerlist extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'Speakerlist';

	/**
	 * Method to get the field input markup for a generic list.
	 * Use the multiple attribute to enable multiselect.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		$html	= '<div class="input-append">';
		$html	.= parent::getInput();
		$app = JFactory::getApplication();
		if ($app->isAdmin())
		{
			$returnpage	= base64_encode('index.php?option=com_sermonspeaker&view=speaker&tmpl=component&layout=close');
			$url = 'index.php?option=com_sermonspeaker&task=speaker.add&layout=modal&tmpl=component&return='.$returnpage;
		}
		else
		{
			$returnpage	= base64_encode(JRoute::_('index.php?view=speakerform&tmpl=component&layout=close'));
			$url = JRoute::_('index.php?task=speakerform.edit&layout=modal&tmpl=component&return='.$returnpage);
		}
		$html	.= '<a class="modal" href="'.$url.'"rel="{handler: \'iframe\', size: {x: 950, y: 650}}">';
		$version	= new JVersion;
		$joomla30	= $version->isCompatible(3.0);
		if ($joomla30)
		{
			$html	.= '<div class="add-on icon-plus-2" rel="tooltip" title="'.JText::_('COM_SERMONSPEAKER_NEW_SPEAKER').'"> </div>';
		}
		else
		{
			$html	.= '<img src="'.JURI::root().'media/com_sermonspeaker/images/plus.png">';
		}
		$html	.= '</a></div>';

		return $html;
	}

	/**
	 * Method to get the field options.
	 *
	 * @return	array	The field option objects.
	 * @since	1.6
	 */
	public function getOptions()
	{
		// Initialize variables.
		$options = array();

		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);

		$query->select('speakers.id As value, home');
		$query->select('CASE WHEN CHAR_LENGTH(c_speakers.title) THEN CONCAT(speakers.name, " (", c_speakers.title, ")") ELSE speakers.name END AS text');
		$query->from('#__sermon_speakers AS speakers');
		$query->join('LEFT', '#__categories AS c_speakers ON c_speakers.id = speakers.catid');
		$query->where('speakers.state = 1');
		$query->order('speakers.name');

		// Get the options.
		$db->setQuery($query);

		$published = $db->loadObjectList();

		$query	= $db->getQuery(true);

		$query->select('speakers.id As value, home');
		$query->select('CASE WHEN CHAR_LENGTH(c_speakers.title) THEN CONCAT(speakers.name, " (", c_speakers.title, ")") ELSE speakers.name END AS text');
		$query->from('#__sermon_speakers AS speakers');
		$query->join('LEFT', '#__categories AS c_speakers ON c_speakers.id = speakers.catid');
		$query->where('speakers.state = 0');
		$query->order('speakers.name');

		// Get the options.
		$db->setQuery($query);

		$unpublished = $db->loadObjectList();
		if (count($unpublished))
		{
			if (count($published))
			{
				array_unshift($published, JHtml::_('select.optgroup', JText::_('JPUBLISHED')));
				array_push($published, JHtml::_('select.optgroup', JText::_('JPUBLISHED')));
			}
			array_unshift($unpublished, JHtml::_('select.optgroup', JText::_('JUNPUBLISHED')));
			array_push($unpublished, JHtml::_('select.optgroup', JText::_('JUNPUBLISHED')));
		}
		// Check for a database error.
		if ($db->getErrorNum())
		{
			JError::raiseWarning(500, $db->getErrorMsg());
		}
		$options = array_merge($published, $unpublished);

		// Merge any additional options in the XML definition.
		//$options = array_merge(parent::getOptions(), $options);

		if ($this->value === '')
		{
			foreach ($options as $option)
			{
				if (isset($option->home) && $option->home)
				{
					$this->value = $option->value;
					break;
				}
			}
		}

		array_unshift($options, JHtml::_('select.option', '0', JText::_('COM_SERMONSPEAKER_SELECT_SPEAKER')));

		return $options;
	}
}
