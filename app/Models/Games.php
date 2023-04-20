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
    public $timestamps = false;
    protected $table = 'games'; // explicit is better than implicit
    // Fields on databse to save model properties
    protected $fillable = array('name', 'age', 'secret_number', 'attempts', 'evaluation', 'outcome', 'elapsed_time', 'rank');
    private $name;
    private $age;
    private $secret_number;
    private $attempts=0;
    private $evaluation;
    private $outcome;
    private $elapsed_time;
    private $rank;
    private $size;
    

    public function __construct($size=SIZE){
        $this->size = $size;
    }

    /**
     * This is where true business logic lives. This function compares the guess that comes 
     * from the user with the secret number and computes evaluation and attempts.
     * @param Illuminate\Http\Request request - The request contains some data about the game and 
     * the very important session.
     * @param string secret - The secret number given by the controller
     * @param string number - The user's provided number
     */
    public function combinate(Request $request, $secret, $number)
    {
        // todo: call something to validate the number
        $request->session()->put('status', 0); // success
        $guess = str_split($number);
        $this->attempts = $request->session()->get('attempts') + 1;
        $request->session()->put('attempts', $this->attempts);
        $expires = $this->getTimeleft($request);
        $elapsed = $this->getTimeUsed($expires);

        if ($number == $secret) {    
            echo "You did it in ".$elapsed." seconds!<br>";
            $evaluation = $this->evaluate($elapsed, $this->attempts);
            $request->session()->put('evaluation', $evaluation);
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
            $request->session()->put('evaluation', 0.0); 
        }
        $request->session()->put('time_used', $elapsed);
        
        return [
            'status' => $request->session()->get('status'),
            'score' => $request->session()->get('score'),
            'attempts' => $request->session()->get('attempts'),
            'evaluation' => $request->session()->get('evaluation'),
            'timeleft' => $expires->format('%i:%s'),
        ];
    }

    /**
     * This funcion will show the final message to the user, store all the session data in the database 
     * and clean cookies and session. These action are really small, splitting them in several functions 
     * to comply with the Single Responsability Principle would be an over-refactorization.
     * @param Illuminate\Http\Request $request - the request contains all the necessary stateful data
     * @return void 
     */
    public function gameOver(Request $request){
        // todo: call something to store the data
        // show a message to the user
        echo "Game ended before timeout<br>";
        echo "Status: ".$request->session()->get('status')."<br>";
        echo "Last score: ".$request->session()->get('score')."<br>";
        echo "Evaluation: ".$request->session()->get('evaluation')."<br>";
        echo "Attempts: ".$request->session()->get('attempts')."<br>";
        
        $this->saveData($request);
        $this->cleanSession($request);
    }

    /**
     * saveData - This method saves data in the database.
     * @param Illuminate\Http\Request $request
     * @return $game boolean
     */
    public function saveData(Request $request)
    {
        //\DB::enableQueryLog();

        $game = \DB::table('games')->insert(
            [
                'name' => $request->session()->get('name'),
                'age' => $request->session()->get('age'),
                'secret_number'=>$_COOKIE['game'],
                'nb_attempts'=>$request->session()->get('attempts'),
                'evaluation'=>$request->session()->get('evaluation'),
                'outcome'=>$request->session()->get('status'),
                'elapsed_time'=>$request->session()->get('time_used'),
                'rank'=>$this->computeRank($request->session()->get('name')),
            ]
        );
        return $game;
    }

    /**
     * cleanSession - destroys the session and makes the cookie expire.
     * @param Illuminate\Http\Request $request
     * @return void
     */
    public function cleanSession(Request $request)
    {
        $request->session()->flush();
        unset($_COOKIE['game']);
        setcookie('game', null, -1, '/'); 
    }

    /**
     * computeRank - This function calculate and returns the average of evaluations.
     * @param $name string
     * @return float expression
     */
    private function computeRank($name)
    {
        
        \DB::enableQueryLog();
        $resultset = \DB::table('games')->where('name', $name)->pluck('evaluation');
        $i = 0;
        $sum = 0;
        foreach($resultset as $item){
            if(isset($item)){
                $sum += $item;
                $i++;
            }
        }
        if($i !==0){
            return $sum/$i;
        }

        dd(\DB::getQueryLog());
        return 1.1;
    }

    /**
     * getTimeleft - returns the currently available time for the game
     * @param Request request - the request that contains the necessary data
     * @return DateInterval
     */
    private function getTimeleft(Request $request)
    {
        $expiry = $request->session()->get('expire_time');
        $now = new \DateTime('now');        
        $exptime = new \DateTime();
        $exptime->setTimestamp($expiry);
        return $now->diff($exptime);
    }

    /**
     * getTimeUsed - Calculates the time elapsed since the beginning of the game. The format method does 
     * not provide the total number of seconds in the date interval, so this must be computed. The 
     * function finally returns the difference between the EXPIRE constant and that computed number
     * of seconds.
     * @param timeleft - A date interval
     * @return int - An integer value expressing the desired value in seconds.
     */
    private function getTimeUsed($timeleft)
    {
        $seconds = $timeleft->format('%s');
        $minutes = $timeleft->format('%i');
        $totalsecondes = $minutes*60 + $seconds;
        return EXPIRE_IN - $totalsecondes;
    }

    /**
     * evaluate - This method computes the evaluation described in the requirements, that is
     * "Time in seconds / 2 + number of attempts". The second parameter is an int and 
     * must be casted to a float in order to diplay the expected result.
     * @param $t: the time elapsed since the beginning of the game
     * @param $a: the number of attempts made to guess the number
     * @return float expressing the desired evaluation.
     */
    private function evaluate($t, $a){
        return $t/2 + (float)$a;
    }

    
}
