<?php
declare(strict_types=1);
namespace NazmulIslam\Utility\Logger;
use Monolog\Logger as MonoLogLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\BrowserConsoleHandler;

/**
 * Description of Logger
 *
 * @author nazmulislam
 */
class Logger implements LoggerInterface
{
    CONST LOG_PATH = __DIR__.'/../../../';
    /**
     * DEBUG (100): Detailed debug information.
     * @param string $message
     * @param array $data
     */
    public static function debug(string $message,array $data = [], string|null $logPath = null):void
    {

       
        $logger = new MonoLogLogger('DEBUG');
        $logger->pushHandler(new StreamHandler(self::LOG_PATH.($logPath ?? $_ENV['LOG_PATH']), MonoLogLogger::DEBUG));
        $logger->debug($message,$data);
    }

    /**
     * INFO (200): Interesting events. Examples: User logs in, SQL logs.
     * @param string $message
     * @param array $data
     */
    public static function info(string $message,string | array | null $data,string|null $logPath = null):void
    {
        $logger = new MonoLogLogger('INFO');
        $logger->pushHandler(new StreamHandler(self::LOG_PATH.($logPath ?? $_ENV['LOG_PATH']), MonoLogLogger::INFO));
        $logger->info($message,$data);
    }

    /**
     * NOTICE (250): Normal but significant events.
     * @param string $message
     * @param array $data
     */
    public static function notice(string $message,string | array | null $data,string|null $logPath = null):void
    {
        $logger = new MonoLogLogger('NOTICE');
        $logger->pushHandler(new StreamHandler(self::LOG_PATH.($logPath ?? $_ENV['LOG_PATH']), MonoLogLogger::NOTICE));
        $logger->notice($message,$data);
    }

    /**
     * WARNING (300): Exceptional occurrences that are not errors. Examples: Use of deprecated APIs, poor use of an API, undesirable things that are not necessarily wrong.
     * @param string $message
     * @param array $data
     */
    public static function warning(string $message,string | array | null $data,string|null $logPath = null):void
    {
        $logger = new MonoLogLogger('WARNING');
        $logger->pushHandler(new StreamHandler(self::LOG_PATH.($logPath ?? $_ENV['LOG_PATH']), MonoLogLogger::WARNING));
        $logger->warning($message,$data);
    }

    /**
     * WARNING (300): Exceptional occurrences that are not errors. Examples: Use of deprecated APIs, poor use of an API, undesirable things that are not necessarily wrong.
     * @param string $message
     * @param array $data
     */
    public static function error(string $message, array $data = [], string|null $logPath = null):void
    {
        $logger = new MonoLogLogger('ERROR');
        $logger->pushHandler(new StreamHandler(self::LOG_PATH.($logPath ?? $_ENV['LOG_PATH']), MonoLogLogger::ERROR));
        $logger->error($message,$data);
    }

    /**
     * CRITICAL (500): Critical conditions. Example: Application component unavailable, unexpected exception.


     * @param string $message
     * @param array $data
     */
    public static function critical(string $message, array $data = [], string|null $logPath = null ):void
    {
        $logger = new MonoLogLogger('CRITICAL');
        $logger->pushHandler(new StreamHandler(self::LOG_PATH.($logPath ?? $_ENV['LOG_PATH']), MonoLogLogger::CRITICAL));
        $logger->critical($message,$data);
    }

    /**
     * ALERT (550): Action must be taken immediately. Example: Entire website down, database unavailable, etc. This should trigger the SMS alerts and wake you up.
     * @param string $message
     * @param array $data
     */
    public static function alert(string $message, array $data = [], string|null $logPath = null):void
    {
        $logger = new MonoLogLogger('ALERT');
        $logger->pushHandler(new StreamHandler(self::LOG_PATH.($logPath ?? $_ENV['LOG_PATH']), MonoLogLogger::ALERT));
        $logger->alert($message,$data);
    }

    /**
     * EMERGENCY (600): Emergency: system is unusable.
     * @param string $message
     * @param array $data
     */
    public static function emergency(string $message, array $data = [], string|null $logPath = null):void
    {
        $logger = new MonoLogLogger('EMERGENCY');
        $logger->pushHandler(new StreamHandler(self::LOG_PATH.($logPath ?? $_ENV['LOG_PATH']), MonoLogLogger::EMERGENCY));
        $logger->emergency($message,$data);
    }

    /**
     * This outputs to the browser console.
     * @param string $message
     * @param array $data
     */
    public static function debugToConsole($data)
    {
        $logger = new MonoLogLogger('GENERAL');
        $logger->pushHandler(new BrowserConsoleHandler(MonoLogLogger::EMERGENCY));
        $logger->emergency('Logger is now Ready');

    }
}
