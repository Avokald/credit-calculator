<?php
session_start();

// https://stackoverflow.com/questions/6287903/how-to-properly-add-csrf-token-using-php
if (!empty($_POST['token'])) {
    if (hash_equals($_SESSION['token'], $_POST['token'])) {
         // Proceed to process the form data
    } else {
         // Log this as a warning and keep an eye on these attempts
    }
}


try {
	include('../env.php');
	// Connection to database
    $conn = new PDO("mysql:host=" . SERVERNAME . ";dbname=" . DBNAME, USERNAME, PASSWORD);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec('use calc;');
    if (empty($_GET['h'])) {
    	echo 'Create your own plan to view this page';
    	die();
    }
    try {
	    $select_hash_query = '
	    	select hash, user_id
	    	from hashes
	    	where hash = :hash;
	    ';

	    $prepared_select_hash_query = $conn->prepare($select_hash_query);

	    if ($prepared_select_hash_query->execute([':hash' => $_GET['h']])) {
		    $hash_get = $prepared_select_hash_query->fetch();
		}

		if (empty($hash_get)) {
			echo 'Incorrect link';
			die();
		}

	}
	catch(PDOException $e) {
		echo 'Create your own plan to view this page';
		die();
	}

	echo 'hello';
    $select_plan_query = '
    	select month, accumulation_balance_currency, earmarked_contribution_currency, share_contribution_currency, sum_over_month_currency
    	from plans
    	where user_id = :user_id;
	';
	echo 'here not ';
	$prepared_select_plan_query = $conn->prepare($select_plan_query);
	$prepared_select_plan_query->execute([':user_id' => $hash_get[1]]);

	$plans = $prepared_select_plan_query->fetchAll();

	echo '<table>';
    foreach($plans as $plan) {
    	echo "<tr>
    			<td>$plan[0]</td>
    			<td>$plan[1]</td>
    			<td>$plan[2]</td>
    			<td>$plan[3]</td>
    			<td>$plan[4]</td>
    		  </td>";
    }
    echo '</table>';
    echo 'end';
}
catch(PDOException $e) {
	$conn = null;
    echo "Connection failed: " . $e->getMessage();
}