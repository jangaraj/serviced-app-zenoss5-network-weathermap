<?php
class SimpleZenoss5Api {

    private $curl;

	function __construct($api_url, $max_collection_interval, $api_vhost) {
        if(empty($api_url)) 
        {
            $api_url =  'https://127.0.0.1/api/query/performance';
            $api_vhost =  'zenoss5.local';
        }
        if(empty($max_collection_interval))
        {
            $maxCollectionInterval = 300; // 5 minutes * 60
        }
        
        $this->curl = curl_init($api_url);
		curl_setopt($this->curl, CURLOPT_URL, $api_url);
		curl_setopt($this->curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        /*
        TODO generate ZAuthToken for authentification 
        "Cookie: ZAuthToken=""14305555847684447154168555105"";
        */
        if($api_vhost != '')
        {
            curl_setopt($this->curl, CURLOPT_HTTPHEADER, array('Host: '.$api_vhost));
        }
        
		curl_setopt($this->curl, CURLOPT_POST, 1);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
	}

	function jsonRequest($data) {
		$json_data = json_encode($data);	
		curl_setopt($this->curl, CURLOPT_POSTFIELDS, $json_data);		
		$response = curl_exec($this->curl);
        if($response === FALSE)
        {
            wm_debug("Problem with Zenoss 5 API request: ".$response.")\n");
            return null;
        }        
        
		return json_decode($response, true);
	}

    function closeCurl() {
        curl_close($this->curl);
    }    

	function getLastValue($host, $key) {
        $now = time() * 1000;
		$data = array(
            'start' => $now,
            'end' => $now - $this->maxCollectionInterval,
            'returnset' => 'LAST',
            'metrics' => array(
                array(
                    'metric' => $host.'/'.$key,
                    'aggregator' => 'avg', 
                )
            )
        );        
        $result = $this->jsonRequest($data);
        	
		if ($result === null) 
        {
			return null;
		} 
        else 
        {
            $response_data = json_decode($response, TRUE);
            if(!isset($response_data['results']['datapoints']) || !isset($response_data['results']['datapoints'][0]))
            {
                return null;
            } 
            else 
            {
                return array($response_data['results']['datapoints'][0]['value'], $response_data['results']['datapoints'][0]['timestamp']);                
            }
		}
	}
}
