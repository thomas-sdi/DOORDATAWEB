<?php
/*************************************************************************************************************
	 *  *  *  *  *  *  *  *  *  *  *   I D E A V A T E   S O L U T I O N S   *  *  *  *  *  *  *  *   *
**************************************************************************************************************
	 * Filename				 :database.php
	 * Description 		  	 :DATABASE CLASS
	 * External Files called :config.php
	 * Global Variables	  	 :$DB
	 * 
	 * Modification Log
	 * Date:20 SEPT           Author:IDEAVATE SOLUTIONS                       		Description
	 * --------------------------------------------------------------------------------------------------------
	 * 
 **************************************************************************************************************/  
//THIS WILL INCLUDE THE CONFIGURATION FILE
require_once("config.php");

//DATABASE  CLASS:STARTS
class Database {
	
	private $connection;
	public  $last_query;
	private $magic_quotes_active;
	private $real_escape_string_exists;
	private $error_message;
	
	/*
		NAME		 :__construct
		FUNCTIONALITY:Automatically called whenever the object is created
		ATTRIBUTES	 :NA
		RETURN		 :NA
	*/
	function __construct() {
    	$this->open_connection();
		$this->magic_quotes_active = get_magic_quotes_gpc();
		$this->real_escape_string_exists = function_exists( "mysql_real_escape_string" );
		$this->error_message = '';
	}

	/*
		NAME		 :open_connection
		FUNCTIONALITY:Open the connection for mysql database
		ATTRIBUTES	 :NA
		RETURN		 :TRUE/FALSE(WITH PROPER JSON RESPONSE)
	*/
	public function open_connection() {
		$this->connection = mysqli_connect(DB_SERVER, DB_USER, DB_PASS,DB_NAME);
		if (!$this->connection) {
			$this->json_response_basic(ERROR_CODE,"Database connection failed");
		}

		mysqli_set_charset($this->connection,"utf8mb4");

        
        /*else {
			$db_select = mysql_select_db(DB_NAME, $this->connection);
			if (!$db_select) {
				$this->json_response_basic(ERROR_CODE,"Database selection failed");
			}
		}*/
        
	}
	
	public function get_error_message(){
		return $this->error_message;
	}

	/*
		NAME		 :close_connection
		FUNCTIONALITY:close the connection for the mysql database
		ATTRIBUTES	 :NA
		RETURN		 :NA
	*/
	public function close_connection() {
		if(isset($this->connection)) {
			mysqli_close($this->connection);
            //$this->connection->close();
			unset($this->connection);
		}
	}

	/*
		NAME		 :query
		FUNCTIONALITY:Run the query
		ATTRIBUTES	 :Query to run
		RETURN		 :Result of the query
	*/
	public function query($sql) {
		$this->last_query = $sql;
		//$result = mysql_query($sql, $this->connection);
        
        $result = $this->connection->query($sql);
		if (!$result){
            $this->error_message = $this->connection->connect_error;
			//$this->error_message = mysql_error();
		}
		//$this->confirm_query($result);
		return $result;
	}
	
	/*
		NAME		 :escape_value
		FUNCTIONALITY:Clean the values so as to store in the database
		ATTRIBUTES	 :Value to clean
		RETURN		 :Cleaned value
	*/
	public function escape_value( $value ) {
		$value=trim($value);
		if( $this->real_escape_string_exists ) { // PHP v4.3.0 or higher
			// undo any magic quote effects so mysql_real_escape_string can do the work
			if( $this->magic_quotes_active ) { $value = stripslashes( $value ); }
            
            $value = mysqli_real_escape_string($this->connection,$value); //mysql_real_escape_string( $value );
		} else { // before PHP v4.3.0
			// if magic quotes aren't already on then add slashes manually
			if( !$this->magic_quotes_active ) { $value = addslashes( $value ); }
			// if magic quotes are active, then the slashes already exist
		}
		return $value;
	}

	public function mysqli_escape_value( $value ) {
            return mysqli_real_escape_string($this->connection,$value); //mysql_real_escape_string( $value );		
	}

	
	// "database-neutral" methods
	public function fetch_array($result_set) {
	    return mysqli_fetch_array($result_set);
	}

	public function fetch_assoc($result_set) {
	    return mysqli_fetch_assoc($result_set);
	}
  
	public function num_rows($result_set) {
		return mysqli_num_rows($result_set);
	}
  
	public function insert_id() {
	    // get the last id inserted over the current db connection
  	  return mysqli_insert_id($this->connection);
	}
  
	public function affected_rows() {
	    return mysqli_affected_rows($this->connection);
	}

	/*
		NAME		 :confirm_query
		FUNCTIONALITY:Confrim the query for execution
		ATTRIBUTES	 :Query
		RETURN		 :FALSE(JSON RESPONSE WITH PROPER MESSAGE)
	*/
	private function confirm_query($result) {
		if (!$result) {
			$output = "We are not able to process your request, Please try again later";
			//$output .= "Last SQL query: " . $this->last_query;
			$this->json_response_basic(ERROR_CODE,$output);
		}
	}

	/*
		NAME		 :json_reponse
		FUNCTIONALITY:Return a json response to devices
		ATTRIBUTES	 :success msg,error msg,third parameter is option(for extra usage)
		RETURN		 :JSON 
	*/
	public function json_response_basic($success,$error){
		$arrayResponseError =array(
									'success'=> $success, 
									'message'=> $error
								);
		$jsonResponseError = json_encode($arrayResponseError);
		
		if ($success <> SUCCESS_CODE){
			error_log("DoorData API error: " . $jsonResponseError . "\n", 0);
		}
		
		echo $jsonResponseError;
		
	/*	echo "<br/>";
		
		echo print_r(json_decode($jsonResponseError));*/
		
		exit;
	}
	public function json_response_adv($data){
		echo $jsonResponseError = json_encode($data);
		exit;
	}

	
}
//DATABASE CLASS:ENDS

//DATABASE CLASS OBJECT CREATTION
$DB = new Database();

?>
