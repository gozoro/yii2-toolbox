<?php

namespace gozoro\toolbox\controllers;


use yii\helpers\Html;
use Yii;

/**
 * Base controller
 *
 * @author gozoro
 *
 * @property string $title sets title to page through view->title
 * @property string $description sets meta tag description
 * @property string $keywords sets meta tag keywords
 */
abstract class Controller extends \yii\web\Controller
{
	/**
	 * Enables page autorefresh after a specified number of seconds
	 * @param int $sec
	 */
	public function autoRefresh($sec)
	{
		\Yii::$app->view->registerMetaTag([
				'http-equiv' => 'refresh',
				'content' => (int)$sec
				]);
	}

	/**
	 * Sets title to page
	 * @param string $title
	 */
	public function setTitle($title)
	{
		$this->view->title = $title;
	}

	/**
	 * Sets meta tag description
	 * @param string $description
	 */
	public function setDescription($description)
	{
		\Yii::$app->view->registerMetaTag([
				'name' => 'description',
				'content' => Html::encode($description)
				]);
	}

	/**
	 * Sets meta tag keywords
	 * @param string $keywords
	 */
	public function setKeywords($keywords)
	{
		\Yii::$app->view->registerMetaTag([
				'name' => 'keywords',
				'content' => Html::encode($keywords)
				]);
	}

	/**
	 * Sets text of flash-message after succes action.
	 *
	 * @param string $message text message text
	 */
	public function alertSuccess($message)
	{
		$session = Yii::$app->session;
		$session->open();
		$session->setFlash('alert-success', $message);
	}

	/**
	 * Sets text of flash-message after failing action.
	 *
	 * @param string $message message text
	 */
	public function alertError($text)
	{
		$session = Yii::$app->session;
		$session->open();
		$session->setFlash('alert-error', $text);
	}

	/**
	 * Sets response format \yii\web\Response::FORMAT_JSON;
	 * @param array $responseArr
	 * @return array
	 */
	public function renderJson($responseArr)
	{
		return $this->asJson($responseArr);
	}

	/**
	 * Sets response format \yii\web\Response::FORMAT_XML;
	 * @param array $responseArr
	 * @return array
	 */
	public function renderXml($responseArr)
	{
		return $this->asXml($responseArr);
	}

	/**
	 * Sets response format \yii\web\Response::FORMAT_RAW;
	 * @param string $rawString
	 * @return string
	 */
	public function renderRaw($rawString)
	{
		Yii::$app->response->format =  \yii\web\Response::FORMAT_RAW;
		return $rawString;
	}
}
