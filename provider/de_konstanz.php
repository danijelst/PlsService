<?php

// providerList

class provider_de_konstanz
{
  var $info = array();
  
  function _providerInfo()
  {
    $info = array(
                  'provider'    => 'de_konstanz', 
                  'country'     => 'Germany', 
                  'city'        => 'Konstanz', 
                  'lastupdate'  => '0',
                  'coordinates' => '47.379022,8.541001'
                );
    
    return $info;
  }
  
  function _data()
  {
    return $this->parseData();
  }

  function getCapacity($httpContent, $searchPh) 
  {
    $l_free  = 'n/a';
    return $l_free;
  }

  function getFree($httpContent, $searchPh) 
  {
    $intPos1 = strpos($httpContent, 'Parhaus: ' . $searchPh);
    $intPos2 = strpos($httpContent, 'bold;">', $intPos1)+strlen('bold;">');
    $intPos3 = strpos($httpContent, '</td>', $intPos2);
    
    $l_stat  = substr($httpContent, $intPos2, $intPos3-$intPos2);
    return $l_stat;
  }
  
  function getOpen($httpContent, $searchPh) 
  {
    $l_stat  = 'n/a';
    return $l_stat;
  }

  function parseData() {
    include_once './http_request.php';
    $classname     = 'http_request';
    $class         = new $classname;
    $strURLContent = $class->get_contents('http://www.konstanz.de/tourismus/01759/01765/index.html');
    
    $info['lastupdate'] = time();
    
    if ($strURLContent == false) {
      return array();
    }
    
    $parkings =   $class->convertCSVtoAssocMArray('./provider/de_konstanz_list.csv');
    
    foreach ($parkings as &$value) 
    {
      //$value['capacity']       = trim($this->getCapacity($strURLContent, $value['key']));
      $value['free']           = trim($this->getFree($strURLContent, $value['key']));
      //$value['open_closed']    = trim($this->getOpen($strURLContent, $value['key']));
      $value['key']            = $value['key'];
      $value['name']           = $value['name'];
    }

    return $parkings;
  }
  
}

?>