<?php

if(!class_exists('FBTemplatizer')) {
class FBTemplatizer
{
	var $path;
	var $templateName;
	var $templateContent;
	var $variables = array();
	
	/**
	 * Constructor.  Pass the template name when creating the object in the first place.
	 * @param string $templateName
	 */
	function FBTemplatizer($templateName)
	{
		$this->path = dirname(dirname(__FILE__)) . '/html';
		$this->templateName = '/' . trim($templateName, '/ ');
	}
	
	function exists()
	{
		return(file_exists($this->path . $this->templateName));
	}
	
	/**
	 * Load the content of the template into the object.
	 */
	private function load()
	{
		if(!$this->templateContent)
			$this->templateContent = implode('', file($this->path . $this->templateName));
	}
	
	public function clear()
	{
		$this->variables = array();
	}
	
	/**
	 * Set key strings in the template to contain the HTML replacement.
	 * 
	 * Example:
	 * $this->set('variable', "Something Else");
	 * Replaces <b>[VARIABLE]</b> with <b>Something Else</b>
	 * 
	 * @param string $key
	 * @param string $htmlReplace
	 */
	public function set($key, $htmlReplace)
	{
		$this->variables[$key] = $htmlReplace;
	}
	
	/**
	 * Set all objects in an indexed array with their key->value pairs.
	 * Usage:
	 * $this->setAll( array( 'key'=>"value" ) );
	 * @param array $params
	 */
	public function setAll($params = array())
	{
		foreach($params as $key->$value)
			$this->set($key, $value);
	}
	
	/**
	 * Returns the currently assigned value for a given key.
	 * @param unknown_type $key
	 */
	public function get($key)
	{
		if(isset($this->variables[$key]))
			return($this->variables[$key]);
		else
			return(false);
	}
	
	/**
	 * Parse the template and return the result.
	 * @param bool $evaluatePHP
	 */
	public function parse($evaluatePHP = true)
	{
		$this->load($templateName);
		$html = $this->templateContent;
		
		foreach($this->variables as $key=>$value)
		{
			try
			{
				// If this template variable is another template, get the output of that template and put it here.
				if(is_a($value, __CLASS__))
					$html = str_replace("[" . strtoupper($key) . "]", $value->parse(), $html);
				else
					$html = str_replace("[" . strtoupper($key) . "]", $value, $html);
			}
			catch(Exception $e)
			{
				var_dump($e->getTraceAsString());
			}
		}
		
		if($evaluatePHP)
		{
			try
			{
				ob_start();
				eval('?>' . $html);
				$html = ob_get_contents();
				ob_end_clean();
			}
			catch(Exception $e)
			{
				
			}
		}
		
		return($html);
	}
	
	/**
	 * 
	 * @param bool $evaluatePHP
	 */
	public function output($evaluatePHP = true)
	{
		echo $this->parse($evaluatePHP);
	}
}
}