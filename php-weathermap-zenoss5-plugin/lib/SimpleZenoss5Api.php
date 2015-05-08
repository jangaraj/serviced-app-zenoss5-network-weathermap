<?php
class SimpleZenoss5Api {

    private $curl;
    private $cookieFile = '/tmp/php-weathermap-zenoss5-cookie.txt';
    private $authCookie = '';

	function __construct($api_url, $login, $password, $max_collection_interval, $api_vhost) {
        if(empty($api_url)) 
        {
            $api_url =  'https://127.0.0.1/api/query/performance';
            $api_vhost =  'zenoss5.local';
        }
        if(empty($max_collection_interval))
        {
            $maxCollectionInterval = 300; // 5 minutes * 60
        }
        
        
        //curl_setopt($this->curl, CURLOPT_URL, $api_url);
        /*
        TODO generate ZAuthToken for authentification 
        "Cookie: ZAuthToken=""14305555847684447154168555105"";
        */
        //curl_setopt($this->curl, CURLOPT_COOKIEJAR, $tmpfname);
        //curl_setopt($this->curl, CURLOPT_COOKIEFILE, $this->$cookieFile);
        
        // get authcookie
        $auth = array(
            '__ac_name' => $login,
            '__ac_password' => $password,
            'submitted' => 'true',
            'came_from' => $api_url + '/zport/dmd'
        );
        $this->curl = curl_init($api_url + '/zport/dmd/zport/acl_users/cookieAuthHelper/login');
        curl_setopt($this->cur, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->cur, CURLOPT_HEADER, true);
        if($api_vhost != '')
        {
            curl_setopt($this->curl, CURLOPT_HTTPHEADER, array('Host: '.$api_vhost));
            $auth['came_from'] = 'https://'+$api_vhost+'/api/query/performance';            
        }
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $auth);
        preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', curl_exec($ch), $matches);
        foreach($matches[1] as $cookie) 
        {
            if(strpos($cookie, 'ZAuthToken') !== false) 
            {
                $this->authCookie = $cookie;
            }
        }
        if(empty($this->authCookie))
        {
            wm_debug("Zenoss 5 authetification was not succesfull\n");
            return FALSE; 
        }
        
        // standard header with authCookie
        curl_setopt($this->curl, CURLOPT_URL, $api_url);
        curl_setopt($this->curl, CURLOPT_HEADER, false);
        curl_setopt($this->curl, CURLOPT_COOKIE, $this->authCookie);
		curl_setopt($this->curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($this->curl, CURLOPT_POST, 1);
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
        	
		if($result === null) 
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

?>