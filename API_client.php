<?php
define('API_LINK', 'http://localhost/API/API_Server/API_Server.php');
define('LOG_DIR', '/tmp/');
define('LOG_FILE', 'API_CLIENT_LOG_ERROR');
define('TIMEOUT', 30);
	
class API_Client{

	private $status_code,
			  $url,
			  $curl_options,
			  $post_data,
			  $response_headers,
			  $body;

	private function write_log($data_array){
		$h=fopen(LOG_DIR . LOG_FILE, 'a');
		fwrite($h, date("l jS \of F Y h:i:s A"). "\n");
		foreach($data_array as $data){
			if(is_object($data) || is_array($data)){
			  fwrite($h, var_export($data, true) . "\n");
			}
			else{
			  fwrite($h, "$data\n");
			}
		}
		fwrite($h, "*************************\n");
	  	fclose($h);
	}

	public function __construct($curl_opts=null){
		if(defined(API_LINK)) {
			$this->url=API_LINK;
		}

  		$this->curl_options=array(
				CURLOPT_URL => API_LINK,
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
			
			//$this->write_log(array($this->status_code));
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
				//$this->write_log(array('dddd'));
			}
		}else {
			$result['Message']='Error Code: ' . curl_errno($ch) . ' Error: ' . curl_error($ch);
			//$this->write_log(array('Last Error:', error_get_last(), 'API CURL ERROR:', $result['CurlErrorCode'], $result['CurlErrorResponse']));
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


$API=new API_Client();







?>