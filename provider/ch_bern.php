<?php

// providerList

class provider_ch_bern
{
  var $info = array();
  var $data = array();
  
  function _providerInfo()
  {
    $info = array(
                  'provider'    => 'ch_bern', 
                  'country'     => 'Switzerland', 
                  'city'        => 'Bern', 
                  'lastupdate'  => '0',
                  'coordinates' => '46.951081,7.438637'
                );
    
    return $info;
  }
  
  function _data()
  {
    return $this->parseData();
  }

  function getCapacity($httpContent, $searchPh) 
  {
    $intPos1 = strpos($httpContent, 'ph=' . $searchPh);
    $intPos1 = strpos($httpContent, 'ph=' . $searchPh, $intPos1 + 1);
    $intPos2 = strpos($httpContent, 'size="2">', $intPos1)+9;
    $intPos3 = strpos($httpContent, "</font>", $intPos2);
    
    $l_free  = substr($httpContent, $intPos2, $intPos3-$intPos2);
    return $l_free;
  }

  function getFree($httpContent, $searchPh) 
  {
    $intPos1 = strpos($httpContent, 'ph=' . $searchPh,0);
    $intPos1 = strpos($httpContent, 'ph=' . $searchPh, $intPos1+1);
    $intPos2 = strpos($httpContent, "color=", $intPos1)+16;
    $intPos3 = strpos($httpContent, "</font>", $intPos2);
    
    $l_stat  = substr($httpContent, $intPos2, $intPos3-$intPos2);
    return $l_stat;
  }

  function parseData() {
    include_once './http_request.php';
    $classname     = 'http_request';
    $class         = new $classname;
    
    $strURLContent = $class->get_contents('http://www.parking-bern.ch/listpark.asp');
    
    $info['lastupdate'] = time();
    
    if ($strURLContent == false) {
      return array();
    }
    
    $parkings =   $class->convertCSVtoAssocMArray('./provider/ch_bern_list.csv');
    
    foreach ($parkings as &$value) 
    {
      $value['capacity']    = $this->getCapacity($strURLContent, $value['key']);
      $value['free']        = $this->getFree($strURLContent, $value['key']);
    }
    
    return $parkings;
  }
  
}


?>