<?php
 /*********************************************************************
 * Web API Client                                                 *
 *                                                                    *
 * Version: 1.0                                                       *
 * Date:    02-10-2018                                                *
 * Author:  Dan Machado                                               *
 * Require  php 5.6 or above                                          *
 **********************************************************************/

define('LOG_DIR', '/tmp/');
define('LOG_FILE', 'API_CLIENT_LOG_ERROR');
define('TIMEOUT', 30);
	
class API_Client{

	private $status_code,
			  $curl_options,
			  $post_data,
			  $response_headers,
			  $body;

	public function __construct($URL, $curl_opts=null){
  		$this->curl_options=array(
				CURLOPT_URL => $URL,
				CURLOPT_HEADER=>true,
				CURLOPT_SSL_VERIFYPEER=>false,
				CURLOPT_HTTPHEADER=>array('Expect:'),
				CURLOPT_FOLLOWLOCATION=>true,
				CURLOPT_RETURNTRANSFER=>true,
				CURLOPT_ENCODING=>"", // to accept all supported encoding types
				CURLOPT_TIMEOUT=>TIMEOUT,  // in seconds
				CURLOPT_POST=>1
			  );		

		if(is_array($curl_opts)){
			foreach($curl_opts as $opt=>$val){
				$this->curl_options[$opt]=$val;
			}
		}

		$this->status_code=200;
		$this->response_headers=array();
	}


	public function Resquest($endpoint, $params, $const_param=null){
		$result=array('ResponseCode'=>500,'Message'=>'', 'Content'=>'');
		
		$this->post_data=array('endpoint'=>$endpoint, 'params'=>json_encode($params), 'constructor'=>'[]');
		if(is_array($const_param)){
			$this->post_data['constructor']=json_encode($const_param);
		}
		
		$ch = curl_init();
		$this->curl_options[CURLOPT_POSTFIELDS]=$this->post_data;
		curl_setopt_array($ch, $this->curl_options);
		$response=curl_exec($ch);
		
		if(curl_errno($ch)==0 ){
			$this->status_code=curl_getinfo($ch, CURLINFO_HTTP_CODE);
			
			$response=explode("\r\n\r\n", $response);
			$header=$response[0];
			$tmp=explode("\r\n", $header);
			for($i=1; $i<count($tmp); $i++){
				$h=explode(':', $tmp[$i]);
				$this->response_headers[$h[0]]=trim($h[1]);
	 		}

			$this->body=trim($response[1]);
			if(!$result=@ json_decode($this->body, true)){
				$result['ResponseCode']=$this->status_code;
				$result['Meta']=$this->response_headers;
			}
		}else {
			$result['Message']='Error Code: ' . curl_errno($ch) . ' Error: ' . curl_error($ch);
		}

		curl_close($ch);

		return $result;
	}

	public function HTTP_StatusCode(){
		return $this->status_code;
	}
	
	public function get_HTTP_headers(){
		return $this->response_headers;
	}
}









?>