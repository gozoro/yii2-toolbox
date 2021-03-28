<?php

namespace gozoro\toolbox\jquery\upload;

use Yii;


require_once Yii::getAlias('@vendor/blueimp/jquery-file-upload/server/php/UploadHandler.php');

/**
 * Upload handler for jquery-file-upload plugin.
 *
 * Uses:
 *
 * (new UploadHandler($options));
 *
 *
 * Default options:
 * - 'upload_dir'        - path to the file upload directory (default value: dirname($_SERVER['SCRIPT_FILENAME'])/files).
 * - 'partial_extension' - this option adds the ".part" extension when the file is chunks loaded (default value: FALSE).
 * - 'accept_file_types' - pattern string or extension array (default value: "/\.(gif|jpe?g|png)$/i").
 * - 'param_name'        - client-side variable name (default value: "files").
 *
 * For other options, see the source class.
 *
 * @see https://github.com/blueimp/jQuery-File-Upload/blob/master/server/php/UploadHandler.php
 */
class UploadHandler extends \UploadHandler
{
	public function __construct($options = null, $initialize = true, $error_messages = null)
	{
		$defaultOptions = array(
			'partial_extension' => false,
		);

		$options = array_merge($defaultOptions, $options);

		if(is_bool($options['partial_extension']) and $options['partial_extension'])
		{
			$options['partial_extension'] = 'part';
		}

		if(is_array($options['accept_file_types']))
		{
			$options['accept_file_types'] = '/\.('.implode('|', $options['accept_file_types']).')$/i';
		}


		parent::__construct($options, $initialize, $error_messages);
	}


	protected function handle_file_upload($uploaded_file, $name, $size, $type, $error, $index = null, $content_range = null)
	{
		$file = new \stdClass();
		$file->name = $this->get_file_name($uploaded_file, $name, $size, $type, $error,
			$index, $content_range);
		$file->size = $this->fix_integer_overflow((int)$size);
		$file->type = $type;
		if ($this->validate($uploaded_file, $file, $error, $index, $content_range))
		{
			$this->handle_form_data($file, $index);
			$upload_dir = $this->get_upload_path();
			if (!is_dir($upload_dir))
			{
				mkdir($upload_dir, $this->options['mkdir_mode'], true);
			}
			$file_path = $this->get_upload_path($file->name);

			if($this->options['partial_extension'])
			{
				$file_path .= '.'.$this->options['partial_extension'];
			}


			$append_file = $content_range && is_file($file_path) && $file->size > $this->get_file_size($file_path);

			if ($uploaded_file && is_uploaded_file($uploaded_file))
			{
				// multipart/formdata uploads (POST method uploads)
				if ($append_file) {
					file_put_contents(
						$file_path,
						fopen($uploaded_file, 'r'),
						FILE_APPEND
					);
				} else {
					move_uploaded_file($uploaded_file, $file_path);
				}
			}
			else
			{
				// Non-multipart uploads (PUT method support)
				file_put_contents(
					$file_path,
					fopen($this->options['input_stream'], 'r'),
					$append_file ? FILE_APPEND : 0
				);
			}

			$file_size = $this->get_file_size($file_path, $append_file);

			if ($file_size === $file->size)
			{

				if($this->options['partial_extension'])
				{
					$file_path_explode = explode('.', $file_path);
					unset( $file_path_explode[ count($file_path_explode)-1 ] );
					$ready_file_path = implode('.', $file_path_explode);
					rename($file_path, $ready_file_path);
				}



				$file->url = $this->get_download_url($file->name);
				if ($this->has_image_file_extension($file->name))
				{
					if ($content_range && !$this->validate_image_file($file_path, $file, $error, $index))
					{
						unlink($file_path);
					}
					else
					{
						$this->handle_image_file($file_path, $file);
					}
				}
			}
			else
			{
				$file->size = $file_size;
				if (!$content_range && $this->options['discard_aborted_uploads'])
				{
					unlink($file_path);
					$file->error = $this->get_error_message('abort');
				}
			}
			$this->set_additional_file_properties($file);
		}
		return $file;
	}
}