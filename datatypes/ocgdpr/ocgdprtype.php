<?php

class OcGdprType extends eZDataType
{
    const DATA_TYPE_STRING = 'ocgdpr';

    /**
     * @var array
     */
    private $classContent;

    function __construct()
    {
        $this->eZDataType(
            self::DATA_TYPE_STRING,
            ezpI18n::tr('kernel/classes/datatypes', "GDPR Acceptance", 'Datatype name'),
            array(
                'serialize_supported' => true
            )
        );
    }

    /**
     * @param eZContentObjectAttribute $objectAttribute
     * @return array
     */
    function objectAttributeContent($objectAttribute)
    {
        /** @var OcGdprDefinition $classContent */
        $classContent = $objectAttribute->classContent();
        return array(
            'text' => $classContent->getText(),
            'disclaimer' => $classContent->getLink(),
            'is_current_user' => $this->isEditorCurrentUser($objectAttribute),
            'value' => intval($objectAttribute->attribute('data_int'))
        );
    }

    /**
     * @param eZContentClassAttribute $classAttribute
     * @return OcGdprDefinition
     */
    function classAttributeContent($classAttribute)
    {
        return OcGdprDefinition::fromClassAttribute($classAttribute);
    }

    /**
     * @param eZContentObjectAttribute $contentObjectAttribute
     * @return bool
     */
    function hasObjectAttributeContent($contentObjectAttribute)
    {
        return intval($contentObjectAttribute->attribute('data_int')) > 0;
    }

    /**
     * @param eZHTTPTool $http
     * @param $base
     * @param eZContentClassAttribute $classAttribute
     * @return bool
     */
    function fetchClassAttributeHTTPInput($http, $base, $classAttribute)
    {
        $textName = $base . '_text_' . $classAttribute->attribute('id');
        if (!$http->hasPostVariable($textName)) {
            return false;
        }

        $linkName = $base . '_link_' . $classAttribute->attribute('id');
        if (!$http->hasPostVariable($linkName)) {
            return false;
        }

        $linkTextName = $base . '_link_text_' . $classAttribute->attribute('id');
        if (!$http->hasPostVariable($linkTextName)) {
            return false;
        }

        $text = $http->postVariable($textName);
        $link = $http->postVariable($linkName);
        $linkText = $http->postVariable($linkTextName);

        $definition = new OcGdprDefinition();
        $definition->setText($text);
        $definition->setLink($link);
        $definition->setLinkText($linkText);

        $definition->setClassAttribute($classAttribute);

        return true;
    }

    /**
     * @param eZHTTPTool $http
     * @param $base
     * @param eZContentObjectAttribute $objectAttribute
     * @return int
     */
    function validateObjectAttributeHTTPInput($http, $base, $objectAttribute)
    {
        $acceptName = $base . '_ocgdpr_data_int_' . $objectAttribute->attribute('id');
        if ($objectAttribute->validateIsRequired() && !$http->hasPostVariable($acceptName) && $this->isEditorCurrentUser($objectAttribute)) {
            $objectAttribute->setValidationError(ezpI18n::tr('kernel/classes/datatypes', 'Input required.'));
            return eZInputValidator::STATE_INVALID;
        }

        return eZInputValidator::STATE_ACCEPTED;
    }

    /**
     * @param eZContentObjectAttribute $objectAttribute
     * @return bool
     */
    private function isEditorCurrentUser($objectAttribute)
    {
        /** @var eZContentObject $object */
        $object = $objectAttribute->attribute("object");
        if (eZUser::isUserObject($object)) {
            return eZUser::currentUserID() == $object->attribute('id') || eZUser::currentUser()->isAnonymous();
        }

        return false;
    }

    /**
     * @param eZHTTPTool $http
     * @param $base
     * @param eZContentObjectAttribute $objectAttribute
     * @return bool
     */
    function fetchObjectAttributeHTTPInput($http, $base, $objectAttribute)
    {
        $acceptName = $base . '_ocgdpr_data_int_' . $objectAttribute->attribute('id');
        if ($http->hasPostVariable($acceptName)) {
            $objectAttribute->setAttribute('data_int', 1);
            return true;
        }else{
            $objectAttribute->setAttribute('data_int', 0);
            return false;
        }
    }

