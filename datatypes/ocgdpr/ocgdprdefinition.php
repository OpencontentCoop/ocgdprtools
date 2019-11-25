<?php

class OcGdprDefinition
{
    const DATA_FIELD = 'data_text5';

    const TEXT_FIELD = 'data_text5';

    const LINK_FIELD = 'data_text4';

    const LINK_TEXT_FIELD = 'data_text3';

    private $text;

    private $link;

    private $link_text;

    private $locales;

    private $currentLocale;

    public function __construct()
    {
        $this->locales = eZINI::instance()->variable('RegionalSettings', 'SiteLanguageList');
        $this->currentLocale = eZLocale::currentLocaleCode();
    }

    private function makeStringToLocaleList($string)
    {
        $data = [];
        foreach ($this->locales as $locale) {
            $data[$locale] = $string;
        }

        return $data;
    }

    /**
     * @param string $locale
     * @return string
     */
    public function getText($locale = null)
    {
        if ($locale === null){
            $locale = $this->currentLocale;
        }
        return $this->text[$locale];
    }

    /**
     * @param mixed $text
     */
    public function setText($text)
    {
        if (!is_array($text)) {
            $text = $this->makeStringToLocaleList($text);
        }
        $this->text = $text;
    }

    /**
     * @param string $locale
     * @return string
     */
    public function getLink($locale = null)
    {
        if ($locale === null){
            $locale = $this->currentLocale;
        }
        return $this->link[$locale];
    }

    /**
     * @param mixed $link
     */
    public function setLink($link)
    {
        if (!is_array($link)) {
            $link = $this->makeStringToLocaleList($link);
        }
        $this->link = $link;
    }

    /**
     * @param string $locale
     * @return string
     */
    public function getLinkText($locale = null)
    {
        if ($locale === null){
            $locale = $this->currentLocale;
        }
        return $this->link_text[$locale];
    }

    /**
     * @param mixed $linkText
     */
    public function setLinkText($linkText)
    {
        if (!is_array($linkText)) {
            $linkText = $this->makeStringToLocaleList($linkText);
        }
        $this->link_text = $linkText;
    }

    /**
     * @param string $locale
     * @return array
     */
    public function toArray($locale = null)
    {
        if ($locale === null) {
            $locale = $this->currentLocale;
        }
        $data = [];
        foreach (array('text', 'link', 'link_text') as $key) {
            $data[$key] = $this->{$key}[$locale];
        }

        return $data;
    }

    public static function fromClassAttribute(eZContentClassAttribute $classAttribute)
    {
        $instance = new OcGdprDefinition();

        if (self::isJson($classAttribute->attribute(self::TEXT_FIELD))){
            $data = json_decode($classAttribute->attribute(self::TEXT_FIELD), true);
            $instance->setText($data['text']);
            $instance->setLink($data['link']);
            $instance->setLinkText($data['link_text']);

        }else {
            $instance->setText($classAttribute->attribute(self::TEXT_FIELD));
            $instance->setLink($classAttribute->attribute(self::LINK_FIELD));
            $instance->setLinkText($classAttribute->attribute(self::LINK_TEXT_FIELD));
        }

        return $instance;
    }

    private static function isJson($string)
    {
        json_decode($string);
        if (json_last_error() === JSON_ERROR_NONE){
            return true;
        }

        return false;
    }

    public function setClassAttribute(eZContentClassAttribute $classAttribute)
    {
        $data = [
            'text' => $this->text,
            'link' => $this->link,
            'link_text' => $this->link_text,
        ];
        $classAttribute->setAttribute(self::DATA_FIELD, json_encode($data));
    }

    public function attributes()
    {
        $asArray = $this->toArray();

        return array_merge(array_keys($asArray), $this->locales);
    }

    public function hasAttribute($key)
    {
        return in_array($key, $this->attributes());
    }

    public function attribute($key)
    {
        if (in_array($key, $this->locales)) {
            return $this->toArray($key);
        }
        $asArray = $this->toArray();
        if (isset($asArray[$key])) {
            return $asArray[$key];
        }

        eZDebug::writeNotice("Attribute $key does not exist", __METHOD__);
        return false;
    }
}