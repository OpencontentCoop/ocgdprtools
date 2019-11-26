<?php
$http = eZHTTPTool::instance();
$tpl = eZTemplate::factory();
/** @var eZModule $module */
$module = $Params['Module'];

$user = eZUser::currentUser();
$gdprAttribute = false;
foreach ($user->contentObject()->dataMap() as $attribute){
    if ($attribute->attribute('data_type_string') == OcGdprType::DATA_TYPE_STRING){
        $gdprAttribute = $attribute;
        break;
    }
}

if (!$gdprAttribute instanceof eZContentObjectAttribute){
    return $module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
}

$postVar = 'ContentObjectAttribute_ocgdpr_data_int_' . $gdprAttribute->attribute('id');

if ($http->hasPostVariable($postVar)){
    $gdprAttribute->setAttribute('data_int', 1);
    $content = $gdprAttribute->content();
    OcGdprRuntimeAcceptanceManager::setOk($gdprAttribute->attribute('contentobject_id'));
    OcGdprListener::logAcceptance(
        eZUser::currentUser()->attribute('login'),
        $content['text'],
        $content['disclaimer']
    );
    $gdprAttribute->store();

    $module->redirectTo('/user/edit');
    return;
}

$gdprAttribute->setAttribute('data_int', 0);
$tpl->setVariable('attribute', $gdprAttribute);

$path = array();
$titlePath = array();
$path[] = array(
    'text' => 'GDPR',
    'url' => false,
    'url_alias' => false
);

$titlePath[] = array(
    'text' => 'GDPR',
    'url' => false,
    'url_alias' => false
);

$Result['content'] = $tpl->fetch('design:gdpr/user_acceptance.tpl');
$Result['path'] = $path;
$Result['title_path'] = $titlePath;

$Result['pagelayout'] = 'gdprpagelayout.tpl';