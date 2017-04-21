<?php

namespace app\controllers;

use app\models\User;
use app\validation\Validator;

class UserController {
    public function addUser($request, $response) {
        $errors = Validator::validate($request->getParams(), null);
        
        if (!empty($errors)) {
            // returning validation error
            return $response->withStatus(400)->withJson($errors);
        }
        else {
            $user = User::create([
			'EMAIL' => $request->getParam('email'),
			'NAME' => $request->getParam('name'),
			'PASSWORD' => password_hash($request->getParam('password'),
                                PASSWORD_DEFAULT)
		]);
            
            if ($user != null) {
                return $response->withStatus(201)->withJson(array());
            }
            else {
                return $response->withStatus(500)->withJson(array());
            }
        }
    }
    
    public function editUser($request, $response, $args) {
        $errors = Validator::validate($request->getParams(), $args['id']);
        
        if (!empty($errors)) {
            // returning validation error
            return $response->withStatus(400)->withJson($errors);
        }
        else {
            $user = User::findOrFail($args['id'])->update([
                        'EMAIL' => $request->getParam('email'),
			'NAME' => $request->getParam('name'),
			'PASSWORD' => password_hash($request->getParam('password'),
                                PASSWORD_DEFAULT)
                        ]);
            
            if ($user != null) {
                return $response->withStatus(200)->withJson(array());
            }
            else {
                return $response->withStatus(500)->withJson(array());
            }
        }
    }
    
    public function getUserDetails($request, $response, $args) {
        $user = User::find($args['id']);
        
        if ($user != null) {
            return $response->withStatus(200)->withJson($user); 
        }
        else {
            return $response->withStatus(500)->withJson((object)[]);
        }
    }
    
    public function getUserChords($request, $response, $args) {
        // find chords own by user with specified id
        $chords = User::findOrFail($args['id'])->chords;
               
        if ($chords != null || !empty($chords)) {
            $chords_array = array();
            
            foreach($chords as $chord) {
                $temp = $chord->toArray();
                // displaying number of times practised at end of each row 
                // instead of within a pivot array
                $temp["NUM_TIMES_PRACT"] = $chord->getNumTimesPractised();
                array_push($chords_array, $temp);
            }
            return $response->withStatus(200)->withJson($chords_array); 
        }
        else {
            return $response->withStatus(500)->withJson(array());
        }
    }
    
    public function login($request, $response) {
        $user = User::login($request->getParam('email'), $request->getParam(
                'password'));
        
        if ($user != null) {
            // sending back user id and api key if successful login
            $user_details = array();
            $user_details["USER_ID"] = $user->USER_ID;
            $user_details["API_KEY"] = $user->API_KEY;
            return $response->withStatus(200)->withJson($user_details);
        }
        else {
            return $response->withStatus(400)->withJson((object)[]);
        }
    }
    
    public function addUserChord($request, $response, $args) {
        $user = User::find($args['id']);
        
        if ($user != null) {
            $old_achievements = $user->ACHIEVEMENTS;
            $old_level = $user->LEVEL;
            
            if ($user->addUserChord($request->getParam('chord_id'))) {
                // passing back achievements and level only if they were updated
                $result = (object)[];
                if ($old_achievements != $user->ACHIEVEMENTS) {
                    $result = array();
                    $result["ACHIEVEMENTS"] = $user->ACHIEVEMENTS;
                }
                if ($old_level != $user->LEVEL) {
                    $result["LEVEL"] = $user->LEVEL;
                }
                
                return $response->withStatus(201)->withJson($result);
            }
            else {
                return $response->withStatus(500)->withJson((object)[]);
            }
        }
        else {
            return $response->withStatus(500)->withJson((object)[]);
        }
    }
    
    public function updateUserChords($request, $response, $args) {
        $user = User::find($args['id']);
        
        if ($user != null) {
            $old_achievements = $user->ACHIEVEMENTS;
            $old_level = $user->LEVEL;
            
            if ($user->updateChordsNumTimesPractised($request->getParam('chord_ids'))) {
                // passing back achievements and level only if they were updated
                $result = (object)[];
                if ($old_achievements != $user->ACHIEVEMENTS) {
                    $result = array();
                    $result["ACHIEVEMENTS"] = $user->ACHIEVEMENTS;
                }
                if ($old_level != $user->LEVEL) {
                    $result["LEVEL"] = $user->LEVEL;
                }
                
                return $response->withStatus(200)->withJson($result);
            }
            else {
                return $response->withStatus(500)->withJson((object)[]);
            }
        }
        else {
            return $response->withStatus(500)->withJson((object)[]);
        }
    }
}
