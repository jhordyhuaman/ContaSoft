<?php


require_model('planilla.php');

$kardex = new kardex();
$kardex->cron_job();