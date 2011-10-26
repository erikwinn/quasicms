<div class="ProductItemList">
<table>
    <thead>
        <tr>
            <th>Product</th><th>Quantity</th><th>Item Price</th><th>Item Total</th>
        </tr>
    </thead>
    <tbody>
<?php    
        foreach($_CONTROL->aryCheckOutItemViews as $objItemView)
            $objItemView->Render();
?>
        <tr>
            <td colspan=3>SubTotal:</td>
            <td colspan=1>
            <?php print money_format('%n',$_CONTROL->ItemsTotalPrice);?>
            </td>
        </tr>
    </tbody>
</table>
</div>
