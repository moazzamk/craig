<?php

// Initialize DB

$pdo = new PDO('sqlite:cars.db');

$sql = <<<SQL
	CREATE TABLE notes (
		car_id int,
		note_text text
	)
SQL;

$pdo->exec($sql);



