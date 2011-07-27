<?php

// providerList

class provider_ch_stgallen
{
  var $info = array();
  var $data = array();
  
  function _providerInfo()
  {
    $info = array(
                  'provider'    => 'ch_stgallen', 
                  'country'     => 'Switzerland', 
                  'city'        => 'St. Gallen', 
                  'lastupdate'  => '0',
                  'coordinates' => ''
                );
    
    return $info;
  }
  
  function _data()
  {
    return $this->parseData();
  }

  function getFree($httpContent, $searchPh) 
  {
    $intPos1 = strpos($httpContent, '">' . $searchPh,0);
    $intPos2 = strpos($httpContent, "\"40\"", $intPos1-70)+4;
    $intPos2 = strpos($httpContent, ">", $intPos2)+1;
    $intPos3 = strpos($httpContent, "</td>", $intPos2-1);
    
    $l_stat  = substr($httpContent, $intPos2, $intPos3-$intPos2);
    
    return $l_stat;
  }

  function parseData() {
    include_once './http_request.php';
    $classname     = 'http_request';
    $class         = new $classname;
    
    $strURLContent = $class->get_contents('http://mobile.pls-sg.ch/');
    
    $info['lastupdate'] = time();
    
    if ($strURLContent == false) {
      return array();
    }
    
    $parkings =   $class->convertCSVtoAssocMArray('./provider/ch_stgallen_list.csv');
    
    foreach ($parkings as &$value) 
    {
      $value['free']        = $this->getFree($strURLContent, $value['key']);
    }
    
    return $parkings;
  }
  
}


?>