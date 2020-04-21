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

    const ERROR = 7;  // Log file name
const WARN = 8; // Log file name
    const INFO = 9;  // Log file name
    const DEBUG = 10;  // Error: error conditions
    private static $fileLogger;  // Warning: warning conditions
    private static $filePath;  // Informational: informational messages
    private static $fileName = "Humm-Payments.log";  // Debug: debug messages

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
     * Logs the debug message
     *
     * @access public
     */
    public static function debug($message, $overideConfig = false)
    {
        self::writeLog($message, self::DEBUG, $overideConfig);
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

        $configLevel = (\Tools::getValue('HUMM_LOG', \Configuration::get('HUMM_LOG')));


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

    /**
     * @param $parameters
     */
    public static function logContent($parameters)
    {
        if (!self::$fileLogger) {
            self::setup();
        }

        if (\Configuration::get('HUMM_LOG'))
            self::INFO($parameters);
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
}
