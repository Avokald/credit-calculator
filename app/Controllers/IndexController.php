<?php 


function store() {
	include '../app/validateForm.php';

	if (!formCorrect()) {
		include '../resources/views/index.php';
	}
	else {
		try {
			global $email_address, $price, $initial_payment_percent, $annual_payment_percent, $months;

			// Connection to database
			include '../env.php';
		    $connection = new PDO("mysql:host=" . SERVERNAME . ";dbname=" . DBNAME, USERNAME, PASSWORD);
		    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		    $connection->exec('use calc;');


		    include '../app/createTables.php';
		    // Table existence check and creation
			if (!tableExists($connection, 'users')) {
				$tableUsers->initializeTable();
			}
			
		    
		    if (!tableExists($connection, 'overviews')) {
		    	$tableOverviews->initializeTable();
		    }


		    if (!tableExists($connection, 'plans')) {
		    	$tablePlans->initializeTable();
		    }

		    if (!tableExists($connection, 'hashes')) {
		    	$tableHashes->initializeTable();
		    }

		    
		    $user_insert = array(
		    	'email_address' => $email_address,
		    	'price' => $price,
		    	'initial_payment_percent' => $initial_payment_percent,
		    	'annual_payment_percent' => $annual_payment_percent,
		    	'months' => $months,
		    );

		    // Queries that insert values into the tables 
		    $connection->beginTransaction();
			$tableUsers->insertValues($user_insert);

		    $user_id = $connection->lastInsertId('id');




		    // TABLE 2
		    $initial_payment_currency = $price * $initial_payment_percent / 100;
		    $entrance_fee_currency = $price * 0.05;

		    $initial_credit = $price - $initial_payment_currency;
		    $monthly_payment_percent = 1 / 12 * $annual_payment_percent / 100;
		    $monthly_payment_currency = $initial_credit * ($monthly_payment_percent / 
		    	(1 - pow(1 + $monthly_payment_percent, (0 - $months))));

		    $total_overpayment_currency = $entrance_fee_currency + ($monthly_payment_currency * $months - $initial_credit);
		    $overpayment_w_entrance_percent = $total_overpayment_currency / $initial_credit * 100;
		    $overpayment_w_o_entrance_percent = ($monthly_payment_currency * $months - $initial_credit) / $initial_credit * 100;


		    $overview_insert = array(
		    	'initial_payment_currency' => round($initial_payment_currency),
		    	'entrance_fee_currency' => round($entrance_fee_currency),
		    	'monthly_payment_currency' => round($monthly_payment_currency),
		    	'total_overpayment_currency' => round($total_overpayment_currency),
		    	'overpayment_w_entrance_percent' => round($overpayment_w_entrance_percent),
		    	'overpayment_w_o_entrance_percent' => round($overpayment_w_o_entrance_percent),
		    	'user_id' => $user_id,
		    );

		    $tableOverviews->insertValues($overview_insert);



		    // TABLE 3
		    $remaining_credit_currency = $initial_credit;
		    for ($i = 1; $i <= $months; $i++) {
			    $earmarked_contribution_currency = $remaining_credit_currency * $monthly_payment_percent;
			    $share_contribution_currency = $monthly_payment_currency - $earmarked_contribution_currency;
			    $plan_insert = array(
			    	'month' => $i,
			    	'remaining_credit_currency' => round($remaining_credit_currency),
			    	'earmarked_contribution_currency' => round($earmarked_contribution_currency),
			    	'share_contribution_currency' => round($share_contribution_currency),
			    	'sum_over_month_currency' => round($monthly_payment_currency),
			    	'user_id' => $user_id,
		    	);
		    	$tablePlans->insertValues($plan_insert);
		    	$remaining_credit_currency -= $share_contribution_currency;
		    }


		    // TABLE 4
		    $user_created_at = $tableUsers->select(['UNIX_TIMESTAMP(created_at)'])
		                                  ->from()
		                                  ->where(['id = ' . $user_id])
		                                  ->getSelection();
		    
		    // Creating unique hash
		    $user_hash = hash('sha256', (
			    			convertTextToInt($email_address)
			    			. $user_created_at[0][0]
			    			. $price 
			    			. $initial_payment_percent 
			    			. $annual_payment_percent 
			    			. $months)
		    );

		    $hash_insert = array(
		    	'hash' => $user_hash,
		    	'user_id' => $user_id
		    );

		    $tableHashes->insertValues($hash_insert);
		    $connection->commit();

			sendLinkMail($email_address, $user_hash);

			include '../resources/views/success.php';
			$connection = null;
		}
		catch(PDOException $e) {
			$connection->rollBack();
			$connection = null;
		    echo "Connection failed: " . $e->getMessage();
		}
	}
}

function index() {
	include '../resources/views/index.php';
}

