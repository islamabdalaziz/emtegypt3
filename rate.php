<?php
/*
Created by: Ramy Abd Alaziz

*/

// ------------- CONFIGURABLE SECTION ------------------------

$mailto = 'req@emtegypt.com' ;
$subject = "Rate Request from the EMT website" ;
$formurl = "http://www.emtegypt.com/rate.html" ;
$errorurl = "http://www.emtegypt.com/error.html" ;
$thankyouurl = "http://www.emtegypt.com/thankyou.html" ;

$email_is_required = 1;
$name_is_required = 1;
$comments_is_required = 1;
$uself = 0;
$use_envsender = 0;
$use_sendmailfrom = 0;
$smtp_server_win = '' ;
$use_webmaster_email_for_from = 0;
$use_utf8 = 1;
$my_recaptcha_private_key = '' ;

// -------------------- END OF CONFIGURABLE SECTION ---------------

define( 'MAX_LINE_LENGTH', 998 );
$headersep = (!isset( $uself ) || !$uself) ? "\r\n" : "\n" ;
$content_type = (!isset( $use_utf8 ) || ($use_utf8 == 0)) ? 'Content-Type: text/plain; charset="iso-8859-1"' : 'Content-Type: text/plain; charset="utf-8"' ;
if (!isset( $use_envsender )) { $use_envsender = 0 ; }
if (isset( $use_sendmailfrom ) && $use_sendmailfrom) {
	ini_set( 'sendmail_from', $mailto );
}
if (isset( $smtp_server_win ) && strlen($smtp_server_win)) {
	ini_set( 'SMTP', $smtp_server_win );
}
$envsender = "-f$mailto" ;
$fullname = (isset($_POST['fullname']))? $_POST['fullname'] : $_POST['name'] ;
$email = $_POST['email'] ;
$comments = $_POST['comments'] ;
$http_referrer = getenv( "HTTP_REFERER" );

if (!isset($_POST['email'])) {
	header( "Location: $formurl" );
	exit ;
}
if (($email_is_required && (empty($email) || !preg_match('/@/', $email))) || ($name_is_required && empty($fullname)) || ($comments_is_required && empty($comments))) {
	header( "Location: $errorurl" );
	exit ;
}
if ( preg_match( "/[\r\n]/", $fullname ) || preg_match( "/[\r\n]/", $email ) ) {
	header( "Location: $errorurl" );
	exit ;
}
if (strlen( $my_recaptcha_private_key )) {
	require_once( 'recaptchalib.php' );
	$resp = recaptcha_check_answer ( $my_recaptcha_private_key, $_SERVER['REMOTE_ADDR'], $_POST['recaptcha_challenge_field'], $_POST['recaptcha_response_field'] );
	if (!$resp->is_valid) {
		header( "Location: $errorurl" );
		exit ;
	}
}
if (empty($email)) {
	$email = $mailto ;
}
$fromemail = (!isset( $use_webmaster_email_for_from ) || ($use_webmaster_email_for_from == 0)) ? $email : $mailto ;

if (function_exists( 'get_magic_quotes_gpc' ) && get_magic_quotes_gpc()) {
	$comments = stripslashes( $comments );
}

$messageproper =
	"This message was sent from:\n" .
	"$http_referrer\n" .
	"------------------------------------------------------------\n" .
	"Name of sender: $fullname\n" .
	"Email of sender: $email\n" .
	"------------------------- COMMENTS -------------------------\n\n" .
	wordwrap( $comments, MAX_LINE_LENGTH, "\n", true ) .
	"\n\n------------------------------------------------------------\n" ;

$headers =
	"From: \"$fullname\" <$fromemail>" . $headersep . "Reply-To: \"$fullname\" <$email>" . $headersep . "X-Mailer: chfeedback.php 2.16.0" .
	$headersep . 'MIME-Version: 1.0' . $headersep . $content_type ;

if ($use_envsender) {
	mail($mailto, $subject, $messageproper, $headers, $envsender );
}
else {
	mail($mailto, $subject, $messageproper, $headers );
}
header( "Location: $thankyouurl" );
exit ;

?>
