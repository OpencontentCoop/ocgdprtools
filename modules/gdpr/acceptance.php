<?php
$http = eZHTTPTool::instance();
$tpl = eZTemplate::factory();

$params = OcGdprRuntimeAcceptanceManager::instance()->getCurrentAcceptanceTemplateVariables();
$tpl->setVariable('acceptance_title', $params['title']);
$tpl->setVariable('acceptance_text', $params['text']);
$tpl->setVariable('acceptance_link', $params['link']);
$tpl->setVariable('acceptance_link_text', $params['link_text']);
$tpl->setVariable('acceptance_button_name', $params['button_name']);
$tpl->setVariable('acceptance_var_name', $params['var_name']);
$tpl->setVariable('original_request_uri', $params['original_request_uri']);
$tpl->setVariable('original_variables', $params['original_variables']);

$path = array();
$titlePath = array();
$path[] = array(
    'text' => $params['title'],
    'url' => false,
    'url_alias' => false
);

$titlePath[] = array(
    'text' => $params['title'],
    'url' => false,
    'url_alias' => false
);

$Result['content'] = $tpl->fetch('design:gdpr/acceptance.tpl');
$Result['path'] = $path;
$Result['title_path'] = $titlePath;