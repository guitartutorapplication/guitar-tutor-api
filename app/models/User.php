<?php

namespace app\models;
use Illuminate\Database\Capsule\Manager as DB;

class User extends \Illuminate\Database\Eloquent\Model {
    protected $table = 'user'; 
    protected $primaryKey = 'USER_ID';
    protected $hidden = ['PASSWORD', 'API_KEY'];
    protected $fillable = ['NAME', 'EMAIL', 'PASSWORD'];
    public $timestamps = false;
    
    const defaultLevel = 1;
    const defaultAchievements = 0;
    const addUserChordAchievements = 100;
    const practiseSessionAchievements = 15;
    const maxAchievements = 6000;
    const achievementsInLevel = 1000;
    
    public function __construct(array $attributes = array()) {
        parent::__construct($attributes);

        // setting up default level and achievements if currently doesn't have 
        // value (e.g. when in the process of adding new user)
        if ($this->LEVEL == null) {
            $this->LEVEL = self::defaultLevel;
        }
        
        if ($this->ACHIEVEMENTS == null) {
            $this->ACHIEVEMENTS = self::defaultAchievements;
        }
        // generating API key if currently doesn't have a value (e.g when adding 
        // new user)
        if ($this->API_KEY == null) {
            $this->API_KEY = md5(uniqid(rand(), true));
        }
    }
    
    public static function authenticate($api_key) {
        // see if api key exists
        $user = User::where('API_KEY', $api_key)->first();
        return $user != null;
    }
    
    public static function login($email, $password) {
        // see if email exists
        $user = User::where('EMAIL', $email)->first();
        
        if ($user != null) {
            // check password input against hashed password
            if (!password_verify($password, $user->PASSWORD)) {
                $user = null;
            }
        }
        return $user;
    }
    
    public function addUserChord($chord_id) {
        $success = true; 
        
        DB::beginTransaction();
        try {
            // adds chord_id & user_id to user_chord table
            $this->chords()->attach($chord_id);
            $this->updateAchievements(self::addUserChordAchievements);
            
            DB::commit();
        } catch (\Exception $e) {
            $success = false;
            // rollback any changes if an error occurs
            DB::rollback();
        }
        return $success;
    }
    
    public function updateChordsNumTimesPractised($chord_ids) {
        $success = true;
        
        DB::beginTransaction();
        try {
            // increments each chord's num times practised by 1
            foreach ($chord_ids as $chord_id) {
                $chord = $this->chords()->wherePivot('CHORD_ID', $chord_id)->first();
                $this->chords()->updateExistingPivot($chord_id, [
                    'NUM_TIMES_PRACT' => ($chord->getNumTimesPractised()) + 1
                ]);
            }
       
            $this->updateAchievements(self::practiseSessionAchievements);
            DB::commit();
            
        } catch (\Exception $e) {
            $success = false;
            // rollback any changes if an error occurs
            DB::rollback();
        }
       return $success;
    }
    
    private function updateAchievements($achievements_increment) {
        // update achievements if less than the maximum number
        if (($this->ACHIEVEMENTS + $achievements_increment) <= self::maxAchievements) {
            $this->ACHIEVEMENTS += $achievements_increment; 
            $this->save();
            
            $this->updateLevel();
        }
    }
    
    private function updateLevel() {
        // if the new achievements means the user is now in the next level, 
        // increment level by one
        if ($this->ACHIEVEMENTS >= ($this->LEVEL * self::achievementsInLevel)) {
            $this->LEVEL++; 
            $this->save();
        }
    }
    
    public function chords() {
        return $this->belongsToMany('app\models\Chord', 'user_chord', 'USER_ID', 
                'CHORD_ID')->withPivot('NUM_TIMES_PRACT');
    }
}