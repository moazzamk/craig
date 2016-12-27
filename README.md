#Craigslist car listings crawler

##Dependencies
- composer (https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx)
- PHP
- SQLite (PDO) extension of PHP

##Installation:
From project's root folder run:

```composer install```

##Running the crawler
```php crawl.php``` will run the crawler and store the data in cars.db in project's root folder


##Web interface
```php -S localhost:9000``` will start the built-in PHP server and you can visit 
```http://localhost:9000/``` to see the listings

##For analytics you might want to connect to SQLite directly which can be done with:
```sqlite3 cars.db```


Note : The crawler currently searches only for Toyota Camrys posted by owners. This can be changed by opening crawl.php and modifying the link on line 27 or changing the keyword on line 5
