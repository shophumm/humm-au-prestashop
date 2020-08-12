{*
* 2007-2017 PrestaShop
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2017 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<div class="hummmodal">
    {capture name=path}
        <a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html':'UTF-8'}"
           title="{l s='Go back to the Checkout' mod='hummprestashop'}">{l s='Checkout' mod='hummprestashop'}</a>
        <span class="navigation-pipe">{$navigationPipe}</span>{l s='Humm' mod='hummprestashop'}
    {/capture}

    {include file="$tpl_dir./breadcrumb.tpl"}

    <h2>{l s='Order summary' mod='hummprestashop'}</h2>

    {assign var='current_step' value='payment'}
    {include file="$tpl_dir./order-steps.tpl"}

    {if $nbProducts <= 0}
        <p class="warning ">{l s='Your shopping cart is empty.' mod='hummprestashop'}</p>
    {else}
        {$form_query}
        <p class="cart_navigation" id="cart_navigation">
            <a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html'}"
               class="button_large">{l s='Other payment methods' mod='hummprestashop'}</a>
        </p>
    {/if}
    <script type="text/javascript">
        $body = $("body");
        $(document).ready(function () {
            $body.addClass("hummloading");
            $('#hummload').submit();
        });
    </script>
