<?php

use losthost\DB\DB;

require_once '../vendor/autoload.php';

define('DB_HOST', 'localhost');
define('DB_USER', 'test');
define('DB_NAME', 'test');
define('DB_PREF', 'ober_');

require_once 'dbpass.php';


DB::connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PREF);
DB::dropAllTables(true, true);
