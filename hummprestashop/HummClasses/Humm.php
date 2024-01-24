<?php

namespace HummClasses;

use HummClasses\Helper\Logger;

if (!defined('_PS_VERSION_'))
    exit;
require_once(dirname(__FILE__) . '/Helper/Logger.php');
/**
 * Class Humm
 * @package HummClasses
 */
class Humm
{


    protected $_api = null;
    protected $_gateway = null;

    const VERSION = '2.0.0';
    const PLATFORM = 'Prestashop';
    const CLIENT = 'Prestashop humm Payment';


    public function __construct()
    {
        $this->context = \Context::getContext();
    }

    /**
     * Bootstrapping function.
     *
     * @access public
     */
    public static function bootstrap()
    {
        Logger::setup();
    }

}
