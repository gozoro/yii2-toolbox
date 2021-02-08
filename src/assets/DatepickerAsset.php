<?php


namespace gozoro\toolbox\assets;

use yii\web\AssetBundle;

/**
 * Bootstrap 3 datepicker asset
 */
class DatepickerAsset extends AssetBundle
{

	public $sourcePath = '@bower/bootstrap-datepicker/dist';

	public $js = [
		'js/bootstrap-datepicker.min.js',
		'locales/bootstrap-datepicker.ru.min.js',
	];

	public $css = [
		'css/bootstrap-datepicker3.min.css',
	];

	public $jsOptions = ['position'=>\yii\web\view::POS_HEAD];

    public $depends = [
		'yii\web\JqueryAsset',
    ];


}
