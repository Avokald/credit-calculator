<?php

$email_address_err = $price_err = $initial_payment_percent_err = $annual_payment_percent_err = $months_err = '';
$email_address = $price = $initial_payment_percent = $annual_payment_percent = $months = '';
$email_address_unsafe = $price_unsafe = $initial_payment_percent_unsafe = $annual_payment_percent_unsafe = $months_unsafe = '';

function formCorrect() {
	global $email_address_err, $price_err, $initial_payment_percent_err, $annual_payment_percent_err, $months_err;
	global $email_address, $price, $initial_payment_percent, $annual_payment_percent, $months;
	global $email_address_unsafe, $price_unsafe, $initial_payment_percent_unsafe, $annual_payment_percent_unsafe, $months_unsafe;

	$email_address_unsafe = $_POST['email_address'];
	$price_unsafe = $_POST['price'];
	$initial_payment_percent_unsafe = $_POST['initial_payment_percent'];
	$annual_payment_percent_unsafe = $_POST['annual_payment_percent'];
	$months_unsafe = $_POST['months'];


	$email_address = makeSafe($email_address_unsafe);
  	if (empty($email_address_unsafe) 
  		|| !preg_match('/^[a-zA-Z0-9\_\.]*\@[a-zA-Z0-9]*\.[a-zA-Z0-9]*/', $email_address_unsafe) 
  		|| (strlen($email_address_unsafe) !== strlen($email_address))) {
    	
    	$email_address_err = 'email_address field is required and should be correct';
  	} 

  	$price = (int) makeSafe($price_unsafe);
	if (empty($price_unsafe) 
		|| !preg_match('/^[0-9]*/', $price_unsafe)
		|| (strlen($price) !== strlen($price_unsafe))
		|| ($price > 50000000)
		|| ($price < 1)) {
		
		$price_err = 'Price field is required and should be correct';
	}

	$initial_payment_percent = (float) makeSafe($initial_payment_percent_unsafe);
	if (empty($initial_payment_percent_unsafe)
	    || !preg_match('/^[0-9\.]*/', $initial_payment_percent_unsafe)
	    || (strlen($initial_payment_percent) !== strlen($initial_payment_percent_unsafe))
	    || ($initial_payment_percent > 100)
	    || ($initial_payment_percent < 30)) {
		
		$initial_payment_percent_err = 'initial payment field is required and should be correct';
	}


	$annual_payment_percent = (float) makeSafe($annual_payment_percent_unsafe);
	if (empty($annual_payment_percent_unsafe)
		|| !preg_match('/^[0-9\.]*/', $annual_payment_percent_unsafe)
		|| (strlen($annual_payment_percent) !== strlen($annual_payment_percent_unsafe))
		|| ($annual_payment_percent > 3)
		|| ($annual_payment_percent < 0.5)) {
		
		$annual_payment_percent_err = 'Annual payment field is required and should be correct';
	}

	$months = (int) makeSafe($months_unsafe);	
	if (empty($months_unsafe)
		|| !preg_match('/^[0-9]*/', $annual_payment_percent_unsafe)
		|| (strlen($months) !== strlen($months_unsafe))
		|| ($months > 180)
		|| ($months < 1)) {
		
		$months_err = 'Months field is required and should be correct';
	}

	return (!$email_address_err && !$price_err && !$initial_payment_percent_err && !$annual_payment_percent_err && !$months_err);
}

