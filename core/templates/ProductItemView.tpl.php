	<div class="ProductView">
		<?php $_CONTROL->lblId->RenderWithName(); ?>

		<?php $_CONTROL->lblManufacturer->RenderWithName(); ?>

		<?php $_CONTROL->lblSupplier->RenderWithName(); ?>

		<?php $_CONTROL->lblCreationDate->RenderWithName(); ?>

		<?php $_CONTROL->lblName->RenderWithName(); ?>

		<?php $_CONTROL->lblModel->RenderWithName(); ?>

		<?php $_CONTROL->lblShortDescription->RenderWithName(); ?>
        
        <?php $_CONTROL->lblLongDescription->RenderWithName(); ?>

<br /><br />
Image:
<br />
        <?php  $_CONTROL->ctlImageLabel->Render(); ?>
<br /><br />

This is a spot in the template to put a generic message pertaining to products in general, eg:

<br /><br />

Finally, if you think there is an error with the price please consult the FAQ. If there are still problems
then contact us though the contact page.

<br /><br />


		<?php $_CONTROL->lblRetailPrice->RenderWithName(); ?>

		<?php $_CONTROL->lblWeight->RenderWithName(); ?>

		<?php $_CONTROL->lblHeight->RenderWithName(); ?>

		<?php $_CONTROL->lblWidth->RenderWithName(); ?>

		<?php $_CONTROL->lblDepth->RenderWithName(); ?>

		<?php $_CONTROL->lblType->RenderWithName(); ?>

		<?php $_CONTROL->lblProductCategoriesAsCategory->RenderWithName(); ?>

		<?php $_CONTROL->lblParentProductsAsRelated->RenderWithName(); ?>

		<?php $_CONTROL->lblProductsAsRelated->RenderWithName(); ?>

	</div>

	<div class="formActions">
        <div class="back"><?php $_CONTROL->btnBack->Render(); ?></div>
        <div class="addtocart"><?php $_CONTROL->btnAddToCart->Render(); ?></div>
	</div>
