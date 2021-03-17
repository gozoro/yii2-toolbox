<?php


namespace gozoro\toolbox\helpers;

use Yii;
use gozoro\toolbox\helpers\Html;
use gozoro\toolbox\assets\DatepickerAsset;
use gozoro\toolbox\assets\ButtonUploadAsset;
use gozoro\toolbox\assets\AutocompleterAsset;
use gozoro\toolbox\assets\FileUploaderAsset;
use yii\helpers\ArrayHelper;

/**
 * Bootstrap 3 HTML helpers
 */
class Bootstrap extends Html
{
	/**
	 * Generates a file input field.
	 * To use a file input field, you should set the enclosing form's "enctype" attribute to
     * be "multipart/form-data". After the form is submitted, the uploaded file information
     * can be obtained via $_FILES[$name] (see PHP documentation).
	 *
	 * @param string $name the name attribute.
	 * @param string $label button label. Default "Upload".
	 * @param array $options the tag options in terms of name-value pairs. These will be rendered as
     * the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 * @return string the generated file input tag
	 * @deprecated since 2021.03.10
	 */
	static function buttonUpload($name, $label = "Upload", $options = [])
	{
		ButtonUploadAsset::register( Yii::$app->view );

		$html = '<label class="btn btn-default btn-upload">'
				. '<span class="glyphicon glyphicon-paperclip"></span> '
				. self::encode($label)
				.' <span class="badge"></span>'
				. Html::fileInput($name, null, $options)
				. '</label>';

		return $html;
	}


