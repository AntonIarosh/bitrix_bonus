<?php
require_once('ConnectDB.php');

$query = 'Select version() as VERSION';

$connection = new ConnectDB();
$pdo = $connection->getPDO();
$ver = $pdo->query($query);
$versions = $ver->fetch();
echo $versions['VERSION'];
print_r($connection->getTables());
