<script>
  (function($){
    var html = '<div class="widget-tagline widget-tagline-cart"  data-zm-widget="tagline" data-zm-info="true"></div>';
    if($(".cart-detailed-totals").length){
      $('.cart-detailed-totals').after(html);
    }else if($('.standard-checkout').length){
      $('.cart_navigation').after(html);
    }
  })(window.jQuery || window.$);
</script>
<style>
  .widget-tagline{
    float: right;
  }
</style>