	/**
	 * Returns HTML with datepicker-input.<br />
	 *
	 * See demo: https://uxsolutions.github.io/bootstrap-datepicker<br />
	 * See manual: https://bootstrap-datepicker.readthedocs.io/en/latest/options.html<br />
	 *
	 * Defalut options:
	 *
	 * 	 - 'language'       => Yii::$app->language,
	 *	 - 'format'         => 'dd.mm.yyyy',
	 *   - 'pickTime'       => false,
	 *   - 'todayBtn'       => 'linked',
	 *   - 'autoclose'      => true,
	 *   - 'todayHighlight' => true,
	 *
	 *   - 'maxlength'    => 10,
	 *   - 'readonly'     => false,
	 *   - 'pickonly'     => false,
	 *   - 'disabled'     => false,
	 *   - 'class'        => false,
	 *   - 'style'        => false,
	 *   - 'placeholder'  => false,
	 *   - 'autocomplete' => null,
	 *
	 * @param string|array $name element name and element id  (if "Form[date]" then id="Form-date"). Array for daterange.
	 * @param string|array $value value in format dd.mm.yyyy. Array for daterange.
	 * @param array $options options, see manual page.
	 *
	 * @return string the generated datepicker input tag.
	 */
	static function datepicker($name, $value = "", $options=null)
	{
		DatepickerAsset::register( Yii::$app->view );

		$defaultOptions = [
				'language'       => Yii::$app->language,
				'format'         => 'dd.mm.yyyy',
				'pickTime'       => false,
				'todayBtn'       => 'linked',
				'autoclose'      => true,
				'todayHighlight' => true,

				'maxlength'    => 10,
				'readonly'     => false,
				'pickonly'     => false,
				'disabled'     => false,
				'class'        => false,
				'style'        => false,
				'placeholder'  => false,
				'autocomplete' => null,
			];

		if(!is_null($options) and is_array($options))
		{
			$defaultOptions = array_merge($defaultOptions, $options);
		}


		if(isset($defaultOptions['maxlength']) and $defaultOptions['maxlength'])
			$maxlength = 'maxlength="'.(int)$defaultOptions['maxlength'].'"';
		else
			$maxlength = '';


		if(isset($defaultOptions['disabled']) and $defaultOptions['disabled'])
			$disabled = 'disabled';
		else
			$disabled = '';

		if(isset($defaultOptions['readonly']) and $defaultOptions['readonly'])
			$readonly = 'readonly';
		else
			$readonly = '';

		if(isset($defaultOptions['pickonly']) and $defaultOptions['pickonly'])
		{
			$readonly = 'readonly';
			$defaultOptions['enableOnReadonly'] = true;

			if(!$disabled)
			{
				if(!empty($defaultOptions['style']))
					$defaultOptions['style'] = 'background-color:#fff;'.$defaultOptions['style'];
				else
					$defaultOptions['style'] = 'background-color:#fff;';
			}
		}


		if(isset($defaultOptions['placeholder']) and $defaultOptions['placeholder'])
			$placeholder = 'placeholder="'.self::encode($defaultOptions['placeholder']).'"';
		else
			$placeholder = '';

		if(empty($defaultOptions['autocomplete']))
		{
			$autocomplete = '';
		}
		else
		{
			if(is_string( $defaultOptions['autocomplete'] ))
				$autocomplete = 'autocomplete="'.self::encode($defaultOptions['autocomplete']).'"';
			elseif($defaultOptions['autocomplete'])
				$autocomplete = 'autocomplete="on"';
			else
				$autocomplete = 'autocomplete="off"';
		}


		if(isset($defaultOptions['class']) and $defaultOptions['class'])
			$class = $defaultOptions['class'];
		else
			$class = '';

		if(isset($defaultOptions['style']) and $defaultOptions['style'])
			$style = 'style="'.$defaultOptions['style'].'"';
		else
			$style = '';


		unset($defaultOptions['maxlength'], $defaultOptions['readonly'], $defaultOptions['pickonly'], $defaultOptions['disabled'],
			$defaultOptions['placeholder'], $defaultOptions['class'], $defaultOptions['style'], $defaultOptions['autocomplete']);

		$jsOptions = self::phpOptions2jsOptions($defaultOptions);

		if(is_string($name))
		{
			$id = str_replace('[', '-', $name);
			$id = str_replace(']', '', $id);


			if(is_array($value))
			{
					throw new \yii\base\Exception("Invalid value datepicker. Value must be string.");
			}

			$html = '<div class="input-group date">
						<input type="text" class="form-control '.$class.'" '.$style.' id="'.$id.'" name="'.$name.'" value="'.self::encode($value).'" '.$maxlength.' '.$placeholder.' '.$readonly.' '.$disabled.' '.$autocomplete.' />
						<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
					</div>

				<script>
					$(document).ready(function()
					{
						$("#'.$id.'").parent().datepicker('.$jsOptions.');
					});
				</script>';
		}
		elseif(is_array($name) and isset($name[0]) and isset($name[1]))
		{
			$id[0] = str_replace('[', '-', $name[0]);
			$id[0] = str_replace(']', '', $id[0]);

			$id[1] = str_replace('[', '-', $name[1]);
			$id[1] = str_replace(']', '', $id[1]);

			if(is_array($value))
			{
				if(!array_key_exists(0, $value) or ! array_key_exists(1, $value))
				{
					throw new \yii\base\Exception("Invalid value datepicker. Value must be array with 2 items.");
				}
			}
			else
			{
				$value = [$value, $value];
			}

			$html = '<div class="input-daterange input-group" id="datepicker">
						<input type="text" class="input-sm form-control '.$class.'" '.$style.' id="'.$id[0].'" name="'.$name[0].'" value="'.self::encode($value[0]).'" '.$maxlength.' '.$placeholder.' '.$readonly.' '.$disabled.' '.$autocomplete.' />
						<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
						<input type="text" class="input-sm form-control '.$class.'" '.$style.' id="'.$id[1].'" name="'.$name[1].'" value="'.self::encode($value[1]).'" '.$maxlength.' '.$placeholder.' '.$readonly.' '.$disabled.' '.$autocomplete.' />
					</div>
					<script>
						$(document).ready(function()
						{
							$("#'.$id[0].'").parent().datepicker('.$jsOptions.');
						});
					</script>
				';
		}
		else
		{
			throw new \yii\base\Exception("Invalid name datepicker. Name must be string or array with 2 items.");
		}

		return $html;
	}


	/**
	 * Returns string with options for insert to javascript code.
	 *
	 * @param array $options php options
	 * @return string
	 */
	private static function phpOptions2jsOptions($options)
	{
		$jsOptions = [];
		foreach($options as $key => $val)
		{
			if(is_bool($val))
			{
				if($val)
					$jsOptions[$key] = $key.':true';
				else
					$jsOptions[$key] = $key.':false';
			}
			elseif(is_int($val) or (is_string($val) and preg_match('/^function/i', $val)))
			{
				$jsOptions[$key] = $key.':'.$val;
			}
			elseif(is_array($val))
			{
				$jsOptions[$key] = $key.':'.json_encode($val);
			}
			else // string
			{
				$jsOptions[$key] = $key.':"'.$val.'"';
			}
		}
		return '{'.implode(',', $jsOptions ).'}';
	}



