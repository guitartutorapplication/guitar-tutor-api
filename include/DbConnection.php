<?php
/**
 * Database connection
 */
class DbConnection {
 
    private $conn; // database connection link
 
    /**
     * Constructor
    */
    function __construct() 
    {    
        
    }
    
    /**
     * Connect to database
     * @return database connection link
     */
    function connect() 
    {
        // include database configuration
        include_once dirname(__FILE__) . '/Config.php';
        
        // connect to mysql database
        $this->conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
        
        // check for connection error
        if (mysqli_connect_errno()) {
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
        }
 
        // return connection link 
        return $this->conn;
    }   
}