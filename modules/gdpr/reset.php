<?php
/** @var eZModule $module */
$module = $Params['Module'];
$id = $Params['ID'];

$object = eZContentObject::fetch((int)$id);
if ($object instanceof eZContentObject && $object->canEdit() && $id != eZUser::currentUserID() && $id != eZUser::anonymousId()){

    foreach ($object->dataMap() as $attribute) {
        if ($attribute->attribute('data_type_string') === OcGdprType::DATA_TYPE_STRING) {
            $attribute->dataType()->reset($attribute);
            eZContentCacheManager::clearContentCache($id);
            break;
        }
    }

    $module->redirectTo($object->attribute('main_node')->attribute('url_alias'));
    return;
}else {
    return $module->handleError(eZError::KERNEL_ACCESS_DENIED, 'kernel');
}
