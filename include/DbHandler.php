<?php
/* 
 * Handles database operations
 */
class DbHandler {
     
    private $conn;
 
    /**
     * Constructor
    */
    function __construct() 
    {   
        // getting DbConnection.php file
        require_once dirname(__FILE__) . '/DbConnection.php';
        
        // opening database connection
        $db = new DbConnection();
        $this->conn = $db->connect();
    }
    
    /** 
     * Retrieving all user's chords
     */
    public function getUserChords($user_id)
    {
        $stmt = $this->conn->prepare("SELECT user_chord.chord_id AS CHORD_ID FROM "
                . "user LEFT OUTER JOIN user_chord ON (user.user_id = "
                . "user_chord.user_id) WHERE user.user_id = ?");
        
        // insert parameters into sql statement
        $stmt->bind_param("s", $user_id);
        
        $stmt->execute();       
        $user_chords = $stmt->get_result();
        $stmt->close();
        
        return $user_chords;
    }
    
    /**
     * Retrieving all chords
     */
    public function getAllChords() 
    {
        $stmt = $this->conn->prepare("SELECT * FROM chord");
        $stmt->execute();       
        $chords = $stmt->get_result();
        $stmt->close();
        
        return $chords;
    }
    
    /**
     * Retrieving all songs
     */
    public function getAllSongs() 
    {
        $stmt = $this->conn->prepare("SELECT song.*, GROUP_CONCAT(song_chord.chord_id) "
                . "AS CHORDS FROM song INNER JOIN song_chord ON song_chord.SONG_ID "
                . "= song.SONG_ID GROUP BY song.SONG_ID");

        $stmt->execute();       
        $songs = $stmt->get_result();
        $stmt->close();
        
        return $songs;
    }
    
    /* *
     * Creating a new user
     * @param string $name user's first name
     * @param string $email user's email address
     * @param string $password user's password
     */
    public function createUser($name, $email, $password) 
    {
        // check if user is already registered
        if (!$this->userExists($email)) {
            $stmt = $this->conn->prepare("INSERT INTO user(name, email,"
                    . " password, level, achievements) VALUES(?, ?, ?, "
                    .DEFAULT_LEVEL. ", " .DEFAULT_ACHIEVEMENTS. ")");
            
            // insert parameters into sql statement
            $stmt->bind_param("sss", $name, $email, $password);
            
            $result = $stmt->execute();
            $stmt->close();
            
            // check registration is successful 
            if ($result) 
            {
                return USER_SUCCESSFULLY_REGISTERED;
            }
            else 
            {
                return USER_REGISTRATION_FAILED;           
            }         
        }
        else 
        {
            // returns that user is already registered with requested email address
            return USER_ALREADY_REGISTERED;
        }
    }
    
    /**
     * Check if user already exists in system
     * @param string $email user's email address
     * @return if user already exists or not 
     */
    private function userExists($email) 
    {
        $stmt = $this->conn->prepare("SELECT user_id FROM user WHERE email = ?");
        
        // insert parameters into sql statement
        $stmt->bind_param("s", $email);
        
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        
        // returns true if one or more results are found
        return $num_rows > 0;
    }
    
    /**
     * Checking user login credentials
     * @param string $email user's email address
     * @param string $password user's password
     */
    public function loginUser($email, $password) 
    {
        $stmt = $this->conn->prepare("SELECT user_id FROM user WHERE email = ? "
                . "AND password = ?");
        
        // insert parameters into sql statement
        $stmt->bind_param("ss", $email, $password);
        
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        
        // returns true if one or more results are found (successful login)
        return $num_rows > 0;
    }
    
    /**
     * Get user details by specified email 
     * @param string $user_id user's id
     */
    public function getUserDetails($user_id)
    {
        $stmt = $this->conn->prepare("SELECT user.* FROM user WHERE user.USER_ID = ?");
        
        // insert parameters into sql statement
        $stmt->bind_param("s", $user_id);
        
        $stmt->execute();       
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        return $user;
    }
    
    /**
     * Update user's details 
     * @param int $user_id user's id
     * @param string $name user's name
     * @param string $email user's email
     * @param string $password user's password
     */
    public function updateUserDetails($user_id, $name, $email, $password) 
    {
        $stmt = $this->conn->prepare("UPDATE user SET name = ?, email = ?, "
                . "password = ? WHERE user_id = ?");
        
        // insert parameters into sql statement
        $stmt->bind_param("ssss", $name, $email, $password, $user_id);
        
        $result = $stmt->execute();
        $stmt->close();
        
        return $result; 
    }
    
    /**
     * Adds a new learnt user chord
     * @param int $user_id the user's id
     * @param int $chord_id the chord's id
     * @return bool if add was successful or not
     */
    public function addNewUserChord($user_id, $chord_id)
    {
        $stmt = $this->conn->prepare("INSERT INTO user_chord(user_id, chord_id, "
                . "num_times_pract) VALUES(?, ?, " .DEFAULT_NUM_TIMES_PRACT. ")");
        
        // insert parameters into sql statement
        $stmt->bind_param("ss", $user_id, $chord_id);
        
        $result = $stmt->execute();
        $stmt->close();
        
        return $result; 
    }
    
    /**
     * Gets user's level and achievements
     * @param string $user_id user's id
     * @return user's level and achievements
     */
    public function getLevelAndAchievements($user_id)
    {
        $stmt = $this->conn->prepare("SELECT level, achievements FROM user "
                . "WHERE user_id = ?");
        
        // insert parameters into sql statement
        $stmt->bind_param("s", $user_id);
        
        $stmt->execute();       
        $level_details = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        return $level_details;
    }
    
    /**
     * Sets user's level and achievements
     * @param int $user_id user's id
     * @param int $level user's level
     * @param int $achievements user's achievements
     * @return bool whether level and achievements were successfully updated
     */
    public function setLevelAndAchievements($user_id, $level, $achievements)
    {
        $stmt = $this->conn->prepare("UPDATE user SET level = ?, achievements = ? "
                . "WHERE user_id = ?");
        
        // insert parameters into sql statement
        $stmt->bind_param("sss", $level, $achievements, $user_id);
                
        $result = $stmt->execute();
        $stmt->close();
        
        return $result; 
    }
    
    public function setChordNumTimesPract($user_id, $chord_id, $num_times_pract)
    {
        $stmt = $this->conn->prepare("UPDATE user_chord SET num_times_pract = ? "
                . "WHERE user_id = ? AND chord_id = ?");
        
        // insert parameters into sql statement
        $stmt->bind_param("sss", $num_times_pract, $user_id, $chord_id);
                
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }
}