<?php


namespace gozoro\toolbox\assets;

use yii\web\AssetBundle;

/**
 * Includes jquery-file-upload script
 */
class JqueryFileUploadAsset extends AssetBundle
{

    public $sourcePath = '@vendor/blueimp/jquery-file-upload';

	public $css = [

    ];

    public $js = [
		'js/vendor/jquery.ui.widget.js',
		'js/jquery.fileupload.js',
    ];

	public $jsOptions = ['position'=>\yii\web\view::POS_HEAD];

    public $depends = [
		'yii\web\JqueryAsset',
	];


}
