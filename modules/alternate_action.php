<?php

/*
Name: Alternate Action - Submit form data to an alternate POST processing script.
Instructions: If you are using the Alternate Action module, you may specify the full URL of the alternate form processor in the THANKYOUTEXT field of the form.  Once the submitted data has been verified, it will be re-posted to this alternate system.  This ability may not be supported on all servers.  Please test before deploying to production systems.
*/

/*
Created by the TruthMedia Internet Group
(website: truthmedia.com       email : webmaster@truthmedia.com)

Plugin Programming and Design by James Warkentin
http://www.warkensoft.com/about-me/

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; version 3 of the License.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

	function formbuilder_startup_alternate_action($form)
	{
		return(true);
	}


	function formbuilder_process_alternate_action($form, $fields)
	{
		// Ensure that the post location in the thankyoutext looks like a valid url.
		$url = trim($form['thankyoutext']);
		$urlregex = '@^[a-z]{3,5}\://([a-z0-9\.\-\:]+)([a-z0-9/=]*)([a-z0-9/\?=]*)@i';
		if(!preg_match($urlregex, $url, $regs)) {
			// Post location does NOT look like a valid url, return an error.
			return(__("Alternate Form Action does NOT look like a valid URL.  Please contact the website administrator.", 'formbuilder'));
		}
		
		// Create data array to be sent to the alternate form processing system.
		$data['name'] = $form['name'];
		$data['subject'] = $form['subject'];
		$data['destination_email'] = $form['recipient'];
		
		foreach($fields as $field)
		{
			$field_name = $field['field_name'];
			$field_post = $field['value'];
			$data[$field_name] = $field_post;
		}
		
		
		// send a request to example.com (referer = jonasjohn.de)
		list($header, $content) = formbuilder_curlRequest(
		    $url,
		    $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
		    $data
		);
		 
		$thankyoutext = $content;
		
		echo "\n<div class='formBuilderSuccess'>" . decode_html_entities($thankyoutext, ENT_NOQUOTES, get_option('blog_charset')) . "</div>";

		return(false);
	}
	
	/*
	 * POST Request using Curl libraries if available.
	 */
	function formbuilder_curlRequest($url, $referer, $_data = null)
	{
		// Fall back to original code if curl not found.
		if(!function_exists('curl_init')) return(formbuilder_PostRequest($url, $referer, $_data));
		
		// Process post variables if any.
		if(!is_null($_data))
		{
			if(!is_array($_data))
			{
				return(false);
			}
			
			$q = http_build_query($_data);
		}
		
		// Initialize the curl connection.
		$ch = curl_init();
		
		// Encode the post data if any.
		if($q)
		{
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $q);
		}
		
		// Initialize other curl settings.
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
		curl_setopt($ch, CURLOPT_REFERER, $referer);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		$data = curl_exec($ch);
		
		curl_close($ch);
		
	    // split the result header from the content
	    $result = explode("\r\n\r\n", $data, 2);
	    
	    $header = isset($result[0]) ? $result[0] : '';
	    $content = isset($result[1]) ? $result[1] : '';
	 
	    // return as array:
	    return array($header, $content);
	}

	/*
	 * POST Request function taken from Jonas John at
	 * http://www.jonasjohn.de/snippets/php/post-request.htm
	 * License: Public Domain
	 * Created: 08/05/2006
	 * Updated: 08/05/2006
	 */
	function formbuilder_PostRequest($url, $referer, $_data) {
	 
	    // convert variables array to string:
	    $data = array();    
	    while(list($n,$v) = each($_data)){
	        $data[] = "$n=$v";
	    }    
	    $data = implode('&', $data);
	    // format --> test1=a&test2=b etc.
	 
	    // parse the given URL
	    $url = parse_url($url);
	    if ($url['scheme'] != 'http') { 
	        die(__('Only HTTP request are supported !', 'formbuilder'));
	    }
	 
	    // extract host and path:
	    $host = $url['host'];
	    $path = $url['path'];
	    
	    // open a socket connection on port 80
	    $fp = fsockopen($host, 80);
	 
	    // send the request headers:
	    fputs($fp, "POST $path HTTP/1.1\r\n");
	    fputs($fp, "Host: $host\r\n");
	    fputs($fp, "Referer: $referer\r\n");
	    fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
	    fputs($fp, "Content-length: ". strlen($data) ."\r\n");
	    fputs($fp, "Connection: close\r\n\r\n");
	    fputs($fp, $data);
	 
	    $result = ''; 
		// Get the headers
		while (!feof($fp)) {
		    $result .= fgets($fp);

			if(substr($result, -4)=="\r\n\r\n") {
				$headers = formbuilder_get_headers($result);
				
				break;
			}
		}

		// Check whether we have to get the data as chunked or not...
		if($headers['TRANSFER-ENCODING'] != 'chunked')
		{
			while (!feof($fp)) 
			{
			    $result .= fgets($fp);
			}
		}
		else
		{
			do {
				// Determine total size of chunk.
				$chunksize = fgets($fp);
				
				$chunksize = hexdec($chunksize);
				
				$tmp = "";
				$remaining = $chunksize;
				
				// Read data until we have hit the chunk size.
				while($remaining > 0)
				{
					$tmp .= fread($fp, $remaining);
					$size_read = strlen($tmp);
					$remaining = $chunksize - $size_read;
				} 
				
				$discard = fgets($fp);
				
				// Add the temporary data to the main data.
				$result .= $tmp;
				
			} while($chunksize > 0);

		}
		
	    // close the socket connection:
	    fclose($fp);
	 
	    // split the result header from the content
	    $result = explode("\r\n\r\n", $result, 2);
	    
	    $header = isset($result[0]) ? $result[0] : '';
	    $content = isset($result[1]) ? $result[1] : '';
	    
	    
	    // return as array:
	    return array($header, $content);
	}
	
	
	
	// Parse out headers to an array.
	function formbuilder_get_headers($header)
	{
		$header = "\r\n" . trim($header);

		// Extract headers to individual array variables.
		$pattern = "#\r?\n([a-z0-9\-]+)\:(.*)\r?\n[a-z0-9\-]+\:#isU";
		$offset = 0;

		while(preg_match($pattern, $header, $regs, PREG_OFFSET_CAPTURE, $offset))
		{
			$headers[strtoupper($regs[1][0])] = trim($regs[2][0]);
			$offset = $regs[0][1]+5;
		}

		if(preg_match("#\r?\n([a-z0-9\-]+)\:(.*)$#isU", $header, $regs, PREG_OFFSET_CAPTURE, $offset))
		{
			$headers[strtoupper($regs[1][0])] = trim($regs[2][0]);
		}
		
		if(eregi("HTTP/([^ ]+) +([0-9]+)", $header, $regs))
		{
			$headers['HTTPVER'] = trim($regs[1]);
			$headers['STATUS'] = trim($regs[2]);
		}
	
		return($headers);
		
	}
?>
