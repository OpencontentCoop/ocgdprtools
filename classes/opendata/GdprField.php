<?php

use Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\BooleanField;

class GdprField extends BooleanField
{
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
        return $postData === 'true' ? '1' : '0';
    }
}
