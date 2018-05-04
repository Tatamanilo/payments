<div class="wrapper">
    <!-- <img src="{$sImagesUrl}/00_logo.png" alt="" width="189" height="48" class="logo" /> -->
    <div id="header">
        <div class="logo">
            <a href="/"><img border="0" alt="" src="{$sImagesUrl}/00_logo.png" width="189" height="48" /></a>
        </div>
    </div><!-- header -->
    <div class="checkoutContainer">
        <div class="containerTop">
            <img src="{$sImagesUrl}/01_mycheckout.jpg" alt="" width="555" height="34" />
            <img src="{$sImagesUrl}/03_step2.jpg" alt="" width="445" height="34" />
        </div>
        <div class="content_in">
            <div class="payment_box">
            <form action="{$url_process}" method="post">
                {foreach from=$data key=post_key item=post_value}
                <input type="hidden" name="{$post_key}" value="{$post_value}" />
                {/foreach}
                {if $products_info}
                <table class="cart-items-title2" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="item_name" width="505"><p>My <strong>Items</strong></p></td>
                        <td class="item_price"width="100"><p>Price</p></td>
                        <td class="item_quantity" width="85"><p>Quantity</p></td>
                    </tr>
                </table>
                <!-- </div> -->
                <table class="cart-items">
                    <tbody>
                    {foreach from=$products_info item=product_info name=proditem}
                    <tr{if $smarty.foreach.proditem.first} class="first {cycle values='odd,even'}" {/if}>
                        <td class="col-2">
                            <div>{$product_info.object_info}</div>
                        </td>
                        <td class="col-3">
                            <div>&nbsp;$&nbsp;{$product_info.object_amount}</div>
                        </td>
                        <td class="col-3">
                            <div>{$product_info.count}</div>
                        </td>
                    </tr>
                    {/foreach}
                    </tbody>
                </table>
                    
                <table class="cart-items-title3" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="item_name" width="505"><p>Total</p></td>
                        <td class="item_price"width="100"><p>&nbsp;$&nbsp;{$data.x_amount|string_format:"%.2f"}</p></td>
                        <td class="item_quantity" width="85">&nbsp;</td>
                    </tr>
                </table>
                {*<input type="button" class="butt_back_page" onclick="history.go(-1);" value="{$translates.back_page|default:"Back"}" />  onClick="history.go(-1)" 
                *}
                <div class="contentButtons2">
                    <input type="button" value="{$translates.b_back|default:"Back"}" class="backbtn" onclick='history.go(-1);' />
                    <input type="submit" class="butt_verif_page" name="confirmed" value="{$translates.verif_ord|default:"Verify"}" />
                    {*<input type="submit" class="butt_verif_page" name="confirmed" value="{$translates.verif_ord|default:"Verify"}" />continuebtn *}
                </div>
                
                {/if}
            </form>
            </div><!-- payment_box -->
            <div class="payment_box2">
            
                <div class="member_info_verif_title">
                       <p> <span class="bold_333">Total:</span> &nbsp;$&nbsp;{$data.x_amount|string_format:"%.2f"}</p>
                </div><!-- member_info_verif -->
                <div class="member_info_verif">
                        <div class="title2">My Payment Method </div>
                        <p>{$data.card_type}&nbsp;&nbsp;{*{$data.x_card_num}*}
                        {assign var=data_card_x value=$data.x_card_num|truncate:12:""}
                        {$data.x_card_num|replace:$data_card_x:"XXXX XXXX XXXX "}</p>
                        <p>Exp: {$data.x_exp_date_month}/{$data.x_exp_date_year}</p>

                </div><!-- member_info_verif -->
                <div class="member_info_verif_round">
                        <div class="title2">My Billing Address</div>
                        <p>{$data.x_first_name|default:""} {$data.x_last_name|default:""}</p>
                        <p>{$data.x_address|default:""}</p>
                        <p>{$data.x_city|default:""}, {$data.x_state|default:""}</p>
                        <p>{$data.x_zip|default:""}</p>
                        <p>{$data.x_country|default:""}</p>
                </div><!-- member_info_verif -->
            </div><!-- payment_box2 -->
    </div><!-- content -->
   </div>
    <div id="clrbottom"></div>

</div><!-- wraper -->