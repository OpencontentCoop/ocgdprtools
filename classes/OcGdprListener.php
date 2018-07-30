<?php

class OcGdprListener
{
    const ACCEPTANCE_GDPR_VARNAME = 'gdpr_runtime_acceptance';

    public static function onInput(eZURI $uri)
    {
        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] !== 'POST' && empty($_POST)) {
            return null;
        }

        if (OcGdprRuntimeAcceptanceManager::instance()->needAcceptance($uri)){

            OcGdprRuntimeAcceptanceManager::instance()->initAcceptance($uri);

            $http = eZHTTPTool::instance();
            $redirectUrl = '/gdpr/acceptance';
            eZURI::transformURI($redirectUrl);
            $http->redirect( $redirectUrl );

            eZExecution::cleanExit();
        }

        return null;
    }
}