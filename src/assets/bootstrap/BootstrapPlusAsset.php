<?php


namespace gozoro\toolbox\assets\bootstrap;

use yii\web\AssetBundle;

/**
 * Includes gozoro/bootstrap3-plus styles and scripts
 */
class BootstrapPlusAsset extends AssetBundle
{

    public $sourcePath = '@vendor/gozoro/bootstrap3-plus/dist';

	public $css = [
        'css/bootstrap3-plus.min.css',
    ];

    public $js = [];

	public $jsOptions = ['position'=>\yii\web\view::POS_HEAD];

    public $depends = [
		'yii\web\JqueryAsset',
		'app\assets\BootstrapAsset',
	];


}
