<?php

use Opencontent\Ocopendata\Forms\Connectors\OpendataConnector\FieldConnector\BooleanField;

class GdprField extends BooleanField
{
    public function getOptions()
    {
        $acceptanceText = $this->attribute->attribute('data_text5');
        $linkText = $this->attribute->attribute('data_text3');
        $linkUrl = $this->attribute->attribute('data_text4');

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
