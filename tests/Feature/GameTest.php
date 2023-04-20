<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Http\Controllers\GameController;

class GameTest extends TestCase
{
    private $controller;


    public function test_combination_in_controller()
    {
        $request = $this->createRequest();
        $gcontroller = new GameController();
        $gcontroller->combination($request, $gcontroller->generateNumber());

    }

    public function test_register_user()
    {
        $request = $this->createRequest();
        $gcontroller = new GameController();
        $this->assertTrue($gcontroller->start($request));
    }

    protected function createRequest(
        $method,
        $content,
        $uri = '/game/start/user1/35',
        $server = ['CONTENT_TYPE' => 'application/json'],
        $parameters = [],
        $cookies = [],
        $files = []
    ) {
        $request = new \Illuminate\Http\Request;
        return $request->createFromBase(\Symfony\Component\HttpFoundation\Request::create($uri, $method, $parameters, $cookies, $files, $server, $content));
    }

}
