<?php


namespace gozoro\toolbox\helpers\html;

use Yii;
use gozoro\toolbox\helpers\html\Html;
use gozoro\toolbox\assets\bootstrap\DatepickerAsset;
use gozoro\toolbox\assets\bootstrap\ButtonUploadAsset;

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
	 * @param type $label button label. Default "Upload".
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
	 * 	 - language: Yii::$app->language,
	 *	 - format: 'dd.mm.yyyy',
	 *   - pickTime: false,
	 *   - todayBtn: 'linked',
	 *   - autoclose: true,
	 *   - todayHighlight: true
	 *
	 * @param string|array $name element name and element id  (if "Form[date]" then id="Form-date"). Array for daterange.
	 * @param string|array $value value in format DD.MM.YYYY. Array for daterange.
	 * @param array $options options, see manual page.
	 */
	static function datepicker($name, $value = "", $options=null)
	{
		DatepickerAsset::register( Yii::$app->view );

		$defaultOptions = [
				'language' => Yii::$app->language,
				 'format' => 'dd.mm.yyyy',
				 'pickTime' => false,
				 'todayBtn' => 'linked',
				 'autoclose' => true,
				 'todayHighlight' => true,
			];

		if(!is_null($options) and is_array($options))
		{
			$defaultOptions = array_merge($defaultOptions, $options);
		}


		$arOptions = [];
		foreach($defaultOptions as $key => $val)
		{
			if(is_bool($val))
			{

				if($val)
					$arOptions[] = $key.': true';
				else
					$arOptions[] = $key.': false';
			}
			elseif(is_int($val) or (is_string($val) and preg_match('/^function/i', $val)))
			{
				$arOptions[] = $key.': '.$val;
			}
			elseif(is_array($val))
			{
				$arOptions[] = $key.': '.json_encode($val);
			}
			else // string
			{
				$arOptions[] = $key.': "'.$val.'"';
			}
		}




		if(is_string($name))
		{
			$id = str_replace('[', '-', $name);
			$id = str_replace(']', '', $id);


			if(is_array($value))
			{
				if(isset($value[0]))
					$value = $value[0];
				else
					throw new \yii\base\Exception("Wrong value datepicker.");
			}



			$html = '
					 <div class="input-group date"  >
						<input type="text" class="form-control" id="'.$id.'" name="'.$name.'" value="'.$value.'" maxlength="10"/>
						<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
					 </div>

				<script>
						$("#'.$id.'").parent().datepicker({ '.implode(',', $arOptions ).' });
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
				if( !( isset($value[0]) and isset($value[1]) ) )
				{
					throw new \yii\base\Exception("Wrong value datepicker.");
				}
			}
			else
			{
				$value = [$value, $value];
			}

			$html = '

					<div class="input-daterange input-group" id="datepicker" >
						<input type="text" class="input-sm form-control" id="'.$id[0].'" name="'.$name[0].'" value="'.$value[0].'" />
						<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
						<input type="text" class="input-sm form-control" id="'.$id[1].'" name="'.$name[1].'" value="'.$value[1].'" />
					</div>
					<script>

						$(document).ready(function()
						{
							$("#'.$id[0].'").parent().datepicker({ '.implode(',', $arOptions ).' });
						});

					</script>
				';


		}
		else
		{
			throw new \yii\base\Exception("Wrong name datepicker.");
		}

		return $html;
	}
}