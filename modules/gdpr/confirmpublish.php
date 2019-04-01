<?php
/** @var eZModule $module */
$module = $Params['Module'];
$http = eZHTTPTool::instance();
$confirm = (bool)$Params['Confirm'];
$objectId = $Params['ObjectID'];
$version = $Params['Version'];
$editLanguage = $Params['Language'];

$objectVersion = eZContentObjectVersion::fetchVersion((int)$version, (int)$objectId);
if ($objectVersion instanceof eZContentObjectVersion
    && $objectVersion->attribute('creator_id') == eZUser::currentUserID()
    && $objectVersion->attribute('status') == eZContentObjectVersion::STATUS_REPEAT) {

    if ($confirm) {
        $processObject = ezpContentPublishingProcess::queue($objectVersion);
        /** @var eZContentObject $object */
        $object = $objectVersion->attribute('contentobject');
        $conflictingVersions = $objectVersion->hasConflicts($editLanguage);
        if ($conflictingVersions) {
            $class = $object->attribute('content_class');
            $tpl = eZTemplate::factory();
            $res = eZTemplateDesignResource::instance();
            $res->setKeys(array(
                array('object', $object->attribute('id')),
                array('remote_id', $object->attribute('remote_id')),
                array('class', $class->attribute('id')),
                array('class_identifier', $class->attribute('identifier')),
                array('class_group', $class->attribute('match_ingroup_id_list'))));

            $tpl->setVariable('edit_language', $editLanguage);
            $tpl->setVariable('current_version', $objectVersion->attribute('version'));
            $tpl->setVariable('object', $object);
            $tpl->setVariable('draft_versions', $conflictingVersions);

            $Result = array();
            $Result['content'] = $tpl->fetch('design:content/edit_conflict.tpl');
        } else {
            $operationResult = eZOperationHandler::execute('content', 'publish',
                array('object_id' => $objectId, 'version' => $version)
            );
            $processObject->remove();
            if ($http->hasSessionVariable('RedirectURIAfterPublish')) {
                $uri = $http->sessionVariable('RedirectURIAfterPublish');
                $http->removeSessionVariable('RedirectURIAfterPublish');
                $module->redirectTo($uri);
            } else {
                $module->redirectTo('content/view/full/' . $object->attribute('main_node_id'));
            }
        }
    } else {
        eZContentOperationCollection::setVersionStatus($objectId, $version, eZContentObjectVersion::STATUS_DRAFT);
        $module->redirectTo('content/edit/' . $objectId . '/' . $version . '/' . $editLanguage);
    }

} else {

    return $module->handleError(eZError::KERNEL_NOT_FOUND, 'kernel');
}
