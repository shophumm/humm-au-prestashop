<?php
namespace Classes\Helper;

use PrestaShop\PrestaShop\Adapter\ServiceLocator;

if (!defined('_PS_VERSION_'))
  exit;

/**
 * Class Api
 * @package Classes\Helper
 */
class Api {

  private static $_api;

  const API_CLASS ='api';

  /**
   * Get module api instance.
   * 
   * @return Class $api
   */
  public static function getApi()
  {  
    if(!is_null(self::$_api)){
      return self::$_api;
    } else {
      return self::setApi();
    }
  }

  /**
   * Set module api instance
   * 
   * @param Class $api
   * @return  Class $api
   */
  public static function setApi($api = null)
  {
    if(is_string($api)){
      self::$_api = new $api;
    } else if(is_object($api)){
      self::$_api =  $api;
    } else {
      $api = self::API_CLASS;
      self::$_api = new $api;
    }

    return self::$_api;
  }


}
