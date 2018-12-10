<?php
namespace gozoro\toolbox\web;

use yii\web\UploadedFile;

/**
 * https://github.com/yiisoft/yii2/pull/16923/files
 */
class Request extends \yii\web\Request
{
	/**
	 * Returns an uploaded file according to the given file input name or the given model attribute.
	 * The name can be a plain string or a string like an array element (e.g. 'Post[imageFile]', or 'Post[0][imageFile]')
	 * or "imageFile" (if $model is the data model).
	 * @param string $name the name of the file input field.
	 * @param \yii\base\Model $model the data model
	 * @return null|UploadedFile the instance of the uploaded file.
	 * Null is returned if no file is uploaded for the specified name.
	 */
	public function file($name, $model = null)
	{
		if($model === null)
			return UploadedFile::getInstanceByName($name);
		else
			return UploadedFile::getInstance($model, $name);
	}
	/**
	 * Returns an array of uploaded files corresponding to the specified file input name or the given model attribute.
	 * This is mainly used when multiple files were uploaded and saved as 'files[0]', 'files[1]',
	 * 'files[n]'..., and you can retrieve them all by passing 'files' as the name.
	 * @param string $name the name of the array of files
	 * @param \yii\base\Model $model the data model
	 * @return UploadedFile[] the array of UploadedFile objects. Empty array is returned
	 * if no adequate upload was found. Please note that this array will contain
	 * all files from all sub-arrays regardless how deeply nested they are.
	 */
	public function files($name, $model = null)
	{
		if($model === null)
			return UploadedFile::getInstancesByName($name);
		else
			return UploadedFile::getInstances($model, $name);
	}
}