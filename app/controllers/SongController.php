<?php

namespace app\controllers;

use app\models\Song;

class SongController {
    public function getSongs($request, $response) {
        // getting all songs with their chords (from song_chord table)
        $songs = Song::with('chords')->get();
             
        if ($songs != null || !empty(songs)) {
            return $response->withStatus(200)->withJson($songs); 
        }
        else {
            return $response->withStatus(500)->withJson((object)[]);
        }
    }
}
