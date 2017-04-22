<?php

namespace app\presenters;

use app\models\Song;

class SongPresenter {
    public function getSongs($request, $response) {
        // getting all songs with their chords (from song_chord table)
        $songs = Song::with('chords')->get();
        
        if ($songs != null || !empty($songs)) {
            return $response->withStatus(200)->withJson($songs); 
        }
        else {
            return $response->withStatus(500)->withJson(array());
        }
    }
}
