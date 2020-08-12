<?php
/**
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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2017 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(dirname(__FILE__) . '/common/HummCommon.php');
require_once(dirname(__FILE__) . '/HummClasses/HummWidgets.php');
require_once(dirname(__FILE__) . '/HummClasses/Humm.php');


class Hummprestashop extends PaymentModule
{
    public function __construct()
    {
        $this->name = 'hummprestashop';
        $this->tab = 'payments_gateways';
        $this->version = HummCommon::HUMM_PLUGIN_VERSION;
        $this->author = 'Humm';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Humm prestashop');
        $this->description = $this->l('Accept payments for your products via humm.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall the humm module?');

        $this->limited_countries = array('AU', 'NZ');
        $this->limited_currencies = array('AUD', 'NZD');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => '1.6.99.99');
        $this->config = Configuration::getMultiple(array('HUMM_MIN_ORDER', 'HUMM_IS_ACTIVE', 'HUMM_API_KEY', 'HUMM_TITLE', 'HUMM_MERCHANT_ID', 'HUMM_TEST', 'HUMM_GATEWAY_URL', 'HUMM_DIAPLAY_BANNER_CATEGORY_PAGE', 'HUMM_DISPLAYT_WIDGET_CARTPAGE', 'HUMM_DISPLAY_BANNER_CARTPAGE', 'HUMM_DISPLAY_BANNER_HOMEPAGE', 'HUMM_DISPLAY_BANNER_PRODUCTPAGE', 'HUMM_DISPLAY_WIDGET_PRODUCTPAGE'));
        $this->humm_widgets = new \HummClasses\HummWidgets($this->context);

        \HummClasses\Humm::bootstrap();
    }

    /**
     * If we need to create update methods: http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        $iso_code = Country::getIsoById(Configuration::get('PS_COUNTRY_DEFAULT'));

        if (in_array($iso_code, $this->limited_countries) == false) {
            $this->_errors[] = $this->l('This module is not available in your country');

            return false;
        }

        //Default values
        Configuration::updateValue('HUMM_TITLE', 'Humm');
        Configuration::updateValue('HUMM_DESCRIPTION', 'Breathe easy with humm, an interest-free installment payment plan.');

        return parent::install() &&
            $this->registerHook('displayTop') &&
            $this->registerHook('displayShoppingCartFooter') &&
            $this->registerHook('displayCheckoutSummaryTop') &&
            $this->registerHook('displayHeader') &&
            $this->registerHook('displayProductPriceBlock') &&
            $this->registerHook('displayProductButtons') &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('payment') && //this is an alias for displayPayment ('payment' is deprecated)
            $this->registerHook('paymentReturn') && // this is an alias for displayPaymentReturn ('paymentReturn' is deprecated)
            $this->registerHook('displayPayment') &&
            $this->registerHook('displayPaymentReturn');
    }

    public function uninstall()
    {
        Configuration::deleteByName('HUMM_IS_ACTIVE');
        Configuration::deleteByName('HUMM_TITLE');
        Configuration::deleteByName('HUMM_COUNTRY');
        Configuration::deleteByName('HUMM_TEST');
        Configuration::deleteByName('HUMM_LOG');
        Configuration::deleteByName('HUMM_GATEWAY_URL');
        Configuration::deleteByName('HUMM_MERCHANT_ID');
        Configuration::deleteByName('HUMM_FORCE_HUMM');
        Configuration::deleteByName('HUMM_MIN_ORDER');
        Configuration::deleteByName('HUMM_DIAPLAY_BANNER_CATEGORY_PAGE');
        Configuration::deleteByName('HUMM_DISPLAY_WIDGET_CARTPAGE');
        Configuration::deleteByName('HUMM_DISPLAY_BANNER_CARTPAG');
        Configuration::deleteByName('HUMM_DISPLAY_BANNER_HOMEPAGE');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If the 'Delete' image button was pressed
         */
        if (Tools::isSubmit('delete_logo') && Tools::getValue('delete_logo')) {
            unlink($this->_path . '/images/' . Configuration::get('HUMM_LOGO'));
            Configuration::updateValue('HUMM_LOGO', '');
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name);
        }

        /**
         * If values have been submitted in the form ('Save' button), process.
         */
        $html = '';
        if (((bool)Tools::isSubmit('submitHummprestashopModule')) == true) {
            $postErrors = $this->_postValidation();
            $this->postProcess(); //we still want to save the correct settings, otherwise they'll be lost the first time
            if (!count($postErrors)) {
                $html .= $this->displayConfirmation($this->l('Humm settings updated.'));
            } else {
                foreach ($postErrors as $err) {
                    $html .= $this->displayError($err);
                }
            }
        } else {
            $html .= '<br />';
        }

        $this->context->smarty->assign('module_dir', $this->_path);
        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

        $html .= $output . $this->renderForm();

        return $html;
    }

    protected function _postValidation()
    {
        /**
         * If values have been submitted in the form, process.
         */
        $postErrors = array();
        if (((bool)Tools::isSubmit('submitHummprestashopModule')) == true) {
            if (!Tools::getValue('HUMM_TITLE')) {
                $postErrors[] = $this->l('Checkout Method is required.');
            }
            if (is_null(Tools::getValue('HUMM_TEST'))) {
                $postErrors[] = $this->l('Is Test? is required.');
            }
            if (!Tools::getValue('HUMM_MERCHANT_ID')) {
                $postErrors[] = $this->l('Merchant ID is required.');
            }
            if (!Tools::getValue('HUMM_API_KEY') && !Configuration::get('HUMM_API_KEY')) //read comment in postProcess() about the particularity of 'password' type input fields
            {
                $postErrors[] = $this->l('API Key is required.');
            }
        }

        return $postErrors;
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        //save the values for the rest of the configuration properties
        Configuration::updateValue('HUMM_IS_ACTIVE', Tools::getValue('HUMM_IS_ACTIVE'));
        Configuration::updateValue('HUMM_TITLE', Tools::getValue('HUMM_TITLE'));
        Configuration::updateValue('HUMM_COUNTRY', Tools::getValue('HUMM_COUNTRY'));
        Configuration::updateValue('HUMM_TEST', Tools::getValue('HUMM_TEST'));
        Configuration::updateValue('HUMM_LOG', Tools::getValue('HUMM_LOG'));
        Configuration::updateValue('HUMM_GATEWAY_URL', Tools::getValue('HUMM_GATEWAY_URL'));
        Configuration::updateValue('HUMM_MERCHANT_ID', Tools::getValue('HUMM_MERCHANT_ID'));
        Configuration::updateValue('HUMM_MIN_ORDER', Tools::getValue('HUMM_MIN_ORDER'));
        Configuration::updateValue('HUMM_FORCE_HUMM', Tools::getValue('HUMM_FORCE_HUMM'));
        Configuration::updateValue('HUMM_DIAPLAY_BANNER_CATEGORY_PAGE', Tools::getValue('HUMM_DIAPLAY_BANNER_CATEGORY_PAGE'));
        Configuration::updateValue('HUMM_DISPLAYT_WIDGET_CARTPAGE', Tools::getValue('HUMM_DISPLAYT_WIDGET_CARTPAGE'));
        Configuration::updateValue('HUMM_DISPLAY_BANNER_CARTPAGE', Tools::getValue('HUMM_DISPLAY_BANNER_CARTPAGE'));
        Configuration::updateValue('HUMM_DISPLAY_BANNER_HOMEPAGE', Tools::getValue('HUMM_DISPLAY_BANNER_HOMEPAGE'));
        Configuration::updateValue('HUMM_DISPLAY_BANNER_PRODUCTPAGE', Tools::getValue('HUMM_DISPLAY_BANNER_PRODUCTPAGE'));
        Configuration::updateValue('HUMM_DISPLAY_WIDGET_PRODUCTPAGE', Tools::getValue('HUMM_DISPLAY_WIDGET_PRODUCTPAGE'));

        $apiKey = strval(Tools::getValue('HUMM_API_KEY'));
        if ($apiKey) {
            //https://www.prestashop.com/forums/topic/347850-possible-bug-with-helperform-and-password-type-fields/
            Configuration::updateValue('HUMM_API_KEY', $apiKey);
        }
    }

    /**
     * Create the form that will be displayed in the configuration of the module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitHummprestashopModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for our inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm(), $this->getConfigWidgetForm()));
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'HUMM_IS_ACTIVE' => Configuration::get('HUMM_IS_ACTIVE'),
            'HUMM_TITLE' => Configuration::get('HUMM_TITLE'),
            'HUMM_COUNTRY' => Configuration::get('HUMM_COUNTRY'),
            'HUMM_TEST' => Configuration::get('HUMM_TEST'),
            'HUMM_LOG' => Configuration::get('HUMM_LOG'),
            'HUMM_GATEWAY_URL' => Configuration::get('HUMM_GATEWAY_URL'),
            'HUMM_MERCHANT_ID' => Configuration::get('HUMM_MERCHANT_ID'),
            'HUMM_API_KEY' => Configuration::get('HUMM_API_KEY'),
            'HUMM_MIN_ORDER' => Configuration::get('HUMM_MIN_ORDER'),
            'HUMM_FORCE_HUMM' => Configuration::get('HUMM_FORCE_HUMM'),
            'HUMM_DIAPLAY_BANNER_CATEGORY_PAGE' => Configuration::get('HUMM_DIAPLAY_BANNER_CATEGORY_PAGE'),
            'HUMM_DISPLAYT_WIDGET_CARTPAGE' => Configuration::get('HUMM_DISPLAYT_WIDGET_CARTPAGE'),
            'HUMM_DISPLAY_BANNER_CARTPAGE' => Configuration::get('HUMM_DISPLAY_BANNER_CARTPAGE'),
            'HUMM_DISPLAY_BANNER_HOMEPAGE' => Configuration::get('HUMM_DISPLAY_BANNER_HOMEPAGE'),
            'HUMM_DISPLAY_BANNER_PRODUCTPAGE' => Configuration::get('HUMM_DISPLAY_BANNER_PRODUCTPAGE'),
            'HUMM_DISPLAY_WIDGET_PRODUCTPAGE' => Configuration::get('HUMM_DISPLAY_WIDGET_PRODUCTPAGE'),
        );
    }

    /**
     * Create the structure of the configuration form.
     */
    protected function getConfigForm()
    {
        $pre16 = version_compare(_PS_VERSION_, '1.6', '<');
        $minimumAmountField = $pre16 ?
            array(
                'type' => 'text',
                'label' => $this->l("Minimum Order Value"),
                'desc' => $this->l('(Must be number) You can set the minimum order/cart value for Humm to show at checkout.'),
                'name' => 'HUMM_MIN_ORDER',
                'placeholder' => '0'
            ) :
            array(
                'type' => 'html',
                'label' => $this->l("Minimum Order Value"),
                'desc' => $this->l('You can set the minimum order/cart value for Humm to show at checkout.'),
                'name' => 'HUMM_MIN_ORDER',
                'size' => 32,
                'required' => true,
                'html_content' => "<input type='number' name='HUMM_MIN_ORDER' id='HUMM_MIN_ORDER' required='required' value='" . (double)Tools::getValue('HUMM_MIN_ORDER', Configuration::get('HUMM_MIN_ORDER')) . "' class='form-control' />"
            );
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'select',
                        'label' => $this->l('Checkout Method'),
                        'name' => 'HUMM_TITLE',
                        'required' => true,
                        'options' => array(
                            'query' => array(
                                array('id' => 'Oxipay', 'name' => 'oxipay'),
                                array('id' => 'Humm', 'name' => 'humm'),
                            ),
                            'id' => 'id',
                            'name' => 'name',
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Active'),
                        'name' => "HUMM_IS_ACTIVE",
                        'is_bool' => true,
                        'class' => 't',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            )
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Country'),
                        'name' => 'HUMM_COUNTRY',
                        'required' => true,
                        'options' => array(
                            'query' => array(
                                array('id' => 'AU', 'name' => 'Australia'),
                                array('id' => 'NZ', 'name' => 'New Zealand'),
                            ),
                            'id' => 'id',
                            'name' => 'name',
                        ),
                    ),
                    $minimumAmountField,
                    array(
                        'type' => 'select',
                        'label' => $this->l('Is Test?'),
                        'name' => 'HUMM_TEST',
                        'required' => true,
                        'options' => array(
                            'query' => array(
                                array('id' => '1', 'name' => 'Yes'),
                                array('id' => '0', 'name' => 'No'),
                            ),
                            'id' => 'id',
                            'name' => 'name',
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('FORCE HUMM'),
                        'name' => 'HUMM_FORCE_HUMM',
                        'required' => true,
                        'options' => array(
                            'query' => array(
                                array('id' => '1', 'name' => 'Yes'),
                                array('id' => '0', 'name' => 'No'),
                            ),
                            'id' => 'id',
                            'name' => 'name',
                        ),
                    ),

                    array(
                        'type' => 'select',
                        'label' => $this->l('HUMM LOG'),
                        'name' => 'HUMM_LOG',
                        'required' => true,
                        'options' => array(
                            'query' => array(
                                array('id' => '1', 'name' => 'Yes'),
                                array('id' => '0', 'name' => 'No'),
                            ),
                            'id' => 'id',
                            'name' => 'name',
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Gateway URL'),
                        'prefix' => '<i class="icon icon-globe"></i>',
                        'name' => 'HUMM_GATEWAY_URL',
                        'desc' => $this->l('This overrides the checkout URL of the payment service. Mainly for testing purpose only. Leave it empty if you are not sure.')
                    ),

                    array(
                        'type' => 'text',
                        'label' => $this->l('Merchant ID'),
                        'prefix' => '<i class="icon icon-user"></i>',
                        'name' => 'HUMM_MERCHANT_ID',
                        'desc' => $this->l('This is the unique number that identifies you as a merchant to the humm Payment Gateway.'),
                        'required' => true
                    ),
                    array(
                        'type' => 'password',
                        'label' => $this->l('API Key'),
                        'prefix' => '<i class="icon icon-key"></i>',
                        'name' => 'HUMM_API_KEY',
                        'desc' => $this->l('This is used to authenticate you as a merchant and to ensure that no one can tamper with the information sent as part of purchase orders.'),
                        'required' => true
                    ),
                ),
            ),
        );
    }

    /**
     * @return array
     */

    protected function getConfigWidgetForm()
    {
        $pre16 = version_compare(_PS_VERSION_, '1.6', '<');
        $switch_type = $pre16 ? 'radio' : 'switch';

        $fields_form_customization = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Widgets', array(), 'Modules.HummPayment.Admin'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'label',
                        'label' => $this->l('Home Page'),
                        'lang' => true
                    ),
                    array(
                        'type' => $switch_type,
                        'label' => $this->l('Display Banner'),
                        'name' => "HUMM_DISPLAY_BANNER_HOMEPAGE",
                        'is_bool' => true,
                        'class' => 't',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            )
                        ),
                    ),

                    array(
                        'type' => 'label',
                        'label' => $this->l('Product Page'),
                        'lang' => true
                    ),
                    array(
                        'type' => $switch_type,
                        'label' => $this->l('Display Strip Banner'),
                        'name' => "HUMM_DISPLAY_BANNER_PRODUCTPAGE",
                        'is_bool' => true,
                        'class' => 't',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            )
                        ),
                    ),
                    array(
                        'type' => $switch_type,
                        'label' => $this->l('Display Widget'),
                        'name' => "HUMM_DISPLAY_WIDGET_PRODUCTPAGE",
                        'is_bool' => true,
                        'class' => 't',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            )
                        ),
                    ),
                    array(
                        'type' => 'label',
                        'label' => $this->l('Cart Page'),
                        'lang' => true
                    ),
                    array(
                        'type' => $switch_type,
                        'label' => $this->l('Display Strip Banner'),
                        'name' => "HUMM_DISPLAY_BANNER_CARTPAGE",
                        'is_bool' => true,
                        'class' => 't',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            )
                        ),
                    ),
                    array(
                        'type' => $switch_type,
                        'label' => $this->l('Display Widget'),
                        'name' => "HUMM_DISPLAYT_WIDGET_CARTPAGE",
                        'is_bool' => true,
                        'class' => 't',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            )
                        ),
                    ),
                    array(
                        'type' => 'label',
                        'label' => $this->l('Category Page'),
                        'lang' => true
                    ),
                    array(
                        'type' => $switch_type,
                        'label' => $this->l('Display Strip Banner'),
                        'name' => "HUMM_DIAPLAY_BANNER_CATEGORY_PAGE",
                        'is_bool' => true,
                        'class' => 't',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            )
                        ),
                    ),


                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
        return $fields_form_customization;
    }

    /**
     *
     */
    public function hookDisplayBackOfficeHeader()
    {
        $this->context->controller->addCSS($this->_path . 'views/css/back.css', 'all');
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path . 'views/js/back.js');
            $this->context->controller->addCSS($this->_path . 'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path . '/views/js/front.js');
        $this->context->controller->addCSS($this->_path . '/views/css/front.css');

    }

    /**
     * This method is used to render the payment button,
     * Take care if the button should be displayed or not.
     */
    public function hookPayment($params)
    {
        return $this->hookDisplayPayment($params);
    }

    public function hookDisplayPayment($params)
    {
        if (!Configuration::get('HUMM_IS_ACTIVE')) {
            return false;
        }
        $cart = $params['cart'];
        $config_values = $this->getConfigFormValues();

        $descriptions = array(
            'Oxipay' => 'Breathe easy with Oxipay, an interest-free installment payment plan.',
            'Humm' => 'Pay in slices. No interest ever.'
        );

        $this->smarty->assign(array(
            'humm_title' => $config_values['HUMM_TITLE'],
            'humm_logo' => strtolower($config_values['HUMM_TITLE']),
            'humm_description' => $descriptions[$config_values['HUMM_TITLE']],
            'this_path_ssl' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->name . '/',
            'humm_validation_errors' => $this->cartValidationErrors($cart)
        ));

        return $this->display(__FILE__, 'views/templates/hook/payment.tpl');
    }

    /**
     * Checks the quote for validity
     * @throws Mage_Api_Exception
     */
    private function cartValidationErrors($cart)
    {
        $shippingAddress = new Address((int)$cart->id_address_delivery);
        $billingAddress = new Address((int)$cart->id_address_invoice);
        $currency = new Currency((int)$cart->id_currency);

        $billingCountryIsoCode = (new Country($billingAddress->id_country))->iso_code;
        $shippingCountryIsoCode = (new Country($shippingAddress->id_country))->iso_code;
        $currencyIsoCode = $currency->iso_code;

        if ($cart->getOrderTotal() <= Configuration::get('HUMM_MIN_ORDER')) {
            return " doesn't support purchases less than $" . Configuration::get('HUMM_MIN_ORDER');
        }

        $countryNames = array(
            'AU' => 'Australia',
            'NZ' => 'New Zealand'
        );
        $currencyCodes = array(
            'AU' => 'AUD',
            'NZ' => 'NZD'
        );
        $countryCode = Configuration::get('HUMM_COUNTRY');

        if ($billingCountryIsoCode != $countryCode || $currencyIsoCode != $currencyCodes[$countryCode]) {
            return " doesn't support purchases from outside " . ($countryNames[$countryCode]) . ".";
        }

        if ($shippingCountryIsoCode != $countryCode) {
            return " doesn't support purchases shipped outside " . ($countryNames[$countryCode]) . ".";
        }

        return "";
    }

    /**
     * This hook is used to display the order confirmation page.
     */
    public function hookPaymentReturn($params)
    {
        return $this->hookDisplayPaymentReturn($params);
    }

    //TODO: is this really needed?

    /**
     * @param $params
     */
    public function hookDisplayPaymentReturn($params)
    {
        if ($this->active == false) {
            return;
        }

        $order = $params['objOrder'];

        if ($order->getCurrentOrderState()->id != Configuration::get('PS_OS_ERROR')) {
            $this->smarty->assign('status', 'ok');
        }

        $this->smarty->assign(array(
            'id_order' => $order->id,
            'reference' => $order->reference,
            'params' => $params,
            'total' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false),
        ));

        return $this->display(__FILE__, 'views/templates/hook/confirmation.tpl');
    }


    /**
     * @param $param
     * @return mixed
     */
    public function hookdisplayProductButtons($param)
    {

        if (Configuration::get('HUMM_DISPLAY_WIDGET_PRODUCTPAGE') && $this->context->controller->php_self == 'product') {
            $this->smarty->assign(array(
                'productPrice' => $param['product']->getPrice()
            ));
            return $this->display(__FILE__, 'views/templates/hook/product_widget.tpl');
        }

    }

    /**
     * @param $param
     * @return string
     * @throws Exception
     */
    public function hookDisplayShoppingCartFooter($param)
    {
        if (Configuration::get('HUMM_DISPLAYT_WIDGET_CARTPAGE')) {
            $this->smarty->assign(array(
                'productPrice' => $this->context->cart->getOrderTotal(true)
            ));
            return $this->display(__FILE__, 'views/templates/hook/product_widget.tpl');
        };
    }

    /**
     * @param $param
     * @return string
     * @throws Exception
     */

    public function hookdisplayCheckoutSummaryTop($param)
    {
        if (Configuration::get('HUMM_DISPLAYT_WIDGET_CARTPAGE')) {
            $this->smarty->assign(array(
                'productPrice' => $this->context->cart->getOrderTotal(true)
            ));
            return $this->display(__FILE__, 'views/templates/hook/product_widget.tpl');
        };
    }

    /**
     * Module Hook Display Top.
     *
     * @access public
     * @return  widget html
     */
    public function hookDisplayHeader()
    {
        $this->context->controller->addJS($this->_path . '/views/js/front.js');
        $this->context->controller->addCSS($this->_path . '/views/css/front.css');

        if ($this->context->controller->php_self == 'index' &&
            Configuration::get('HUMM_DISPLAY_BANNER_HOMEPAGE')) {
            $html = $this->humm_widgets->render_banner_product();
            var_export($html);
        } else if ($this->context->controller->php_self == 'product' &&
            Configuration::get('HUMM_DISPLAY_BANNER_PRODUCTPAGE')) {
            $html = $this->humm_widgets->render_banner_product();
            var_export($html);
        } else if ($this->context->controller->php_self == 'category' &&
            Configuration::get('HUMM_DIAPLAY_BANNER_CATEGORY_PAGE')) {
            $html = $this->humm_widgets->render_banner_product();
            var_export($html);
        }
    }


}
