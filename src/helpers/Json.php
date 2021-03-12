<?php

namespace gozoro\toolbox\helpers;

use yii\helpers\ArrayHelper;

/**
 * Json helper
 */
class Json extends \yii\helpers\Json
{

	/**
	 * Encodes the given options into a JSON string.
	 *
	 * @param array $options
	 * @return string
	 */
	public static function optionsEncode(array $options)
	{
		$jsOptions = [];

		foreach($options as $key => $val)
		{
			if(is_string($val) and strpos($val, 'function')===0)
			{
				$jsOptions[$key] = $key.':'.$val;
			}
			elseif(ArrayHelper::isAssociative($val))
			{
				$jsOptions[$key] = $key.':'.static::optionsEncode($val);
			}
			else
			{
				$jsOptions[$key] = $key.':'.static::encode($val);
			}
		}


		return '{'.implode(',', $jsOptions).'}';
	}
}
