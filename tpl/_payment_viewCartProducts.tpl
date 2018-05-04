<script src="{$sMainSiteUrl}js/cartNew.js" type="text/javascript"></script>

    <form id="prodedit" method="post" action="{$sMainSiteUrl}{$cartProductsPurchase_locname}.htm" name="prodedit">
        {assign var="amount" value=0}
        <input type="hidden" name="payment" value="1" />
        {if $error}
            <div class="p10">{$error}</div>
        {else}
        <div class="cart-status">
            <div class="status-1">
                <h1>My <strong>Shopping Cart</strong></h1>
            </div>
            <div class="status-2">
                My Order Total <strong>$<span class="total_amount">{$total_amount|string_format:"%.2f"}</span></strong>
            </div>
            <div class="status-3">
                Items in My Cart <strong><span class="total_count">{$objects|@count|default:"0"}</span></strong>
            </div>
            <div class="clear-all">&nbsp;</div>
        </div>
        <div class="cart-nav">
            <div class="nav-1">
                <a href="javascript:history.go(-1)" ><div class="continueShoppingButton buttonShadow113"><div class="buttonLeftSection"><img src="{$sImagesUrl}/09-buttonArrows.png" alt="" /></div><div class="buttonRightSectionDetailed">Continue Shopping</div></div></a>
            </div>
            <div class="nav-2">
                <div class="checkoutButton buttonShadow113" onClick="prodedit.submit()"><div class="buttonLeftSection"><img src="{$sImagesUrl}/10-buttonsLock.png" alt="" /></div><div class="buttonRightSectionDetailed">Checkout</div></div>
            </div>
            <!--<div class="nav-3">
                <a href="#" onclick="checkSum({$prod_id}); return false;" class="buttonGray buttonCorners5 buttonShadow113 updateCart">Update Cart</a>
            </div> -->
            <div class="clear-all">&nbsp;</div>
        </div>
        <div class="cart-items-title">
            My <strong>Items</strong> <br />{if $unique_transaction && $products} *NOTICE... You have a pending transaction in another open uQast window. Please finish that transaction now or you will have to restart the transaction. If you don't have an open uQast window, it could be the result of a connection issue. Please proceed with your current transaction.{/if}
        </div>
        <table class="cart-items">
            <thead>
                <tr> 
                    <th colspan="2" class="first">Item</th>
                    <th>Quantity</th>
                    <th>Item Price</th>
                    <th>Total</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody>
            {foreach from=$objects item=object key=prod_id name=cartitem}
                {if isset($object.info.price)}
                    <tr {if $smarty.foreach.cartitem.first}class="first"{/if} id="cart_tr_{$prod_id}">
                        <td class="col-1">
                            <div class="prod_thumb_border">
                                {$object.index}
                                <a href="{$object.info.link}">
                                    {assign var="thumb_folder" value=$object.info_additional.type|cat:"ThumbsBucket"}
                                    <img src="{$sMainSiteUrl}globals/inc/image_output.php?image=http://{$settings.general.mainBucket}.{$settings.general.s3Domain}/{$settings.general.$thumb_folder}/{$object.info_additional.type_info.thumbnail|replace:'_thumb':'_thumb_small'}&cap={$sImagesUrl}/freeiq.png" />
                                </a>
                            </div>
                        </td>
                        <td class="col-2">
                            <div>
                                <a href="{$object.info.link}">{$object.info.name}</a>
                                <p>{$object.info_additional.descr}</p>
                            </div>
                        </td>
                        <td class="col-3">
                            <div>
                                <input type="text" id="product_count_{$prod_id}" name="product_count[{$prod_id}]" value="{$object.count}" onchange="return changeCartProductCount({$prod_id}, this.value);" />
                            </div>
                        </td>
                        <td class="col-4">
                            <div>
                                $<span id="product_price_{$prod_id}">{$object.info.price|string_format:"%.2f"}</span>
                            </div>
                        </td>
                        <td class="col-5">
                            <div>
                                $<span title="allamount" class="product_total_amount" id="product_total_amount_{$prod_id}">{$object.info.price*$object.count|string_format:"%.2f"|default:'0'}</span>
                            </div>
                        </td>
                        <td class="col-6">
                            <div>
                                <a href="#" onclick="removeCartProduct({$prod_id}); return false;" class="cart-button-delete"><span>Delete</span></a>
                            </div>
                        </td>
                    </tr>
                {/if}
            {/foreach}
            <tr>
                <td class="col-7" colspan="6">
                    $<span class="total_amount">{$total_amount|string_format:"%.2f"}</span>
                </td>
            </tr>
        </table>
        <div class="cart-nav">
            <div class="nav-1">
                <a href="javascript:history.go(-1)"><div class="continueShoppingButton buttonShadow113"><div class="buttonLeftSection"><img src="{$sImagesUrl}/09-buttonArrows.png" alt="" /></div><div class="buttonRightSectionDetailed">Continue Shopping</div></div></a>
            </div>
            <div class="nav-2">
                <div class="checkoutButton buttonShadow113" onClick="prodedit.submit()"><div class="buttonLeftSection"><img src="{$sImagesUrl}/10-buttonsLock.png" alt="" /></div><div class="buttonRightSectionDetailed">Checkout</div></div>
            </div>
            <div class="nav-3">
                <a href="#" onclick="clearCartProducts(); return false;" class="buttonGray buttonCorners5 buttonShadow113 updateCart">Clear Cart</a>
                <!--<a href="#" onclick="checkSum({$prod_id}); return false;" class="buttonGray buttonCorners5 buttonShadow113 updateCart">Update Cart</a>-->
            </div>
            <div class="clear-all">&nbsp;</div>
        </div>
        <div id="errors">{$error_msg|default:''}</div>
    {/if}
</form>