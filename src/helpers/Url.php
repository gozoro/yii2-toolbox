<?php

namespace gozoro\toolbox\helpers;



/**
 * URL helper.
 */
class Url extends \yii\helpers\Url
{

	/**
	 * Scheme (http, https, ...)
	 * @var string
	 */
	protected $scheme;

	protected $host;
	protected $port;
	protected $user;
	protected $pass;
	protected $path;

	/**
	 * Array with parameters.
	 * @var array
	 */
	protected $queryParams = [];

	/**
	 * fragment (string after #).
	 * @var string
	 */
	protected $fragment;


	/**
	 * Parse URL
	 * @param string $url
	 */
	public function __construct($url)
	{
		$parts = parse_url($url);

		if($parts === false)
		{
			$parts = [];
		}

		$this->scheme = isset($parts['scheme']) ? $parts['scheme'] : null;
		$this->host   = isset($parts['host']) ? $parts['host'] : null;
		$this->port   = isset($parts['port']) ? $parts['port'] : null;
		$this->user   = isset($parts['user']) ? $parts['user'] : null;
		$this->pass   = isset($parts['pass']) ? $parts['pass'] : null;
		$this->path   = isset($parts['path']) ? $parts['path'] : null;
		$this->fragment  = isset($parts['fragment']) ? $parts['fragment'] : null;

		$query  = isset($parts['query']) ? $parts['query'] : null;
		$this->queryParams = $this->parseQuery($query);
	}


	protected function parseQuery($query)
	{
		if($query)
		{
			$parts = explode('&', $query);

			$params = array();
			foreach($parts as $p)
			{
				$keyval = explode('=', $p);

				if(isset($keyval[1]))
				{
					$params[ $keyval[0] ] = urldecode( $keyval[1] );
				}
				else
				{
					$params[ $keyval[0] ] = null;
				}
			}

			return $params;
		}
		else
		{
			return array();
		}
	}


	public function setScheme($scheme)
	{
		$this->scheme = trim($scheme);
		return $this;
	}

	public function getScheme()
	{
		return $this->scheme;
	}

	public function setHost($host)
	{
		$this->host = trim($host);
		return $this;
	}

	public function getHost()
	{
		return $this->host;
	}

	public function setPort($port)
	{
		$this->port = (int)$port;
		return $this;
	}

	public function getPort()
	{
		return $this->port;
	}

	public function setUser($user)
	{
		$this->user = $user;
		return $this;
	}

	public function getUser()
	{
		return $this->user;
	}

	public function setPass($pass)
	{
		$this->pass = $pass;
		return $this;
	}

	public function getPass()
	{
		return $this->pass;
	}

	public function setPath($path)
	{
		$this->path = $path;
		return $this;
	}

	public function getPath()
	{
		return $this->path;
	}

	public function setQuery($query)
	{
		$this->queryParams = $this->parseQuery($query);
		return $this;
	}

	public function getQuery()
	{
		return http_build_query($this->queryParams);
	}

	/**
	 * Array of query params.
	 *
	 * @param array $params
	 */
	public function setQueryParams(array $params)
	{
		$this->queryParams = $params;
		return $this;
	}

	/**
	 * Returns array of query params as (key => value).
	 * @return array
	 */
	public function getQueryParams()
	{
		return $this->queryParams;
	}

	/**
	 * Add query parameter.
	 *
	 * @param string $name query param name
	 * @param string $value query param value
	 * @param bool $force if true then sets immediately. if false then sets if param not exists.
	 * @return $this
	 */
	public function addQueryParam($name, $value, $force = true)
	{
		if($force)
		{
			$this->queryParams[$name] = $value;
		}
		else
		{
			if(!array_key_exists($name, $this->queryParams))
				$this->queryParams[$name] = $value;
		}
		return $this;
	}

	/**
	 * Removes parameter from query.
	 *
	 * @param string $name name of query parameter
	 */
	public function removeQueryParam($name)
	{
		unset($this->queryParams[$name]);
		return $this;
	}

	/**
	 * Sets fragment string (string after #).
	 * @param string $fragment
	 */
	public function setFragment($fragment)
	{
		$this->fragment = $fragment;
		return $this;
	}

	/**
	 * Returns fragment string (string after #).
	 * @return string
	 */
	public function getFragment()
	{
		return $this->fragment;
	}


	/**
	 * Returns created URL.
	 * @return string
	 */
	public function createUrl()
	{
		$url = '';

		$scheme = $this->getScheme();
		$host   = $this->getHost();
		$port   = $this->getPort();
		$user   = $this->getUser();
		$pass   = $this->getPass();
		$path   = $this->getPath();
		$query  = $this->getQuery();
		$frag	= $this->getFragment();


		if($scheme)
			$url .= $scheme.'://';


		if($host)
		{
			if($user)
			{
				$url .= $user.':';

				if($pass)
					$url .= $pass;

				$url .= '@';
			}

			$url .= $this->host;

			if($port)
				$url .= ':'.$port;
		}

		if($path)
			$url .= $path;

		if($query)
			$url .= '?'.$query;

		if($frag)
			$url .= '#'.$frag;


		return $url;
	}

	/**
	 * Returns TRUE when url is normal (exists scheme and host).
	 * @return bool
	 */
	public function isNormal()
	{
		return !empty($this->scheme) and !empty($this->host);
	}

	/**
	 * Returns TRUE when url is normal and scheme is http or https.
	 * @return bool
	 */
	public function isHttpOrHttps()
	{
		$scheme = strtolower($this->getScheme());

		return $this->isNormal() and ($scheme == 'http' or $scheme == 'https');
	}

	/**
	 * Returns TRUE when url is normal and scheme is http.
	 * @return bool
	 */
	public function isHttp()
	{
		$scheme = strtolower($this->getScheme());

		return $this->isNormal() and ($scheme == 'http');
	}

	/**
	 * Returns TRUE when url is normal and scheme is https.
	 * @return bool
	 */
	public function isHttps()
	{
		$scheme = strtolower($this->getScheme());

		return $this->isNormal() and ($scheme == 'https');
	}

	/**
	 * Returns TRUE when url is normal and scheme is ftp.
	 * @return bool
	 */
	public function isFtp()
	{
		$scheme = strtolower($this->getScheme());

		return $this->isNormal() and ($scheme == 'ftp');
	}
}