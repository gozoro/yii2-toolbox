<?php


namespace gozoro\toolbox\helpers;

use Yii;
use gozoro\toolbox\assets\DatepickerAsset;

/**
 * HTML helpers
 */
class Html extends \yii\helpers\Html
{

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


	/**
	 * Translates russian string to transliteration
	 * @param string $str
	 * @return string
	 */
	static public function rus2translit($str)
	{
		$converter = array(
			'а' => 'a',   'б' => 'b',   'в' => 'v',
			'г' => 'g',   'д' => 'd',   'е' => 'e',
			'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
			'и' => 'i',   'й' => 'y',   'к' => 'k',
			'л' => 'l',   'м' => 'm',   'н' => 'n',
			'о' => 'o',   'п' => 'p',   'р' => 'r',
			'с' => 's',   'т' => 't',   'у' => 'u',
			'ф' => 'f',   'х' => 'h',   'ц' => 'c',
			'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
			'ь' => '\'',  'ы' => 'y',   'ъ' => '\'',
			'э' => 'e',   'ю' => 'yu',  'я' => 'ya',

			'А' => 'A',   'Б' => 'B',   'В' => 'V',
			'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
			'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',
			'И' => 'I',   'Й' => 'Y',   'К' => 'K',
			'Л' => 'L',   'М' => 'M',   'Н' => 'N',
			'О' => 'O',   'П' => 'P',   'Р' => 'R',
			'С' => 'S',   'Т' => 'T',   'У' => 'U',
			'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
			'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
			'Ь' => '\'',  'Ы' => 'Y',   'Ъ' => '\'',
			'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',
		);
		return strtr($str, $converter);
	}

	/**
	 * Translates string to URL-view (translit + strtolower + remove spaces)
	 * @param string $str
	 * @return string
	 */
	static public function str2url($str)
	{
		$str = self::rus2translit($str);
		$str = \mb_strtolower($str, \Yii::$app->charset);
		$str = \preg_replace('/[^-a-z0-9_]+/u', '-', $str);
		$str = \trim($str, "-");
		return $str;
	}


	/**
	 * Returns text with selected links and email. Replaces \n to tag BR.
	 * @param string $text
	 * @return string
	 */
	static public function text($text)
	{
		$text= preg_replace("/(^|[\n ])([\w]*?)((ht|f)tp(s)?:\/\/[\w]+[^ \,\"\n\r\t<]*)/is", "$1$2<a href=\"$3\">$3</a>", $text);
		$text= preg_replace("/(^|[\n ])([\w]*?)((www|ftp)\.[^ \,\"\t\n\r<]*)/is", "$1$2<a href=\"http://$3\">$3</a>", $text);
		$text= preg_replace("/(^|[\n ])([a-z0-9&\-_\.]+?)@([\w\-]+\.([\w\-\.]+)+)/i", "$1<a href=\"mailto:$2@$3\">$2@$3</a>", $text);
		return nl2br($text);
	}



	/**
	 * Return string with CSRF hidden input
	 * @return string
	 */
	static public function csrfHiddenInput()
	{
		return self::hiddenInput(\Yii::$app->getRequest()->csrfParam, \Yii::$app->getRequest()->getCsrfToken(), []);
	}
}