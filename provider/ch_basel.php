<?php

// providerList

class provider_ch_basel
{
  var $info = array();
  var $data = array();
  
  function _providerInfo()
  {
    $info = array(
                  'provider'    => 'ch_basel', 
                  'country'     => 'Switzerland', 
                  'city'        => 'Basel', 
                  'lastupdate'  => '0',
                  'coordinates' => '47.56038,7.581196'
                );
    
    return $info;
  }
  
  function _data()
  {
    return $this->parseData();
  }

  function getFree($httpContent, $searchPh) 
  {
    $intPos1 = strpos($httpContent, 'parkhaus/' . $searchPh . '.php',0);
    $intPos2 = strpos($httpContent, "'text'><b>", $intPos1)+10;
    $intPos3 = strpos($httpContent, "</b>", $intPos2);
    
    $l_stat  = substr($httpContent, $intPos2, $intPos3-$intPos2);
    return $l_stat;
  }
  
  function getOpen($httpContent, $searchPh) 
  {
    $intPos1 = strpos($httpContent, 'parkhaus/' . $searchPh . '.php',0);
    $intPos2 = strpos($httpContent, "'text'>", $intPos1)+7;
    $intPos3 = strpos($httpContent, "</td>", $intPos2);
    
    $l_stat  = substr($httpContent, $intPos2, $intPos3-$intPos2);
    return $l_stat;
  }

  function parseData() {
    include_once './http_request.php';
    $classname     = 'http_request';
    $class         = new $classname;
    
    $strURLContent = $class->get_contents('http://www.parkleitsystem-basel.ch/status.php');
    
    $info['lastupdate'] = time();
    
    if ($strURLContent == false) {
      return array();
    }
    
    $parkings =   $class->convertCSVtoAssocMArray('./provider/ch_basel_list.csv');
    
    foreach ($parkings as &$value) 
    {
      $value['free']        = $this->getFree($strURLContent, $value['key']);
      $value['open_closed'] = $this->getOpen($strURLContent, $value['key']);
    }
    
    return $parkings;
  }
  
}


?>