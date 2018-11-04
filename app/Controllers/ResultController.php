<?php

function index() {
	//session_start();

	// https://stackoverflow.com/questions/6287903/how-to-properly-add-csrf-token-using-php
	if (!empty($_POST['token'])) {
	    if (!hash_equals($_SESSION['token'], $_POST['token'])) {
	         echo 'Error';
	         die();
	    }
	}


	try {
		require_once '../env.php';
		// Connection to database
	    $connection = new PDO("mysql:host=" . SERVERNAME . ";dbname=" . DBNAME, USERNAME, PASSWORD);
	    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	    $connection->exec('use calc;');

		require_once '../app/createTables.php';
	    if (empty($_GET['h'])) {
	    	echo '<a href="/">Create your own plan to view this page</a>';
	    	die();
	    }
	    try {
	    	$user_ids = $tableHashes->select(['user_id'])
	    	                        ->from()
	    	                        ->where(['hash', '=', $_GET['h']])
	    	                        ->getSelection();

			if (empty($user_ids)) {
				echo 'Incorrect link';
				die();
			}
	        $user_id = $user_ids[0][0];
		}
		catch(PDOException $e) {
			echo 'Create your own plan to view this page';
			die();
		}

		$overviews = $tableOverviews->select(['initial_payment_currency', 'entrance_fee_currency', 
    	   				'monthly_payment_currency', 'total_overpayment_currency', 
			    	    'overpayment_w_entrance_percent', 'overpayment_w_o_entrance_percent'])
			    	   		          ->from()
			    	   		          ->where(['user_id', '=', $user_id])
			    	   		          ->getSelection();
	    $overview = $overviews[0];

		$plans = $tablePlans->select(['month', 'remaining_credit_currency', 
			'earmarked_contribution_currency', 'share_contribution_currency', 'sum_over_month_currency'])
		                          ->from()
		                          ->where(['user_id', '=', $user_id])
		                          ->getSelection();
		
	    $connection = null;
	    require_once '../resources/views/result.php';
	}
	catch(PDOException $e) {
		$connection = null;
	    echo "Connection failed: " . $e->getMessage();
	}
}