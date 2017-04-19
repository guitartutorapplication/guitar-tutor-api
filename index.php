<?php

require 'vendor/autoload.php';
include 'bootstrap.php';

use app\middleware\Authentication;

$app = new \Slim\App(['settings' => ['displayErrorDetails' => true]]);

$container = $app->getContainer();
$container['ChordController'] = function() {
    return new \app\controllers\ChordController();
};
$container['SongController'] = function() {
    return new \app\controllers\SongController();
};
$container['UserController'] = function() {
    return new \app\controllers\UserController();
};

// /chords route
$app->get('/chords', 'ChordController:getChords')->add(new Authentication());
// /songs route
$app->get('/songs', 'SongController:getSongs')->add(new Authentication());
// /users route
$app->post('/users', 'UserController:addUser');
// /users/login route
$app->post('/users/login', 'UserController:login');
// /users/{id} route
$app->put('/users/{id}', 'UserController:editUser')->add(new Authentication());
$app->get('/users/{id}', 'UserController:getUserDetails')->add(new Authentication());
// /users/{id}/chords route
$app->get('/users/{id}/chords', 'UserController:getUserChords')->add(new Authentication());
$app->post('/users/{id}/chords', 'UserController:addUserChord')->add(new Authentication());
$app->put('/users/{id}/chords', 'UserController:updateUserChords')->add(new Authentication());

$app->run();