<?php
// PHP Weathermap 0.9 pluggable datasource for Zenoss 5 
// reads a pair of values from the Zenoss 5 by using Zenoss Query library https://github.com/zenoss/query

// TARGET zenoss5:host:in:out

require_once(__DIR__."/../SimpleZenoss5Api.php");

class WeatherMapDataSource_zenoss5 extends WeatherMapDataSource {

	private $zenoss5Api;
    
	function Init(&$map)
	{
        if(function_exists('curl_version'))
        {
            wm_debug("Zenoss 5 Init: PHP curl extension is not installed\n");
            return FALSE;
        }
		$this->zenoss5Api = new SimpleZenoss5Api($map->get_hint('zenoss5_api_url'), $map->get_hint('zenoss5_login'), $map->get_hint('zenoss5_password'), $map->get_hint('max_collection_interval'), $map->get_hint('zenoss5_api_vhost'));
        return TRUE;
	}

	function Recognise($targetstring)
	{ 
		if(preg_match("/^zenoss5:([-a-zA-Z0-9_\.\/\[\]]+):([-a-zA-Z0-9_\.\/\[\]]+):([-a-zA-Z0-9_\.\/\[\]]+):([-a-zA-Z0-9_\.\/\[\]]+)$/", $targetstring, $matches))
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	function ReadData($targetstring, &$map, &$item)
	{
        $data[IN] = $data[OUT] = -1;
        $data_time = 0;

		if(preg_match("/^zenoss5:([-a-zA-Z0-9_\.\/\[\]]+):([-a-zA-Z0-9_\.\/\[\]]+):([-a-zA-Z0-9_\.\/\[\]]+):([-a-zA-Z0-9_\.\/\[\]]+)$/", $targetstring, $matches))
		{       
            list($data[IN], $data_time)  = $this->zenoss5Api->getLastValue($matches[2], $matches[3]);
            list($data[OUT], $data_time) = $this->zenoss5Api->getLastValue($matches[2], $matches[4]);
		}

		wm_debug("Zenoss 5 ReadData: Returning (".($data[IN]===null?'null':$data[IN]).",".($data[OUT]===null?'null':$data[IN]).",$data_time)\n");
		return (array($data[IN], $data[OUT], $data_time));
	}
}

?>