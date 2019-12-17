<?php

use Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\BooleanField;

class GdprField extends BooleanField
{
    private $isEditorCurrentUser = false;

    public function __construct($attribute, $class, $helper)
    {
        parent::__construct($attribute, $class, $helper);
        if (($this->getHelper()->hasParameter('object')
                && eZUser::currentUserID() == $this->getHelper()->getParameter('object'))
            || eZUser::currentUser()->isAnonymous()){
            $this->isEditorCurrentUser = true;
        }
    }

    public function getSchema()
    {
        $schema = parent::getSchema();

        if (!$this->isEditorCurrentUser){
            $schema['required'] = false;
        }

        return $schema;
    }

    public function getOptions()
    {
        /** @var OcGdprDefinition $attributeContent */
        $attributeContent = $this->attribute->content();
        $acceptanceText = $attributeContent->getText();
        $linkText = $attributeContent->getLinkText();
        $linkUrl = $attributeContent->getLink();

        eZURI::transformURI($linkUrl);

        return array(
            "helper" => $this->attribute->attribute('description'),
            'type' => 'checkbox',
            'rightLabel' => "$acceptanceText <a target='_blank' href=\"$linkUrl\">$linkText</a>"
        );
    }

    public function setPayload($postData)
    {
        if (!$this->isEditorCurrentUser && $postData === 'false'){
            return '000';
        }
        return $postData === 'true' ? '1' : '0';
    }
}
