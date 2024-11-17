<?php

use losthost\DB\DB;

$sth = DB::prepare('SHOW TABLES');
$sth->execute();

while ($row = $sth->fetch(PDO::FETCH_NUM)) {
    if (preg_match("/^ober(?!_telle)_.*$/", $row[0])) {
        $drop = DB::exec("DROP TABLE $row[0]");
        echo "$row[0] dropped.\n";
    }
}