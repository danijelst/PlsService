<?php

include_once './output_formatter.php';

function cmp($a, $b)
{
  return strcmp($a['distance'], $b['distance']);
}

//expects another instance of Coordinate
//used http://www.meridianworlddata.com/Distance-Calculation.asp for formula
function distanceTo($from, $to)
{
   $distance = 6378.7;
   $radians_conv = 57.2958;   // is 180/pi and is used to convert latitude or longitude from degrees to radians.
   
   $fromArray = explode(',', $from);
   $toArray   = explode(',', $to);
   
   $lat1 = $fromArray[0]/$radians_conv;
   $lon1 = $fromArray[1]/$radians_conv;
   
   $lat2 = $toArray[0]/$radians_conv;
   $lon2 = $toArray[1]/$radians_conv;
   
   return $distance * acos(sin($lat1) * sin($lat2) + cos($lat1) * cos($lat2) * cos($lon2 - $lon1));
}

function recursive_array_replace($find, $replace, &$data) {
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                recursive_array_replace($find, $replace, $data[$key]);
            } else {
                $data[$key] = utf8_encode(str_replace($find, $replace, $value));
            }
        }
    } else {
        $data = utf8_encode(str_replace($find, $replace, $data));
    }
} 

function _listProviders($filter_country, $filter_city, $filter_provider, $coordinates)
{
  $providerList = array();
  // file sysstem loop trought provider folder 
  $provider_dir = dir('provider');
  /*echo strtolower($filter_country).'<br>';
  echo strtolower($filter_city).'<br>';
  echo strtolower($filter_provider).'<br>';
  echo strtolower($coordinates).'<br><br><br>';*/
  
  while (false !== ($provider_file = $provider_dir->read())) 
  {
    if (is_file('./provider/' . $provider_file) && end(explode(".", $provider_file)) == 'php')
    {
      include_once './provider/' . $provider_file;
      
      $provider_class_name = str_replace('-', '_', $provider_file);
      $provider_class_name = 'provider_' . str_replace('.php', '', $provider_class_name);
      
      // Check that the class exists before trying to use it
      if (class_exists($provider_class_name)) 
      {
        
        $class_methods = get_class_methods($provider_class_name);
        if (in_array('_providerInfo', $class_methods))
        {
          $class         = new $provider_class_name;
          $provider_info = $class->_providerInfo();
          //echo strtolower($provider_info['country']).'<br>';
          if (
              (strtolower($provider_info['country'])  == strtolower($filter_country) 
               or $filter_country == '') 
          and (strtolower($provider_info['city'])     == strtolower($filter_city) 
               or $filter_city == '')
          and (strtolower($provider_info['provider']) == strtolower($filter_provider) 
               or $filter_provider == '')
          ) {
            //echo 'drin';
            array_push($providerList, $provider_info);
          }
        }
      }
    }
  }
  
  if ($coordinates != '')
  {
    foreach ($providerList as &$city) 
    {
      $value                = $city['coordinates'];
      $distance             = distanceTo($coordinates, $value);
      $city['distance']     = $distance;
    }
    usort($providerList, "cmp");
  }
  
  return $providerList;
}

function _providerData($providerName, $coordinates)
{
  $return        = array();
  $data          = array();
  $providerList  = _listProviders('', '', $providerName, '');
  $provider_file = $providerName . '.php';
  
  if (is_file('./provider/' . $provider_file))
  {
    include_once './provider/' . $provider_file;
    
    $provider_class_name = str_replace('-', '_', $provider_file);
    $provider_class_name = 'provider_' . str_replace('.php', '', $provider_class_name);
    
    // Check that the class exists before trying to use it
    if (class_exists($provider_class_name)) 
    {
      $class_methods = get_class_methods($provider_class_name);
      if (in_array('_data', $class_methods))
      {  
        $class         = new $provider_class_name;
        $data = $class->_data();
      }
    }
  }
  
  if ($coordinates != '')
  {
    foreach ($data as &$parking) 
    {
      $value                = $parking['coordinates'];
      $distance             = distanceTo($coordinates, $value);
      $parking['distance']  = $distance;
    }
    usort($data, "cmp");
  }
  
  array_push($return, $providerList);
  array_push($return, $data);
  
  return $return;
}


