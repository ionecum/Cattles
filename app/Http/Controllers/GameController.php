<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Games;



class GameController extends Controller
{
    /**
     * @OA\Get(
     *    path="/game",
     *    operationId="index",
     *    tags={"Bull", "Cows", "game"},
     *    summary="Play Bull and Cows online!",
     *    description="This application will allow you to play the Bull&Cows game.",
     *    @OA\Parameter(name="name", in="query", description="name", required=true,
     *        @OA\Schema(type="string")
     *    ),
     *    @OA\Parameter(name="age", in="query", description="the age of the user", required=true,
     *        @OA\Schema(type="integer")
     *    ),
     *    @OA\Parameter(name="game", in="query", description="an instance to Games model", required=true
     *    ),
     *     @OA\Response(
     *          response=200, description="Success",
     *          @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example="200"),
     *             @OA\Property(property="data",type="object")
     *          )
     *       )
     *  )
     */
    private $game; // the game model
    private $name; // the name of the user
    private $age; // the age of the user
    private $secretNumber; // will be stored in a cookie and will expire with it.

    public function __construct(){
        $this->game = new Games();
        $this->secretNumber = $this->generateNumber();
    }

    /**
     * 
     * @OA\Post(
     *      path="/game/start/<name>/<age>",
     *      
     *      operationId="initialize",
     *      tags={"Initialization"},
     *      summary="Initialization of the game",
     *      description="Initialize the game",
     *      @OA\RequestBody(
     *         required=true,
     *         
     *     @OA\Response(
     *          response=200, description="Success",
     *          @OA\JsonContent(
     *             @OA\Property(property="result", type="bool", example=""),
     *          )
     *       )
     *  )
     */
    /**
     * Start the game by initializing data and launche the first match by calling combination().
     *
     * @return \Illuminate\Http\Response
     */
    public function start(Request $request)
    {
        if(!$this->checkname($request->name) || !$this->checkage($request->age)){
            die("invalid input");
        }

        // The constant EXPIRE_IN is defined in routes/web.php
        $expire = time() + EXPIRE_IN;
        $request->session()->put('expire_time', $expire);
        $request->session()->put('name', $request->name);
        $request->session()->put('age', $request->age);
        if(setcookie("game", $this->secretNumber, $expire, "/", "", false, true)){
            
            //var_dump($this->secret);
            $this->combination($request);
            return true;   
        }else{
            echo "error setting cookie";
            return false;
        }
    }

    /**
     * @OA\Get(
     *    path="game/combinate/{number}",
     *    operationId="play",
     *    summary="Play a turn in the game",
     *    description="Play a turn in the game of cows and bulls",
     *    @OA\Parameter(name="request", description="The Request object", required=true,
     *        @OA\Schema(type="Request")
     *    ),
     *    @OA\Parameter(name="number", description="The number to guess", required=true,
     *        @OA\Schema(type="string")
     *    ),
     *     @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *          @OA\Property(property="status_code", type="integer", example="200"),
     *           ),
     *        )
     *       )
     *  )
     */
    /**
     * Excecute a "move" in the game, a turn. 
     * @param int $number
     * @return $this->game->combinate should return a JSON value in real life (this one is commented).
     *  It's echoed for testing purposes.
     */
    public function combination(Request $request, $number="999")
    {
        /* If the number is 999, it's the first time this function is running and the user did not send a guess yet. At this stage, the frontend should have a form to prompt the user's guess. */
        if($number != "999"){
            // implement number validation
            if(!$this->checkguess($number, SIZE)){
                die("Invalid number");
            }
            
            if(isset($_COOKIE['game']) && $_COOKIE['game'] == true){
                /* The secret number and the result are echoed for debugging purposes. Comment 
                the vardump and the echo and uncomment the return to get a real JSON response in production */
                var_dump($_COOKIE['game']). " ";
                
                //return response()->json($this->game->combinate($request, $_COOKIE['game'], $number));
                echo response()->json($this->game->combinate($request, $_COOKIE['game'], $number));
                
            }else{
                // todo: call something to save the data
                $this->endGame($request);
                $this->game->cleanSession($request);    
            }
        }
        
    }

    /**
     * This function shows the final message to the user, it's usually shown when the game has timed out
     * and the user did not win. 
     * @param request - Contains data with session and cookies.
     */
    public function endGame(Request $request)
    {
        echo "The game is now ended<br>";
        echo "Status: ".$request->session()->get('status')."<br>";
        echo "Last evaluation: ".$request->session()->get('score')."<br>";
        echo "Attempts: ".$request->session()->get('attempts')."<br>";
    }

    /**
     * The job of this function is to generate the secret number.
     * @param void
     * @return int
     */
    private function generateNumber()
    {
        // array_flip guarantees that choosen number never begins with 0
        return implode(array_rand(array_flip(range(1,9)), SIZE));
    }

    /**
     * checkname - Performs a simple validation for the name
     * @param $name string
     * @return boolean
     */
    private function checkname($name)
    {
        return preg_match("/^[A-Za-z]{2,16}$/", $name);
    }

    /**
     * checkage - Performs a simple validation for age
     * @param $age int
     * @return boolean
     */
    private function checkage($age)
    {
        return preg_match("/^[1-9][0-9]$/", $age);
    }

    /**
     * checkguess - Performs a simple validation for the number
     * @param $g string
     * @param $s constant int
     * @return boolean
     */
    private function checkguess($g, $s)
    {
      return count(str_split($g)) == $s &&
        preg_match("/^[1-9]{{$s}}$/", $g);
    }
}
