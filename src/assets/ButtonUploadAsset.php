<?php


namespace gozoro\toolbox\assets;

use yii\web\AssetBundle;

/**
 * Bootstrap 3 button upload asset
 */
class ButtonUploadAsset extends AssetBundle
{

	public $sourcePath = '@vendor/gozoro/yii2-toolbox/src/resources/bootstrap/button.upload';

	public $js = [
		'js/button.upload.min.js',
	];

	public $css = [
		'css/button.upload.min.css'
	];

	public $jsOptions = ['position'=>\yii\web\view::POS_HEAD];

    public $depends = [
		'yii\web\JqueryAsset',
    ];


}
