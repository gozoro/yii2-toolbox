<?php


namespace gozoro\toolbox\helpers;

use Yii;
use gozoro\toolbox\helpers\Html;
use gozoro\toolbox\assets\DatepickerAsset;
use gozoro\toolbox\assets\ButtonUploadAsset;
use gozoro\toolbox\assets\AutocompleterAsset;

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
	 */
	static function buttonUpload($name, $label = "Upload", $options = [])
	{
		ButtonUploadAsset::register( Yii::$app->view );

		$html = '<label class="btn btn-default btn-upload">'
				. '<span class="glyphicon glyphicon-paperclip"></span> '
				. self::encode($label)
				.' <span class="badge"></span>'
				. self::fileInput($name, null, $options)
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
}