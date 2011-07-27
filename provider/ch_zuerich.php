<?php

// providerList

class provider_ch_zuerich 
{
  var $info = array();
  
  function _providerInfo()
  {
    $info = array(
                  'provider'    => 'ch_zuerich', 
                  'country'     => 'Switzerland', 
                  'city'        => 'Zurich', 
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
    $intPos1 = strpos($httpContent, '<title>' . $searchPh);
    $intPos2 = strpos($httpContent, '<description>', $intPos1)+strlen('<description>');
    $intPos2 = strpos($httpContent, '/', $intPos2)+strlen('/');
    $intPos3 = strpos($httpContent, '</description>', $intPos2);
    
    $l_stat  = substr($httpContent, $intPos2, $intPos3-$intPos2);
    return $l_stat;
  }
  
  function getOpen($httpContent, $searchPh) 
  {
    $intPos1 = strpos($httpContent, '<title>' . $searchPh);
    $intPos2 = strpos($httpContent, '<description>', $intPos1)+strlen('<description>');
    $intPos3 = strpos($httpContent, '/', $intPos2);
    
    $l_stat  = substr($httpContent, $intPos2, $intPos3-$intPos2);
    return $l_stat;
  }

  function parseData() {
    include_once './http_request.php';
    $classname     = 'http_request';
    $class         = new $classname;
    $strURLContent = $class->get_contents('http://www.pls-zh.ch/plsFeed/rss');
    
    $info['lastupdate'] = time();
    
    if ($strURLContent == false) {
      return array();
    }
    
    $parkings =   $class->convertCSVtoAssocMArray('./provider/ch_zuerich_list.csv');
    
    foreach ($parkings as &$value) 
    {
      //$value['capacity']       = trim($this->getCapacity($strURLContent, $value['key']));
      $value['free']           = trim($this->getFree($strURLContent, $value['key']));
      //$value['open_closed']    = trim($this->getOpen($strURLContent, $value['key']));
      $value['key']            = str_replace("&uuml;", "u", mb_convert_encoding($value['key'], 'HTML-ENTITIES', 'UTF-8'));
      $value['name']           = str_replace("&uuml;", "u", $value['name']);
    }

    return $parkings;
  }
  
}

?>