	/**
	 * Returns HTML with autocomplte input.
	 *
	 * Default options:
	 *
	 * - maxResults  => 0,   // maximum number of suggestions (0 - no limits).
	 * - minChars    => 1,   // minimum number of characters for the suggestions.
	 * - timeout     => 500, // keyboard input timeout.
	 * - matchRegexp => 'function(value, escape){return RegExp(escape(value), 'i')}', // function returns a regexp-object used for filtering.
	 * - matchValue  => 'function(item, index){return item;}',                        // function returns a value used for filtering.
	 * - itemDisplay => 'function(item, index){return item;}',                        // function returns a value used for display a suggestions.
	 * - itemValue   => null,                                                         // you can set a function returns a value for the request
	 *                                                                                // (the default value is matchValue).
	 * - emptyValue  => ''                                                            // empty value when itemValue is used.
	 * - ajaxData    => 'function(value){return {value:value};}',                     // function returns default ajax-request data
	 * - hiddenValue => '',                                                           // default value for the hidden input
	 *
	 * - 'maxlength'   => false,
	 * - 'readonly'    => false,
	 * - 'pickonly'    => false,
	 * - 'disabled'    => false,
	 * - 'class'       => false,
	 * - 'style'       => false,
	 * - 'placeholder' => false,
	 * - 'autocomplete' => 'off',
	 *
	 *
	 * @param string $name element name and element id  (if "Form[name]" then id="Form-name").
	 * @param string $value current text input value.
	 * @param array|string $variants array of items or url to AJAX GET-request and JSON response.
	 * @param array $options options.
	 * @return string the generated autocompleter input tag.
	 */
	static function autocompleter($name, $value="", $variants=[], $options=null)
	{
		AutocompleterAsset::register( Yii::$app->view );

		$defaultOptions = [
				'maxlength'   => false,
				'readonly'    => false,
				'disabled'    => false,
				'class'       => false,
				'style'       => false,
				'placeholder' => false,
				'autocomplete'=> 'off'
		];


		if(!is_null($options) and is_array($options))
		{
			$defaultOptions = array_merge($defaultOptions, $options);
		}


		if(isset($defaultOptions['maxlength']) and $defaultOptions['maxlength'])
			$maxlength = 'maxlength="'.(int)$defaultOptions['maxlength'].'"';
		else
			$maxlength = '';


		if(isset($defaultOptions['disabled']) and $defaultOptions['disabled'])
			$disabled = 'disabled';
		else
			$disabled = '';

		if(isset($defaultOptions['readonly']) and $defaultOptions['readonly'])
			$readonly = 'readonly';
		else
			$readonly = '';




		if(isset($defaultOptions['placeholder']) and $defaultOptions['placeholder'])
			$placeholder = 'placeholder="'.self::encode($defaultOptions['placeholder']).'"';
		else
			$placeholder = '';

		if(empty($defaultOptions['autocomplete']))
		{
			$autocomplete = '';
		}
		else
		{
			if(is_string( $defaultOptions['autocomplete'] ))
				$autocomplete = 'autocomplete="'.self::encode($defaultOptions['autocomplete']).'"';
			elseif($defaultOptions['autocomplete'])
				$autocomplete = 'autocomplete="on"';
			else
				$autocomplete = 'autocomplete="off"';
		}

		if(isset($defaultOptions['class']) and $defaultOptions['class'])
			$class = $defaultOptions['class'];
		else
			$class = '';

		if(isset($defaultOptions['style']) and $defaultOptions['style'])
			$style = 'style="'.$defaultOptions['style'].'"';
		else
			$style = '';


		unset($defaultOptions['maxlength'], $defaultOptions['readonly'], $defaultOptions['disabled'],
			  $defaultOptions['placeholder'], $defaultOptions['class'], $defaultOptions['style'], $defaultOptions['autocomplete']);



		$jsOptions = self::phpOptions2jsOptions($defaultOptions);




		if(is_string($name))
		{
			$id = str_replace('[', '-', $name);
			$id = str_replace(']', '', $id);

			if(is_array($variants))
			{
				$jsVariants = json_encode($variants);
			}
			elseif(is_string($variants))
			{
				$jsVariants = '"'.$variants.'"'; // ajax url
			}
		}
		else
		{
			throw new \yii\base\Exception("Invalid name autocompleter.");
		}




		return
		'<input id="'.$id.'" name="'.$name.'" type="text" class="form-control autocompleter '.$class.'" '.$style.' value="'.self::encode($value).'" '.$maxlength.' '.$placeholder.' '.$readonly.' '.$disabled.' '.$autocomplete.' />
		<script>
			$(document).ready(function()
			{
				$("#'.$id.'").autocompleter('.$jsVariants.', '.$jsOptions.' );
			});
		</script>';
	}


