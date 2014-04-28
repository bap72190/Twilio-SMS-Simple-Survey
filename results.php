<?php
// Get the PHP helper library from twilio.com/docs/php/install
require_once('Twilio.php'); // Loads the library
 
 
 if(isset($_GET['live'])) {
	 
	//********SET CONFIGURATION BELOW AND CONFIGURE TABLE LAYOUT AS NEEDED ******************
	// Your Account Sid, Auth Token, and Number from twilio.com/user/account
	$sid = ''; 
	$token = ''; 
	$twilio_number = '';
	$start_date = '2014-03-09'; //start date to retrieve texts from
	$end_date = '2014-03-11'; //last date of texts to retrieve

	$client = new Services_Twilio($sid, $token);
	 
	 $out_arr = array();
	 
	// Loop over the list of messages and echo a property for each one
	foreach ($client->account->sms_messages->getIterator(0, 50, array('DateSent>' => $start_date,'DateSent<' => $end_date,'To' => $twilio_number,)) as $message) {
		$from = $message->from;
		$body = $message->body;
		$date = $message->datesent;
		
		if(!isset($out_arr[$from])){
		$out_arr[$from][] = $body;
		}
		else{
			$out_arr[$from][] = $body;
		}
	}
	$encoded = json_encode($out_arr);
	file_put_contents('logs.txt', $encoded);
	
	echo"<a style='background-color:yellow;'>Live results have been pulled from Twilio. They will be displayed below. If you would like quicker page loads, click <a href='results.php' style='background-color:yellow;'>here</a><a style='background-color:yellow;'> for cached results.</a>";
 }
	
	//display output 
	
	$s = file_get_contents('logs.txt');
	$data = json_decode($s,true);
	?>
 <html>
   <head>
    <style type="text/css">@import "sortable/themes/blue/style.css";</style>
    <script type="text/javascript" src="sortable/jquery-latest.js"></script> 
	<script type="text/javascript" src="sortable/jquery.tablesorter.js"></script>
	<script type="text/javascript">
	$(document).ready(function() 
    { 
        $("#myTable").tablesorter(); 
    } 
	);  
	</script>
   </head>
   <body>
        <br /><p><h1 align="center">Answers to questions</h1></p>
        <table class="tablesorter" id="myTable">
        <thead>
        <tr style='font-size:18px;font-weight:bold;'><th width="181">Cell Number</th><th width="218">Name</th><th width="186">Third Answer</th><th width="498">Fourth Answer</th></tr>
        </thead>
        <tbody>
         <?
           foreach ($data as $key => $value){
                $value = array_reverse($value);
				//Keep increasing array key number for more answers, as seen below
                echo "<tr><td>".$key."</td><td>".$value[0]."</td><td>".$value[1]."</td><td>".$value[2]."</td></tr>";
           }
        
		?></tbody></table><?	
         
         echo"<br /><br /><p><h2 align='center'><u>Raw output for each phone number (messages sorted by oldest to newest)</u></h2></p>";
         foreach ($data as $key => $value){
            $value = array_reverse($value);
            echo "<br /><p style='font-size:20px;font-weight:bolder;'>".$key." = </p>";
            foreach ($value as $result){
                echo "<ul><p>".$result.", </p></ul>";
             }
         }
         ?>
   </body>
</html>