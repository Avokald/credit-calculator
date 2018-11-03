<?php
session_start();

// https://stackoverflow.com/questions/6287903/how-to-properly-add-csrf-token-using-php
if (empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}
$token = $_SESSION['token'];

include './helpers.php';

include './validateForm.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && formCorrect()) {
	try {
		global $email_address, $price, $initial_payment_percent, $annual_payment_percent, $months;

		printNewLine([$email_address, $price, $initial_payment_percent, $annual_payment_percent, $months]);
		include '../env.php';

		// Connection to database
	    $conn = new PDO("mysql:host=" . SERVERNAME . ";dbname=" . DBNAME, USERNAME, PASSWORD);
	    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	    $conn->exec('use calc;');


	    include './createTables.php';
	    // Table existence check and creation
		if (!tableExists($conn, 'users')) {
			$tableUsers->initializeTable($conn);
		}
		
	    
	    if (!tableExists($conn, 'overviews')) {
	    	$tableOverviews->initializeTable($conn);
	    }


	    if (!tableExists($conn, 'plans')) {
	    	$tablePlans->initializeTable($conn);
	    }

	    if (!tableExists($conn, 'hashes')) {
	    	$tableHashes->initializeTable($conn);
	    }

	    
	    $user_insert = array(
	    	'email_address' => $email_address,
	    	'price' => $price,
	    	'initial_payment_percent' => $initial_payment_percent,
	    	'annual_payment_percent' => $annual_payment_percent,
	    	'months' => $months,
	    );

	    // Queries that insert values into the tables 
		$tableUsers->insertValues($user_insert);

	    $user_id = $conn->lastInsertId('id');




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
	    //$user_created_at = $conn->query('select UNIX_TIMESTAMP(created_at) from users where id = ' . $user_id . ';');
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
	}
	catch(PDOException $e) {
		$conn = null;
	    echo "Connection failed: " . $e->getMessage();
	}

	sendLinkMail($email_address);

	header('Location: result.php?h=' . $user_hash);
	$conn = null;
	die();
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Calculator</title>

<style>
label {
	display: block;
	margin: 0.5em 1em;
}
.error {
	color: red;
	font-weight: bold;
}
</style>
</head>
<body>
<form class="main_form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST">
	<input type="hidden" name="token" value="<?= $token ?>" />
	<label>Почта:
		<input type="text" name="email_address" placeholder="john@example.com" value="<?= $email_address_unsafe ?>">
	</label>
	<?php if (!empty($email_address_err)): ?>
		<p class="error"><?= $email_address_err ?></p>
	<?php endif; ?>
	
	<label>Стоимость недвижимости (не более 50'000'000 тнг.):
		<input type="text" name="price" placeholder="10000000" 
		       max="50000000" min="1" step="1" value="<?= $price_unsafe ?>">
	</label>
	<?php if (!empty($price_err)): ?>
		<p class="error"><?= $price_err ?></p>
	<?php endif; ?>
	
	<label>Первоначальный взнос (не менее 30%):
		<input type="text" name="initial_payment_percent" placeholder="50" 
			   max="100" min="30" step="0.01" value="<?= $initial_payment_percent_unsafe ?>">
	</label>
	<?php if (!empty($initial_payment_percent_err)): ?>
		<p class="error"><?= $initial_payment_percent_err ?></p>
	<?php endif; ?>
	
	<label>Целевой взнос (максимум: 3% минимум: 0.5%):
		<input type="text" name="annual_payment_percent" placeholder="3" 
		       max="3" min="0.5" step="0.01" value="<?= $annual_payment_percent_unsafe ?>">
	</label>
	<?php if (!empty($annual_payment_percent_err)): ?>
		<p class="error"><?= $annual_payment_percent_err ?></p>
	<?php endif; ?>
	
	<label>Срок рассрочки (не более 180 мес.):
		<input type="text" name="months" placeholder="12" 
			   max="180" min="1" step="1" value="<?= $months_unsafe ?>">
	</label>
	<?php if (!empty($months_err)): ?>
		<p class="error"><?= $months_err ?></p>
	<?php endif; ?>

	<input type="submit" name="submit" value="Рассчитать">
</form>
</body>
</html>