	/**
	 * Generates a file input field.
	 * To use a file input field, you should set the enclosing form's "enctype" attribute to
	 * be "multipart/form-data". After the form is submitted, the uploaded file information
	 * can be obtained via $_FILES[$name] (see PHP documentation).
	 * @param string $name the name attribute.
	 * @param string|null $value the value attribute. If it is null, the value attribute will not be generated.
	 * @param array $options the tag options in terms of name-value pairs. These will be rendered as
	 * the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 * If a value is null, the corresponding attribute will not be rendered.
	 * See [[renderTagAttributes()]] for details on how attributes are being rendered.
	 *
	 *
	 * Default options:
	 * - 'id' => $name, // Important! "file[]" replace to "file", "file[video]" replace to "file-video"
	 * - 'class' => 'btn btn-default',
	 * - 'data-selected-class' => 'btn btn-success',
	 * - 'content' => 'Attach a file',
	 * - 'text' => 'Attach a file',
	 *
	 * - 'multiple' => false,
	 * - 'accept' => ''
	 * - input => [class=>'file-input-hidden', id=uniqid()] // hidden input file tag
	 * - filearea => [class=>'file-input-filearea', id=uniqid()] // tag with file names
	 *
	 * @return string the generated file input tag
	 */
	static function fileInput($name, $value = null, $options = [])
	{
		FileUploaderAsset::register( Yii::$app->view );

		//$label = (Yii::$app->language == 'ru-RU') ? 'Прикрепить' : 'Attach a file';

		$defaultOptions = [
			'id'       => str_replace(['[]', '[', ']'], ['', '-', ''], $name),
			'label'    => (Yii::$app->language == 'ru-RU') ? 'Прикрепить' : 'Attach a file',
			'class'    => 'btn btn-default',
			'data-selected-class' => 'btn btn-success',
			'content'  => '<i class="glyphicon glyphicon-paperclip"></i> %LABEL% <span class="badge"></span>',
			'type' => 'button',

			'input'    => ['id'=> uniqid('input'), 'class'=>'file-input-hidden'],
			'filearea' => ['id'=> uniqid('filearea') , 'class'=> 'file-input-filearea'],
		];


		$options = ArrayHelper::merge($defaultOptions, $options);

		$inputId      = $options['input']['id'];
		$buttonId     = $options['id'];
		$fileareaId   = $options['filearea']['id'];
		$class        = $options['class'];
		$label        = $options['label'];
		$content      = str_replace('%LABEL%', $label, $options['content']);
		$inputAttr    = $options['input'];
		$fileareaAttr = $options['filearea'];

		if(isset($options['multiple']) and !isset($inputAttr['multiple'])) $inputAttr['multiple'] = $options['multiple'];
		if(isset($options['accept']) and !isset($inputAttr['accept']))   $inputAttr['accept'] = $options['accept'];
		unset($options['input'], $options['filearea'], $options['content'], $options['label'], $options['multiple'], $options['accept']);

		$btnAttr = $options;


		$html = '<div>'
			. Html::fileInput($name, $value, $inputAttr)
			. '<button '.static::renderTagAttributes($btnAttr).'>'.$content.'</button>'
			. '<filearea '.static::renderTagAttributes($fileareaAttr).'></filearea></div>'
			. '<script>
				$(document).ready(function()
				{
					$("#'.static::encode($inputId).'").change(function()
					{
						$("#'.static::encode($buttonId).'").change();
					});

					$("#'.static::encode($buttonId).'").click(function()
					{
						$("#'.static::encode($inputId).'").click();
					})
					.change(function()
					{
						var files = $("#'.static::encode($inputId).'").get(0).files;
						var countFiles = files ? files.length : 1;
						var fileNames = [], i;

						for(i=0; i<countFiles; i++)
						{
							fileNames.push( files[i].name );
						}

						var $btn = $(this);
						var selectedFiles = fileNames.join(",\n");
						var selClass = $btn.data("selected-class");

						$btn.attr("class", selClass).attr("title", selectedFiles).find(".badge").html(countFiles);
						$("#'.static::encode($fileareaId).'").attr("title", selectedFiles).html(selectedFiles);
					})
					.parents("form").on("reset", function()
					{
						$("#'.static::encode($buttonId).'").attr("class", "'.static::encode($class).'").find(".badge").html("");
						$("#'.static::encode($fileareaId).'").attr("title", "").html("");
					});
				});
			</script>';

		return $html;
	}



