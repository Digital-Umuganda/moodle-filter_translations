<?php
include('../../config.php');

if ($_GET['target'] == "contentlanguage") {
    $DB->set_field('config_plugins', 'value', $_GET['lang'], ['plugin' => 'filter_translations', 'name' => 'contentlanguage']);
} else if ($_GET['target'] == "showpopuptranslation") {
    // echo "<script>alert('".json_encode($_GET)."')</script>";
    $DB->set_field('config_plugins', 'value', $_GET['state'], ['plugin' => 'filter_translations', 'name' => 'usepopuptranslation']);
}

purge_caches();

header('Location: '. $_GET['redirectto']);