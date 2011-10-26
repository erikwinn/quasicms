<div id="formControls">
    <table id="CreateAccoutFields" width="100%">
<!--  Account fields   -->
         <tr><td><?php $_CONTROL->txtUsername->RenderWithName(); ?></td></tr>
         <tr><td><?php $_CONTROL->txtPassword->RenderWithName(); ?></td></tr>
         <tr><td><?php $_CONTROL->txtPassword2->RenderWithName(); ?></td></tr>
<!--    Person fields   -->
         <tr><td><?php $_CONTROL->txtEmailAddress->RenderWithName(); ?></td></tr>
         <tr><td><?php $_CONTROL->txtNamePrefix->RenderWithName(); ?></td></tr>
         <tr><td><?php $_CONTROL->txtFirstName->RenderWithName(); ?></td></tr>
         <tr><td><?php $_CONTROL->txtMiddleName->RenderWithName(); ?></td></tr>
         <tr><td><?php $_CONTROL->txtLastName->RenderWithName(); ?></td></tr>
         <tr><td><?php $_CONTROL->txtNameSuffix->RenderWithName(); ?></td></tr>
         <tr><td><?php $_CONTROL->txtCompanyName->RenderWithName(); ?></td></tr>
<!--    Address fields   -->
         <tr><td><?php $_CONTROL->txtStreet1->RenderWithName(); ?></td></tr>
         <tr><td><?php $_CONTROL->txtStreet2->RenderWithName(); ?></td></tr>
         <tr><td><?php $_CONTROL->txtSuburb->RenderWithName(); ?></td></tr>
         <tr><td><?php $_CONTROL->txtCity->RenderWithName(); ?></td></tr>
         <tr><td><?php $_CONTROL->txtCounty->RenderWithName(); ?></td></tr>
         <tr><td><?php $_CONTROL->lstZone->RenderWithName(); ?></td></tr>
         <tr><td><?php $_CONTROL->lstCountry->RenderWithName(); ?></td></tr>
         <tr><td><?php $_CONTROL->txtPostalCode->RenderWithName(); ?></td></tr>
<!--    Person (phone) field   -->
         <tr><td><?php $_CONTROL->txtPhoneNumber->RenderWithName(); ?></td></tr>
    </table>

    </div>
	<div id="formActions">
    <div id="save"> <?php $_CONTROL->btnSave->Render();?></div>
	<div id="cancel"><?php  $_CONTROL->btnCancel->Render();?></div>
    <div id="reset"> <?php $_CONTROL->btnReset->Render();?></div>
    </div>
