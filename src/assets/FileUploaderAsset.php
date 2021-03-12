<?php


namespace gozoro\toolbox\assets;

use yii\web\AssetBundle;

/**
 * Bootstrap 3 file uploader asset
 */
class FileUploaderAsset extends AssetBundle
{

	public $sourcePath = '@vendor/gozoro/yii2-toolbox/src/resources/file.uploader';

	public $js = [

	];

	public $css = [
		'file.uploader.css'
	];

	public $jsOptions = ['position'=>\yii\web\view::POS_HEAD];

    public $depends = [
		
    ];


}
