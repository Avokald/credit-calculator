<?php
session_start();

// https://stackoverflow.com/questions/6287903/how-to-properly-add-csrf-token-using-php
if (!empty($_POST['token'])) {
    if (!hash_equals($_SESSION['token'], $_POST['token'])) {
         echo 'Error';
         die();
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
	    	select user_id
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
        $user_id = $hash_get[0];
	}
	catch(PDOException $e) {
		echo 'Create your own plan to view this page';
		die();
	}

    $select_overview_query = '
        select initial_payment_currency, entrance_fee_currency, monthly_payment_currency, total_overpayment_currency, overpayment_w_entrance_percent, overpayment_w_o_entrance_percent
        from overviews
        where user_id = :user_id;
    ';

    $select_plan_query = '
    	select month, remaining_credit_currency, earmarked_contribution_currency, share_contribution_currency, sum_over_month_currency
    	from plans
    	where user_id = :user_id;
	';
	$prepared_select_plan_query = $conn->prepare($select_plan_query);
    $prepared_select_overview_query = $conn->prepare($select_overview_query);

    $prepared_select_overview_query->execute([':user_id' => $user_id]);
	$prepared_select_plan_query->execute([':user_id' => $user_id]);

	$plans = $prepared_select_plan_query->fetchAll();
    $overview = $prepared_select_overview_query->fetchAll()[0];

	echo "
    <table>
    <tbody>
        <tr>
            <td>Первоначальный взнос : {$overview[0]}</td>
            <td>Вступительный взнос : {$overview[1]}</td>
            <td>Ежемесячный платеж : {$overview[2]}</td>
        </tr>
        <tr>
            <td>Всего переплата : {$overview[3]}</td>
            <td>Переплата с учетом в.в. : {$overview[4]}</td>
            <td>Переплата без учета в.в. : {$overview[5]}</td>
        </tr>
    </tbody>
    </table>
    ";
    echo '
    <table>
    <thead>
        <tr>
            <td>Месяц</td>
            <td>Остаток паенакопления</td>
            <td>Целевой взнос</td>
            <td>Паевый взнос</td>
            <td>Сумма оплат за месяц</td>
        </tr>
    </thead>
    <tbody>
    ';
    foreach($plans as $plan) {
    	echo "<tr>
    			<td>{$plan[0]}</td>
    			<td>{$plan[1]}</td>
    			<td>{$plan[2]}</td>
    			<td>{$plan[3]}</td>
    			<td>{$plan[4]}</td>
    		  </tr>";
    }
    echo '</tbody>';
    echo '</table>';
    $conn = null;
    die();
}
catch(PDOException $e) {
	$conn = null;
    echo "Connection failed: " . $e->getMessage();
}