<?php

include('./Table.php');


$tableUsers = new Table($conn, 'users', function($table) {
	$table->createColumn('id')
		  ->setType('int')
		  ->setAutoIncrement()
		  ->setPrimaryKey();

	$table->createColumn('email_address')
		  ->setType('varchar(255)');

	$table->createColumn('price')
	      ->setType('int');

	$table->createColumn('initial_payment_percent')
	      ->setType('float');

	$table->createColumn('annual_payment_percent')
	      ->setType('float');

	$table->createColumn('months')
	      ->setType('int');

	$table->createColumn('created_at')
	      ->setType('timestamp')
	      ->setDefaultTimestamp();
	}
);

$tableOverviews = new Table($conn, 'overviews', function($table) {
	$table->createColumn('id')
		  ->setType('int')
		  ->setAutoIncrement()
		  ->setPrimaryKey();

    $table->createColumn('initial_payment_currency')
    	  ->setType('int');

    $table->createColumn('entrance_fee_currency')
    	  ->setType('int');

    $table->createColumn('monthly_payment_currency')
    	  ->setType('int');

    $table->createColumn('total_overpayment_currency')
    	  ->setType('int');

    $table->createColumn('overpayment_w_entrance_percent')
    	  ->setType('int');

    $table->createColumn('overpayment_w_o_entrance_percent')
    	  ->setType('int');

    $table->createColumn('user_id')
          ->setType('int')
          ->setNullable();

    $table->setForeignKey('user_id', 'users', 'id');
});

$tablePlans = new Table($conn, 'plans', function($table) {
	$table->createColumn('id')
	      ->setType('int')
	      ->setAutoIncrement()
	      ->setPrimaryKey();

	$table->createColumn('month')
	      ->setType('int');

	$table->createColumn('remaining_credit_currency')
	      ->setType('int');

	$table->createColumn('earmarked_contribution_currency')
	      ->setType('int');

	$table->createColumn('share_contribution_currency')
	      ->setType('int');

	$table->createColumn('sum_over_month_currency')
	      ->setType('int');

	$table->createColumn('user_id')
	      ->setType('int')
	      ->setNullable();

	$table->setForeignKey('user_id', 'users', 'id');
});

$tableHashes = new Table($conn, 'hashes', function($table) {
	$table->createColumn('id')
	      ->setType('int')
	      ->setAutoIncrement()
	      ->setPrimaryKey();
	$table->createColumn('hash')
	      ->setType('varchar(255)')
	      ->setUnique();

	$table->createColumn('user_id')
	      ->setType('int');

	$table->setForeignKey('user_id', 'users', 'id');

    // https://stackoverflow.com/questions/13996565/is-there-a-recommended-size-for-a-mysql-primary-key
	$table->setIndex('hash(64)');
});


