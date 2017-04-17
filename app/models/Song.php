<?php

namespace app\models;

class Song extends \Illuminate\Database\Eloquent\Model {   
    protected $table = 'song'; 
    protected $primaryKey = 'SONG_ID';

    public function chords() {
        return $this->belongsToMany('app\models\Chord', 'song_chord', 'SONG_ID', 
                'CHORD_ID');
    }
}