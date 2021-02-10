<?php

/**
 * This example shows how to handle a simple contact form safely.
 */

//Import PHPMailer class into the global namespace
use PHPMailer\PHPMailer\PHPMailer;

$msg = '';
//Don't run this unless we're handling a form submission
if (array_key_exists('email', $_POST)) {
    date_default_timezone_set('Etc/UTC');

    require 'vendor/autoload.php';

    //Create a new PHPMailer instance
    try {
    $mail = new PHPMailer();
    //Send using SMTP to localhost (faster and safer than using mail()) – requires a local mail server
    //See other examples for how to use a remote server such as gmail
    $mail->isSMTP();
    if(isset($_POST['smtptype']) && strcasecmp($_POST['smtptype'],"1") == 0){
        //Enable SMTP debugging
        // SMTP::DEBUG_OFF = off (for production use)
        // SMTP::DEBUG_CLIENT = client messages
        // SMTP::DEBUG_SERVER = client and server messages
        //$mail->SMTPDebug = SMTP::DEBUG_SERVER;

        //Set the hostname of the mail server
        $mail->Host = $_POST['smtpserver'];
        // use
        // $mail->Host = gethostbyname('smtp.gmail.com');
        // if your network does not support SMTP over IPv6

        //Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
        $mail->Port = $_POST['smtpport'];

        //Set the encryption mechanism to use - STARTTLS or SMTPS
        $mail->SMTPSecure = $_POST['smtpsec'];

        //Whether to use SMTP authentication
        $mail->SMTPAuth = true;

        //Username to use for SMTP authentication - use full email address for gmail
        $mail->Username = $_POST['smtpuser'];

        //Password to use for SMTP authentication
        $mail->Password = $_POST['smtppass'];

    } else {
        
        $mail->Host = 'localhost';
        $mail->Port = 25;
    }
    $mail->XMailer = ' ';
    if(!empty($_POST['timeout'])){
        set_time_limit((int)$_POST['timeout']);
    }
    //Use a fixed address in your own domain as the from address
    //**DO NOT** use the submitter's address here as it will be forgery
    //and will cause your messages to fail SPF checks
    $mail->setFrom($_POST['email'], $_POST['name']);
    //Choose who the message should be sent to
    //Validate address selection before trying to use it
    if (array_key_exists('toemail', $_POST)) {
        $mail->addAddress($_POST['toemail']);
    } else {
        //Fall back to a fixed address if dept selection is invalid or missing
        $mail->addAddress('support@walmart.com');
    }
    //Put the submitter's address in a reply-to header
    //This will fail if the address provided is invalid,
    //in which case we should ignore the whole request
    if ($mail->addReplyTo($_POST['email'], $_POST['name'])) {
        $mail->Subject = $_POST['subject'];
        //Keep it simple - don't use HTML
        $mail->isHTML(true);
        //Build a simple message body
        $mail->Body = $_POST['message'];
        $total = (int)$_POST['count'];
        $delay = empty($_POST['delay'])?1:(int)$_POST['delay'];
        for($i=0;$i<$total;$i++){
            //Send the message, check for errors
            if (!$mail->send()) {
                //The reason for failing to send will be in $mail->ErrorInfo
                //but it's unsafe to display errors directly to users - process the error, log it on your server.
                $msg = 'Sorry, something went wrong. Please try again later.';
            } else {
                $msg = 'Message sent! Thanks for contacting us. Count:' .$i;
            }
            //Sleep
            sleep($delay);
        }
    } else {
        $msg = 'Invalid email address, message ignored.';
    }
    } catch (Exception $e) {
    $msg = $$e->errorMessage(); //Pretty error messages from PHPMailer
}
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Send email</title>
</head>
<body>
<h1>Send Email Tool</h1>
<?php if (!empty($msg)) {
    echo "<h2>$msg</h2>";
} ?>
<form method="POST">
    <label for="name">From Name: <input type="text" name="name" id="name"></label><br>
    <label for="email">From Email address: <input type="email" name="email" id="email"></label><br>
    <label for="subject">Subject: <input type="text" name="subject" id="subject"></label><br>
    <label for="message">Message: <textarea name="message" id="message" rows="20" cols="200"></textarea></label><br>
    <label for="toemail">Send to email <input type="text" name="toemail" id="toemail"></label><br>
    <label for="count">Number of mail <input type="text" name="count" id="count" value="1"></label><br>
    <label for="delay">Delay for Second <input type="text" name="delay" id="delay" value="1"></label><br>
    <label for="timeout">Maximum execution timeout <input type="text" name="timeout" id="timeout" value="260"></label><br>
    <label for="smtp">SMTP Server Type:</label> 
    <select id="smtptype" name="smtptype" >
     <option value="0">Builtin</option>
     <option value="1">External</option>
    </select><br>
    <label for="smtpserver">SMTP Server: <input type="text" name="smtpserver" id="smtpserver"></label><br>
    <label for="smtpport">SMTP Port: <input type="text" name="smtpport" id="smtpport"></label><br>
    <label for="smtpsec">SMTP Sercure Type:</label> 
    <select id="smtpsec" name="smtpsec" >
     <option value="tls">TLS</option>
     <option value="ssl">SSL</option>
    </select><br>
    <label for="smtpuser">SMTP Username: <input type="text" name="smtpuser" id="smtpuser"></label><br>
    <label for="smtppass">SMTP Password: <input type="text" name="smtppass" id="smtppass"></label><br>
    <input type="submit" value="Send">
</form>
</body>
</html>
