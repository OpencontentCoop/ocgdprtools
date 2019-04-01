<div class="container">
    <h2>
        Confermi la pubblicazione del contenuto?
    </h2>

    {foreach $version.data_map as $attribute}
        {if $attribute.has_content}
            <div class="row edit-row">
                <div class="col-md-4">
                    <strong>{$attribute.contentclass_attribute_name}</strong>
                </div>
                <div class="col-md-8">
                    <div class="Prose u-padding-left-s">
                        {attribute_view_gui attribute=$attribute show_newline=true()}
                    </div>
                </div>
            </div>
        {/if}
    {/foreach}


    <div class="row">
        <div class="col-md-6 text-left">
            <a class="btn btn-danger" href="{concat('gdpr/confirmpublish/0/', $version.contentobject_id, '/', $version.version, '/', $language)|ezurl('no')}">
                Non confermo
            </a>
        </div>
        <div class="col-md-6 text-right">
            <a class="btn btn-success" href="{concat('gdpr/confirmpublish/1/', $version.contentobject_id, '/', $version.version, '/', $language)|ezurl('no')}">
                Confermo la pubblicazione
            </a>
        </div>
    </div>
</div>