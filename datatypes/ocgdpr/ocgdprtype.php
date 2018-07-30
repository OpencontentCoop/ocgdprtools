<?php

class OcGdprType extends eZDataType
{
    const DATA_TYPE_STRING = 'ocgdpr';

    const TEXT_VARIABLE = 'data_text5';

    const LINK_VARIABLE = 'data_text4';

    const LINK_TEXT_VARIABLE = 'data_text3';

    /**
     * @var eZContentClassAttribute
     */
    private $classContent;

    function __construct()
    {
        $this->eZDataType(
            self::DATA_TYPE_STRING,
            ezpI18n::tr( 'kernel/classes/datatypes', "GDPR Acceptance", 'Datatype name' ),
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
        if ($this->classContent === null) {
            $this->classContent = $objectAttribute->classContent();
        }
        return array(
            'disclaimer' => $this->classContent->attribute(self::LINK_VARIABLE),
            'value' => intval($objectAttribute->attribute('data_int'))
        );
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
        $textName = $base . '_' . self::TEXT_VARIABLE . '_' . $classAttribute->attribute('id');
        if (!$http->hasPostVariable($textName)) {
            return false;
        }

        $linkName = $base . '_' . self::LINK_VARIABLE . '_' . $classAttribute->attribute('id');
        if (!$http->hasPostVariable($linkName)) {
            return false;
        }

        $linkTextName = $base . '_' . self::LINK_TEXT_VARIABLE . '_' . $classAttribute->attribute('id');
        if (!$http->hasPostVariable($linkTextName)) {
            return false;
        }

        $text = trim($http->postVariable($textName));
        $link = trim($http->postVariable($linkName));
        $linkText = trim($http->postVariable($linkTextName));

        $classAttribute->setAttribute(self::TEXT_VARIABLE, $text);
        $classAttribute->setAttribute(self::LINK_VARIABLE, $link);
        $classAttribute->setAttribute(self::LINK_TEXT_VARIABLE, $linkText);

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
        if ($objectAttribute->validateIsRequired() && !$http->hasPostVariable($acceptName)) {
            $objectAttribute->setValidationError(ezpI18n::tr('kernel/classes/datatypes', 'Input required.'));
            return eZInputValidator::STATE_INVALID;
        }

        return eZInputValidator::STATE_ACCEPTED;
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
        }

        return false;
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

eZDataType::register( OcGdprType::DATA_TYPE_STRING, 'OcGdprType' );