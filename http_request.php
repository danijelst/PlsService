<?php

class http_request
{
  function get_contents($url) {
    
    $use_cache = false;
    $cachedir  = './cache/';
    $cachefile = $cachedir . md5($url) . '.html';
    $cachetime = 45;
    
    // Serve from the cache if it is younger than $cachetime
    if (file_exists($cachefile) && time() - $cachetime < filemtime($cachefile)) {
      if (filesize($cachefile) > 0)
        $use_cache = true;
    }
    
    if ($use_cache) {
      // get cached file 
      $fp = fopen($cachefile, 'r');
      $strURLContent = fread($fp, filesize($cachefile));
      fclose($fp);
    } else {
      // get file from url
      $strURLContent = file_get_contents($url);
      
      if (file_exists($cachedir)) {
        // Cache the output to a file
        $fp = fopen($cachefile, 'w');
        fwrite($fp, $strURLContent);
        fclose($fp);
      }
    }
    
    return $strURLContent;
    
  }
  
  function convertCSVtoAssocMArray($file)
  {
    $delimiter  = ";";
    $result     = Array();
    $size       = filesize($file) +1;
    $file       = fopen($file, 'r');
    $header     = fgetcsv($file, $size, $delimiter);
    $cols       = count($header);
    
    while ($row = fgetcsv($file, $size, $delimiter))
    {
      for($i = 0; $i < $cols; $i++)
      {
        if(array_key_exists($i, $row))
        {
          $mapped_row[$header[$i]] = utf8_encode($row[$i]);
        }
      }
      $result[] = $mapped_row;
    }
    fclose($file);
    
    return $result;
  }
  
  
}


?>