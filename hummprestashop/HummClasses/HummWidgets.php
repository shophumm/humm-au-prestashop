<?php
namespace Classes;

if (!defined('_PS_VERSION_'))
  exit;

use \HummClasses\Helper\Logger as HummLogger;
use PrestaShop\PrestaShop\Core\Module\WidgetInterface;


/**
 * Class hummWidgets
 * @package Classes
 */
class hummWidgets extends Humm
{

  public $context = null;

  public function __construct($context)
  {
    $this->context = $context;
  }

  /**
   * Renders the banner in the shop page.
   *
   * @access public
   * @return html
   */
  public function render_banner_shop()
  {
    return  $this->_render_banner();
  }

  /**
   * Renders the banner in the cart page.
   *
   * @access public
   * @return html
   */
  public function render_banner_cart()
  {
    return $this->_render_banner();
  }

  /**
   * Renders the banner in the product page.
   *
   * @access public
   * @return html
   */
  public function render_banner_product()
  {
    return  $this->_render_banner();
  }

  /**
   * Renders the banner in the category page.
   *
   * @access public
   * @return html
   */
  public function render_banner_category()
  {
    return $this->_render_banner();
  }

  /**
   * Renders the banner in the category page.
   *
   * @access public
   * @return html
   */
  public function fetch($templatePath, $cache_id = null, $compile_id = null)
  {
      if ($cache_id !== null) {
          \Tools::enableCache();
      }

      if(version_compare(_PS_VERSION_, '1.7', '<')){
        list(,$template)  = explode(":",$templatePath);
        $templatePath  = 'modules/'.$template;
      }

      $template = $this->context->smarty->createTemplate(
          $templatePath,
          $cache_id,
          $compile_id,
          $this->context->smarty
      );

      if ($cache_id !== null) {
          \Tools::restoreCacheSettings();
      }

      return $template->fetch();
  }

  /**
   * Renders the element which contains the merchant public key and the api environment.
   *
   * @access public
   * @return html
   */
  public function render_root_el()
  {
    //echo '<div data-zm-merchant="'.$this->_gateway->api_settings['merchant_public_key'].'" data-env="'.$this->_gateway->environment.'"></div> ';
  }

  /**
   * Renders the widget below add to cart / proceed to checkout button in product or cart pages.
   *
   * @access public
   * @return html
   */
  public function render_widget_general()
  {
    echo '<div class="widget-product-cart" data-zm-asset="productwidget" data-zm-widget="popup"  data-zm-popup-asset="termsdialog"></div>';
  }

  /**
   * Renders the widget below add to cart / proceed to checkout button in product or cart pages.
   *
   * @access public
   * @return html
   */
  public function render_widget_cart()
  {
    return $this->fetch('module:hummprestashop/views/templates/hooks/cart_widget.tpl');
  }

  /**
   * Renders the widget below add to cart / proceed to checkout button in product or cart pages.
   *
   * @access public
   * @return html
   */
  public function render_widget_product()
  {
    return $this->fetch('module:hummprestashop/views/templates/hooks/product_widget.tpl');
  }

  /**
   * Renders the banner across the shop, cart, product, category pages.
   *
   * @access private
   * @return html
   */
  private function _render_banner()
  {
    return $this->fetch('module:hummprestashop/views/templates/hooks/banner.tpl');
  }

  /**
   * Renders the tagline after the price in product page.
   *
   * @access public
   * @return html
   */
  public function render_tagline_product()
  {
    return $this->fetch('module:hummprestashop/views/templates/hooks/tagline.tpl');
  }

  /**
   * Renders the tagline after the total price in cart.
   *
   * @access public
   * @return html
   */
  public function render_tagline_cart()
  {
    return $this->fetch('module:hummprestashop/views/templates/hooks/tagline-cart.tpl');
  }

}
