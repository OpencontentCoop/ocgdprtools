{* DO NOT EDIT THIS FILE! Use an override template instead. *}
{default attribute_base='ContentObjectAttribute'}
{let data_int=cond( is_set( $#collection_attributes[$attribute.id]), $#collection_attributes[$attribute.id].data_int, $attribute.data_int )}
    <div class="checkbox">
    <label>
    <input type="checkbox" name="{$attribute_base}_ocgdpr_data_int_{$attribute.id}" {$data_int|choose( '', 'checked="checked"' )} />
    <div style="font-weight: normal">{$attribute.contentclass_attribute.data_text5}</div>
    <a target="_blank" href="{$attribute.contentclass_attribute.data_text4|wash()}">{$attribute.contentclass_attribute.data_text3|wash()}</a>
    </label>
    </div>
{/let}
{/default}