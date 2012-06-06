<?php

if (function_exists('http_build_url')) 
{
	function formbuilder_create_url($parts) 
	{
		$parts += array('path' => preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']));
		return http_build_url($parts);
	}
} 
else 
{
	function formbuilder_create_url($parts) 
	{
		static $prefixes = array('password' => ':', 'host' => '://', 'port' => ':',
								 'path' => '/', 'query' => '?', 'fragment' => '#');
		$parts += array(
			'scheme' => isset($_SERVER['HTTPS']) ? 'https' : 'http',
			'host' => $_SERVER['HTTP_HOST'],
			'port' => $_SERVER['SERVER_PORT'],
			'path' => preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']),
			);

		switch ($parts['scheme'].$parts['port']) {
		case 'http80':
		case 'https443':
			unset($parts['port']);
			break;
		}
		if (isset($parts['username'])) {
			$parts['host'] = '@' . $parts['host'];
		}

		$url = $parts['scheme'];
		foreach ($prefixes as $key => $pre) {
			if (! isset($parts[$key])) {
				continue;
			}
			if ($parts[$key][0] !== $pre) {
				$url .= $pre;
			}
			$url .= $parts[$key];
		}
		return $url;
	}
}
