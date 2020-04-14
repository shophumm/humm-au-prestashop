<?php

namespace HummClasses\Helper;

if (!defined('_PS_VERSION_'))
    exit;

/**
 * Class Logger
 * @package HummClasses\Classes\Helper
 */
class Logger
{

    private static $fileLogger;  // Log file name

    private static $filePath; // Log file name
    private static $fileName = "Humm-Payments.log";  // Log file name
    const ERROR = 7;  // Error: error conditions
    const WARN = 8;  // Warning: warning conditions
    const INFO = 9;  // Informational: informational messages
    const DEBUG = 10;  // Debug: debug messages

    /**
     * Logger constructor.
     * @param $context
     */
    public function __construct()
    {
        self::$fileLogger = new \FileLogger;

        if (version_compare(_PS_VERSION_, '1.7', '>='))
            self::$filePath = _PS_ROOT_DIR_ . '/app/logs/';
        else
            self::$filePath = _PS_ROOT_DIR_ . '/log/';

        self::$fileLogger->setFilename(self::$filePath . self::$fileName);

    }

    /**
     * Sets up the logger and defines the log file path.
     *
     * @access public
     */

    public static function setup()
    {
        if (!self::$fileLogger) {
            self::$fileLogger = new \FileLogger;

            if (version_compare(_PS_VERSION_, '1.7', '>='))
                self::$filePath = _PS_ROOT_DIR_ . '/app/logs/';
            else
                self::$filePath = _PS_ROOT_DIR_ . '/log/';

            self::$fileLogger->setFilename(self::$filePath . self::$fileName);
        }
        else {
            self::info("log is built in ".self::$filePath);
        }
    }


    /**
     * @param $message
     * @param int $level
     * @param bool $overideConfig
     * @return bool
     */
    public static function writeLog($message, $level = self::INFO, $overideConfig = false)
    {

        if (!$message) {
            return false;
        }

        $configLevel = (\Tools::getValue('HUMM_LOGGING', \Configuration::get('HUMM_LOGGING')));

        $configLevel = 1;

        if (!$overideConfig) { // You can use this variable to log regardless of config settings.
            if (!$configLevel) {
                return false;
            }
        }

        if ($configLevel < 7) {
            $configLevel = self::INFO; // default log level
        }

        if ($level > $configLevel) {
            return false;
        }

        switch ($level) {
            case self::DEBUG:
                self::$fileLogger->level = \FileLogger::DEBUG;
                self::$fileLogger->logDebug($message);
                break;

            case self::INFO:
                self::$fileLogger->level = \FileLogger::INFO;
                self::$fileLogger->logInfo($message);
                break;

            case self::WARN:
                self::$fileLogger->level = \FileLogger::WARNING;
                self::$fileLogger->logWarning($message);
                break;

            case self::ERROR:
                self::$fileLogger->level = \FileLogger::ERROR;
                self::$fileLogger->logError($message);
                break;

            default:
                self::$fileLogger->level = \FileLogger::INFO;
                self::$fileLogger->logInfo($message);
                break;
        }
    }

    /**
     * Logs the debug message
     *
     * @access public
     */
    public static function debug($message, $overideConfig = false)
    {
        self::writeLog($message, self::DEBUG, $overideConfig);
    }

    /**
     * Logs the info message
     *
     * @access public
     */
    public static function info($message)
    {
        self::writeLog($message, self::INFO);
    }

    /**
     * Logs the warnings
     *
     * @access public
     */
    public static function warn($message)
    {
        self::writeLog($message, self::WARN);
    }

    /**
     * Logs the error message
     *
     * @access public
     */
    public static function error($message)
    {
        self::writeLog($message, self::ERROR);
    }

}
