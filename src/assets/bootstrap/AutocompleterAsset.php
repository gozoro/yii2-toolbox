<?php


namespace gozoro\toolbox\assets\bootstrap;

use yii\web\AssetBundle;

/**
 * Bootstrap 3 autocompleter asset
 */
class AutocompleterAsset extends AssetBundle
{

	public $sourcePath = '@vendor/gozoro/yii2-toolbox/src/resources/bootstrap/autocompleter';

	public $js = [
		'js/autocompleter.min.js',
	];

	public $css = [
		'css/autocompleter.min.css'
	];

	public $jsOptions = ['position'=>\yii\web\view::POS_HEAD];

    public $depends = [
		'yii\web\JqueryAsset',
    ];


}
