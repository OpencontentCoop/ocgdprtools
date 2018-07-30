<?php

class OcGdprRuntimeAcceptanceManager
{
    const POST_VARNAME = 'gdpr_runtime_acceptance';

    const SESSION_REQUEST_VARNAME = 'gdpr_original_request_uri';
    const SESSION_URI_VARNAME = 'gdpr_original_uri';
    const SESSION_VARS_VARNAME = 'gdpr_original_variables';

    /**
     * @var eZINI
     */
    private $ini;

    private $uriList = array();

    public static function instance()
    {
        return new OcGdprRuntimeAcceptanceManager();
    }

    private function __construct()
    {
        $this->ini = eZINI::instance('gdprtools.ini');
        $this->uriList = $this->ini->variable('RuntimeAcceptance', 'UriList');
    }

    public function needAcceptance(eZURI $uri)
    {
        if ($this->hasAcceptance($uri)){
            return false;
        }
        $isNeedAcceptanceUri = in_array($uri->URI, $this->uriList);

        $settings = $this->getAcceptanceSettings($uri);
        $isNeedAcceptanceButton = isset($_POST[$settings['ButtonName']]);

        return $isNeedAcceptanceUri && $isNeedAcceptanceButton;
    }

    public function initAcceptance(eZURI $uri)
    {
        $http = eZHTTPTool::instance();
        $http->setSessionVariable(self::SESSION_REQUEST_VARNAME, $_SERVER['REQUEST_URI']);
        $http->setSessionVariable(self::SESSION_URI_VARNAME, $uri);
        $http->setSessionVariable(self::SESSION_VARS_VARNAME, $_POST);
    }

    public function getCurrentAcceptanceTemplateVariables()
    {
        $http = eZHTTPTool::instance();
        /** @var eZURI $uri */
        $uri = $http->sessionVariable(self::SESSION_URI_VARNAME);

        $settings = $this->getAcceptanceSettings($uri);

        return array(
            'title' => $settings['Title'],
            'text' => $settings['Text'],
            'link' => $settings['Link'],
            'link_text' => $settings['LinkText'],
            'button_name' => $settings['ButtonName'],
            'var_name' => $this->generatePostVarName($uri),
            'original_request_uri' => $http->sessionVariable(self::SESSION_REQUEST_VARNAME),
            'original_variables' => $http->sessionVariable(self::SESSION_VARS_VARNAME),
        );
    }

    private function getAcceptanceSettings(eZURI $uri)
    {
        $identifier = null;
        foreach ($this->uriList as $id => $uriString){
            if ($uri->URI == $uriString){
                $identifier = $id;
            }
        }

        $settings = array();
        if ($this->ini->hasGroup('RuntimeAcceptance_' . $identifier)){
            $settings = $this->ini->group('RuntimeAcceptance_' . $identifier);
        }
        $settings = array_merge(
            $this->ini->group('RuntimeAcceptance_default'),
            $settings
        );

        return $settings;
    }

    private function generatePostVarName(eZURI $uri)
    {
        return self::POST_VARNAME;
    }

    private function hasAcceptance(eZURI $uri)
    {
        $http = eZHTTPTool::instance();
        $postVarName = $this->generatePostVarName($uri);
        if ($http->hasPostVariable($postVarName)){
            $http->removeSessionVariable(self::SESSION_REQUEST_VARNAME);
            $http->removeSessionVariable(self::SESSION_URI_VARNAME);
            $http->removeSessionVariable(self::SESSION_VARS_VARNAME);

            return true;
        }

        return false;
    }

}