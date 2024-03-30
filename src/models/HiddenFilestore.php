<?php

namespace gozoro\toolbox\models;

use Yii;
use yii\web\NotFoundHttpException;

/**
 * This class is intended for working with hidden file storage (files are not accessible by direct link).
 * @author Gozoro <gozoro@yandex.ru>
 */
class HiddenFilestore
{
	/**
	 * Path to filestore
	 * @var string
	 */
	private $path;


	/**
	 * This class is intended for working with hidden file storage (files are not accessible by direct link).
	 * @param string $path path to filestore folder
	 */
	public function __construct($path)
	{
		$this->path = $path;
	}

	/**
	 * Returns absolute path to filestore folder
	 * @return string
	 */
	public function getPath()
	{
		return realpath($this->path);
	}

	/**
	 * Download the file by hash
	 * @param string $hash hash of file
	 * @return string (file content)
	 * @throws NotFoundHttpException
	 */
	public function download($hash)
	{
		$files = $this->getFileHashList();

		if(isset($files[$hash]))
		{
			$filepath = $files[$hash];

			$send = Yii::$app->response->sendFile($filepath, basename($filepath));
			return $send;
		}
		else
		{
			throw new NotFoundHttpException("Файл не найден.");
		}
	}

	/**
	 * Returns structure of files from folder with $path.
	 *
	 * Example of strucure:
	 * [
	 *	hash1 => '/files/file1.txt',
	 *	hash2 => '/files/file2.txt',
	 *	'folder1' => [
	 *	hash1 => '/files/folder1/file3.txt',
	 *	hash2 => '/files/folder1/file4.txt',
	 *	]
	 * ]
	 *
	 *
	 * @return array
	 */
	public function getFileStructure()
	{
		return $this->structureFolder($this->path);
	}



	private function structureFolder($path)
	{
		$structure = [];


		$scandir = scandir($path);
		unset($scandir[0], $scandir[1]); // remove . and ..

		foreach($scandir as $item)
		{
			$itempath = realpath($path.'/'.$item);

			if(is_dir($itempath))
			{
				$structure[$item] = $this->structureFolder($itempath);
			}
			else
			{
				$hash = md5($itempath);
				$structure[$hash] = $itempath;
			}
		}


		return $structure;
	}

	/**
	 * Returns array of hash and files.
	 *
	 * [
	 *	hash1 => '/files/file1.txt',
	 *	hash2 => '/files/file2.txt',
	 *	hash1 => '/files/folder1/file3.txt',
	 *	hash2 => '/files/folder1/file4.txt',
	 * ]
	 *
	 * @param array $fileStructure
	 * @return array
	 */
	public function getFileHashList()
	{
		$structure = $this->getFileStructure();
		return $this->structure2hashlist($structure);
	}

	private function structure2hashlist($fileStructure)
	{
		$list = [];

		foreach($fileStructure as $key => $value)
		{
			if(is_array($value))
			{
				$folderList = $this->structure2hashlist($value);
				foreach($folderList as $fkey => $fvalue)
					$list[$fkey] = $fvalue;
			}
			else
			{
				$list[$key] = $value;
			}
		}

		return $list;
	}
}