	/**
	 * Returns HTML with Jquery-File-Upload button.
	 *
	 * See: https://github.com/blueimp/jQuery-File-Upload/
	 *
	 * Default options:
	 *
	 * - 'id' => $name,                // Important! "file[]" replace to "file", "file[video]" replace to "file-video"
	 * - 'class' => 'btn btn-default',
	 * - 'content' => 'Upload',
	 * - 'multiple' => false,
	 * - 'accept' => '',
	 * - filelist => [
	 *      'class' => 'file-uploader-filelist'
	 *   ]
	 * - 'data-unique-name' => 1,      // send unique file names
	 * - 'data-max-upload-files' => 0, // limit upload files, 0 - no limit
	 * - pluginOptions => [
	 *      'url' => $url,
	 *      'type' => 'POST',
	 *      'maxChunkSize' => 5 * 1024 * 1024, // 5 MB
	 *      'dataType' => 'json',
	 *      'sequentialUploads' => true,
	 *      'paramName' => $name,
	 *   ] // more options see https://github.com/blueimp/jQuery-File-Upload/wiki/Options
	 *
	 *
	 * @param string $name param name
	 * @param string|array $url url for upload
	 * @param array $options
	 * @return string
	 */
	static function fileUploader($name, $url, $options = [])
	{
		JqueryToolsAsset::register( Yii::$app->view );
		JqueryFileUploadAsset::register( Yii::$app->view );
		FileUploaderAsset::register( Yii::$app->view );

		if(is_array($url))
			$url = Url::to($url);

		if(!empty($options['id']))
			$id = $options['id'];
		else
			$id = str_replace(['[]', '[', ']'], ['', '-', ''], $name);

		if(!empty($options['filelist']['id']))
			$filelistId = $options['filelist']['id'];
		else
			$filelistId = uniqid('filelist');


		$label = (Yii::$app->language == 'ru-RU') ? 'Загрузить' : 'Upload';

		$defaultOptions = [
			'id' => $id,
			'content'             => '<i class="glyphicon glyphicon-paperclip"></i> '.$label,
			'class'               => 'btn btn-default',
			'data-selected-class' => 'btn btn-default',
			'filearea' => [
				'style'=>'display:none;'
				],

			'data-unique-name' => 1,
			'data-max-upload-files' => 0,

			'filelist' => [
				'id' => $filelistId,
				'class' => 'file-uploader-filelist'
				],

			'input' => [
				'id'=> uniqid('uploader')
				],

			'pluginOptions' => [
				'url' => $url,
				'type' => 'POST',
				'maxChunkSize' => 5 * 1024 * 1024, // 5 MB
				'dataType' => 'json',
				'sequentialUploads' => true,
				'paramName' => $name,

				'add' => 'function(e, data)
				{
					var $list = $("#'.static::encode($filelistId).'");
					var $btn = $("#'.static::encode($id).'");

					$btn.attr("title", "");

					var maxFiles = $btn.data("max-upload-files");

					if(maxFiles)
					{
						globalFileCount++;

						if(globalFileCount > maxFiles)
						{
							return false;
						}
					}


					var file = data.files[0];
					file.id = "file" + $.generateRandomString(32);

					var matches = file.name.match(/\.([^.]+)$/), ext = "";
					if (matches)
					{
						ext = matches[1];
					}
					file.uniqueName = file.id + "." + ext;

					var htmlFile = `<div id="${file.id}" class="file">
						<div class="name" title="${file.name}"><a href="#" class="click-remove-upload-file"><i class="glyphicon glyphicon-trash"></i></a> ${file.name}</div>
						<div class="size">${ $.filesizeFormat(file.size)}</div>
						<div class="percent">0%</div>
						<div class="error"></div>
						<div class="progress"></div>
						</div>`;

					$list.append(htmlFile);

					if(maxFiles)
					{
						if( $list.children().length >= maxFiles )
						{
							$btn.attr("disabled", true).attr("data-max-limit-lock", 1);
						}
					}


					$(`#${file.id} .click-remove-upload-file`).click(function(event)
					{
						event.preventDefault();
						$(`#${file.id}`).remove();

						var $list = $("#'.static::encode($filelistId).'");

						if(globalXHRFILES[file.id])
						{
							globalXHRFILES[file.id].abort();
						}

						$btn = $("#'.static::encode($id).'");
						var maxFiles = $btn.data("max-upload-files");

						if(maxFiles)
						{
							globalFileCount--;
							var isLock = $btn.data("max-limit-lock");

							if(isLock)
							{
								$btn.attr("disabled", false).attr("data-max-limit-lock", 0);
							}
						}
					});

					globalXHRFILES[file.id] = data.submit();
				}',
				'submit' => 'function(e, data)
				{
					var isUniqueName = $("#'.static::encode($id).'").data("unique-name");
					var file = data.files[0];

					if(isUniqueName)
						data.files[0].uploadName = file.uniqueName;

				}',
				'progress' => 'function(e, data)
				{
					var progress = parseInt((data.loaded / data.total) * 100, 10);
					var file = data.files[0];

					$(`#${file.id} .percent`).html(`${progress}%`);
					$(`#${file.id} .progress`).html(`<div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="${progress}" aria-valuemin="0" aria-valuemax="100" style="width: ${progress}%;"></div>`);
				}',
				'done' => 'function(e, data)
				{
					var file = data.files[0];
					var isUniqueName = $("#'.static::encode($id).'").data("unique-name");
					var res = data.result.'.static::encode($name).'[0];

					if(res.error)
					{
						$(`#${file.id} .error`).html(res.error);
						$(`#${file.id} .progress`).hide();
						$(`#${file.id} .percent`).html("");
						$(`#${file.id} .size`).html("");
					}
					else
					{
						$(`#${file.id}`).addClass("text-success");
						$(`#${file.id} .size`).html(`<i class="glyphicon glyphicon-ok"></i>`);
						$(`#${file.id} .progress`).remove();

						if(isUniqueName)
							$(`#${file.id}`).append(`<input type="hidden" name="file[${file.uniqueName}]" value="${file.name}">`);
						else
							$(`#${file.id}`).append(`<input type="hidden" name="file[]" value="${file.name}">`);
					}
				}',
				'fail' => 'function(e, data)
				{

				}',
			],
		];

		$options = ArrayHelper::merge($defaultOptions, $options);

		$inputId         = $options['input']['id'];
		$filelistOptions = $options['filelist']; unset($options['filelist']);
		$pluginOptions   = $options['pluginOptions']; unset($options['pluginOptions']);


		$html = '<filelist '.static::renderTagAttributes($filelistOptions).'></filelist>';

		$html.= static::fileInput('', null, $options);

		$html.= '<script>

			$(document).ready(function()
			{
				var globalFileCount = 0;
				var globalXHRFILES = {};

				$("#'.static::encode($inputId).'").fileupload('.Json::optionsEncode($pluginOptions).');

				$("#'.static::encode($id).'").parents("form").on("reset", function()
				{
					$("#'.static::encode($filelistId).'").html("");

					for(var i in globalXHRFILES)
					{
						globalXHRFILES[i].abort();
					}

					var $btn = $("#'.static::encode($id).'");
					var maxFiles = $btn.data("max-upload-files");

					if(maxFiles)
					{
						globalFileCount = 0;
						var isLock = $btn.data("max-limit-lock");

						if(isLock)
						{
							$btn.attr("disabled", false).attr("data-max-limit-lock", 0);
						}
					}
				});
			});

			</script>';

		return $html;
	}
}