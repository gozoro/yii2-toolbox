<?php


namespace gozoro\toolbox\assets;

use yii\web\AssetBundle;

/**
 * Jquery tools asset
 * @deprecated since 2021.03.11
 */
class JqueryToolsAsset extends AssetBundle
{

	public $sourcePath = '@vendor/gozoro/yii2-toolbox/src/resources/jquery.tools';

	public $js = [
		'jquery.tools.min.js',
	];

	public $css = [

	];

	public $jsOptions = ['position'=>\yii\web\view::POS_HEAD];

    public $depends = [
		'yii\web\JqueryAsset',
    ];


}
