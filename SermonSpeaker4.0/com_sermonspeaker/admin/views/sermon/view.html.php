<?php
// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View to edit a sermon.
 *
 * @package		Sermonspeaker.Administrator
 */
class SermonspeakerViewSermon extends JView
{
//	protected $state;
	protected $item;
	protected $form;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->item		= $this->get('Item');
		$this->form		= $this->get('Form');

		// add Javascript for Form Elements enable and disable
		$enElem = 'function enableElement(ena_elem, dis_elem) {
			ena_elem.disabled = false;
			dis_elem.disabled = true;
		}';
		// add Javascript for Scripture Links buttons
		$sendText = 'function sendText(elem, open, close) {
			elem.value = open+elem.value+close;
		}';

		$this->params	= &JComponentHelper::getParams('com_sermonspeaker');

		$document =& JFactory::getDocument();
		$document->addScriptDeclaration($enElem);
		$document->addScriptDeclaration($sendText);

		$session	= JFactory::getSession();
		if($this->params->get('enable_flash')){
			// Prepare Flashuploader
			$audioTypes = '*.aac; *.m4a; *.mp3';
			$videoTypes = '*.mp4; *.mov; *.f4v; *.flv; *.3gp; *.3g2';
			$targetURL 	= JURI::root().'administrator/index.php?option=com_sermonspeaker&task=file.upload&'.$session->getName().'='.$session->getId().'&'.JUtility::getToken().'=1&format=json';
			// SWFUpload
			JHTML::Script('media/com_sermonspeaker/swfupload/swfupload.js');
			JHTML::Script('media/com_sermonspeaker/swfupload/swfupload.queue.js');
			JHTML::Script('media/com_sermonspeaker/swfupload/fileprogress.js');
			JHTML::Script('media/com_sermonspeaker/swfupload/handlers.js', true);
			$uploader_script = '
				window.onload = function() {
					upload1 = new SWFUpload({
						upload_url: "'.$targetURL.'",
						flash_url : "'.JURI::root().'media/com_sermonspeaker/swfupload/swfupload.swf",
						file_size_limit : "100MB",
						file_types : "'.$audioTypes.'",
						file_types_description : "'.JText::_('COM_SERMONSPEAKER_FIELD_AUDIOFILE_LABEL', 'true').'",
						file_upload_limit : "0",
						file_queue_limit : "0",
						button_image_url : "'.JURI::root().'media/com_sermonspeaker/swfupload/XPButtonUploadText_61x22.png",
						button_placeholder_id : "btnUpload1",
						button_width: 61,
						button_height: 22,
						button_window_mode: "transparent",
						debug: false,
						swfupload_loaded_handler: function() {
							document.id(\'btnCancel1\').removeClass(\'ss-hide\');
						},
						file_dialog_start_handler : fileDialogStart,
						file_queued_handler : fileQueued,
						file_queue_error_handler : fileQueueError,
						file_dialog_complete_handler : fileDialogComplete,
						upload_start_handler : uploadStart,
						upload_progress_handler : uploadProgress,
						upload_error_handler : uploadError,
						upload_success_handler : function uploadSuccess(file, serverData) {
							try {
								var progress = new FileProgress(file, this.customSettings.progressTarget);
								var data = JSON.decode(serverData);
								if (data.status == "1") {
									progress.setComplete();
									progress.setStatus(data.error);
									document.id("jform_audiofile_text").value = data.path;
								} else {
									progress.setError();
									progress.setStatus(data.error);
								}
								progress.toggleCancel(false);
							} catch (ex) {
								this.debug(ex);
							}
						},
						upload_complete_handler : uploadComplete,
						custom_settings : {
							progressTarget : "infoUpload1",
							cancelButtonId : "btnCancel1"
						}
							
					});
					upload2 = new SWFUpload({
						upload_url: "'.$targetURL.'",
						flash_url : "'.JURI::root().'media/com_sermonspeaker/swfupload/swfupload.swf",
						file_size_limit : "100MB",
						file_types : "'.$videoTypes.'",
						file_types_description : "'.JText::_('COM_SERMONSPEAKER_FIELD_VIDEOFILE_LABEL', 'true').'",
						file_upload_limit : "0",
						file_queue_limit : "0",
						button_image_url : "'.JURI::root().'media/com_sermonspeaker/swfupload/XPButtonUploadText_61x22.png",
						button_placeholder_id : "btnUpload2",
						button_width: 61,
						button_height: 22,
						button_window_mode: "transparent",
						debug: false,
						swfupload_loaded_handler: function() {
							document.id(\'btnCancel2\').removeClass(\'ss-hide\');
						},
						file_dialog_start_handler : fileDialogStart,
						file_queued_handler : fileQueued,
						file_queue_error_handler : fileQueueError,
						file_dialog_complete_handler : fileDialogComplete,
						upload_start_handler : uploadStart,
						upload_progress_handler : uploadProgress,
						upload_error_handler : uploadError,
						upload_success_handler : function uploadSuccess(file, serverData) {
							try {
								var progress = new FileProgress(file, this.customSettings.progressTarget);
								var data = JSON.decode(serverData);
								if (data.status == "1") {
									progress.setComplete();
									progress.setStatus(data.error);
									document.id("jform_videofile_text").value = data.path;
								} else {
									progress.setError();
									progress.setStatus(data.error);
								}
								progress.toggleCancel(false);
							} catch (ex) {
								this.debug(ex);
							}
						},
						upload_complete_handler : uploadComplete,
						custom_settings : {
							progressTarget : "infoUpload2",
							cancelButtonId : "btnCancel2"
						}
							
					});
					upload3 = new SWFUpload({
						upload_url: "'.$targetURL.'&addfile=true",
						flash_url : "'.JURI::root().'media/com_sermonspeaker/swfupload/swfupload.swf",
						file_size_limit : "100MB",
						file_upload_limit : "0",
						file_queue_limit : "0",
						button_image_url : "'.JURI::root().'media/com_sermonspeaker/swfupload/XPButtonUploadText_61x22.png",
						button_placeholder_id : "btnUpload3",
						button_width: 61,
						button_height: 22,
						button_window_mode: "transparent",
						debug: false,
						swfupload_loaded_handler: function() {
							document.id(\'btnCancel3\').removeClass(\'ss-hide\');
						},
						file_dialog_start_handler : fileDialogStart,
						file_queued_handler : fileQueued,
						file_queue_error_handler : fileQueueError,
						file_dialog_complete_handler : fileDialogComplete,
						upload_start_handler : uploadStart,
						upload_progress_handler : uploadProgress,
						upload_error_handler : uploadError,
						upload_success_handler : function uploadSuccess(file, serverData) {
							try {
								var progress = new FileProgress(file, this.customSettings.progressTarget);
								var data = JSON.decode(serverData);
								if (data.status == "1") {
									progress.setComplete();
									progress.setStatus(data.error);
									document.id("jform_addfile_text").value = data.path;
								} else {
									progress.setError();
									progress.setStatus(data.error);
								}
								progress.toggleCancel(false);
							} catch (ex) {
								this.debug(ex);
							}
						},
						upload_complete_handler : uploadComplete,
						custom_settings : {
							progressTarget : "infoUpload3",
							cancelButtonId : "btnCancel3"
						}
							
					});
				}
			';
			$document->addScriptDeclaration($uploader_script);
		}

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		JRequest::setVar('hidemainmenu', true);

		$user		= JFactory::getUser();
		$userId		= $user->get('id');
		$isNew		= ($this->item->id == 0);
		$canDo		= SermonspeakerHelper::getActions();

		JToolBarHelper::title(JText::_('COM_SERMONSPEAKER_SERMONS_TITLE'), 'sermons');

		// Build the actions for new and existing records.
		if ($isNew)  {
			// For new records, check the create permission.
			if ($canDo->get('core.create')) {
				JToolBarHelper::apply('sermon.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('sermon.save', 'JTOOLBAR_SAVE');
				JToolBarHelper::custom('sermon.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			}

			JToolBarHelper::cancel('sermon.cancel', 'JTOOLBAR_CANCEL');
		} else {
			// Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
			if ($canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by == $userId)) {
				JToolBarHelper::custom('sermon.write_id3', 'export.png', 'export_f2.png', 'Write ID3', false);
				JToolBarHelper::divider();
				JToolBarHelper::apply('sermon.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('sermon.save', 'JTOOLBAR_SAVE');

				// We can save this record as copy, but check the create permission first.
				if ($canDo->get('core.create')) {
					JToolBarHelper::custom('sermon.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
					JToolBarHelper::custom('sermon.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
				}
			}

			JToolBarHelper::cancel('sermon.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}