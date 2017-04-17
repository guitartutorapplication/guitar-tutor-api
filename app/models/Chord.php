<?php

namespace app\models;

class Chord extends \Illuminate\Database\Eloquent\Model {
    protected $table = 'chord'; 
    protected $primaryKey = 'CHORD_ID';
    protected $hidden = ['pivot'];
    
    public function getNumTimesPractised() {
        return $this->pivot->NUM_TIMES_PRACT;
    }
}