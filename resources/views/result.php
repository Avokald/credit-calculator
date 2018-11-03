<?php 

?>
<table>
<tbody>
    <tr>
        <td>Первоначальный взнос : <?= $overview[0] ?></td>
        <td>Вступительный взнос : <?= $overview[1] ?></td>
        <td>Ежемесячный платеж : <?= $overview[2] ?></td>
    </tr>
    <tr>
        <td>Всего переплата : <?= $overview[3] ?></td>
        <td>Переплата с учетом в.в. : <?= $overview[4] ?></td>
        <td>Переплата без учета в.в. : <?= $overview[5] ?></td>
    </tr>
</tbody>
</table>

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
<?php foreach($plans as $plan) { ?>
    <tr>
        <td><?= $plan[0] ?></td>
        <td><?= $plan[1] ?></td>
        <td><?= $plan[2] ?></td>
        <td><?= $plan[3] ?></td>
        <td><?= $plan[4] ?></td>
    </tr>
<?php } ?>
</tbody>
</table>
