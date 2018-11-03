<?php 
	global $email_address_err, $price_err, $initial_payment_percent_err, $annual_payment_percent_err, $months_err;
	global $email_address, $price, $initial_payment_percent, $annual_payment_percent, $months;
	global $email_address_unsafe, $price_unsafe, $initial_payment_percent_unsafe, $annual_payment_percent_unsafe, $months_unsafe;
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
		<input type="email_address" name="email_address" placeholder="john@example.com" value="<?= $email_address_unsafe ?>">
	</label>
	<?php if (!empty($email_address_err)): ?>
		<p class="error"><?= $email_address_err ?></p>
	<?php endif; ?>
	
	<label>Стоимость недвижимости (не более 50'000'000 тнг.):
		<input type="number" name="price" placeholder="10000000" 
		       max="50000000" min="1" step="1" value="<?= $price_unsafe ?>">
	</label>
	<?php if (!empty($price_err)): ?>
		<p class="error"><?= $price_err ?></p>
	<?php endif; ?>
	
	<label>Первоначальный взнос (не менее 30%):
		<input type="number" name="initial_payment_percent" placeholder="50" 
			   max="100" min="30" step="0.01" value="<?= $initial_payment_percent_unsafe ?>">
	</label>
	<?php if (!empty($initial_payment_percent_err)): ?>
		<p class="error"><?= $initial_payment_percent_err ?></p>
	<?php endif; ?>
	
	<label>Целевой взнос (максимум: 3% минимум: 0.5%):
		<input type="number" name="annual_payment_percent" placeholder="3" 
		       max="3" min="0.5" step="0.01" value="<?= $annual_payment_percent_unsafe ?>">
	</label>
	<?php if (!empty($annual_payment_percent_err)): ?>
		<p class="error"><?= $annual_payment_percent_err ?></p>
	<?php endif; ?>
	
	<label>Срок рассрочки (не более 180 мес.):
		<input type="number" name="months" placeholder="12" 
			   max="180" min="1" step="1" value="<?= $months_unsafe ?>">
	</label>
	<?php if (!empty($months_err)): ?>
		<p class="error"><?= $months_err ?></p>
	<?php endif; ?>

	<input type="submit" name="submit" value="Рассчитать">
</form>
</body>
</html>