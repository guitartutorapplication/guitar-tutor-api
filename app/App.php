<?php
require_once '../include/DbHandler.php';
use Slim\Slim;

class App extends Slim 
{
    private $db; 
    
    function __construct(array $userSettings = [])
    {
        parent::__construct($userSettings);
        
        $this->setDbHandler(new DbHandler());
        
        $this->get('/chords', array($this, 'getChords'));
        
        $this->get('/songs', array($this, 'getSongs'));
        
        $this->post('/users', array($this, 'addUser'));
        $this->put('/users/:id', array($this, 'editUser'));
        $this->get('/users/:id', array($this, 'getUser'));
        $this->post('/users/login', array($this, 'userLogin'));
        $this->post('/users/:id/chords', array($this, 'addUserChord'));
        $this->get('/users/:id/chords', array($this, 'getUserChords'));
        $this->put('/users/:user_id/chords/', 
                array($this, 'updateUserChords'));    
    }
    
    public function setDbHandler($dbHandler)
    {
        $this->db = $dbHandler;
    }
    
    function getUserChords($user_id)
    {
        // get user's chords
        $user_chords = $this->db->getUserChords($user_id);
        
        if ($user_chords != null) {
            $response = array();
            while ($chord = $user_chords->fetch_assoc())
            {
                $temp = array();
                $temp["chord_id"] = $chord["CHORD_ID"];
                $temp["name"] = $chord["NAME"];
                $temp["type"] = $chord["TYPE"];
                $temp["level_required"] = $chord["LEVEL_REQUIRED"];
                $temp["diagram_filename"] = $chord["DIAGRAM_FILENAME"];
                $temp["video_filename"] = $chord["VIDEO_FILENAME"];
                $temp["audio_filename"] = $chord["AUDIO_FILENAME"];
                $temp["num_times_pract"] = $chord["NUM_TIMES_PRACT"];
                // pushing values onto end of array
                array_push($response, $temp);           
            }

            $this->echoResponse(200, $response);
        }
        else {
            $this->echoResponse(500, (object)[]);
        }
    }
    
    function getUser($user_id)
    {
        // get user's details 
        $user = $this->db->getUserDetails($user_id);

        if ($user != null) 
        {
            $response = array();
            $response["user_id"] = $user["USER_ID"];
            $response["name"] = $user["NAME"];
            $response["email"] = $user["EMAIL"];
            $response["level"] = $user["LEVEL"];
            $response["achievements"] = $user["ACHIEVEMENTS"];
            $this->echoResponse(200, $response);
        }
        else 
        {
            $this->echoResponse(500, (object)[]);
        }
    }
    
    /**
     * Update number of times a chord has been practised (could be multiple chords)
     * method: PUT
     * URL: /users/id/chords
     * params: user_id
     */
    function updateUserChords($user_id) 
    {
        // reading post parameters
        $chord_ids = $this->request->params('chord_ids');
        
        $level_details = $this->db->getLevelAndAchievements($user_id); 

        if ($level_details != null) {
            $achievements = $level_details["achievements"];
            $level = $level_details["level"];
            $achievements_update = false;
            $level_update = false;
            
            if ($achievements < MAX_ACHIEVEMENTS) {
                $achievements_update = true;
                
                if (($achievements + 15) >= ($level * ACHIEVEMENTS_DIVISOR)) {
                    $level_update = true;
                }
            }
            
            $level_details = $this->db->updateChordNumTimesPract($user_id, $chord_ids, 
                    $achievements_update, $level_update);
            
            if ($level_details != null) {
                if (!$level_update && !$achievements_update) {
                    $this->echoResponse(200, (object)[]);
                }
                else {
                    $response = array();
                    $response["achievements"] = $level_details["achievements"];
                    if ($level_update) {
                        $response["level"] = $level_details["level"];
                    }
                    $this->echoResponse(200, $response);
                }
            }
            else {
                $this->echoResponse(500, (object)[]);
            }
        }
        else {
            // respond with failure
            $this->echoResponse(500, (object)[]);
        }
    }
    
     /**
     * Add a new learnt chord for user
     * method: POST
     * URL: /users/id/chords
     * params: user_id, chord_id
     */
    function addUserChord($user_id) 
    {
        // reading post parameters
        $chord_id = $this->request->params('chord_id');
        
        $level_details = $this->db->getLevelAndAchievements($user_id); 

        if ($level_details != null) {
            $achievements = $level_details["achievements"];
            $level = $level_details["level"];
            $achievements_update = false;
            $level_update = false;
            
            if ($achievements < MAX_ACHIEVEMENTS) {
                $achievements_update = true;
                
                if (($achievements + 100) >= ($level * ACHIEVEMENTS_DIVISOR)) {
                    $level_update = true;
                }
            }
            
            $level_details = $this->db->addNewUserChord($user_id, $chord_id, 
                    $achievements_update, $level_update);
            if ($level_details != null) {
                if (!$level_update && !$achievements_update) {
                    $this->echoResponse(200, (object)[]);
                }
                else {
                    $response = array();
                    $response["achievements"] = $level_details["achievements"];
                    if ($level_update) {
                        $response["level"] = $level_details["level"];
                    }
                    $this->echoResponse(200, $response);
                }
            }
            else {
                $this->echoResponse(500, (object)[]);
            }
        }
        else {
            // respond with failure
            $this->echoResponse(500, (object)[]);
        }
    }
    
