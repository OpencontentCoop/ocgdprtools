<div class="mb-3">
    <form action="{'gdpr/user_acceptance/'|ezurl('no')}" method="post">

        <div class="row">
            <div class="col">
                {attribute_edit_gui attribute=$attribute}
            </div>
        </div>

        <div class="row">
            <div class="col text-right">
                <input class="btn btn-success" type="submit" value="{'Save'|i18n( 'design/admin/settings' )}" />
            </div>
        </div>
    </form>
</div>
