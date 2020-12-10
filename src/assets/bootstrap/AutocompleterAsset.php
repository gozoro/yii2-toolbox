<?php


namespace gozoro\toolbox\assets\bootstrap;

use yii\web\AssetBundle;

/**
 * Bootstrap 3 autocompleter asset
 */
class AutocompleterAsset extends AssetBundle
{

	public $sourcePath = '@vendor/gozoro/jquery-autocompleter/src';
	public $js = [
		'js/jquery.autocompleter.min.js',
	];

	public $css = [
		'css/jquery.autocompleter.min.css'
	];

	public $jsOptions = ['position'=>\yii\web\view::POS_HEAD];

    public $depends = [
		'yii\web\JqueryAsset',
    ];


}
