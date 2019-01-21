<?php


namespace gozoro\toolbox\assets\jquery;

use yii\web\AssetBundle;

/**
 * Includes jquery-ajaxform script
 */
class AjaxFormAsset extends AssetBundle
{

    public $sourcePath = '@vendor/gozoro/jquery-ajaxform/dist';

	public $css = [

    ];

    public $js = [
		'jquery.ajaxform.min.js',
    ];

	public $jsOptions = ['position'=>\yii\web\view::POS_HEAD];

    public $depends = [
		'yii\web\JqueryAsset',
	];


}