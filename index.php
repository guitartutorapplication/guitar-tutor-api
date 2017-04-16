<?php

require 'vendor/autoload.php';
include 'bootstrap.php';

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
$app->get('/chords', 'ChordController:getChords');
// /songs route
$app->get('/songs', 'SongController:getSongs');
// /users route
$app->post('/users', 'UserController:addUser');
// /users/login route
$app->post('/users/login', 'UserController:login');
// /users/{id} route
$app->put('/users/{id}', 'UserController:editUser');
$app->get('/users/{id}', 'UserController:getUserDetails');
// /users/{id}/chords route
$app->get('/users/{id}/chords', 'UserController:getUserChords');
$app->post('/users/{id}/chords', 'UserController:addUserChord');
$app->put('/users/{id}/chords', 'UserController:updateUserChords');

$app->run();