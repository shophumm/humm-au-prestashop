<?php
namespace HummClasses\Helper;

if (!defined('_PS_VERSION_'))
  exit;

/**
 * Class ErrorHandling
 * @package Classes\Helper
 */
class ErrorHandling{

  public static function getMappedErrorCode($code){
    $mapped = array(
      "account_insufficient_funds" => "Humm-0001",
      "account_inoperative" => "Humm-0002",
      "account_locked" => "Humm-0003",
      "amount_invalid" => "Humm-0004",
      "fraud_check" => "Humm-0005",
      "invalid_state" => "Humm-0006",
    );
    if(array_key_exists($code, $mapped)){
      return $mapped[$code];
    }
    return;
  }
}
?>
