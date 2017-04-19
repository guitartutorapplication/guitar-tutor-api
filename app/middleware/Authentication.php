<?php

namespace app\middleware;

use app\models\User;

class Authentication {
    public function __invoke($request, $response, $next) {
        $api_key = $request->getHeader('Authorization');
        // only goes on to complete API request if api key is valid
        if ($api_key == null || !User::authenticate($api_key)) {
            return $response->withStatus(401)->withJson((object)[]);
        }
        else {
            return $next($request, $response);
        } 
    }
}
