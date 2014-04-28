<?
// start the session
session_start();
	
//Config info here
// Your Account Sid from twilio.com/user/account **USED TO VERIFY THE SCRIPT CALLING THIS PAGE IS TWILIO AND NOT A ROGUE SITE
$sid = ""; 
$token = ""; 

$num_questions = 3;//number of questions follow format below when adding new questions.
$q1 = "Thanks, here is the second question?";
$q2 = "Awesome, last question. Here is the last wuestion?";
$q3 = "Great! Thanks for participating in this survey!";

//response to users who text in for a survey they already completed.
$already_complete = "Thanks! You have already completed this survey.";
	
header("content-type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";

?>
<Response><Message>
<? //can add from="" to sms verb above and specify a phone number to use for this response
    

error_reporting(E_ERROR);//Disable errors from being displayed

//Get sms message details supplied by Twilio
$from = $_POST['From'];
$from = substr($from, 2);
$to = $_POST['To'];
$to = substr($to, 2);
$body = trim($_POST['Body']);//Get message and trim whitespace before and after the first and last words
$body_lwr = strtolower($body);//Change body to lower case, to avoid missing stop or help becuase of case sensitivity
$accountid = $_POST['AccountSid'];


//** STANDARD RESPONSES FOR HELP AND STOP TEXTS. REQUIRED BY TWILIO!
if($accountid == $sid) {//check if the file calling this script is the correct one.
	if ($body_lwr == 'stop') {//stop texts to user
		echo"You will no longer receive texts from this number.";	
	} else if ($body_lwr == 'help') {//send help text
		echo"No help is offered for this service. To stop texts, reply STOP.";	
	}
	else if (($body_lwr == 'clear') && ($debug==true))
	{
		$_SESSION = array();//clear session --this is for debugging and testing purposes.
		echo"Session cleared. You can now continue as if your session had never existed. If you want to clear session, but continue where left off, use clearhidden.";
	}
	else if (($body_lwr == 'clearhidden') && ($debug==true))
	{
		$_SESSION = array();//clear session --this is for debugging and testing purposes.
		echo"";
	}
	
else{//Start the question sequence now
	
	if($_SESSION['active'] != 'yes'){//users is not in an active session, lets check to see if their previous one expired, else lets start a new one for them.
		//Lets get the users last message so we can figure out what question we were on when their session expired!
		// Get the PHP helper library from twilio.com/docs/php/install
		include_once('Twilio.php'); // Loads the library
		$client = new Services_Twilio($sid, $token);
		
		// Loop over the list of messages and echo most recent message user received
		foreach ($client->account->sms_messages->getIterator(0, 50, array(
			//'DateSent>' => '2014-03-09',
			//'DateSent<' => '2014-03-09',
			'From' => $to, //  filter by 'From'...
			'To' => $from, // ...or by 'To'
		)) as $message) {
			$last_msg = $message->body;
			break;//end for loop after one message, thats all we need!
		}
	
	
		for($x=1; $x<=$num_questions; $x++) {//figure out if user was on a question or not, if they were, restore their session
			$q_var = 'q'.$x;
			if($last_msg == $$q_var) {
				$current_q = $x;
			}
		}
	
		if(!isset($current_q))
			$current_q = 0;
			
		if($last_msg == $already_complete)//user alrady completed survey, set current question to last one so it will tell them they already completed survey
			$current_q = $num_questions;
		
		$_SESSION['currentq'] = $current_q;
		$_SESSION['active'] = 'yes';//set session active so that they can continue from where they left off when replying
		
	}//end else for checking if session is active
	
	//Now lets interpret the users response and feed them their next question, if there is one!
	if($_SESSION['active'] == 'yes'){
		
		if($_SESSION['currentq'] >= $num_questions) {//check if user has already completed the survey
			echo $already_complete;	
		}else{
			$next_question = $_SESSION['currentq']+1;
			$question_var = 'q'.$next_question;
			echo $$question_var;//display next question to user
			$_SESSION['currentq'] = $next_question;//set session to next question
		}
		
	}//if session is active
	
}//End question else statement

}//End account sid check if statement

else {
	echo"Server error. Please contact an admin for help.";
	}

?>
</Message></Response>

