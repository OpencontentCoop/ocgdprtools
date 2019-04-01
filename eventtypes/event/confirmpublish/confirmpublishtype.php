<?php

class ConfirmPublishType extends eZWorkflowEventType
{
    const WORKFLOW_TYPE_STRING = "confirmpublish";

    public function __construct()
    {
        parent::__construct(
            ConfirmPublishType::WORKFLOW_TYPE_STRING, 'Richiede la conferma di pubblicazione/aggiornamento'
        );
        $this->setTriggerTypes(array('content' => array('publish' => array('before'))));
    }

    public function execute($process, $event)
    {
        $http = eZHTTPTool::instance();
        $parameters = $process->attribute('parameter_list');
        $objectId = $parameters['object_id'];
        $version = $parameters['version'];

        /** @var eZContentObjectVersion $objectVersion */
        $objectVersion = eZContentObjectVersion::fetchVersion((int)$version, (int)$objectId);
        $queued = ezpContentPublishingProcess::fetchByContentVersionId($objectVersion->attribute('id'));

        if ($objectVersion instanceof eZContentObjectVersion
            && !$queued
            && $http->hasPostVariable('PublishButton')) { // funziona solo se si pubblica da web (non da REST)

            if ($http->hasPostVariable('RedirectURIAfterPublish')) {
                $http->setSessionVariable('RedirectURIAfterPublish', $http->postVariable('RedirectURIAfterPublish'));
            }

            $process->Template = array();
            $process->Template['templateName'] = 'design:gdpr/confirmpublish.tpl';
            $process->Template['templateVars'] = array(
                'version' => $objectVersion,
                'language' => $objectVersion->initialLanguageCode()
            );

            return eZWorkflowType::STATUS_FETCH_TEMPLATE_REPEAT;
        }

        if ($queued) {
            $queued->remove();
        }

        return eZWorkflowType::STATUS_ACCEPTED;
    }
}

eZWorkflowEventType::registerEventType(ConfirmPublishType::WORKFLOW_TYPE_STRING, 'ConfirmPublishType');
