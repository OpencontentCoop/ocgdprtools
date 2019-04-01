<div class="checkbox" style="margin-bottom: 10px;text-align: left;">
    <input id="{$custom_field.identifier}"
           type="checkbox"
           {if $custom_field.is_required}required=""{/if}
           name="{$custom_field.identifier}"
            {$custom_field.value|choose( '', 'checked="checked"' )}
           value="1"
           style="margin-bottom: 0"
    />
    <span>{$custom_field.gdpr_text|wash()} <a target="_blank" href="{$custom_field.gdpr_link|wash()}">{$custom_field.gdpr_link_text|wash()}</a></span>
</div>