function logAccess() {
  $data = array();
  
  /*
  define("WURFL_DIR", "../util/WURFL/");
  define("RESOURCES_DIR", "../util/");

  require_once WURFL_DIR . 'Application.php';

  $wurflConfigFile = RESOURCES_DIR . 'wurfl-config.xml';
  $wurflConfig = new WURFL_Configuration_XmlConfig($wurflConfigFile);
  $wurflManagerFactory = new WURFL_WURFLManagerFactory($wurflConfig);
  $wurflManager = $wurflManagerFactory->create();
  $requestingDevice = $wurflManager->getDeviceForHttpRequest($_SERVER);
  
  $key = "device_os";
  array_push($data, array($key => $requestingDevice->getCapability($key)));
  $key = "model_name";
  array_push($data, array($key => $requestingDevice->getCapability($key)));
  $key = "device_os_version";
  array_push($data, array($key => $requestingDevice->getCapability($key)));
  $key = "has_qwerty_keyboard";
  array_push($data, array($key => $requestingDevice->getCapability($key)));
  $key = "pointing_method";
  array_push($data, array($key => $requestingDevice->getCapability($key)));
  $key = "is_tablet";
  array_push($data, array($key => $requestingDevice->getCapability($key)));
  $key = "resolution_height";
  array_push($data, array($key => $requestingDevice->getCapability($key)));
  $key = "resolution_width";
  array_push($data, array($key => $requestingDevice->getCapability($key)));
  $key = "resolution_width";
  array_push($data, array($key => $requestingDevice->getCapability($key)));
  $key = "resolution_width";
  array_push($data, array($key => $requestingDevice->getCapability($key)));
  */
  
  array_push($data, array('date' => gmdate('Y-m-d H:i:s')));
  array_push($data, array('location' => $_REQUEST['ll']));
  array_push($data, array('ip' => $_SERVER['REMOTE_ADDR']));
  array_push($data, array('user_agent' => $_SERVER['HTTP_USER_AGENT']));
  array_push($data, array('q1' => $_REQUEST['q1']));
  array_push($data, array('q2' => $_REQUEST['q2']));
  array_push($data, array('q3' => $_REQUEST['q3']));
  array_push($data, array('func' => $_REQUEST['func']));
  array_push($data, array('format' => $_REQUEST['format']));
  
  $msg           = array2json($data);
  $filename      = 'requests/request_'.gmdate('Ymd').'.log';
  $fp = fopen($filename,"a+");
  fwrite($fp, $msg."\n");
  fclose($fp);
  
  return true;
}


  $param_q1     = $_REQUEST['q1'];
  $param_q2     = $_REQUEST['q2'];
  $param_q3     = $_REQUEST['q3'];
  $param_ll     = $_REQUEST['ll'];
  $param_func   = $_REQUEST['func'];
  $param_format = $_REQUEST['format'];
  $param_v      = $_REQUEST['v'];
  $param_k      = $_REQUEST['k'];
  
  if ($param_q1 != '' 
   or $param_q2 != '' 
   or $param_q3 != '' 
   or $param_ll != '' 
   or $param_func != '' 
   or $param_format != '' 
   or $param_k != ''
   or $param_v != '') {
    $l_homepage = false;
  } else {
    $l_homepage = true;
  }
  
  if ($l_homepage == false) {
    // A bug in PHP < 5.2.2 makes $HTTP_RAW_POST_DATA not set by default,
    // but we can do it ourself.
    if ( !isset( $HTTP_RAW_POST_DATA ) ) {
      $HTTP_RAW_POST_DATA = file_get_contents( 'php://input' );
    }

    // fix for mozBlog and other cases where 
    if ( isset($HTTP_RAW_POST_DATA) )
      $HTTP_RAW_POST_DATA = trim($HTTP_RAW_POST_DATA);

    logAccess(); 
    
    if ($param_v == '' 
     or $param_v == '1') {
      switch ($param_func) {
        case 'country':
          $new_array = array();
          
          $providers = _listProviders($param_q1, $param_q2, '', $param_ll);
          foreach ($providers as &$value) 
          {
            array_push($new_array, $value['country']);
          }
          $result = array_unique($new_array);
          break;
        case 'cities':
          $result = _listProviders($param_q1, $param_q2, '', $param_ll);
          break;
        case 'parkings':
          $providers = _listProviders($param_q2, $param_q3, '', $param_ll);
          foreach ($providers as &$value) 
          {
            $result = _providerData($value['provider'], $param_ll);
          }
          break;
        default:
          if ($param_q1 == '') {
            if ($param_q2 != '' OR $param_q3 != '') 
            {
              $providers = _listProviders($param_q2, $param_q3, '', $param_ll);
              foreach ($providers as &$value) 
              {
                $result = _providerData($value['provider'], $param_ll);
              }
            } elseif ($param_ll != '') {
              $providers = _listProviders('', '', '', $param_ll); 
              foreach ($providers as &$value) 
              {
                $result = _providerData($value['provider'], $param_ll);
                break;
              }
            }
          } else {
            $result = _providerData($param_q1, $param_ll);
          }
      }

      recursive_array_replace("", "", $result);

      if (count($result) >= 1) {
        switch ($param_format) {
          case 'xml':
            echo (array2xml($result, 1));
            break;
          case 'php': 
            print_r($result);
            break;
          case 'kml': 
            header('Content-Type: application/xml; charset=UTF-8');
            echo array2kml($result);
            break;
          case 'kmz': 
            createZip('./cache/pls-stojnic.kmz', array2kml($result));
            streamFile('./cache/pls-stojnic.kmz');
            break;
          case 'json': 
            header("Content-type: text/plain");
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: Fri, 07 Jul 2000 01:00:00 GMT');
            echo '[' . (JSON_print($result, FALSE)) . ']';
            break;
          default:
            $return = array2json($result);
            header('Accept-Ranges:bytes');
            header('Content-Length: '.strlen($return)); 
            header('Content-type: application/json');
            //header("Content-type: text/plain");
            //header('Cache-Control: no-cache, must-revalidate');
            //header('Expires: Fri, 07 Jul 2000 01:00:00 GMT');
            echo $return;
        }
      }
    } else {
      // new version of getting data 
      include_once "cron/func_update.php";
      include_once "cron/func_database_begin.php";

      registerSession($conn, $param_k);
      
      if (sessionValid($conn)) {
        // find nearest location 
        switch ($param_func) {
          case 'country': 
            // return list of all supported countries
            break;
          case 'cities':
            // return list of all cities from that country 
            if ($param_q1 == '') {
              // default city is lucerne
              $param_q1 = 8;
            }
            // get data
            // TODO 
            break;
          default:
            if ($param_ll != '') {
              // identify nearest city 
              // TODO 
              $param_q1 = 8;
              // lookup for city 
              $cities = getCitites($conn, '', $param_q1);
              $output = getCitiesData($conn, $cities, true, false);
            }
        }
      } else {
        // return error for invalid key 
        // TODO
        $output = array("error");
      }
      
      // format output 
      // TODO 
      print_r($output);
    }
  }
?>