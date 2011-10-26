<div class="AddressEditPanel">
    <div class="formControls">
        <table>
		<tr><td><?php $_CONTROL->txtTitle->RenderWithName(); ?></td></tr>

		<tr><td>
            <?php
                $_CONTROL->lstMyPeople->RenderWithName();
                $_CONTROL->btnAddPerson->RenderWithName();
            ?>
        </td></tr>

		<tr><td><?php $_CONTROL->txtStreet1->RenderWithName(); ?></td></tr>

		<tr><td><?php $_CONTROL->txtStreet2->RenderWithName(); ?></td></tr>

		<tr><td><?php $_CONTROL->txtSuburb->RenderWithName(); ?></td></tr>

		<tr><td><?php $_CONTROL->txtCity->RenderWithName(); ?></td></tr>

		<tr><td><?php $_CONTROL->txtCounty->RenderWithName(); ?></td></tr>

		<tr><td><?php $_CONTROL->lstZone->RenderWithName(); ?></td></tr>

		<tr><td><?php $_CONTROL->lstCountry->RenderWithName(); ?></td></tr>

		<tr><td><?php $_CONTROL->txtPostalCode->RenderWithName(); ?></td></tr>

		<tr><td><?php $_CONTROL->lstType->RenderWithName(); ?></td></tr>
	</div>
        </table>

	<div class="formActions">
        <table width="100%"><tr><td>
		<div id="save"><?php $_CONTROL->btnSave->Render(); ?></div>
		<div id="cancel"><?php $_CONTROL->btnCancel->Render(); ?></div>
		<div id="delete"><?php $_CONTROL->btnDelete->Render(); ?></div>
        </td></tr></table>
	</div>
</div>