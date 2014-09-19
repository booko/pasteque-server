<?php

// This is the paypal asynchronous notification callback.
// When a payment is done with paypal a message is sent to this page
// in order to check all parameters and do post-payment process.
//
// First step is to acknowledge the received message by sending it back
// to paypal.
//
// Second step is to verify the content of the message.
//
// Third step is validating the subscription or refusing the payment.

namespace Pasteque;

function reset_log() {
	$f = fopen( dirname( __FILE__ ) . "/log.txt", "w+b" );
	fclose( $f );
}

function die_log( $log ) {
	$f = fopen( dirname( __FILE__ ) . "/log.txt", "a+b" );
	if ( $f !== false ) {
		fwrite( $f, $log . "\n" );
		fclose( $f );
	}
	die( $log );
}

function _log( $log ) {
	$f = fopen( dirname( __FILE__ ) . "/log.txt", "a+b" );
	if ( $f !== false ) {
		fwrite( $f, $log . "\n" );
		fclose( $f );
	}
	echo( $log . "\n" );
}

reset_log();
_log("starting");

$sandbox = ( $_POST['test_ipn'] == 1 );

// First step: acknowledgment
// read the post from PayPal system and add 'cmd'
$req = 'cmd=_notify-validate';

foreach ($_POST as $key => $value) {
	$enc_value = urlencode(stripslashes($value));
	$req .= "&$key=$enc_value";
	_log("POST " . $key . "=" . $value);
}
if ( $sandbox ) {
	$url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
	$host = "Host: www.sandbox.paypal.com";
} else {
	$url = "https://www.paypal.com/cgi-bin/webscr";
	$host = "Host: www.paypal.com";
}
// post back to PayPal system to validate
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_HTTPHEADER, array($host));
$res = curl_exec($ch);

if ( $res === false ) {
	$error = curl_error( $ch );
	curl_close( $ch );
	die_log( "Curl error " . $error );
}
if ( strcmp( $res, "VERIFIED" ) == 0 ) {
	// paypal received the acknowledgment
	$ack = true;
	// check that payment_amount/payment_currency are correct
	// process payment
} else if ( strcmp( $res, "INVALID" ) == 0 ) {
	// log for manual investigation
	die_log( "INVALID response" );
} else {
	die_log( "Unknown response " . $res );
}
curl_close( $ch );

if ( ! $ack ) {
	// Error. Will retry on next notification
	die_log( "No acknowledgment " . $res );
}

_log( "Valid response from Paypal Received." );

// Second step: verify content
require_once(__DIR__ . "/inc/constants.php");
PT::$ABSPATH = __DIR__;
require_once(PT::$ABSPATH . "/inc/load.php");

$cfg = getConfig();
$paypal_email = $cfg['pp_email'];
$paypal_sandbox_email = $cfg['pp_sandbox_email'];

_log( "Pasteque data loaded." );

// assign posted variables to local variables
$item_count = $_POST['num_cart_items'];
$prd_ids = array();
for ($i = 0; $i < $item_count; $i++) {
    $prd_ids[] = $_POST['item_number' . ($i + 1)];
}
$payment_status = $_POST['payment_status'];
$payment_amount = $_POST['mc_gross'] - $_POST['tax'];
$payment_currency = $_POST['mc_currency'];
$txn_id = $_POST['txn_id'];
$receiver_email = $_POST['receiver_email'];
$custom = $_POST['custom'];
$user_email = $_POST['payer_email'];
$user_id = $_POST['custom'];

_log("POST data retreived");

$pdo = PDOBuilder::getPDO($user_id);
_log("pdo");
$db = new DB('mysql');

_log("DB connexion established");

// Check transaction state
if ( $payment_status != "Completed" ) {
	// Transaction is not completed, just ignore it.
	die_log("Transaction not completed.");
}
_log("Payment status OK");

// Check email
if ( ( ! $sandbox && $receiver_email != $paypal_email )
     || ( $sandbox && $receiver_email != $paypal_sandbox_email ) ) {
	// This was not for us
	die_log("Receiver email is not correct " . $receiver_email );
}
_log("Receiver email OK");

// Check if it is not already parsed
//$sqlTxnChk = "select transaction from transactions where transaction = :t";
//$stmtTxnChk = $pdo->prepare($sqlTxnChk);
//$stmtTxnChk->bindParam(":t", $txn_id);
//if ($stmtTxnChk->execute() === false) {
//    $err = $stmtTxnChk->errorInfo();
//    die_log("Transaction sql error " . $err[2]);
//}
//if ($stmtTxnChk->fetch() !== false) {
//	die_log("Transaction already processed " . $txn_id);
//}
//$sqlTxnIns = "insert into transactions (transaction) value (:t)";
//$stmtTxnIns = $pdo->prepare($sqlTxnIns);
//$stmtTxnIns->bindParam(":t", $txn_id);
//if ($stmtTxnIns->execute() === false) {
//    $err = $stmtTxnChk->errorInfo();
//	die_log("Cannot insert txn id " . $txn_id . " " . $err[2]);
//}
//_log("Transaction registered");

// Check amount
$modules = $cfg['pp_modules'];
$total = 0;
foreach ($prd_ids as $prd) {
    foreach ($modules as $module) {
        if ($prd == $module['module']) {
            $total += $module['price'];
            break;
        }
    }
}
if (abs($total - $payment_amount) > 0.005) { // Float arithmetic
    // Amount mismatch, notify it
    if ( $sandbox ) {
		$dest = $paypal_sandbox_email;
	} else {
		$dest = $paypal_email;
	}
    $mail_body = "Vous avez reçu une transaction d'un montant incorrect de  "
            . $payment_amount . $payment_currency . " pour " . $user_id
            . " <" . $user_email . ">. Ci-dessous le détail de la commande :"
            . "\n\n";
    foreach ($prd_ids as $prd) {
        $mail_body .= $prd . "\n";
    }
    mail( $paypal_email, "Commande erronée", $mail_body);
	die_log( "Wrong payment amount or currency " . $payment_amount
	         . $payment_currency . "instead of " . $total);
}

_log( "Transaction check passed." );

// Update modules
$modules_string = implode(",", $prd_ids);
_log("Modules " . $modules_string);
$sql = "update MODULES set modules = concat(modules, \",\", :mod) where user_id = :id";
$stmt = $pdo->prepare($sql);
_log("Statement prepared");
$stmt->bindParam(":mod", $modules_string);
$stmt->bindParam(":id", $user_id);
_log("executing " . $sql . " with user " . $user_id . " and mods " . $modules_string);
if (!$stmt->execute()) {
    if ( $sandbox ) {
		$dest = $paypal_sandbox_email;
	} else {
		$dest = $paypal_email;
	}
    $mail_body = "La commande de $user_id <" . $user_email . "> n'a pas abouti."
            . " Ci-dessous le détail de la commande et l'erreur d'execution :"
            . "\n\n";
    foreach ($prd_ids as $prd) {
        $mail_body .= $prd . "\n";
    }
    $err = $stmt->errorInfo();
    $mail_body .= "\n\n" . $sql . "\n"
            . "SQLSTATE: " . $err[0] . "\n"
            . "CODE: " . $err[1] . "\n"
            . "MESSAGE: " . $err[2];
    mail( $paypal_email, "Erreur lors du traitement d'une commande", $mail_body);
	die_log( "Parse error " . $sql . " - "  . $err[2]);
}
