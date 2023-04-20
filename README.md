## Bulls and Cows in Laravel
This application reproduces the old game Bulls and Cows. Rules are simple, you have to guess a 4-digits number. If you'll figure out one digit, but in the wrong place, you will get a cow, if you guess the digit in the right place, you will get a bull. 

You'll win if you guess the number before the time limit and lose otherwise. Since a specific time has not been specified in the requirements, I set it to 5 minutes to win or lose a game. A game also involves an evaluation that consist of a string "nBnC" which shows the number of bulls and cows you have collected. You'll also get a rank according to your current score compared to the average of past scores.

### Usage
To initialize the game, simply fetch
http://127.0.0.1:8000/game/start/yourname/35
If game initialized correctly 1 will be diplayed. 

Then fetch
http://127.0.0.1:8000/game/combinate/2345
Where "2345" represent the number you are guessing.
Each request to this url is a "move" in the game. By default the game runs in test mode. You will see a vardump with the secret number to test the application and you could try several numbers and check how the bulls and cows are counted. The game will automatically expire within 5 minutes.

To end the game, simply put a number that matches the secret number. Additional information will be shown, including the database query that was executed. And all the session data will be cleaned up. If you press F5 at that time, you will get an empty board.

If you want a true JSON response, simply comment the line

`echo response()->json($this->game->combinate($request, $_COOKIE['game'], $number));`

and uncomment the line
`return response()->json($this->game->combinate($request, $_COOKIE['game'], $number));`

in GameController.php, lines 63 and 64. You will get a more beautyful response, but you will no longer be able to see the secret number, you will have to play for real. 

In the gameOver method of Game.php, you may want to return a custom status code or JSON response instead of the echos. 
Something similar can be made in the endGame method of GameController.php, this is the method that get called if the game expires and you did not guess the number.

Since no frontend is connected to the project, I'm unsure about what kind of status code or JSON response has to be returned, so I left the question open. 

### Technical Warnings
I. The data consists of name, age, secret_number,score,nb_attempts,evaluation,outcome and rank. It has been stored in a single table because it was a requirement, but in real life, this would be incorrect, since storing these value on a single table would normally break database normalization. 
II. The project will not use user's authentication because this was not in the requirements. In real life you would use a session within the authenticated user's session to time limit the game. This is difficult to perform withtout authenticated users.
III. I have decided to handle endpoints using ordinary http queries through routes/web, because a restful api is basically an interface that receives a query and returns a response in JSON format. I know Laravel has Sanctum and ressourceful routes for API handling, but these are advanced features that are unnecessary for this exercise, plus it was not in the requirements.
IV. The business logic lives in the model but the secret number is generated in the controller. Generating it from the controller or the model directly, doesn't make a true difference, I decided to generate it in the controller, so all the inputs come from the same place.
V. The secret number is stored into a cookie, the other persistent data such as name, age, etc, are stored in a session variable. This because the cookie is a more natural choice for something that will expire after a limited time and the game life is strictly related to the secret number. Hence, the secret number will expire with the game. It would be nice to store everything in the same place, but, in PHP you can't store an array in a cookie, so I decided to store the secret number in a cookie.
VI. I tested most of the application by checking the outputs directly, because I'm not very familiar with testing facilities in Laravel. The last time I used it was 3 years ago.

