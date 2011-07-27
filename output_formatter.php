<?php

function streamFile($file) {
  $filename = $file;
  $fsize = @filesize($filename);
  $fh = fopen($filename, 'rb', false);
  $data = fread($fh, $fsize);
  fclose($fh); 
  
  header("Content-type: application/octet-stream");
  header("Content-Disposition: attachment; filename=\"pls-stojnic.kmz\"");
  header("Content-length: " . strlen($data) . "\n\n");
  
  // output data
  echo $data;
}

function createZip($file, $string) {
  $zip = new ZipArchive();
  $filename = "./" . $file;

  if ($zip->open($filename, ZIPARCHIVE::CREATE)!==TRUE) {
    exit("cannot open <$filename>\n");
  }

  $zip->addFromString("pls-stojnic.kml", $string);
  
  $zip->close();
}


function array2kml($array) {
  if (is_array($array)) {
    $xml  = "<?xml version='1.0' encoding='UTF-8'?>\n";
    $xml .= "<kml xmlns='http://earth.google.com/kml/2.0'>\n";
    $xml .= "<Document>\n";
    
    $xml .= "  <Style id='styleHighlight'>\n";
    $xml .= "    <IconStyle>\n";
    $xml .= "      <Icon>\n";
    $xml .= "        <href>http://maps.google.com/mapfiles/ms/icons/parkinglot.png</href>\n";
    $xml .= "      </Icon>\n";
    $xml .= "    </IconStyle>\n";
    $xml .= "  </Style>\n";
    
    // file name
    $xml .= "  <name>pls-stojnic.kml</name>\n";
    $xml .= "  <Folder>\n";
    // description
    //$xml .= "<description></description>\n";
    // name
    //$xml .= "<name>" . htmlspecialchars($array[0][0]['country']) . ' - ' . htmlspecialchars($array[0][0]['city']) . "</name>\n";
    //$xml .= "<open>1</open>\n";
    
    foreach ($array[1] as $key=>$value) {
      
      $key = strtolower($key);
      $coordinates = explode(',', $value['coordinates']);
      
      $xml .= "  <Placemark>\n";
      
      $xml .= "    <name>" . $value['name'] . "</name>\n";  //utf8_decode
      $xml .= "    <description><![CDATA[" . "<P>Free parkings: " . $value['free'] . "<BR />Capacity: " . $value['capacity'] . "</P>]]></description>\n";
      $xml .= "    <styleUrl>#styleHighlight</styleUrl>\n";
      
      /*
      $xml .= "    <LookAt id='look" . $value['key'] . "'>\n";
      $xml .= "      <longitude>" . $coordinates[0] . "</longitude>\n";
      $xml .= "      <latitude>" . $coordinates[1] . "</latitude>\n";
      $xml .= "      <heading>-60</heading>\n";
      $xml .= "      <tilt>70</tilt>\n";
      $xml .= "      <range>6300</range>\n";
      $xml .= "    </LookAt>\n";
      */
      
      $xml .= "    <Point id='point" . $value['key'] . "'>\n";
      $xml .= "      <coordinates>" . $coordinates[1] . "," .$coordinates[0] . "</coordinates>\n";
      $xml .= "    </Point>\n";
      
      $xml .= "  </Placemark>\n";
    }
    $xml .= "  </Folder>\n";
    $xml .= "</Document>\n";
    $xml .= "</kml>";
  }
  return $xml;
}



function array2Json($array)
{
  $output = json_encode($array);
  //$output = substr($output, 1, -1);
  return $output."\n";

}

function JSON_print($result, $child = false)
{
 foreach($result as $key => $val)
 {
  $is_child = 0;
  if(is_array($val)){ $AjaxReturn[] = JSON_print($val,true); $is_child++;}
  else $AjaxReturn[] = '\'' . $key . '\' : \'' . $val .'\'';
 }

 $return_json = $is_child > 0? ''.implode(', ',$AjaxReturn).'' : '{'.implode(', ',$AjaxReturn).'}';
 
 if($child) return $return_json;
 else print $return_json;

 exit;
} 


function array2xml($array, $level=1) {
        $xml = '';
   // if ($level==1) {
   //     $xml .= "<array>\n";
   // }
    foreach ($array as $key=>$value) {
        $key = strtolower($key);
        if (is_object($value)) {$value=get_object_vars($value);}// convert object to array
        
        if (is_array($value)) {
            $multi_tags = false;
            foreach($value as $key2=>$value2) {
             if (is_object($value2)) {$value2=get_object_vars($value2);} // convert object to array
                if (is_array($value2)) {
                    $xml .= str_repeat("\t",$level)."<$key>\n";
                    $xml .= array2xml($value2, $level+1);
                    $xml .= str_repeat("\t",$level)."</$key>\n";
                    $multi_tags = true;
                } else {
                    if (trim($value2)!='') {
                        if (htmlspecialchars($value2)!=$value2) {
                            $xml .= str_repeat("\t",$level).
                                    "<$key2><![CDATA[$value2]]>". // changed $key to $key2... didn't work otherwise.
                                    "</$key2>\n";
                        } else {
                            $xml .= str_repeat("\t",$level).
                                    "<$key2>$value2</$key2>\n"; // changed $key to $key2
                        }
                    }
                    $multi_tags = true;
                }
            }
            if (!$multi_tags and count($value)>0) {
                $xml .= str_repeat("\t",$level)."<$key>\n";
                $xml .= array2xml($value, $level+1);
                $xml .= str_repeat("\t",$level)."</$key>\n";
            }
      
         } else {
            if (trim($value)!='') {
             echo "value=$value<br>";
                if (htmlspecialchars($value)!=$value) {
                    $xml .= str_repeat("\t",$level)."<$key>".
                            "<![CDATA[$value]]></$key>\n";
                } else {
                    $xml .= str_repeat("\t",$level).
                            "<$key>$value</$key>\n";
                }
            }
        }
    }
   //if ($level==1) {
    //    $xml .= "</array>\n";
   // }
    return $xml;
}


?>