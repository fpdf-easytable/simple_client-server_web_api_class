<?php
define('LOG_DIR', '/tmp/');
define('LOG_FILE', 'API_LOG_ERROR');

class WebService{

	protected function class_loader($class){
		/*	
		$file='classes/' . $class . '.class.php';
		if(file_exists($file)){
			include_once $file;
		}else {
			include_once strtolower($file);
		}
		/**/
	}

	protected function outPut(&$response){
		/*
		ob_clean();
		ob_start();
		header('Content-Type: application/json');
		header('Content-Length: ' . strlen($response));
		print $response;
		ob_end_flush();
		/**/
	}

	public function __construct(){
		spl_autoload_register(array($this, 'class_loader'));
	}


	private function HTML_entities(&$array){
		foreach($array as $k=>$v){
			if(is_array($v)) {
				HTML_entities($v);
				$array[$k]=$v;
			}else {
				$array[$k]=html_entity_decode(htmlentities($v, ENT_SUBSTITUTE, "UTF-8"), ENT_QUOTES, "UTF-8");
			}
		}
		return;
	}


	private function write_debugger($data_array){
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



	//protected function shutdown_function(){
	function __destruct(){
		$error=error_get_last();
		//$this->write_debugger(array('fff'));
	//*
		if($error!==NULL){
			$error_codes=array(
				'1'=>"E_ERROR 	1 	A fatal run-time error, that can't be recovered from. The execution of the script is stopped immediately.",
				//2 E_WARNING 	2 	A run-time warning. It is non-fatal and most errors tend to fall into this category. The execution of the script is not stopped.
				'4'=>"E_PARSE 	4 	The compile-time parse error. Parse errors should only be generated by the parser.",
				//8 E_NOTICE 	8 	A run-time notice indicating that the script encountered something that could possibly an error, although the situation could also occur when running a script normally.
				'16'=>"E_CORE_ERROR 	16 	A fatal error that occur during the PHP's engine initial startup. This is like an E_ERROR, except it is generated by the core of PHP.",
				//32 E_CORE_WARNING 	32 	A non-fatal error that occur during the PHP's engine initial startup. This is like an E_WARNING, except it is generated by the core of PHP.
				'64'=>"E_COMPILE_ERROR 	64 	A fatal error that occur while the script was being compiled. This is like an E_ERROR, except it is generated by the Zend Scripting Engine.",
				//128 E_COMPILE_WARNING 	128 	A non-fatal error occur while the script was being compiled. This is like an E_WARNING, except it is generated by the Zend Scripting Engine.
				'256'=>"E_USER_ERROR 	256 	A fatal user-generated error message. This is like an E_ERROR, except it is generated by the PHP code using the function trigger_error() rather than the PHP engine.",
				//512 E_USER_WARNING 	512 	A non-fatal user-generated warning message. This is like an E_WARNING, except it is generated by the PHP code using the function trigger_error() rather than the PHP engine
				//1024 E_USER_NOTICE 	1024 	A user-generated notice message. This is like an E_NOTICE, except it is generated by the PHP code using the function trigger_error() rather than the PHP engine.
				'2048'=>"E_STRICT 	2048 	Not strictly an error, but triggered whenever PHP encounters code that could lead to problems or forward incompatibilities",
				'4096'=>"E_RECOVERABLE_ERROR 	4096 	A catchable fatal error. Although the error was fatal, it did not leave the PHP engine in an unstable state. If the error is not caught by a user defined error handler (see set_error_handler()), the application aborts as it was an E_ERROR.",
				//8192 E_DEPRECATED 	8192 	A run-time notice indicating that the code will not work in future versions of PHP
				//16384 E_USER_DEPRECATED 	16384 	A user-generated warning message. This is like an E_DEPRECATED, except it is generated by the PHP code using the function trigger_error() rather than the PHP engine.
				//32767 E_ALL 	32767 	All errors and warnings, except of level E_STRICT prior to PHP 5.4.0.
			);
			if(!headers_sent() && isset($error_codes[$error['type']])) {
				$this->write_debugger(array("FATAL_ERROR:", $er, $error));
				$response=json_encode(array('ResponseCode'=>500,'Message'=>$error));
				$this->outPut($response);
			}else{
				$this->write_debugger(array("UNKNOWN ERROR:", $error, $_POST));
			}
		}
	}

	public function process_request(){
		$response='';
		$ERROR=array('ResponseCode'=>500,'Message'=>'');
		if(isset($_POST) && isset($_POST['endpoint']) && isset($_POST['params']) && isset($_POST['constructor'])) {
			$tmp=explode('.',trim($_POST['endpoint']));
			if(isset($tmp[1]) && $tmp[0] && $tmp[1]){
				$class=$tmp[0];
				$method=$tmp[1];
				try{
					$reflection=@ new ReflectionClass($class);
					$instance_class=$reflection->newInstanceArgs(json_decode($_POST['constructor'],true));
				
					if(method_exists($instance_class,$method)){	
						$method_test = new ReflectionMethod($class, $method);
						if($method_test->isPublic()) {
							$response=call_user_func_array(array($instance_class, $method), array_values(json_decode($_POST['params'],true)));
							if(is_array($response)) {
								$this->HTML_entities($response);
								$response=json_encode($response);
							}
							else{
								$ERROR['Message']='The result of the method is not an array';
							}
						}else {
							$ERROR['Message']='Unreachable method';
						}
					}else {
						$ERROR['Message']='Unreachable method';
					}
				}
				catch (Exception $e){
					//$this->write_debugger(array($e));
					$ERROR['Message']=$e->getMessage();
				}
				
			}
			else{
				$ERROR['Message']='Endpoint:' . $_POST['endpoint'] . ', is invalid.';		
			}
		}else {
			$ERROR['Message']='Incomplete data.';
		}

		if($response==''){
			$response=json_encode($ERROR);
		}
		$this->outPut($response);
	}
}

class WebService2 extends WebService{

	protected function class_loader($class){
		$file=strtolower('classes/' . $class . '.class.php');
		if(file_exists($file)){
			include_once $file;
		}else {
			throw new Exception("Class '{$class}' not found");
		}
	}

	protected function outPut(&$response){
		ob_clean();
		ob_start();
		header('Content-Type: application/json');
		header('Content-Length: ' . strlen($response));
		print $response;
		ob_end_flush();
	}
}


$web_service=new WebService2();
//echo 'dddd';

$web_service->process_request();

//
?>