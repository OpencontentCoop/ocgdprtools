<?php

class OcGdprRuntimeAcceptanceManager
{
    const POST_VARNAME = 'gdpr_runtime_acceptance';

    const SESSION_REQUEST_VARNAME = 'gdpr_original_request_uri';
    const SESSION_URI_VARNAME = 'gdpr_original_uri';
    const SESSION_VARS_VARNAME = 'gdpr_original_variables';

    const PREFERENCE_KEY_PREFIX = 'gdpr_acceptance_';

    const PROFILE_PREFERENCE_KEY = 'gdpr_acceptance_profile';
    const PROFILE_PREFERENCE_VALUE_OK = 'ok';
    const PROFILE_PREFERENCE_VALUE_KO = 'ko';
    const PROFILE_PREFERENCE_VALUE_INDETERMINATE = 'indeterminate';
    const PROFILE_PREFERENCE_VALUE_REQUEST_CHANGE = 'change';

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
        $hasRequiredFields = true;
        $requiredFields = isset($settings['RequiredFieldName']) ? (array)$settings['RequiredFieldName'] : array();
        foreach ($requiredFields as $field) {
            if (!isset($_POST[$field]) || empty($_POST[$field])){
                $hasRequiredFields = false;
            }
         }

        return $isNeedAcceptanceUri && $isNeedAcceptanceButton && $hasRequiredFields;
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
        if (!$uri instanceof eZURI){
            return false;
        }
        $settings = $this->getAcceptanceSettings($uri);

        return array(

            'title' => $settings['Title'],
            'text' => $settings['Text'],
            'link' => $settings['Link'],
            'link_text' => $settings['LinkText'],
            'button_name' => $settings['ButtonName'],
            
            'var_name' => $this->generatePostVarName($uri),
            'is_checked' => $this->isAccepted($uri),
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

        $settings['identifier'] = $identifier;
        return $settings;
    }

    private function isAccepted(eZURI $uri)
    {
        $settings = $this->getAcceptanceSettings($uri);
        $preferenceKey = self::PREFERENCE_KEY_PREFIX . $settings['identifier'];
        return eZPreferences::value($preferenceKey) !== false;
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

            $settings = $this->getAcceptanceSettings($uri);
            $preferenceKey = self::PREFERENCE_KEY_PREFIX . $settings['identifier'];
            eZPreferences::setValue($preferenceKey, time());

            $originalPostData = $http->sessionVariable(self::SESSION_VARS_VARNAME);
            $user = isset($settings['UserFieldName']) && isset($originalPostData[$settings['UserFieldName']]) ?
                $originalPostData[$settings['UserFieldName']] :
                eZUser::currentUser()->attribute('login');

            OcGdprListener::logAcceptance(
                $user,
                $settings['Text'],
                $settings['Link']
            );

            $http->removeSessionVariable(self::SESSION_REQUEST_VARNAME);
            $http->removeSessionVariable(self::SESSION_URI_VARNAME);
            $http->removeSessionVariable(self::SESSION_VARS_VARNAME);

            return true;
        }

        return false;
    }

    public function checkCurrentUserProfile()
    {
        if (eZUser::currentUser()->isAnonymous()){
            return true;
        }

        $now = time();
        $preferenceValue = eZPreferences::value(self::PROFILE_PREFERENCE_KEY);
        if (strpos($preferenceValue, self::PROFILE_PREFERENCE_VALUE_REQUEST_CHANGE) !== false){
            $parts = explode(':', $preferenceValue);
            $inChangeFrom = 0;
            if (isset($parts[1])){
                $inChangeFrom = $parts[1];
            }
            if ($inChangeFrom == 0 || ($inChangeFrom > 0 && ($now - $inChangeFrom) > 10)){
                self::setKo();
                $preferenceValue = self::PROFILE_PREFERENCE_VALUE_KO;
            }
        }
        if ($preferenceValue === false){
            $gdprAttribute = false;
            /** @var eZContentObject $object */
            $object = eZUser::currentUser()->attribute('contentobject');
            if ($object instanceof eZContentObject) {
                foreach ($object->dataMap() as $attribute) {
                    if ($attribute->attribute('data_type_string') === OcGdprType::DATA_TYPE_STRING) {
                        $gdprAttribute = $attribute;
                        break;
                    }
                }
            }
            if ($gdprAttribute instanceof eZContentObjectAttribute){
                $preferenceValue = $gdprAttribute->hasContent() ? self::PROFILE_PREFERENCE_VALUE_OK : self::PROFILE_PREFERENCE_VALUE_KO;
            }else{
                $preferenceValue = self::PROFILE_PREFERENCE_VALUE_INDETERMINATE;
            }

            eZPreferences::setValue(
                self::PROFILE_PREFERENCE_KEY,
                $preferenceValue
            );
        }

        eZDebug::writeDebug($preferenceValue . "($now)", __METHOD__);

        return $preferenceValue != self::PROFILE_PREFERENCE_VALUE_KO;
    }

    public static function setKo($userId = false)
    {
        eZPreferences::setValue(
            self::PROFILE_PREFERENCE_KEY,
            self::PROFILE_PREFERENCE_VALUE_KO,
            $userId
        );
    }

    public static function setOk($userId = false)
    {
        eZPreferences::setValue(
            self::PROFILE_PREFERENCE_KEY,
            self::PROFILE_PREFERENCE_VALUE_OK,
            $userId
        );
    }

    public static function setChanging($userId = false)
    {
        eZPreferences::setValue(
            self::PROFILE_PREFERENCE_KEY,
            self::PROFILE_PREFERENCE_VALUE_REQUEST_CHANGE . ':' . time(),
            $userId
        );
    }

}
