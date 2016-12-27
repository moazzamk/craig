<?php

require __DIR__ . '/vendor/autoload.php';

$keyword = 'camry';
//$link = 'http://dallas.craigslist.org/search/dal/cto?auto_make_model=camry&s=';

$pdo = new PDO('sqlite:cars.db');
$pdo->exec('
	create table cars (
		title varchar(255),
		year int,
		class varchar(255),
		price int,
		miles int,
		city varchar(255),
		status varchar(255),
		condition varchar(255),
		cylinders varchar(255),
		link varchar(255)
	)
');



$client = new GuzzleHttp\Client([
	'base_uri' => 'http://dallas.craigslist.org/search/dal/cto?auto_make_model=' . urlencode($keyword),
	'timeout' => 2.0
]);

$start = 0;
$range = 100;
$matches = [];

$response = get_list($client, $keyword, $start);
$totalPages = preg_first_match('/(?<=totalcount"\>)[0-9]+/', $response);
print "Total results found: $totalPages\n";


for ($i = 0; $i < $totalPages; $i += $range) {
	print $i . PHP_EOL;

	$response = get_list($client, $keyword, $i);
	$links = get_links($response);

	foreach ($links as $link) {
		get_details($pdo, $link);
		sleep(2); // sleep for 2 seconds so craigslist doesnt ban our ip
	}

}

function get_links($body)
{
	$start = strpos($body, '<div class="rows"');
	$end = strpos($body, '<div id="mapcontainer"');
	$subject = substr($body, $start, $end - $start);


	$matches = [];
	preg_match_all('/(?<=<a href="\/dal)[a-zA-Z0-9._\/-]+/', $body, $matches);

	$matches = $matches[0];
	array_walk($matches, function (&$val) {
		$val = '/dal' . $val;
	});

	return array_unique($matches);
}

function get_details($pdo, $link)
{

	$body = file_get_contents('http://dallas.craigslist.org' . $link);

	print 'http://dallas.craigslist.org' . $link . "\n";

	$title = preg_first_match('/(?<=titletextonly">)[^<]+/', $body);

	$year = preg_first_match('/(?<=<p class="attrgroup"><span><b>)[0-9]+/', $body);
	if (empty($year)) {
		$year = preg_first_match('/[0-9]+/', $title);
	}

	$class = get_classy($title);

	$city = preg_first_match('/(?<=<small>).*(?=<\/small>        <span class="js)/', $body);
	$city = str_replace(['(', ')', ' ',], ['', '', ''], $city);



	$price = preg_first_match('/(?<=class="price">)[$a-zA-Z0-9._\/-]+/', $body);
	$price = str_replace('$', '', $price);

	$titleStatus = preg_first_match('/(?<=title status: <b>)[$a-zA-Z0-9._\/-]+/', $body);

	$miles = preg_first_match('/(?<=odometer: <b>)[$a-zA-Z0-9._\/-]+/', $body);
	$miles = str_replace(['k', 'K'], ['000', '000'], $miles);

	$condition = preg_first_match('/(?<=condition: <b>)[$a-zA-Z0-9._\/-]+/', $body);
	$cylinders = preg_first_match('/(?<=cylinders: <b>)[$a-zA-Z0-9._\/-]+/', $body);
	

	$postBodySpos = stripos($body, 'id="postingbody">');
	$postBodyEpos = strpos($body, '</section', $postBodySpos);
	$postBody = substr($body, $postBodySpos + 18, $postBodyEpos  - ($postBodySpos + 18));

//var_dump($postBody);die;



	$sql = <<<SQL

		INSERT INTO cars (
			title,
			year,
			class,
			price,
			miles,
			city ,
			status,
			condition,
			cylinders,
			link
		)
		VALUES (
			'$title',
			'$year' ,
			'$class',
			'$price',
			'$miles',
			'$city' ,
			'$titleStatus',
			'$condition',
			'$cylinders',
			'$link'
		)
SQL;

	$pdo->exec($sql);
}

function get_classy($title)
{
	$classes = [
		'CE',
		'LE',
		'SE',
		'XLE',
	];

	$tmp = explode(' ', $title);
	foreach ($tmp as $item) {
		if (in_array(strtoupper($item), $classes)) {
			return $item;
		}
	}

}


function get_list($client, $keyword, $start=0) 
{
	$response = $client->request('GET', null, [
		'query' => [
			'auto_make_model' => $keyword,
			's' => $start
		]
	]);

	return (string)$response->getBody();
}


function preg_first_match($pattern, $subject)
{
	$matches = [];
	preg_match($pattern, $subject, $matches);

	if (empty($matches[0])) {
		return null;
	}

	return $matches[0];
}




