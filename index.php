<?php

require 'vendor/autoload.php';
include 'bootstrap.php';

use app\middleware\Authentication;

$app = new \Slim\App(['settings' => ['displayErrorDetails' => true]]);
// adds presenters to slim container
$container = $app->getContainer();
$container['ChordPresenter'] = function() {
    return new \app\presenters\ChordPresenter();
};
$container['SongPresenter'] = function() {
    return new \app\presenters\SongPresenter();
};
$container['UserPresenter'] = function() {
    return new \app\presenters\UserPresenter();
};

// /chords route
$app->get('/chords', 'ChordPresenter:getChords')->add(new Authentication());
// /songs route
$app->get('/songs', 'SongPresenter:getSongs')->add(new Authentication());
// /users route
$app->post('/users', 'UserPresenter:addUser');
// /users/login route
$app->post('/users/login', 'UserPresenter:login');
// /users/{id} route
$app->put('/users/{id}', 'UserPresenter:editUser')->add(new Authentication());
$app->get('/users/{id}', 'UserPresenter:getUserDetails')->add(new Authentication());
// /users/{id}/chords route
$app->get('/users/{id}/chords', 'UserPresenter:getUserChords')->add(new Authentication());
$app->post('/users/{id}/chords', 'UserPresenter:addUserChord')->add(new Authentication());
$app->put('/users/{id}/chords', 'UserPresenter:updateUserChords')->add(new Authentication());

$app->run();