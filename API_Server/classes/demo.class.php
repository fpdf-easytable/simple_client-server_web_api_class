<?php

class Demo {
	
	private function error($id){
	  $result=array(); 
	   /*      
         ResponseCode =200
         Message=''

         ResponseCode=400,
         Message='Bad Request'

         ResponseCode=404
         Message="No data Found"

         'ResponseCode' = 498;
         'Message' = 'Invalid token';

         'ResponseCode' = 500;
         'Message' = 'SQL ERROR: ' . $e->getMessage();

         */	
	}

   private $name, $last_name, $age;

	public function __construct(){//$name, $lastname, $age) {
		$this->name='Elephant';//$name;
		$this->size='Very big';//$lastname;
		$this->weight='Very heavy';
	}
	
	public function get_data($str){
		$returnarray=array('ResponseCode'=>400,
		                   'ResponseMessage'=>'Bad request', 
		                   'Content'=>'');
		if(isset($this->$str)){
			$returnarray=array('ResponseCode'=>200,
		                   'ResponseMessage'=>'', 
		                   'Content'=>$this->$str);
		                   //throw new zc\Exception('sfsdfsdf');
		}
		return $returnarray;
	}
   
}



?>