<?php
include('../../config.php');

$DB->set_field('config_plugins', 'value', $_GET['lang'], ['plugin' => 'filter_translations', 'name' => 'contentlanguage']);

purge_caches();

header('Location: '. $_GET['redirectto']);