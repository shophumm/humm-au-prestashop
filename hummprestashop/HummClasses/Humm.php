<?php

namespace HummClasses;

if (!defined('_PS_VERSION_'))
    exit;


use HummClasses\Helper\Logger as HummLogger;

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
    const CLIENT = 'Prestashop Humm Payment';


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
        HummLogger::setup();
    }

}
