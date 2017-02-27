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
        $this->put('/users/:user_id/chords/:chord_id', 
                array($this, 'updateUserChord'));    
    }
    
    public function setDbHandler($dbHandler)
    {
        $this->db = $dbHandler;
    }
    
    function getUserChords($user_id)
    {
        $response = array();
        // get user's chords
        $user_chords = $this->db->getUserChords($user_id);
        
        $response["error"] = false;
        $response["user_chords"] = array();
                
        while ($user_chord = $user_chords->fetch_assoc())
        {
            $temp = array();
            $temp["chord_id"] = $user_chord["CHORD_ID"];
            // pushing values onto end of array
            array_push($response["user_chords"], $temp);           
        }
        
        $this->echoResponse(200, $response);
    }
    
    function getUser($user_id)
    {
        $response = array();
        // get user's details 
        $user = $this->db->getUserDetails($user_id);

        if ($user != null) 
        {
            // respond with success
            $response["error"] = false;
            $response["user_id"] = $user["USER_ID"];
            $response["name"] = $user["NAME"];
            $response["email"] = $user["EMAIL"];
            $response["level"] = $user["LEVEL"];
            $response["achievements"] = $user["ACHIEVEMENTS"];
            $this->echoResponse(200, $response);
        }
        else 
        {
            // respond with failure
            $response["error"] = true;
            $response["message"] = "An error occured while trying to retrieve details.";
            $this->echoResponse(500, $response);
        }
    }
    
    /**
     * Update number of times a chord has been practised
     * method: PUT
     * URL: /users/id/chords/id
     * params: user_id, chord_id, num_times_pract
     */
    function updateUserChord($user_id, $chord_id) 
    {
        // checking for all required parameters
        $this->verifyRequiredParams(array('num_times_pract'));

        $response = array();

        // reading post parameters
        $num_times_pract = $this->request->params('num_times_pract');

        $result = $this->db->setChordNumTimesPract($user_id, $chord_id, $num_times_pract);

        // if new chord number of times practised successfully updated
        if ($result)
        {
            // get user's level and achievements
            $level_details = $this->db->getLevelAndAchievements($user_id);

            if ($level_details != null)
            {
                $level = $level_details["level"];
                $achievements = $level_details["achievements"];

                if ($achievements < MAX_ACHIEVEMENTS)
                {
                    // add to achievements
                    $achievements += ACHIEVEMENTS_INCREASE;
                    $remainder = $achievements % ACHIEVEMENTS_DIVISOR;

                    // if achievements is a multiple of 1000
                    if ($remainder == 0)
                    {
                            // progress to next level
                            $level++; 
                    }
                }

                // set user's level and achievements
                $result = $this->db->setLevelAndAchievements($user_id, $level, $achievements);

                if ($result)
                {
                    // response with success
                    $response["error"] = false;
                    $response["message"] = "Number of times chord has been practised "
                            . "has been successfully updated.";
                    $this->echoResponse(200, $response);
                }
                else
                {
                    // respond with failure
                    $response["error"] = true; 
                    $response["message"] = "An error occured while trying to update "
                        . "user's level details.";
                    $this->echoResponse(500, $response);
                }
            }
            else
            {
                // respond with failure
                $response["error"] = true;
                $response["message"] = "An error occured while trying to retrieve "
                        . "user's level details.";
            }

        }
        else 
        {
            // respond with failure
            $response["error"] = true;
            $response["message"] = "An error occured while trying to update the number "
                    . "of times a chord has been practised.";
            $this->echoResponse(500, $response);
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
        // checking for all required parameters
        $this->verifyRequiredParams(array('chord_id'));

        $response = array();

        // reading post parameters
        $chord_id = $this->request->params('chord_id');

        $result = $this->db->addNewUserChord($user_id, $chord_id);

        // if new chord successfully added
        if ($result)
        {
            // get user's level and achievements
            $level_details = $this->db->getLevelAndAchievements($user_id);

            if ($level_details != null)
            {
                $level = $level_details["level"];
                $achievements = $level_details["achievements"];

                if ($achievements < MAX_ACHIEVEMENTS)
                {
                    // add to achievements
                    $achievements += ACHIEVEMENTS_INCREASE;
                    $remainder = $achievements % ACHIEVEMENTS_DIVISOR;

                    // if achievements is a multiple of 1000
                    if ($remainder == 0)
                    {
                            // progress to next level
                            $level++; 
                    }
                }

                // set user's level and achievements
                $result = $this->db->setLevelAndAchievements($user_id, $level, $achievements);

                if ($result)
                {
                    // response with success
                    $response["error"] = false;
                    $response["message"] = "Successfully added new chord.";
                    $this->echoResponse(200, $response);
                }
                else
                {
                    // respond with failure
                    $response["error"] = true; 
                    $response["message"] = "An error occured while trying to update "
                        . "user's level details.";
                    $this->echoResponse(500, $response);
                }
            }
            else
            {
                // respond with failure
                $response["error"] = true;
                $response["message"] = "An error occured while trying to retrieve "
                        . "user's level details.";
                $this->echoResponse(500, $response);
            }

        }
        else 
        {
            // respond with failure
            $response["error"] = true;
            $response["message"] = "An error occured while trying to add new chord.";
            $this->echoResponse(500, $response);
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
        // checking for all required parameters
        $this->verifyRequiredParams(array('email', 'password'));

        $response = array();

        // reading post parameters
        $email = $this->request->params('email');
        $password = $this->request->params('password');  

        // check user's login credentials
        if($this->db->loginUser($email, $password)) 
        {
            $response["error"] = false;
            $response["message"] = "Login successful.";
            $this->echoResponse(200, $response);
        }
        else
        {
            $response["error"] = true;
            $response["message"] = "Login failed. Incorrect email or password.";
            $this->echoResponse(400, $response);
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
        // checking for all required parameters
        $this->verifyRequiredParams(array('name', 'email', 'password'));

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
            // respond with success
            $response["error"] = false;
            $response["message"] = "User's details successfully updated.";
            $this->echoResponse(200, $response);
        }
        else
        {
            // respond with failure
            $response["error"] = true;
            $response["message"] = "An error occurred while trying to update user's details.";
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
        // checking for all required parameters
        $this->verifyRequiredParams(array('name', 'email', 'password'));

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
            // respond with success
            $response["error"] = false;
            $response["message"] = "User has been successfully registered";
            $this->echoResponse(201, $response);
        }
        else if ($result == USER_REGISTRATION_FAILED) 
        {
            // respond with failure
            $response["error"] = true;
            $response["message"] = "An error has occurred during registration";
            $this->echoResponse(500, $response);
        }
        else if ($result == USER_ALREADY_REGISTERED)
        {
            // respond with failure
            $response["error"] = true;
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

        // setting response
        $response = array();
        $response["error"] = false;
        $response["songs"] = array();

        // looping through result and preparing songs array
        while ($song = $result->fetch_assoc())
        {
            $temp = array();
            // getting fields from result
            $temp["title"] = $song["TITLE"];
            $temp["artist"] = $song["ARTIST"];
            $temp["contents"] = $song["CONTENTS"];
            $temp["chords"] = explode(",", $song["CHORDS"]);
            // pushing values onto end of array
            array_push($response["songs"], $temp);
        }

        // display response
        $this->echoResponse(200, $response);
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

        // setting response
        $response = array();
        $response["error"] = false;
        $response["chords"] = array();

        // looping through result and preparing chords array
        if ($result != null)
        {
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
                // pushing values onto end of array
                array_push($response["chords"], $temp);
            }
            
            // display response
            $this->echoResponse(200, $response);
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
     * Verifying the required parameters in the request
     * @param $required_fields required parameters
     */
    function verifyRequiredParams($required_fields) 
    {
        // assuming no error
        $error = false;

        // setting error fields to blank
        $error_fields = "";

        // getting request parameters
        $request_params = $_REQUEST;

        // handling PUT request parameters
        if ($_SERVER['REQUEST_METHOD'] == 'PUT') 
        {
            // storing PUT parameters in request params variable
            parse_str($this->request->getBody(), $request_params);
        }

        // looping through all the parameters
        foreach($required_fields as $field) 
        {
            //if any required parameters are missing
            if (!isset($request_params[$field]) || 
                    strlen(trim($request_params[$field])) <= 0) 
            {
                // set error to true
                $error = true;

                // concatnating the missing parameters into error fields
                $error_fields .= $field . ', ';
            }
        }

        // if there is a parameter missing
        if ($error)
        {
            // creating response array
            $response = array();

            // adding missing/empty required parameters to response array
            $response["error"] = true;
            $response["message"] = 'Required field(s) ' . 
                    substr($error_fields, 0, -2) . ' is missing or empty ';

            // responding with error
            $this->echoResponse(400, $response);
            $this->stop();
        }
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
            $response["error"] = true;
            $response["message"] = "Invalid email address";
            $this->echoResponse(400, $response);
            $this->stop();
        }
    }
}

