{foreach ezini('RegionalSettings', 'SiteLanguageList') as $locale}
<fieldset>
    <legend>{fetch( 'content', 'locale', hash( 'locale_code', $locale ) ).language_name|wash()}</legend>
    <p>{$class_attribute.content[$locale].text|wash()}</p>
    <p><a href="{$class_attribute.content[$locale].link|wash()}">{$class_attribute.content[$locale].link_text|wash()}</a></p>
</fieldset>
{/foreach}