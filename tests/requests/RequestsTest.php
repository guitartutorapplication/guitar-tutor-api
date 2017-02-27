<?php
require_once '../include/DbHandler.php';
require_once '../app/App.php';
use PHPUnit\Framework\TestCase;
/* 
 * Testing SLIM requests 
 */
class RequestsTest extends TestCase
{
    private $app;
    
    public function setUp() 
    {
        $this->app = $this->getMockBuilder(App::class)
                ->getMock();
        // create db handler stub to avoid db calls
        $db_handler = $this->createMock(DbHandler::class);
        $this->app->setDbHandler($db_handler);
    }
    
    /** @test */
    public function GETChordsRequest_CallsGetChordsInApp() 
    {
        $this->app->environment = \Slim\Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'PATH_INFO' => '/chords',
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => '81'
        ]);
        
        $this->app->expects($this->once())->method('getChords');
        
        $this->app->run();          
    }
    
    /** @test */
    public function GETSongsRequest_CallsGetSongsInApp()
    {
        $this->app->environment = \Slim\Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'PATH_INFO' => '/songs',
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => '81'
        ]);
        
        $this->app->expects($this->once())->method('getSongs');
        
        $this->app->run(); 
    }
    
    /** @test */
    public function POSTUsersRequest_CallsAddUserInApp()
    {
        $this->app->environment = \Slim\Environment::mock([
            'REQUEST_METHOD' => 'POST',
            'PATH_INFO' => '/users',
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => '81'
        ]);
        
        $this->app->expects($this->once())->method('addUser');
        
        $this->app->run(); 
    }
    
    /** @test */
    public function PUTUsersRequest_CallsEditUserInApp()
    {
        $this->app->environment = \Slim\Environment::mock([
            'REQUEST_METHOD' => 'POST',
            'PATH_INFO' => '/users/id',
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => '81'
        ]);
        
        $this->app->expects($this->once())->method('editUser');
        
        $this->app->run(); 
    }
    
    /** @test */
    public function GETUsersRequest_CallsGetUserInApp()
    {
        $this->app->environment = \Slim\Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'PATH_INFO' => '/users/id',
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => '81'
        ]);
        
        $this->app->expects($this->once())->method('getUser');
        
        $this->app->run(); 
    }
    
    /** @test */
    public function POSTUsersChordsRequest_CallsAddUserChordInApp()
    {
        $this->app->environment = \Slim\Environment::mock([
            'REQUEST_METHOD' => 'POST',
            'PATH_INFO' => '/users/id/chords',
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => '81'
        ]);
        
        $this->app->expects($this->once())->method('addUserChord');
        
        $this->app->run();
    }
    
    /** @test */
    public function GETUsersChordsRequest_CallsGetUserChordsInApp()
    {
        $this->app->environment = \Slim\Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'PATH_INFO' => '/users/id/chords',
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => '81'
        ]);
        
        $this->app->expects($this->once())->method('getUserChords');
        
        $this->app->run();
    }
    
    /** @test */
    public function PUTUsersChordsRequest_CallsUpdateUserChordInApp()
    {
        $this->app->environment = \Slim\Environment::mock([
            'REQUEST_METHOD' => 'PUT',
            'PATH_INFO' => '/users/id/chords/id',
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => '81'
        ]);
        
        $this->app->expects($this->once())->method('updateUserChord');
        
        $this->app->run();
    }
            
    /** @test */
    public function POSTUsersLoginRequest_CallsUserLoginInApp()
    {
        $this->app->environment = \Slim\Environment::mock([
            'REQUEST_METHOD' => 'PUT',
            'PATH_INFO' => '/users/id/chords/id',
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => '81'
        ]);
        
        $this->app->expects($this->once())->method('updateUserChord');
        
        $this->app->run();
    }
}