<p class="payment_module">
  <a class="zipmoneypayment"  title="{$checkout_title}" style="cursor: pointer;">
    <img src="{$zm_logo_url}" alt="{$checkout_title}"/> <span data-zm-asset='checkouttitle' data-zm-widget='inline'>Zip - Own it now, pay later</span>
  </a>
</p>
<div class="zipmoneypayment_payment_module_description">
  <span data-zm-widget='inline' data-zm-asset='checkoutdescription'></span>
  <a href="#" id="zipmoney-learn-more" class="zip-hover"  zm-widget="popup"  zm-popup-asset="checkoutdialog">Learn More</a>
  <script type="text/javascript">if(window.$zmJs!=undefined) window.$zmJs._collectWidgetsEl(window.$zmJs);</script>
  <style type="text/css">#zipmoney-learn-more{ display: inline !important; margin-left: 5px !important; }</style>
</div>

<script type="text/javascript">
  $(".widget-tagline").click(function(e){
    e.preventDefault();
  })
</script>

<script type="text/javascript" src="https://static.zipmoney.com.au/checkout/checkout-v1.js"></script>
<script type="text/javascript">
  window.zipInitialised = false;
  var setupZip = function(){
    if(!window.zipInitialised){
      window.zipInitialised = true;
      var purchaseButton = ".payment_module .zipmoneypayment";
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
      document.querySelector(purchaseButton).addEventListener("click", zipClick);
    }
  };

  window.onload = function(){
    setupZip();
  }
  if(document.querySelector(".payment_module .zipmoneypayment")){
    setupZip();
  }
</script>
