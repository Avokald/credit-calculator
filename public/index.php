<?php
session_start();

// https://stackoverflow.com/questions/6287903/how-to-properly-add-csrf-token-using-php
if (empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}
$token = $_SESSION['token'];


function makeSafe($data) {
  	$data = trim($data);
  	$data = stripslashes($data);
  	$data = htmlspecialchars($data);
  	return $data;
}

function printNewLine($args) {
	foreach ($args as $data) {
    	echo '<br>';
    	echo $data;
	}
}

function convertTextToInt(string $str): int {
	$result = '';
	for ($i = 0; $i < strlen($str); $i++) {
		$result .= ord($str[$i]);
	}
	return (int) $result;
}

$email_address_err = $price_err = $initial_payment_percent_err = $annual_payment_percent_err = $months_err = '';
$email_address = $price = $initial_payment_percent = $annual_payment_percent = $months = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$email_address_unsafe = $_POST['email_address'];
	$price_unsafe = $_POST['price'];
	$initial_payment_percent_unsafe = $_POST['initial_payment_percent'];
	$annual_payment_percent_unsafe = $_POST['annual_payment_percent'];
	$months_unsafe = $_POST['months'];

  	if (empty($email_address_unsafe) || !preg_match('/[a-zA-Z0-9\_\.]*\@[a-zA-Z0-9]*\.[a-zA-Z0-9]*/', $email_address_unsafe)) {
    	$email_address_err = 'email_address field is required and should be correct';
  	} 
  	else {
    	$email_address = makeSafe($email_address_unsafe);
  	}


	if (empty($price_unsafe)) {
		$price_err = 'Price field is required and should be correct';
	} 
	else {
		$price = (int) makeSafe($price_unsafe);
		if ($price > 50000000 || $price < 0) {
			$price_err = 'Price field is required and should be correct';
		}
	}


	if (empty($initial_payment_percent_unsafe)) {
		$initial_payment_percent_err = 'initial payment field is required and should be correct';
	} 
	else {
		$initial_payment_percent = (float) makeSafe($initial_payment_percent_unsafe);
		if ($initial_payment_percent > 100 || $initial_payment_percent < 30) {
			$initial_payment_percent_err = 'initial payment field is required and should be correct';
		}
	}


	if (empty($annual_payment_percent_unsafe)) {
		$annual_payment_percent_err = 'Annual payment field is required and should be correct';
	} 
	else {
		$annual_payment_percent = (float) makeSafe($annual_payment_percent_unsafe);
		if ($annual_payment_percent > 3 || $annual_payment_percent < 0.5) {
			$annual_payment_percent_err = 'Annual payment field is required and should be correct';
		}
	}


	if (empty($months_unsafe)) {
		$months_err = 'Months field is required and should be correct';
	} 
	else {
		$months = (int) makeSafe($months_unsafe);
		if ($months > 180 || $months <= 0) {
			$months_err = 'Months field is required and should be correct';
		}
	}

	if (!$email_address_err && !$price_err && !$initial_payment_percent_err && !$annual_payment_percent_err && !$months_err) {

	    // https://stackoverflow.com/questions/1717495/check-if-a-database-table-exists-using-php-pdo
	    function tableExists($pdo, $table) {
		    try {
		        $result = $pdo->query("SELECT 1 FROM $table LIMIT 1");
		    } catch (Exception $e) {
		        return FALSE;
		    }
		    return $result !== FALSE;
		}

		try {
			include('../env.php');
			// Connection to database
		    $conn = new PDO("mysql:host=" . SERVERNAME . ";dbname=" . DBNAME, USERNAME, PASSWORD);
		    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		    $conn->exec('use calc;');
			echo 'connected';

		    // Table existence check and creation
		    if (!tableExists($conn, 'users')) {
		    	$conn->exec('
		    		create table users (
		    			id int not null auto_increment primary key,
		    			email_address varchar(255) not null, 
		        		price int not null, 
		        		initial_payment_percent float not null, 
		        		annual_payment_percent float not null, 
		        		months int not null,
		        		created_at timestamp default current_timestamp
		        	);
		        ');
		    }

		    if (!tableExists($conn, 'overviews')) {
		    	$conn->exec('
	    			create table overviews (
	    				id int not null auto_increment primary key,
	    				initial_payment_currency int not null,
	    				entrance_fee_currency int not null,
	    				monthly_payment_currency int not null,
	    				total_overpayment_currency int not null,
	    				overpayment_w_entrance_percent int not null,
	    				overpayment_w_o_entrance_percent int not null,
		        		user_id int,
		        		foreign key (user_id) references users(id)
	    			);
	    		');
		    }


		    if (!tableExists($conn, 'plans')) {
		    	$conn->exec('
	    			create table plans (
	    				id int not null auto_increment primary key,
	    				month int not null,
	    				remaining_credit_currency int not null,
	    				earmarked_contribution_currency int not null,
	    				share_contribution_currency int not null,
	    				sum_over_month_currency int not null,
		        		user_id int,
		        		foreign key (user_id) references users(id)
	    			);
	    		');
		    }

		    // https://stackoverflow.com/questions/13996565/is-there-a-recommended-size-for-a-mysql-primary-key
		    if (!tableExists($conn, 'hashes')) {
		    	$conn->exec('
		    		create table hashes (
		    			id int not null auto_increment primary key,
		    			hash varchar(255) unique,
		    			user_id int,
		    			foreign key (user_id) references users(id),
		    			index(hash(64))
		    		);
		    	');
		    }


		    // Queries that insert values into the tables 
		    // https://stackoverflow.com/questions/60174/how-can-i-prevent-sql-injection-in-php
    		$create_plan_query = "
    		insert into plans (month,  remaining_credit_currency,  earmarked_contribution_currency,
    		     share_contribution_currency,  sum_over_month_currency,  user_id) 
    			       values(:month, :remaining_credit_currency, :earmarked_contribution_currency,
    			:share_contribution_currency, :sum_over_month_currency, :user_id);
    	    ";

    	    $create_overview_query = "
insert into overviews(initial_payment_currency,  entrance_fee_currency,  monthly_payment_currency,
     total_overpayment_currency,  overpayment_w_entrance_percent,  overpayment_w_o_entrance_percent,  user_id) 
	          values(:initial_payment_currency, :entrance_fee_currency, :monthly_payment_currency,
	:total_overpayment_currency, :overpayment_w_entrance_percent, :overpayment_w_o_entrance_percent, :user_id);
    	    ";

    		$create_user_query = "
    		insert into users (email_address,  price,  initial_payment_percent,  annual_payment_percent,  months) 
			           values(:email_address, :price, :initial_payment_percent, :annual_payment_percent, :months);
		    ";

		    $create_hash_query = "
		    	insert into hashes (hash, user_id)
		    			   values (:hash, :user_id);
		    ";


		    // Prepared queries executed with user data
    		$prepared_create_plan_query = $conn->prepare($create_plan_query);

    		$prepared_create_overview_query = $conn->prepare($create_overview_query);

    		$prepared_create_user_query = $conn->prepare($create_user_query);

    		$prepared_create_hash_query = $conn->prepare($create_hash_query);



		    $user_insert = array(
		    	':email_address' => $email_address,
		    	':price' => $price,
		    	':initial_payment_percent' => $initial_payment_percent,
		    	':annual_payment_percent' => $annual_payment_percent,
		    	':months' => $months,
		    );

		    $prepared_create_user_query->execute($user_insert);

		    $user_id = $conn->lastInsertId('id');



		    // TABLE 2
		    $initial_payment_currency = $price * $initial_payment_percent / 100;
		    $entrance_fee_currency = $price * 0.05;

		    $initial_credit = $price - $initial_payment_currency;
		    $monthly_payment_percent = 1 / 12 * $annual_payment_percent / 100;
		    $monthly_payment_currency = $initial_credit * ($monthly_payment_percent / 
		    	(1 - pow(1 + $monthly_payment_percent, (0 - $months))));

		    // printNewLine([$initial_payment_currency, $entrance_fee_currency, $monthly_payment_percent, $initial_credit,$monthly_payment_currency]);


		    $total_overpayment_currency = $entrance_fee_currency + ($monthly_payment_currency * $months - $initial_credit);
		    $overpayment_w_entrance_percent = $total_overpayment_currency / $initial_credit * 100;
		    $overpayment_w_o_entrance_percent = ($monthly_payment_currency * $months - $initial_credit) / $initial_credit * 100;


		    $overview_insert = array(
		    	':initial_payment_currency' => round($initial_payment_currency),
		    	':entrance_fee_currency' => round($entrance_fee_currency),
		    	':monthly_payment_currency' => round($monthly_payment_currency),
		    	':total_overpayment_currency' => round($total_overpayment_currency),
		    	':overpayment_w_entrance_percent' => round($overpayment_w_entrance_percent),
		    	':overpayment_w_o_entrance_percent' => round($overpayment_w_o_entrance_percent),
		    	':user_id' => $user_id,
		    );
		    $prepared_create_overview_query->execute($overview_insert);



		    // TABLE 3
		    $remaining_credit_currency = $initial_credit;
		    for ($i = 1; $i <= $months; $i++) {
			    $earmarked_contribution_currency = $remaining_credit_currency * $monthly_payment_percent;
			    $share_contribution_currency = $monthly_payment_currency - $earmarked_contribution_currency;
			    $plan_insert = array(
			    	':month' => $i,
			    	':remaining_credit_currency' => round($remaining_credit_currency),
			    	':earmarked_contribution_currency' => round($earmarked_contribution_currency),
			    	':share_contribution_currency' => round($share_contribution_currency),
			    	':sum_over_month_currency' => round($monthly_payment_currency),
			    	':user_id' => $user_id,
		    	);
		    	$prepared_create_plan_query->execute($plan_insert);
		    	$remaining_credit_currency -= $share_contribution_currency;
		    }


		    // TABLE 4
		    $user_created_at = $conn->query('select UNIX_TIMESTAMP(created_at) from users where id = ' . $user_id . ';');
		    // Creating unique hash
		    $user_hash = hash('sha256', (
			    			convertTextToInt($email_address)
			    			. $user_created_at->fetch()[0]
			    			. $price 
			    			. $initial_payment_percent 
			    			. $annual_payment_percent 
			    			. $months)
		    );

		    $hash_insert = array(
		    	':hash' => $user_hash,
		    	':user_id' => $user_id
		    );

		    $prepared_create_hash_query->execute($hash_insert);
		}
		catch(PDOException $e) {
			$conn = null;
		    echo "Connection failed: " . $e->getMessage();
		}


	// 1
	// $to      = 'nobody@example.com';
	// $subject = 'the subject';
	// $message = "Your plan is available: mysite.com/result.php?h={$user_hash}";
	// $headers = 'From: webmaster@example.com' . "\r\n" .
	//     'Reply-To: webmaster@example.com' . "\r\n" .
	//     'X-Mailer: PHP/' . phpversion();
	// mail($to, $subject, $message, $headers);

	// 2
	// require_once('./Mail/Mail.php');
	// $from = '';
	// $to = '';
	// $subject = 'Hi!';
	// $body = "Your plan is available: mysite.com/result.php?h={$user_hash}";

	// $headers = array(
	//     'From' => $from,
	//     'To' => $to,
	//     'Subject' => $subject
	// );

	// $smtp = Mail::factory('smtp', array(
	//         'host' => 'ssl://smtp.gmail.com',
	//         'port' => '465',
	//         'auth' => true,
	//         'username' => '',
	//         'password' => ''
	//     ));

	// $mail = $smtp->send($to, $headers, $body);

	// // if (PEAR::isError($mail)) {
	// //     echo('<p>' . $mail->getMessage() . '</p>');
	// // } else {
	// //     echo('<p>Message successfully sent!</p>');
	// // }


	// 3
	// $name = '';
	// $email = '';
	// $message = "Your plan is available: mysite.com/result.php?h={$user_hash}";
	// $subject = "Contact form submitted!";
	// $to = '';
	// $body = $message;
	// $headers = "From: $email\r\n";
	// $headers .= "Content-type: text/html\r\n";

	// mail($to, $subject, $body, $headers);


	header('Location: result.php?h=' . $user_hash);

	$conn = null;
	die();
	}
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
		<input type="email_address" name="email_address" value="john@example.com">
	</label>
	<?php if (!empty($email_address_err)): ?>
		<p class="error"><?= $email_address_err ?></p>
	<?php endif; ?>
	
	<label>Стоимость недвижимости (тнг):
		<input type="number" name="price" value="1000000" max="50000000" min="0" step="1">
	</label>
	<?php if (!empty($price_err)): ?>
		<p class="error"><?= $price_err ?></p>
	<?php endif; ?>
	
	<label>Первоначальный взнос (%):
		<input type="number" name="initial_payment_percent" value="50" max="100" min="30" step="0.01">
	</label>
	<?php if (!empty($initial_payment_percent_err)): ?>
		<p class="error"><?= $initial_payment_percent_err ?></p>
	<?php endif; ?>
	
	<label>Целевой взнос (%):
		<input type="number" name="annual_payment_percent" value="3" max="3" min="0.5" step="0.01">
	</label>
	<?php if (!empty($annual_payment_percent_err)): ?>
		<p class="error"><?= $annual_payment_percent_err ?></p>
	<?php endif; ?>
	
	<label>Срок рассрочки (мес):
		<input type="number" name="months" value="12" max="180" min="1" step="1">
	</label>
	<?php if (!empty($months_err)): ?>
		<p class="error"><?= $months_err ?></p>
	<?php endif; ?>

	<input type="submit" name="submit" value="Рассчитать">
</form>
</body>
</html>