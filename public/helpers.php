<?php 

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

// To send an email start smtp mail-server on port 25
function sendLinkMail($email_address) {
	$name = 'avokald';
	$email = 'baldykov_a@yahoo.com';
	$message = "Your plan is available: calc.local/result?h={$user_hash}";
	$subject = "Contact form submitted!";
	$body = $message;
	$headers = "From: {$name} at {$email}\r\n";
	$headers .= "Content-type: text/html\r\n";
	mail($email_address, $subject, $body, $headers);
}

// https://stackoverflow.com/questions/1717495/check-if-a-database-table-exists-using-php-pdo
function tableExists($pdo, $table) {
    try {
        $result = $pdo->query("SELECT 1 FROM $table LIMIT 1");
    } catch (Exception $e) {
        return FALSE;
    }
    return $result !== FALSE;
}