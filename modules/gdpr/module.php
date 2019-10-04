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
$ViewList['reset'] = array(
    'functions' => array('reset'),
    'script' => 'reset.php',
    'params' => array('ID')
);
$ViewList['user_acceptance'] = array(
    'functions' => array('acceptance'),
    'script' => 'user_acceptance.php',
    'params' => array()
);

$FunctionList = array();
$FunctionList['acceptance'] = array();
$FunctionList['reset'] = array();

