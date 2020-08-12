<?php

namespace HummClasses;

use HummClasses\Helper\Logger;

require_once(dirname(__FILE__) . '/Humm.php');
if (!defined('_PS_VERSION_'))
    exit;

/**
 * Class hummWidgets
 * @package Classes
 */
class HummWidgets extends Humm
{

    public $context = null;
    public $log = null;

    public function __construct($context)
    {
        $this->context = $context;
        Humm::bootstrap();
    }

    /**
     * Renders the banner in the shop page.
     *
     * @access public
     * @return html
     */
    public function render_banner_shop()
    {
        return $this->_render_banner();
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
        return $this->_render_banner();
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

        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            list(, $template) = explode(":", $templatePath);
            $templatePath = 'modules/' . $template;
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
        Logger::info($templatePath);
        return $template->fetch();
    }

    /**
     * Renders the widget below add to cart / proceed to checkout button in product or cart pages.
     *
     * @access public
     * @return html
     */
    public function render_widget_cart()
    {
        return $this->fetch('module:hummprestashop/views/templates/hook/cart_widget.tpl');
    }

    /**
     * Renders the widget below add to cart / proceed to checkout button in product or cart pages.
     *
     * @access public
     * @return html
     */
    public function render_widget_product()
    {
        return $this->fetch('module:hummprestashop/views/templates/hook/product_widget.tpl');
    }

    /**
     * Renders the banner across the shop, cart, product, category pages.
     *
     * @access private
     * @return html
     */
    private function _render_banner()
    {
        return $this->fetch('module:hummprestashop/views/templates/hook/banner.tpl');
    }

}
