<h1>{$acceptance_title}</h1>
<form method="POST" action="{$original_request_uri|wash()}">
    {foreach $original_variables as $key => $value}
        <input type="hidden" name="{$key|wash()}" value="{$value|wash()}" />
    {/foreach}
    <div class="checkbox">
        <label>
            <input type="checkbox" name="{$acceptance_var_name|wash()}" {if $acceptance_is_checked}checked="checked"{/if} />
            <p>{$acceptance_text}</p>
            <a target="_blank" href="{$acceptance_link|wash()}">{$acceptance_link_text|wash()}</a>
        </label>
    </div>

    <input class="button" name="{$acceptance_button_name|wash()}" value="Accetto" type="submit" />
</form>
