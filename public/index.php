<?php

print('<pre>');
var_dump('hello 12');
echo phpinfo();
ini_set("log_errors", 1); // включить лог ошибок
ini_set("error_log", "../../php-error.log"); // расположение лог-файла ошибок
error_log( "Hello, errors!" ); // записать в лог-файл значение/строку
