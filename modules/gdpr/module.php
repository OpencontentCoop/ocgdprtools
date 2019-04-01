<?php

$Module = array(
    'name' => 'Gdpr',
    'variable_params' => true
);

$ViewList = array();
$ViewList['acceptance'] = array(
    'functions' => array('acceptance'),
    'script' => 'acceptance.php',
    'params' => array()
);
$ViewList['confirmpublish'] = array(
    'functions' => array('acceptance'),
    'script' => 'confirmpublish.php',
    'params' => array('Confirm', 'ObjectID', 'Version', 'Language'),
    'unordered_params' => array(),
    'default_navigation_part' => 'ezcontentnavigationpart',
);

$FunctionList = array();
$FunctionList['acceptance'] = array();

