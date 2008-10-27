<?php

/*
 * download_file
 * description: downloads a binary file
 *
 */

// downloads a file, courtesy to php.net (http://nl2.php.net/manual/en/function.fopen.php#72521)
function download_file($file_source, $file_target)
{        
 	// Preparations
	$file_source = str_replace(' ', '%20', html_entity_decode($file_source)); // fix url format
	if (file_exists($file_target)) { chmod($file_target, 0777); } // add write permission
	
	// Begin transfer
	if (($rh = fopen($file_source, 'rb')) === FALSE) { return false; } // fopen() handles
	if (($wh = fopen($file_target, 'wb')) === FALSE) { return false; } // error messages.
	while (!feof($rh))
	{
		// unable to write to file, possibly because the harddrive has filled up
		if (fwrite($wh, fread($rh, 1024)) === FALSE) { 
			fclose($rh); 
			fclose($wh); 
			return false; 
		}
	}
	
	// Finished without errors
	fclose($rh);
	fclose($wh);
	return true;
}

?>