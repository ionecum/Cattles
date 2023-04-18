<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * This class will deal with the business logic of the game. Note: I could have used 
 * a private variable for the session but this would force a $this->setRequest in the controller
 * and the logic would be more fragile and bug-prone. I prefer to pass a session parameter to each 
 * method. This is not a resource-consuming operation like hitting the database.
 */
class Games extends Model
{
    use HasFactory;
    
    private $name;
    private $age;
    private $secretNumber;
    private $attempts=0;
    private $evaluation;
    private $outcome;
    private $rank;
    private $size;

    public function __construct($size=4){
        $this->size = $size;
    }

    /**
     * This is where true business logic lives. This function compares the guess that comes 
     * from the user with the secret number and computes evaluation and attempts.
     * @param request - The request contains some data about the game and the very important session.
     * @param string secret - The secret number given by the controller
     * @param string number - The user's provided number
     */
    public function combinate(Request $request, $secret, $number)
    {
        // todo: call something to validate the number
        $request->session()->put('status', 0); // success
        $guess = str_split($number);
        $this->attempts = $request->session()->get('attempts') + 1;
        $request->session()->put(
            'attempts', $this->attempts
        );
        
        if ($number == $secret) {
            echo "You did it in ".$request->session()->get('attempts')." attempts!<br>";
            $request->session()->put('status', 1); // success
            return $this->gameOver($request);
        } else {
            $bulls = 0;
            $cows = 0;
            
            for ($i = 0; $i < $this->size; $i++) {
                for ($j = 0; $j < $this->size; $j++) {
                  if ($secret[$i] === $guess[$j]) {
                    if ($i === $j) {
                      $bulls++;
                    } else {
                      $cows++;
                    }
                  } else continue;
                }
            }

            $request->session()->put('score', $bulls."B".$cows."C");
        }
        return [
            'status' => $request->session()->get('status'),
            'score' => $request->session()->get('score'),
            'attempts' => $request->session()->get('attempts')
        ];

    }

    /**
     * This funcion will show the final message to the user, store all the session data in the database 
     * and clean cookies and session. These action are really small, splitting them in several functions 
     * to comply with the Single Responsability Principle would be an over-refactorization.
     * @param request - the request contains all the necessary stateful data
     * @return void 
     */
    public function gameOver(Request $request){
        // todo: call something to store the data
        // show a message to the user
        echo "The game is ended before timeout<br>";
        echo "Status: ".$request->session()->get('status')."<br>";
        echo "Last evaluation: ".$request->session()->get('score')."<br>";
        echo "Attempts: ".$request->session()->get('attempts')."<br>";
        $this->cleanSession($request);
         
    }

    public function cleanSession(Request $request){
        // destroy the session and make the cookie expire. Game over.
        $request->session()->flush();
        unset($_COOKIE['game']);
        setcookie('game', null, -1, '/'); 
    }

    private function checkguess($g)
    {
      return count(array_unique(str_split($g))) == $this->size &&
        preg_match("/^[1-9]{{$size}}$/", $g);
    }
}
