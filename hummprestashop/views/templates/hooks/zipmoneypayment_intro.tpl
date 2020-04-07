{*
* 2007-2015 PrestaShop
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
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<section>  
  <p>
   <span data-zm-widget='inline' data-zm-asset='checkoutdescription'></span>
   <a href="#" id="zipmoney-learn-more" class="zip-hover"  zm-widget="popup"  zm-popup-asset="checkoutdialog">Learn More</a>
   <script type="text/javascript">if(window.$zmJs!=undefined) window.$zmJs._collectWidgetsEl(window.$zmJs);</script>
   <style type="text/css">#zipmoney-learn-more{ display: inline !important; margin-left: 5px !important; }</style>
  </p>

  <script type="text/javascript" src="https://static.zipmoney.com.au/checkout/checkout-v1.js"></script>
  <script type="text/javascript">
    window.zipInitialised = false;
    var zipHasBeenSet = false;
    var setupZip = function(){
      if(!window.zipInitialised){
        window.zipInitialised = true;
        var purchaseButton = "#payment-confirmation button.btn-primary";
        var Zip_isInContext = "{$zm_in_context}";
        if(!Zip_isInContext){
          Zip_isInContext = "false";
        }
        var replaceAll = function(search, replace, subject){
          while(subject.indexOf(search) > -1){
            subject = subject.replace(search, replace);
          }
          return subject;
        }

        var redirectUri = replaceAll('&amp;', '&', "{$redirectUri}");
        var checkoutUri = replaceAll('&amp;', '&', "{$checkoutUri}");
        var zipClick = function(e){
          e.preventDefault();
          e.stopPropagation();
          Zip.Checkout.init({
            redirect: Zip_isInContext != "true",
            checkoutUri: checkoutUri,
            redirectUri: redirectUri,
            onComplete: function(response){
              // console.log(response);
              if(response.state == "approved" || response.state == "referred"){
                var nextStep = redirectUri + (redirectUri.indexOf('?') > -1 ? "&" : "?") + "result=" + response.state + "&checkoutId=" + response.checkoutId;
                // console.log(nextStep);
                window.location.href = nextStep;
              }
            }
          });
        };

        var payment_options = document.querySelectorAll('#checkout-payment-step .payment-options input[name="payment-option"]');
       
        for(var i = 0; i < payment_options.length; i ++){
          
          if(payment_options[i].getAttribute("data-module-name") == "zipmoneypayment")
          {
            var parentNode = payment_options[i].parentNode;
            while(parentNode.className.indexOf('payment-option') < 0){
              parentNode = parentNode.parentNode;
            }
            parentNode.querySelector('label span').innerHTML = "<span data-zm-asset='checkouttitle' data-zm-widget='inline'>Zip - Own it now, pay later</span>";
          }

          payment_options[i].onclick = function(){
            if(this.getAttribute("data-module-name") == "zipmoneypayment"){
              zipHasBeenSet = true;
              document.querySelector(purchaseButton).addEventListener("click", zipClick);
            } else {
              if(zipHasBeenSet){
                document.querySelector(purchaseButton).removeEventListener("click", zipClick);
                zipHasBeenSet = false;
              }
            }
          }
        }
      }
    };

    window.onload = function(){
      setupZip();
    }

    if(document.querySelector("#payment-confirmation button.btn-primary")){
      setupZip();
    }
    
    
  </script>
</section>