     /**
     * Login user
     * method: POST
     * URL: /users/login
     * params: email, password
     */
    function userLogin() 
    {
         // reading post parameters
        $email = $this->request->params('email');
        $password = $this->request->params('password');  

        // check user's login credentials
        $user_id = $this->db->loginUser($email, $password);
        if($user_id != null) 
        {
            $response = array();
            $response["user_id"] = $user_id["user_id"];
            $this->echoResponse(200, $response);
        }
        else
        {
            $this->echoResponse(500, (object)[]);
        }
    }
    
     /**
     * Edit user details
     * method: PUT
     * URL: /users/:id
     * params: name, email, password
     */
    function editUser($user_id) 
    {
        $response = array();

        // reading put parameters
        $name = $this->request->params('name');
        $email = $this->request->params('email');
        $password = $this->request->params('password');

        // check if valid email 
        $this->validateEmail($email);

        $result = $this->db->updateUserDetails($user_id, $name, $email, $password);

        if ($result)
        {
            $response["message"] = "Successfully updated user details.";
            $this->echoResponse(200, $response);
        }
        else
        {
            $response["message"] = "An error occured while updating user details.";
            $this->echoResponse(500, $response);
        }
    }
    
     /**
     * Register user
     * method: POST
     * URL: /users
     * params: name, email, password
     */
    function addUser() 
    {
        // reading post parameters
        $name = $this->request->params('name');
        $email = $this->request->params('email');
        $password = $this->request->params('password');

        // check if valid email 
        $this->validateEmail($email);

        $result = $this->db->createUser($name, $email, $password);

        $response = array();

        // check for result of registration
        if ($result == USER_SUCCESSFULLY_REGISTERED) 
        {
            $response["message"] = "User successfully registered";
            $this->echoResponse(201, $response);
        }
        else if ($result == USER_REGISTRATION_FAILED) 
        {
            $response["message"] = "An error occurred while registering";
            $this->echoResponse(500, $response);
        }
        else if ($result == USER_ALREADY_REGISTERED)
        {
            $response["message"] = "The specified email address is already registered";
            $this->echoResponse(400, $response);
        }
    }
    
    /**
     * Listing all songs
     * method: GET
     * URL: /songs
     */
    function getSongs() 
    {
        // fetching all chords from db handler
        $result = $this->db->getAllSongs();

        if ($result != null) {
            $response = array();
            // looping through result and preparing songs array
            while ($song = $result->fetch_assoc())
            {
                $temp = array();
                // getting fields from result
                $temp["title"] = $song["TITLE"];
                $temp["artist"] = $song["ARTIST"];
                $temp["audio_filename"] = $song["AUDIO_FILENAME"];
                $temp["contents"] = $song["CONTENTS"];
                $temp["chords"] = array();
                // retrieving chords for song
                $chords_result = $this->db->getSongChords($song["SONG_ID"]);
                if ($chords_result != null) {
                    while ($chord = $chords_result->fetch_assoc())
                    {
                        $chord_temp = array();
                        // getting fields from result
                        $chord_temp["chord_id"] = $chord["CHORD_ID"];
                        $chord_temp["name"] = $chord["NAME"];
                        $chord_temp["type"] = $chord["TYPE"];
                        $chord_temp["level_required"] = $chord["LEVEL_REQUIRED"];
                        $chord_temp["diagram_filename"] = $chord["DIAGRAM_FILENAME"];
                        $chord_temp["video_filename"] = $chord["VIDEO_FILENAME"];
                        // pushing values onto end of array
                        array_push($temp["chords"], $chord_temp);
                    }
                    // pushing values onto end of array
                    array_push($response, $temp);
                }
                else {
                    $this->echoResponse(500, (object)[]);
                    break;
                }
            }
            // display response
            $this->echoResponse(200, $response);
        }
        else {
            $this->echoResponse(500, (object)[]);
        }
    }

    /**
    * Listing all chords
    * method: GET
    * URL: /chords
    */
    function getChords()
    {
        // fetching all chords from db handler
        $result = $this->db->getAllChords();

        // looping through result and preparing chords array
        if ($result != null)
        {
            // setting response
            $response = array();
            
            while ($chord = $result->fetch_assoc())
            {
                $temp = array();
                // getting fields from result
                $temp["chord_id"] = $chord["CHORD_ID"];
                $temp["name"] = $chord["NAME"];
                $temp["type"] = $chord["TYPE"];
                $temp["level_required"] = $chord["LEVEL_REQUIRED"];
                $temp["diagram_filename"] = $chord["DIAGRAM_FILENAME"];
                $temp["video_filename"] = $chord["VIDEO_FILENAME"];
                $temp["audio_filename"] = $chord["AUDIO_FILENAME"];
                // pushing values onto end of array
                array_push($response, $temp);
            }
            
            // display response
            $this->echoResponse(200, $response);
        }
        else {
            $this->echoResponse(500, (object)[]);
        }
    }
    
     /**
     * Display response in JSON format
     * @param string $status_code http response code
     * @param int $response json response
     */
    function echoResponse($status_code, $response)
    {
        // setting http response code
        $this->status($status_code);

        // setting response content type to JSON
        $this->contentType('application/json');

        // displaying response in JSON
        echo json_encode($response);
    }
    
     /**
     * Checking if email is valid
     * @param string $email user's email address
     */
    function validateEmail($email) {
        // check if email is valid or not
        if (filter_var($email, FILTER_VALIDATE_EMAIL) == false) 
        {
            // respond with error if not valid
            $response["message"] = "Invalid email address";
            $this->echoResponse(400, $response);
            $this->stop();
        }
    }
}

