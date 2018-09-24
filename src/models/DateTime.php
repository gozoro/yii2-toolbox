<?php

namespace gozoro\toolbox\models;

/**
 *
 *
 * @author gozoro
 * @deprecated since version 0.0.1
 */
class DateTime extends \DateTime
{
	const FORMAT_DATETIME = 'Y-m-d H:i:s';
	const FORMAT_DATE     = 'Y-m-d';
	const FORMAT_TIME     = 'H:i:s';



	private $formatDefault = self::FORMAT_DATETIME;

	/**
	 *
	 *
	 * @param string|null $datetime if null then method format() returns empty string
	 * @throws \yii\base\Exception
	 */
	public function __construct($datetime="now", \DateTimeZone $timezone=null)
	{
		if(is_null($datetime))
		{
			parent::__construct('1970-01-01', new \DateTimeZone('UTC'));
			$this->setTimestamp(0);
		}
		else
		{
			parent::__construct($datetime, $timezone);
		}
	}

	public function format($format=self::FORMAT_DATETIME)
	{
		if($this->getTimestamp())
			return parent::format($format);
		else
			return null;
	}

	public function __toString()
	{
		if($this->getTimestamp())
			return $this->format($this->formatDefault);
		else
			return '';
	}

	/**
	 * set default format for __toString().
	 * @param string $format
	 * @return $this
	 */
	public function setDefaultFormat($format=self::FORMAT_DATETIME)
	{
		$this->formatDefault = $format;
		return $this;
	}
}
