{* DO NOT EDIT THIS FILE! Use an override template instead. *}
{default attribute_base='ContentObjectAttribute'}
{let data_int=cond( is_set( $#collection_attributes[$attribute.id]), $#collection_attributes[$attribute.id].data_int, $attribute.data_int )}
    <div class="checkbox">
    <label>
	    <input type="checkbox" name="{$attribute_base}_ocgdpr_data_int_{$attribute.id}" {$data_int|choose( '', 'checked="checked"' )} />
	    <span style="font-weight: normal">{$attribute.contentclass_attribute.content.text}</span>
	    <a target="_blank" href="{$attribute.contentclass_attribute.content.link|wash()}">{$attribute.contentclass_attribute.content.link_text|wash()}</a>
    </label>
    </div>
{/let}
{/default}