{if $status == 'ok'}
    <form action="https://www.netcommercepay.com/iPAY/" method="post" id="form-netcommerce">
        <input type="hidden" name="payment_mode" value="{$isTest}">
        <input type="hidden" name="txtAmount" value="{$total}">
        <input type="hidden" name="txtCurrency" value="{$numbercurrency}">
        <input type="hidden" name="txtIndex" value="{$reference}">
        <input type="hidden" name="txtMerchNum" value="{$merchantId}">
        <input type="hidden" name="txthttp" value="{$response}">
        <input type="hidden" name="signature" value="{$signature}">
        <input type="hidden" name="first_name" value="{$p_billing_name}">
        <input type="hidden" name="last_name" value="{$p_billing_lastname}">
        <input type="hidden" name="email" value="{$p_billing_email}">
        <input type="hidden" name="mobile" value="{$phone}">
        <input type="hidden" name="address_line1" value="{$address}">
        <input type="hidden" name="city" value="{$city}">
        <input type="hidden" name="country" value="{$country}">
    </form>
    <script>
        document.getElementById("form-netcommerce").submit();
    </script>
{else}
    <div class="cprow">
        <div class="cpcolumn">
            <div class="cpalert">
                {l s='We have noticed that there is a problem with your order. If you think this is an error, you can contact our' mod='netcommerceipay'}
                <a href="{$link->getPageLink('contact', true)|escape:'html'}">{l s='customer service department.' mod='netcommerceipay'}</a>.
            </div>
        </div>
    </div>
{/if}
