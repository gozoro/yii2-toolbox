<?php
namespace gozoro\toolbox\models;

use Yii;
use yii\helpers\Url;


/**
 * Abstract model to store the uploaded files
 *
 * @property int $id identifier
 * @property string $name name of file
 * @property string $hash unique hash
 * @property string $mime the MIME-type of the uploaded file (such as "image/gif").
 *                        Since this MIME type is not checked on the server-side, do not take this value for granted.
 *                        Instead, use [[\yii\helpers\FileHelper::getMimeType()]] to determine the exact MIME type.
 * @property int $size file size in bytes
 * @property string $uploadDate file upload date and time
 *
 * @property string $path full path to the file in the filestore
 * @property string $url link to download the file
 * @property string $encryptName encrypted name of file in filestore
 */
abstract class File extends \yii\db\ActiveRecord
{
	/**
	 * Returns the date and time in the specified format.
	 * @param string $format format
	 * @return string
	 */
	public function getUploadDateFormatted($format = 'Y-m-d H:i:s')
	{
		$ts = strtotime($this->uploadDate);

		if($this->uploadDate)
			return date($format, $ts);
		else
			null;
	}

	/**
	 * Returns the path to the directory where the files are stored.
	 * @return string
	 */
	abstract public function getFilestore();

	/**
	 * Returns the web-path to the directory where the files are stored.
	 * @return string
	 */
	abstract public function getFilestoreUrl();

	/**
	 * Returns encrypted name of file in filestore.
	 * @return string
	 */
	public function getEncryptName()
	{
		return $this->id.'_'.$this->hash.'.'.$this->getExtension();
	}

	/**
	 * Returns link to download the file
	 * @return stirng
	 */
	public function getUrl()
	{
		return $this->getFilestoreUrl().'/'.$this->getEncryptName();
	}

	/**
	 *  Returns full path to the file in the filestore
	 * @return string
	 */
	public function getPath()
	{
		return $this->getFilestore().'/'.$this->getEncryptName();
	}

	/**
	 * Returns extension of stored file.
	 * @return string
	 */
	public function getExtension()
	{
		$name = basename($this->name);
		$parts = explode('.', $name);

		if(count($parts)>1)
			$ext = strtolower( $parts[ count($parts)-1 ] );
		else
			$ext = null;

		return $ext;
	}

	/**
	 * Returns TRUE if stored file is image (jpeg, jpg, png, gif)
	 * @return bool
	 */
	public function isImage()
	{
		$ext = $this->getExtension();
		return in_array($ext, ['jpg', 'jpeg', 'png', 'gif']);
	}

	/**
	 * Returns TRUE if stored file is video (mp4)
	 * @return bool
	 */
	public function isVideo()
	{
		$ext = $this->getExtension();
		return in_array($ext, ['mp4']);
	}

	/**
	 * Moves the uploaded file to file storage and inserts a record in the database.
	 * @param \yii\web\UploadedFile $uploadedFile
	 * @param bool $deleteTempFile whether to delete the temporary file after saving.
     * If true, you will not be able to save the uploaded file again in the current request.
	 * @return \static
	 * @throws \yii\base\Exception
	 */
	static public function saveFile(\yii\web\UploadedFile $uploadedFile, $deleteTempFile = true)
	{
		$file = new static();
		$file->name = $uploadedFile->name;
		$file->hash = Yii::$app->security->generateRandomString(32);
		$file->size = $uploadedFile->size;
		$file->mime = $uploadedFile->type;
		$file->uploadDate = date('Y-m-d H:i:s');

		$filestore = $file->getFilestore();


		if($file->beforeFilestore())
		{
			if(file_exists($filestore))
			{
				if(is_writable($filestore))
				{
					if($file->save())
					{
						if($uploadedFile->saveAs($file->getPath(), $deleteTempFile))
						{
							$file->afterFilestore();
							return $file;
						}
						else
						{
							$file->delete();
							throw new \yii\base\Exception("Uploaded file could not be saved.");
						}
					}
					else
					{
						throw new \yii\base\Exception("Failed to save file record to database.");
					}
				}
				else
				{
					throw new \yii\base\Exception("Directory $filestore is not writable.");
				}
			}
			else
			{
				throw new \yii\base\Exception("Directory $filestore is not exists.");
			}
		}
	}


	public function beforeFilestore()
	{
		$filestore = $this->getFilestore();

		if(!file_exists($filestore))
		{
			mkdir($filestore, 0775, true);
		}
		return true;
	}


	public function afterFilestore()
	{

	}



	public function afterDelete()
	{
		parent::afterDelete();
		unlink($this->getPath());
	}

}