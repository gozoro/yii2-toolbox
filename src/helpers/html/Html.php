<?php


namespace gozoro\toolbox\helpers\html;

use Yii;
use gozoro\toolbox\assets\DatepickerAsset;
use gozoro\toolbox\assets\ButtonUploadAsset;

/**
 * HTML helpers
 */
class Html extends \yii\helpers\Html
{
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

	/**
	 * Returns string with video tag or youtube-iframe.
	 *
	 * @param string $path path to video file or youtube-url
	 * @param array $options ['id', 'title', 'poster', 'width'].<br />
	 *
	 * id - the unique identifier of the tag. Default used function uniqid('video_').<br />
	 * title - text over video.<br />
	 * poster - poster image path.<br />
	 * width - width (default 400px).<br />
	 *
	 * @return string
	 */
	static public function video($path, $options=[])
	{
		if(isset($options['id']))
			$id = $options['id'];
		else
			$id = uniqid('video_');


		if(isset($options['title']))
			$title = $options['title'];
		else
			$title = '';

		if(isset($options['poster']))
			$poster = $options['poster'];
		else
			$poster = '';

		if(isset($options['width']))
			$width = $options['width'];
		else
			$width = '400px';




		$parsed = parse_url($path);

		if(isset($parsed['host']) and preg_match('/youtube\.com/', $parsed['host']))
		{

			$html = '<iframe id="youtube_iframe_'.$id.'" style="width:100%; max-width:'.$width.';" src="'.$path.'" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>'

				. '<script>'
				. 'function youtube_resizer_'.$id.'(){'
				. 'var iframe = $("#youtube_iframe_'.$id.'");'
				. 'var width=iframe.width();'
				. 'var height = width/1.618;'
				. 'iframe.css("height", height);'
				. '}'
				. '$(window).resize(youtube_resizer_'.$id.'); '
				. '$(document).ready(youtube_resizer_'.$id.'); '
				. '</script>';

		}
		else
		{

			$html = ''
				. '<div style="max-width:'.$width.'; position: relative;">'
				. '<div id="'.$id.'_title" style="position: absolute; top:10px; left:10px; color:white;font-size: 12px;">'
				.  static::encode($title)
				. '</div>'

				. '<video id="'.$id.'" controls style="width:100%; object-fit:cover;" '.(($poster)?'poster="'.$poster.'"':'').'>'
				. '<source src="'.$path.'">'
				. '</video>'
				. '</div>'

				. '<script>'
				. '$("#'.$id.'").on("play", function(){$("#'.$id.'_title").hide();});'
				. '$("#'.$id.'").on("pause", function(){$("#'.$id.'_title").show();});'
				. '</script>';
		}

		return $html;
	}
}