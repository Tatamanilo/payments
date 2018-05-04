<script src="{$sMainSiteUrl}js/ecommertc.js" type="text/javascript"></script> 
<link type="text/css" href="{$sImagesUrl}/iQast_payment_style.css" rel="stylesheet">


<div class="checkoutContainer">
<div class="containerTop">
    <img src="{$sImagesUrl}/01_mycheckout.jpg" alt="" width="555" height="34" />
    <img src="{$sImagesUrl}/02_step1.jpg" alt="" width="445" height="34" />
</div>


<div class="content_in">
    <form method="post" action="" id="payment_form" name="payment_form">
        <div class="full">
                <p>{$translates.please_select|default:"Please select your preferred form of payment"}</p>
                {if $payment_systems}
                    {foreach from=$payment_systems item=payment_system key=id name="ps_loop"}
                    <div class="paymentOption">
                        <input type="radio" name="payment_system_name" value="{$payment_system.payment_system_name}" {if $smarty.foreach.ps_loop.first}selected{/if} /> {* {if ($user_id == "-1")}disabled{/if} />*}
                            {if $payment_system.payment_system_name == 'authnet'}
                                <img src="{$sImagesUrl}/07_creditcards.jpg" alt="" width="182" height="39" />
                            {elseif $payment_system.payment_system_name == 'paypal'}
                                <img src="{$sImagesUrl}/08_paypal.jpg" width="101" height="39" />
                            {else}
                                {$payment_system.payment_system_name}
                            {/if}
                    </div>
                    {/foreach}
                {/if}
        </div>    
        <div class="contentButtons">
            <input type="hidden" name="step" value="2" />
            <a href="#" onclick="history.back()"><img src="{$sImagesUrl}/05_backbtn.png" alt="" width="100" height="40" /></a>
            <a href="#" onClick="payment_form.submit(); return false;"><img src="{$sImagesUrl}/06_continuebtn.png" alt="" width="133" height="40" /></a>
        </div>
    </form>   
</div><!-- payment_box -->

{literal}
<script type="text/javascript">                                        
$(":radio[name=payment_system]").filter(":checked").css('background','r_butt_green.png');
</script>
{/literal}