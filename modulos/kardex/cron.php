<?php

require_model('kardex.php');

$kardex = new kardex();
$kardex->cron_job();