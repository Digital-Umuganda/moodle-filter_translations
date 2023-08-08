<?php
include('../../config.php');

$DB->set_field('config_plugins', 'value', $_GET['lang'], ['plugin' => 'filter_translations', 'name' => 'widgetlanguage']);

purge_caches();

// echo "<script>location.back()</script>";