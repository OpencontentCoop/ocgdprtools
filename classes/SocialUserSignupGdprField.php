<?php

class SocialUserSignupGdprField extends SocialUserSignupCustomField
{
    public function __construct()
    {
        $socialUserIni = eZINI::instance('social_user.ini');
        $settings = $socialUserIni->group('SignupCustomField_gdpracceptance');
        /** @var eZContentClassAttribute[] $dataMap */
        $dataMap = SocialUserRegister::getUserClass()->dataMap();
        $settings['AttributeIdentifier'] = false;
        foreach ($dataMap as $identifier => $attribute){
            if ($attribute->attribute('data_type_string') == OcGdprType::DATA_TYPE_STRING){
                $settings['AttributeIdentifier'] = $identifier;
            }
        }
        parent::__construct($settings);
    }

    public function setFromRequest()
    {
        if ($this->classAttribute instanceof eZContentClassAttribute) {
            $postName = $this->classAttribute->attribute('identifier');
            $isRequired = $this->classAttribute->attribute('is_required');

            $http = eZHTTPTool::instance();
            if ($http->hasPostVariable($postName)) {
                $this->value = $http->postVariable($postName);
                eZDebug::writeDebug($this->value, __METHOD__);
            } elseif ($isRequired) {
                throw new InvalidArgumentException(ezpI18n::tr(
                    'social_user/signup',
                    'Inserire tutti i dati richiesti'
                ));

            }
        }
    }

    public function getFormTemplatePath()
    {
        if ($this->classAttribute instanceof eZContentClassAttribute) {
            return 'design:gdpr/social_user_field.tpl';
        }

        return null;
    }

    public function attributes()
    {
        $attributes = parent::attributes();
        $attributes[] = 'gdpr_text';
        $attributes[] = 'gdpr_link';
        $attributes[] = 'gdpr_link_text';

        return $attributes;
    }

    public function attribute($key)
    {
        if (!$this->classAttribute instanceof eZContentClassAttribute){
            return false;
        }

        /** @var OcGdprDefinition $classAttributeContent */
        $classAttributeContent = $this->classAttribute->content();

        if ($key == 'gdpr_text'){
            return $classAttributeContent->getText();
        }

        if ($key == 'gdpr_link'){
            return $classAttributeContent->getLink();
        }

        if ($key == 'gdpr_link_text'){
            return $classAttributeContent->getLinkText();
        }

        return parent::attribute($key);
    }
}