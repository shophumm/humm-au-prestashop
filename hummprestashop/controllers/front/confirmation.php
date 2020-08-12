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

require_once( dirname( __FILE__ ) . '/../../common/HummCommon.php' );
require_once(dirname(__FILE__) . '/../../HummClasses/Helper/Logger.php');


class HummprestashopConfirmationModuleFrontController extends ModuleFrontController {
    public function postProcess() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $query = $_POST;
        }
        elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $scheme = ( ! empty( $_SERVER['HTTPS'] ) )? 'https' : 'http';

            $full_url = sprintf(
                '%s://%s%s',
                $scheme,
                $_SERVER['HTTP_HOST'],
                $_SERVER['REQUEST_URI']
            );
    
            $parts    = parse_url( $full_url, PHP_URL_QUERY ); 
            parse_str( $parts, $query );
        }

        \HummClasses\Helper\Logger::logContent(sprintf(" End Transaction for Return Query%s Mothod %s",json_encode($query),$_SERVER['REQUEST_METHOD']));

        $isValid = HummCommon::isValidSignature( $query, Configuration::get( 'HUMM_API_KEY' ) );

        if ( ! $isValid ) {
            \HummClasses\Helper\Logger::logContent(sprintf("Signature error "));
            $this->errors[] = $this->module->l( 'An error occured with the humm payment. Please contact the merchant to have more information' );

            return $this->setTemplate( 'error.tpl' );
        }

        $transactionId = Tools::getValue( "x_gateway_reference" );
        $cart_id       = explode( "-", Tools::getValue( 'x_reference' ) )[0];
        $secure_key    = explode( "-", Tools::getValue( 'x_reference' ) )[1];

        $cart           = new Cart( (int) $cart_id );
        $customer       = new Customer( (int) $cart->id_customer );
        $payment_status = Configuration::get( 'PS_OS_PAYMENT' ); // Default value for a payment that succeed.

        //We are not using a second script to be used by the Payment Gateway to issue the async callback
        //to notify us to validate the order remotely (i.e. validation.php). We are using this 
        //confirmation.php script for both uses (user browser redirection validation and remote async 
        //callback validation). For this reason, if the async callback has been already issued, this
        //order is already in 'PS_OS_PAYMENT' and we don't need to 'validateOrder' again (as this would
        //result in the 'Cart cannot be loaded or an order has already been placed using this cart' error
        //-the one from PrestaShop/classes/PaymentModule.php-).
        /**
         * If the order has been validated we try to retrieve it
         */
        $order_id = Order::getOrderByCartId( (int) $cart_id );
        if ( $order_id ) {
            $order = new Order( (int) $order_id );
            if ( $order && $order->getCurrentState() == $payment_status ) {
                //if the order had already been validated by the async callback from the Payment Gateway
                //and the payment was successful...
                //Because only successful transactions generate orders
                \HummClasses\Helper\Logger::logContent(sprintf("End Transaction by Directly Return without Add New Order %s %s",$order_id,$secure_key));
                $this->redirectToOrderConfirmationPage( $cart_id, $order_id, $secure_key );
                return true;
            }
        }

        /**
         * Converting cart into a valid order
         */
        $module_name = $this->module->displayName;
        $currency_id = (int) Context::getContext()->currency->id;

        if ( $isValid && Tools::getValue( 'x_result' ) == 'completed' ) {
            $message = "Humm authorisation success. Transaction #$transactionId";
            $this->module->validateOrder( $cart_id, $payment_status, $cart->getOrderTotal(), $module_name, $message, array(), $currency_id, false, $secure_key );

            /**
             * If the order has been validated we try to retrieve it
             */
            $order_id = Order::getOrderByCartId( (int) $cart->id );

            if ( $order_id && ( $secure_key == $customer->secure_key ) ) {
                /**
                 * The order has been placed so we redirect the customer on the confirmation page.
                 */
                \HummClasses\Helper\Logger::logContent(sprintf("End Transaction By Add New Order %s %s",$order_id,$secure_key));
                $this->redirectToOrderConfirmationPage( $cart_id, $order_id, $secure_key );
            } else {
                /**
                 * An error occured and is shown on a new page.
                 */
                $this->errors[] = $this->module->l( 'An error occured. Please contact the merchant to have more information.' );
                \HummClasses\Helper\Logger::logContent(sprintf("end transaction in the errors %s %s %s"),$order_id,$secure_key,$customer->secure_key);
                return $this->setTemplate( 'error.tpl' );
            }
        } else {
            /**
             * An error occured and is shown on a new page.
             */
            $this->errors[] = $this->module->l( 'Payment has been declined by provider humm' );
            $link           = $this->context->link->getPageLink( 'order', true, null, "step=3" );
            $this->context->smarty->assign( 'checkout_link', $link );

            return $this->setTemplate( 'error.tpl' );
        }
    }

    /**
     * @param $cart_id
     * @param $order_id
     * @param $secure_key
     */
    private function redirectToOrderConfirmationPage( $cart_id, $order_id, $secure_key ) {
        $module_id = $this->module->id;
        Tools::redirect( 'index.php?controller=order-confirmation&id_cart=' . $cart_id . '&id_module=' . $module_id . '&id_order=' . $order_id . '&key=' . $secure_key );
    }

}
