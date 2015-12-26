<?php

require "EU4Reader.php";

set_time_limit(60);

$time = time();

$eu4 = new EU4Reader('file.eu4');

$res = $eu4->getResult();

file_put_contents('result.txt', print_r($res, true));

printf("Time: %d sec<br>", time() - $time);
printf("RAM usage: %dMB", memory_get_peak_usage(true) / 1024 / 1024);