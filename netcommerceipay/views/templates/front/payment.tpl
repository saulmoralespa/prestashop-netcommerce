{*
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
{capture name=path}{l s='Netcommerce iPAY' mod='netcommerceipay'}{/capture}
<div class="col-xs-12 col-sm-12 col-md-12">
    <div class="wrap">
        <div id="validation-netcommerceipay">
            <h1 class="page-heading">{l s='RESUMEN DEL PEDIDO' mod='netcommerceipay'}</h1>
            {assign var='current_step' value='payment'}
            {include file="$tpl_dir./order-steps.tpl"}
            {if $nbProducts <= 0}
                <p class="warning" style="text-align: center; font-size: 16px;">{l s='Your shopping cart is empty.' mod='netcommerceipay'}</p>
            {else}
                <form action="{$link->getModuleLink('netcommerceipay', 'validation', [], true)|escape:'html'}" id="form-netcommerceipay" method="post">
                    <div class="box cheque-box">
                        <h3 class="page-subheading" style="text-align: center; font-size: 10px;">
                            <img src="{$this_path}logo.png" alt="{l s='Bank wire' mod='bankwire'}"/>
                            <div>
                                {l s='You have chosen to pay with Netcommerce iPAY' mod='netcommerceipay'}
                            </div>
                        </h3>
                        <div>
                            <table style="width: 100%;">
                                <tr>
                                    <td style="border: solid 1px; text-align: center;">
                                        {l s='The total amount of your order is' mod='netcommerceipay'}
                                    </td>
                                    <td style="border: solid 1px;text-align: center;">
                                        <span id="amount" class="price">{displayPrice price=$total}</span>
                                        {if $use_taxes == 1}
                                            {l s='(VAT included)' mod='netcommerceipay'}
                                        {/if}
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div id="cart_navigation" class="cart_navigation clearfix">
                        <input type="submit"
                               style="background:#F0943E;color:#FFFFFF;font-size:16px;border-radius:10px;"
                               value="{l s='CONFIRMED MY ORDER' mod='netcommerceipay'}"
                               class="button btn btn-default pull-right"/>
                    </div>
                </form>
            {/if}
        </div>
    </div>
</div>