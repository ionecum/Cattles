<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Games;

class GameController extends Controller
{
    private $game; // the game model
    private $name; // the name of the user
    private $age; // the age of the user
    private $secretNumber; // will be stored in a cookie and will expire with it.

    public function __construct(){
        $this->game = new Games();
        $this->secretNumber = $this->generateNumber();
    }
    /**
     * Start the game by initializing data and launche the first match by calling combination().
     *
     * @return \Illuminate\Http\Response
     */
    public function start(Request $request)
    {
        $request->session()->put('name', $request->name);
        $request->session()->put('age', $request->age);
        if(setcookie("game", $this->secretNumber, time()+300, "/", "", false, true)){
            
            //var_dump($this->secret);
            $this->combination($request);    
        }else{
            echo "error setting cookie";
        }
    }

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
    private function generateNumber($SIZE=4)
    {
        // array_flip guarantees that choosen number never begins with 0
        return implode(array_rand(array_flip(range(1,9)), $SIZE));
    }
}