    /**
     * @param eZHTTPTool $http
     * @param $action
     * @param eZContentObjectAttribute $contentObjectAttribute
     * @param $parameters
     */
    function customObjectAttributeHTTPAction($http, $action, $contentObjectAttribute, $parameters)
    {
        switch ($action) {
            case 'force_reaccept' :
                {
                    if (!$this->isEditorCurrentUser($contentObjectAttribute)){
                        $this->reset($contentObjectAttribute);
                    }
                }
        }
    }

    /**
     * @param eZContentObjectAttribute $contentObjectAttribute
     */
    public function reset($contentObjectAttribute)
    {
        $contentObjectAttribute->setAttribute('data_int', 0);
        $contentObjectAttribute->store();
        OcGdprRuntimeAcceptanceManager::setKo($contentObjectAttribute->attribute('contentobject_id'));
    }

    /**
     * @param eZContentObjectAttribute $contentObjectAttribute
     * @param eZContentObject $contentObject
     * @param array $publishedNodes
     */
    function onPublish($contentObjectAttribute, $contentObject, $publishedNodes)
    {
        $content = $this->objectAttributeContent($contentObjectAttribute);
        if ($this->isEditorCurrentUser($contentObjectAttribute)) {
            if ($content['value'] == 1) {
                OcGdprRuntimeAcceptanceManager::setOk($contentObjectAttribute->attribute('contentobject_id'));
                OcGdprListener::logAcceptance(
                    eZUser::currentUser()->attribute('login'),
                    $content['text'],
                    $content['disclaimer']
                );
            }else{
                OcGdprRuntimeAcceptanceManager::setKo($contentObjectAttribute->attribute('contentobject_id'));
            }
        }
    }

    /**
     * @param eZHTTPTool $http
     * @param $base
     * @param eZContentObjectAttribute $objectAttribute
     * @return bool
     */
    function validateCollectionAttributeHTTPInput($http, $base, $objectAttribute)
    {
        if ($objectAttribute->validateIsRequired()) {
            if ($http->hasPostVariable($base . "_ocgdpr_data_int_" . $objectAttribute->attribute("id"))) {
                $data = $http->postVariable($base . "_ocgdpr_data_int_" . $objectAttribute->attribute("id"));
                if (isset($data)) {
                    return eZInputValidator::STATE_ACCEPTED;
                }
            } else {
                $objectAttribute->setValidationError(ezpI18n::tr('kernel/classes/datatypes',
                    'Input required.'));
                return eZInputValidator::STATE_INVALID;
            }
        }

        return eZInputValidator::STATE_ACCEPTED;
    }

    /**
     * @param eZInformationCollection $collection
     * @param eZInformationCollectionAttribute $collectionAttribute
     * @param eZHTTPTool $http
     * @param $base
     * @param eZContentObjectAttribute $objectAttribute
     * @return bool
     */
    function fetchCollectionAttributeHTTPInput($collection, $collectionAttribute, $http, $base, $objectAttribute)
    {
        if ($http->hasPostVariable($base . "_ocgdpr_data_int_" . $objectAttribute->attribute("id"))) {
            $data = $http->postVariable($base . "_ocgdpr_data_int_" . $objectAttribute->attribute("id"));
            if (isset($data) && $data !== '0' && $data !== 'false')
                $data = 1;
            else
                $data = 0;
        } else {
            $data = 0;
        }
        $collectionAttribute->setAttribute('data_int', $data);
        return true;
    }

    function isIndexable()
    {
        return false;
    }

    function isInformationCollector()
    {
        return true;
    }

    function metaData($contentObjectAttribute)
    {
        return null;
    }

    /**
     * @param eZContentObjectAttribute $objectAttribute
     * @return string
     */
    function toString($objectAttribute)
    {
        return $objectAttribute->attribute('data_int');
    }

    /**
     * @param eZContentObjectAttribute $objectAttribute
     * @return bool
     */
    function fromString($objectAttribute, $string)
    {
        $objectAttribute->setAttribute('data_int', (int)$string);
        $objectAttribute->store();

        return true;
    }

}

eZDataType::register(OcGdprType::DATA_TYPE_STRING, 'OcGdprType');