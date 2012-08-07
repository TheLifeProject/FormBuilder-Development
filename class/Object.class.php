<?php

if(!class_exists('Object')) {
	class Object {
		
		var $creation_time;
		
		function debug($msg = '', $forced = false)
		{
			$run_time = microtime(true) - $this->creation_time;
			$otype = get_class($this);
			
			ob_start();
			if($msg)
				print_r($msg);
			$result = ob_get_contents();
			ob_end_clean();
			
			echo "<pre>$otype DEBUG [$run_time]: ";
			echo htmlentities($result);
			echo "\n</pre>";
			
		}
	
		/**
		 * Function to allow setting of internal object values.
		 */
		function vset( $variable, $value = '' )
		{
			if(!isset($this->$variable)) 
				return(false);
			else {
				if($this->$variable !== $value)
				{
					$this->$variable = $value;
				}
				return(true);
			}
		}
		
		/**
		 * Function to return or print the current object as a string.
		 */
		function toString($print = true)
		{
			$result = "";
	
			if(ob_start())
			{
				$this->debug($this);
				$result = ob_get_contents();
				ob_end_clean();
			}
			else
			{
				foreach($this as $key=>$value)
				{
					$result .= $key . ": " . $value . "\n";
				}
			}
	
			if($print) echo $result;
			return($result);
		}
		
		function copyToObj( $target )
		{
			foreach($this as $property=>$value)
			{
				$target->$property = $value; 
			}
			return(true);
		}
		
		function error($msg)
		{
			trigger_error($msg, E_USER_ERROR);
		}
		
		function warning($msg)
		{
			trigger_error($msg, E_USER_WARNING);
		}
		
		/**
		 * Standard PHP5 Constructor
		 */
		function __construct()
		{
			$this->creation_time = microtime(true);
	#		$this->debug('Created New ' . get_class($this));
		}
		
		/**
		 * Standard PHP5 Destructor
		 */
		function __destruct()
		{
		}
		
		/**
		 * PHP4 style constructor
		 * 
		 * This PHP4 style constructor will only be called by a system running PHP4.  It will register the
		 * PHP5 style destructor for running on shutdown, and will proceed to run the PHP5 style constructor
		 * manually.
		 * 
		 */
		function Object() {
			// register __destruct method as shutdown function
			if(function_exists('register_shutdown_function')) 
				register_shutdown_function(array(&$this, "__destruct"));
			else 
				trigger_error("Shutdown function does not exist.", E_USER_ERROR);
			
			return($this->__construct());
		}
	
	}
}


?>