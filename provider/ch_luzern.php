<?php

// providerList

class provider_ch_luzern 
{
  var $info = array();
  
  function _providerInfo()
  {
    $info = array(
                  'provider'    => 'ch_luzern', 
                  'country'     => 'Switzerland', 
                  'city'        => 'Luzern', 
                  'lastupdate'  => '0',
                  'coordinates' => '47.05,8.3'
                );
    
    return $info;
  }
  
  function _data()
  {
    return $this->parseData();
  }

  function getCapacity($httpContent, $searchPh) 
  {
    $intPos1 = strpos($httpContent, '>' . $searchPh);
    $intPos2 = strpos($httpContent, 'align="right">', $intPos1)+strlen('align="right">');
    $intPos3 = strpos($httpContent, "</td>", $intPos2);
    
    $l_free  = substr($httpContent, $intPos2, $intPos3-$intPos2);
    return $l_free;
  }

  function getFree($httpContent, $searchPh) 
  {
    $intPos1 = strpos($httpContent, '<td>' . $searchPh);
    $intPos2 = strpos($httpContent, 'align="right">', $intPos1)+strlen('align="right">');
    $intPos3 = strpos($httpContent, "</td>", $intPos2);
    
    $l_stat  = substr($httpContent, $intPos2, $intPos3-$intPos2);
    return $l_stat;
  }

  function parseData() {
    include_once './http_request.php';
    $classname     = 'http_request';
    $class         = new $classname;
    $strURLContent = $class->get_contents('http://www.pls-luzern.ch/');
    
    $info['lastupdate'] = time();
    
    if ($strURLContent == false) {
      return array();
    }
    
    $parkings =   $class->convertCSVtoAssocMArray('./provider/ch_luzern_list.csv');
    
    foreach ($parkings as &$value) 
    {
      //$value['capacity']    = "0"; //$this->getCapacity($strURLContent, $value['key']);
      $value['free']        = $this->getFree($strURLContent, $value['key']);
    }

    return $parkings;
  }
}

?>