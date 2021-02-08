<?php


namespace gozoro\toolbox\assets;

use yii\web\AssetBundle;

/**
 * Includes gozoro/bootstrap-sidebar styles and scripts
 */
class SidebarAsset extends AssetBundle
{

    public $sourcePath = '@vendor/gozoro/bootstrap-sidebar/dist';

	public $css = [
        'css/bootstrap-sidebar.min.css',
    ];

    public $js = [
		'js/bootstrap-sidebar.min.js',
    ];

	public $jsOptions = ['position'=>\yii\web\view::POS_HEAD];

    public $depends = [
		'yii\web\JqueryAsset',
		'app\assets\BootstrapAsset',
	];


}
