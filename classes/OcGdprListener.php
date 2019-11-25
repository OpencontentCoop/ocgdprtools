<?php

class OcGdprListener
{
    const ACCEPTANCE_GDPR_VARNAME = 'gdpr_runtime_acceptance';

    public static function onInput(eZURI $uri)
    {
        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] !== 'POST' && empty($_POST)) {

            if (($uri->URI == '' || $uri->URI == 'user/edit') && OcGdprRuntimeAcceptanceManager::instance()->checkCurrentUserProfile() === false) {
                OcGdprRuntimeAcceptanceManager::setChanging();
                $http = eZHTTPTool::instance();
                $redirectUrl = '/gdpr/user_acceptance';
                eZURI::transformURI($redirectUrl);
                $http->redirect($redirectUrl);
            } else {

                return null;
            }
        }

        if (OcGdprRuntimeAcceptanceManager::instance()->needAcceptance($uri)) {

            OcGdprRuntimeAcceptanceManager::instance()->initAcceptance($uri);

            $http = eZHTTPTool::instance();
            $redirectUrl = '/gdpr/acceptance';
            eZURI::transformURI($redirectUrl);
            $http->redirect($redirectUrl);

            eZExecution::cleanExit();
        }

        return null;
    }

    public static function logAcceptance($userLogin, $text, $link)
    {
        eZAudit::writeAudit('gdpr-acceptance', [
            'User login' => $userLogin,
            'Text' => $text,
            'Link' => $link
        ]);
    }
}