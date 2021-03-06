<?php
/**
 * @package     SermonSpeaker
 * @subpackage  Component.Administrator
 * @author      Thomas Hunziker <admin@sermonspeaker.net>
 * @copyright   © 2016 - Thomas Hunziker
 * @license     http://www.gnu.org/licenses/gpl.html
 **/

defined('_JEXEC') or die();

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('filelist');

/**
 * Creates the filelist dropdown for sermon file select
 */
class JFormFieldCustomFileList extends JFormFieldFileList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 */
	public $type = 'CustomFileList';

	/**
	 * Method to get the field input markup for the custom filelist.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   5.1.2
	 */
	protected function getInput()
	{
		$this->params = JComponentHelper::getParams('com_sermonspeaker');

		// Get and sanitize file parameter
		$this->file = (string) $this->element['file'];
		$this->file = (in_array($this->file, array('audio', 'video', 'addfile'))) ? $this->file : 'audio';

		// Mode: 0 = Default, 1 = Vimeo, 2 = Amazon S3, 3 = Extern
		$this->mode = $this->params->get('path_mode_' . $this->file, 0);

		$html = '';

		// Check Filename for possible problems
		$filename = JFile::stripExt(basename($this->value));

		if ($filename != JApplicationHelper::stringURLSafe($filename))
		{
			$html .= '<div class="alert alert-warning">'
				. '<button type="button" class="close" data-dismiss="alert">&times;</button>'
				. '<span class="icon-notification"></span> '
				. JText::_('COM_SERMONSPEAKER_FILENAME_NOT_IDEAL')
				. '</div>';
		}

		$html .= '<div class="input-prepend input-append">'
			. '<div id="' . $this->fieldname . '_text_icon" class="btn add-on icon-radio-checked" onclick="toggleElement(\''
			. $this->fieldname . '\', 0);"> </div>'
			. '<input name="' . $this->name . '" id="' . $this->id . '_text" class="' . $this->class . '" value="'
			. htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '" type="text">';

		// Add Lookup button if not addfile field
		if ($this->file != 'addfile')
		{
			$html .= '<div class="btn add-on hasTooltip icon-wand" onclick="lookup(document.getElementById(\'' . $this->id . '_text\'))" title="'
				. JText::_('COM_SERMONSPEAKER_LOOKUP') . '"> </div>';
		}

		// Add Google Picker if enabled and not audio field
		if ($this->params->get('googlepicker') && $this->file != 'audio')
		{
			$html .= '<div class="btn add-on hasTooltip" onclick="create' . ucfirst($this->file) . 'Picker();" title="' . JText::_('COM_SERMONSPEAKER_GOOGLEPICKER_TIP') . '">'
				. '<img src="' . JURI::root() . 'media/com_sermonspeaker/icons/16/drive.png">'
				. '</div>';
		}

		$html .= '</div>'
			. '<br />'
			. '<div class="input-prepend input-append">'
			. '<div id="' . $this->fieldname . '_icon" class="btn add-on icon-radio-unchecked" onclick="toggleElement(\''
			. $this->fieldname . '\', 1);"> </div>';

		$html .= parent::getInput();

		if (!$this->mode && $this->file != 'addfile')
		{
			$html .= '<div class="btn add-on hasTooltip icon-wand" onclick="lookup(document.getElementById(\''
				. $this->id . '\'))" title="' . JText::_('COM_SERMONSPEAKER_LOOKUP') . '"> </div>';
		}

		$html .= '</div>';

		$html .= $this->getUploader();

		return $html;
	}

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		if (!$this->mode)
		{
			// Fallback to 'path' for B/C with versions < 5.0.3
			$dir = trim($this->params->get('path_' . $this->file, $this->params->get('path', 'images')), '/');

			// Add year/month to the directory if enabled.
			if ($this->params->get('append_path', 0))
			{
				// In case of an edit, we check for the sermon_date and choose the year/month of the sermon.
				$append = ($ts = strtotime($this->form->getValue('sermon_date'))) ? '/' . date('Y', $ts) . '/' . date('m', $ts) : '/' . date('Y') . '/' . date('m');

				// Check if directory exists, fallback to base directory if not.
				$dir = is_dir(JPATH_ROOT . '/' . $dir . $append) ? $dir . $append : $dir;
			}

			// Add language to the directory if enabled.
			if ($this->params->get('append_path_lang', 0))
			{
				// In case of an edit, we check for the language set, otherwise we use the active language.
				$language = $this->form->getValue('language');
				$jlang    = JFactory::getLanguage();
				$append   = ($language && ($language != '*')) ? '/' . $language : '/' . $jlang->getTag();

				// Check if directory exists, fallback to base directory if not.
				$dir = is_dir(JPATH_ROOT . '/' . $dir . $append) ? $dir . $append : $dir;
			}

			$this->directory = $dir;

			// Set file filter from params
			$this->filetypes = $this->params->get($this->file . '_filetypes');

			if ($this->filetypes)
			{
				$this->filetypes = array_map('trim', explode(',', $this->filetypes));
				$filter          = '\.' . implode('$|\.', $this->filetypes) . '$';
				$this->filter    = $filter;
			}

			// Get the field options.
			$options = parent::getOptions();

			// Add directory to the value.
			foreach ($options as $option)
			{
				$option->value = '/' . $dir . '/' . $option->value;
			}

			return $options;
		}
		elseif ($this->mode == 1)
		{
			$options = array();
			$url     = 'http://vimeo.com/api/v2/' . $this->params->get('vimeo_id') . '/videos.xml';

			if ($xml = simplexml_load_file($url))
			{
				foreach ($xml->video as $video)
				{
					$option['value'] = $video->url;
					$option['text']  = $video->title;
					$options[]       = $option;
				}

				return $options;
			}
		}
		elseif ($this->mode == 2)
		{
			// Initialize variables.
			$options = array();

			// Add missing constant in PHP < 5.5
			defined('CURL_SSLVERSION_TLSv1') or define('CURL_SSLVERSION_TLSv1', 1);

			// Include the S3 class
			require_once JPATH_COMPONENT_ADMINISTRATOR . '/s3/S3.php';

			// AWS access info
			$awsAccessKey = $this->params->get('s3_access_key');
			$awsSecretKey = $this->params->get('s3_secret_key');
			$customBucket = $this->params->get('s3_custom_bucket');
			$bucket       = $this->params->get('s3_bucket');

			// Instantiate the class
			$s3 = new S3($awsAccessKey, $awsSecretKey);

			if (!$customBucket)
			{
				$region = $s3->getBucketLocation($bucket);
				$prefix = ($region == 'US') ? 's3' : 's3-' . $region;
			}

			$folder = '';

			// Add year/month to the directory if enabled.
			if ($this->params->get('append_path', 0))
			{
				// In case of an edit, we check for the sermon_date and choose the year/month of the sermon.
				$folder .= ($ts = strtotime($this->form->getValue('sermon_date'))) ? date('Y', $ts) . '/' . date('m', $ts) : date('Y') . '/' . date('m');
				$folder .= '/';
			}

			// Add language to the directory if enabled.
			if ($this->params->get('append_path_lang', 0))
			{
				// In case of an edit, we check for the language set, otherwise we use the active language.
				$language = $this->form->getValue('language');
				$jlang    = JFactory::getLanguage();
				$folder .= ($language && ($language != '*')) ? $language : $jlang->getTag();
				$folder .= '/';
			}

			$bucket_contents = $s3->getBucket($bucket, $folder);

			// Fallback to root if folder doesn't exist
			if (!$bucket_contents)
			{
				$bucket_contents = $s3->getBucket($bucket);
			}

			// Show last modified files first
			uasort(
				$bucket_contents,
				function ($a, $b)
				{
					return $b['time'] - $a['time'];
				}
			);

			foreach ($bucket_contents as $file)
			{
				// Don't show the "folder"
				if (substr($file['name'], -1) == '/')
				{
					continue;
				}

				$domain          = ($customBucket) ? $bucket : $prefix . '.amazonaws.com/' . $bucket;
				$fname           = $file['name'];
				$furl            = 'https://' . $domain . '/' . $fname;
				$option['value'] = $furl;
				$option['text']  = $fname;
				$options[]       = $option;
			}

			return $options;
		}
		elseif ($this->mode == 3)
		{
			$options = array();
			$url     = $this->params->get('extern_path');

			if ($xml = simplexml_load_file($url))
			{
				foreach ($xml->file as $file)
				{
					$option['value'] = $file->URL;
					$option['text']  = $file->name;
					$options[]       = $option;
				}

				return $options;
			}
		}
	}

	/**
	 * Generates the Uploader
	 *
	 * @return string
	 */
	protected function getUploader()
	{
		JHtml::_('jquery.framework');
		JHtml::Script('media/com_sermonspeaker/plupload/plupload.full.min.js');

		// Load localisation
		$tag  = str_replace('-', '_', JFactory::getLanguage()->getTag());
		$path = 'media/com_sermonspeaker/plupload/i18n/';
		$file = $tag . '.js';

		if (file_exists(JPATH_SITE . '/' . $path . $file))
		{
			JHtml::Script($path . $file);
		}
		else
		{
			$tag_array = explode('_', $tag);
			$file      = $tag_array[0] . '.js';

			if (file_exists(JPATH_SITE . '/' . $path . $file))
			{
				JHtml::Script($path . $file);
			}
		}

		$uploadURL = JUri::base() . 'index.php?option=com_sermonspeaker&task=file.upload&'
			. JSession::getFormToken() . '=1&format=json';

		$plupload_script = '
			jQuery(document).ready(function() {
				var uploader_' . $this->fieldname . ' = new plupload.Uploader({
					browse_button: "browse_' . $this->fieldname . '",
					url: "' . $uploadURL . '&type=' . $this->file . '",
					drop_element: "' . $this->fieldname . '_drop",
		';

		// Add File filters
		$types = $this->params->get($this->file . '_filetypes');
		$types = array_map('trim', explode(',', $types));
		$types = implode(',', $types);
		$text  = strtoupper('COM_SERMONSPEAKER_FIELD_' . $this->fieldname . '_LABEL');

		if ($types)
		{
			$plupload_script .= '
					filters : {
						mime_types: [
							{title : "' . JText::_($text, 'true') . '", extensions : "' . $types . '"},
						]
					},';
		}

		$plupload_script .= '
				});

				uploader_' . $this->fieldname . '.init();
				var closeButton = "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>";

				uploader_' . $this->fieldname . '.bind("FilesAdded", function(up, files) {
					var html = "";
					plupload.each(files, function(file) {
						html += "<div id=\"" + file.id + "\" class=\"alert alert-info\">"
						 	+ file.name + " (" + plupload.formatSize(file.size) + ") "
							+ "<progress id=\"" + file.id + "_progress\" max=\"100\"></progress></div>";
					});
					document.getElementById("filelist_' . $this->fieldname . '").innerHTML += html;
					uploader_' . $this->fieldname . '.start();
				});

				uploader_' . $this->fieldname . '.bind("BeforeUpload", function(up, file) {
					up.setOption("multipart_params", {
						"date":document.id("' . $this->formControl . '_sermon_date").value,
						"language":document.id("' . $this->formControl . '_language").value,
					})
				});

				uploader_' . $this->fieldname . '.bind("UploadProgress", function(up, file) {
					document.getElementById(file.id + "_progress").setAttribute("value", file.percent);
					document.getElementById(file.id + "_progress").innerHtml = "<b>" + file.percent + "%</b>";
				});

				uploader_' . $this->fieldname . '.bind("FileUploaded", function(up, file, response) {
					if(response.status == 200){
						var data = jQuery.parseJSON(response.response);
						if (data.status == 1){
							jQuery("#" + file.id).removeClass("alert-info").addClass("alert-success");
							document.getElementById(file.id).innerHTML = data.error + closeButton;
							document.id("' . $this->id . '_text").value = data.path;
						}else{
							jQuery("#" + file.id).removeClass("alert-info").addClass("alert-error");
							jQuery("#" + file.id + "_progress").replaceWith(" &raquo; ' . JText::_('ERROR') . ': " + data.error + closeButton);
						}
					}
				});

				uploader_' . $this->fieldname . '.bind("Error", function(up, err) {
					document.getElementById("filelist_' . $this->fieldname . '").innerHTML += "<div class=\"alert alert-error\">Error #"
						+ err.code + ": " + err.message + closeButton + "</div>";
				});

				uploader_' . $this->fieldname . '.bind("PostInit", function(up) {
					jQuery("#upload-noflash").remove();
					if(up.features.dragdrop){
						jQuery("#' . $this->fieldname . '_drop").addClass("drop-area");
					}
				});
			});
		';
		JFactory::getDocument()->addScriptDeclaration($plupload_script);

		$html = '<div id="plupload_' . $this->fieldname . '" class="uploader">
					<div id="filelist_' . $this->fieldname . '" class="filelist"></div>
					<a id="browse_' . $this->fieldname . '" href="javascript:;" class="btn btn-small">'
			. JText::_('COM_SERMONSPEAKER_UPLOAD')
			. '</a>
				</div>';

		return $html;
	}
}
