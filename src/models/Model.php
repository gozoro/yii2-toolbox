<?php

namespace gozoro\toolbox\models;


/**
 * Model with method datetimeAttributes()
 *
 * @author gozoro
 * @deprecated since version 0.0.1
 */
abstract class Model extends \yii\db\ActiveRecord
{
	public function afterFind()
	{
		parent::afterFind();



		$datetimeAttributes = $this->datetimeAttributes();
		foreach($datetimeAttributes as $name => $name_format)
		{

			if(is_string($name))
			{
				$format = $name_format;
			}
			else
			{
				$name = $name_format;
				$format = null;
			}

			if($this->hasAttribute($name))
			{
				$value = $this->{$name};

				if( in_array($name, $datetimeAttributes ) )
				{
					$this->setDatetimeAttribute($name, $value, $format);
				}
				elseif( array_key_exists($name, $datetimeAttributes) )
				{
					$this->setDatetimeAttribute($name, $value, $format);
				}
			}
		}
	}

	public function __set($name, $value)
	{
		parent::__set($name, $value);

		if( $this->hasAttribute($name) and (is_string($value) or is_null($value)) )
		{
			$datetimeAttributes = $this->datetimeAttributes();

			if( in_array($name, $datetimeAttributes ) )
			{
				$this->setDatetimeAttribute($name, $value);
			}
			elseif( array_key_exists($name, $datetimeAttributes) )
			{
				$this->setDatetimeAttribute($name, $value, $datetimeAttributes[$name]);
			}
		}
	}

	/**
	 * @return array of attribute names or [attribute name]=>DateTime::FORMAT_
	 */
	public function datetimeAttributes()
	{
		return [];
	}

	public function setDatetimeAttribute($name, $value, $format=null)
	{
		$datetime = new DateTime($value);

		if(!is_null($format))
			$datetime->setDefaultFormat($format);

		$this->setAttribute($name, $datetime);
	}

	public function toArray(array $fields = array(), array $expand = array(), $recursive = true)
	{
		$arr = parent::toArray($fields, $expand, $recursive);


		foreach($this->datetimeAttributes() as $name => $name_format)
		{
			if(is_string($name))
			{
				$format = $name_format;
			}
			else
			{
				$name = $name_format;
				$format = null;
			}


			if(array_key_exists($name, $arr))
			{
				$arr[$name] = (string)$this->{$name};
			}
		}

		return $arr;
	}

}