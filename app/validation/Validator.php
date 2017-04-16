<?php

namespace app\validation;

use app\models\User;

class Validator {
    public static function validate($input, $user_id) {
        $errors = array();
        
        // check email is valid
        if (filter_var($input['email'], FILTER_VALIDATE_EMAIL) == false) {
            array_push($errors, "Invalid email address");
        }
        
        // check is email is already registered
        if ($user_id == null) {
            $isRegistered = User::where('EMAIL', $input['email'])->count() > 0;
        }
        else {
            // if an edit, don't include own user id
            $isRegistered = User::where('EMAIL', $input['email'])->where(
                    'USER_ID', '!=', $user_id)->count() > 0;
        }
        if ($isRegistered) {
            array_push($errors, "The specified email address is already registered");
        }
        
        // check whether password is too short
        if (strlen($input['password']) < 8) {
            array_push($errors, "Password must be at least 8 characters in length");
        }
        
        // check whether password has at least one upper case letter
        if (strcmp($input['password'], strtolower($input['password'])) == 0) {
            array_push($errors, "Password must have at least one upper case letter");
        }
        
        // check whether password has at least one lower case letter
        if (strcmp($input['password'], strtoupper($input['password'])) == 0) {
            array_push($errors, "Password must have at least one lower case letter");
        }
        
        // check whether password has at least one number
        if (!preg_match('/\d/', $input['password'])) {
            array_push($errors, "Password must have at least one number");
        }
        
        return $errors;
    }
}

