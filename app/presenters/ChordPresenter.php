<?php

namespace app\presenters;

use app\models\Chord;

class ChordPresenter {
    public function getChords($request, $response) {
        $chords = Chord::all();
        
        if ($chords != null || !empty($chords)) {
            return $response->withStatus(200)->withJson($chords); 
        }
        else {
            return $response->withStatus(500)->withJson(array());
        }
    }
}
