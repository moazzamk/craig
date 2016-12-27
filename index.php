<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$pdo = new PDO('sqlite:cars.db');

$sql = <<<SQL
	SELECT 
		price 
	FROM cars
SQL;

$sql = <<<SQL
select
	 title,
		year ,
		class ,
		price ,
		miles ,
		city ,
		status, 
		condition,
		cylinders,
		'<a href="http://dallas.craigslist.org/' || link || '" target="_blank">adf</a>' as link
 
from cars 
where miles < 150000 
	and year > 2005 
	and  status not in ('rebuilt', 're-built', 'salvage') 
order by price, year, city

SQL;



$rs = $pdo->query($sql);
$rows = $rs->fetchAll(PDO::FETCH_ASSOC);

?>

<table>
	<tr>
		<?php
			$keys = array_keys($rows[0]);
			foreach ($keys as $key) {
				print "<th>{$key}</th>";
			}

		?>
	</tr>
	<?php foreach ($rows as $row): ?>
		<tr>
			<?php foreach ($row as $item): ?>
				<td> <?= $item; ?> </td>
			<?php endforeach; ?>
		</tr>
	<?php endforeach; ?>
</table>
