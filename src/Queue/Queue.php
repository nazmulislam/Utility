<?php

declare(strict_types=1);

namespace NazmulIslam\Utility\Queue;

use NazmulIslam\Utility\Logger\Logger;


/**
 * Description of QueueController
 *
 * @author nazmulislam
 */
class Queue
{

    static function addToQueue(
        array $args,
        string $queue,
        int $deliveryMode = 2,
        bool $passive = false,
        bool $durable = true,
        bool $exclusive = false,
        bool $auto_delete = false,
        bool $nowait = false,
        $arguments = null,
        $ticket = null
    ) {
        try {

            if (!array_key_exists('httpHost', $args['args'])) {
                if (isset($_SERVER['HTTP_HOST']) && strtolower(php_sapi_name()) !== 'cli') {
                    $args['args']['httpHost'] = $_SERVER['HTTP_HOST'];
                }
            }
            if (!array_key_exists('httpScheme', $args['args'])) {
                if (isset($_SERVER['REQUEST_SCHEME']) && strtolower(php_sapi_name()) !== 'cli') {
                    $args['args']['httpScheme'] = $_SERVER['REQUEST_SCHEME'];
                }
            }
            
            //Logger::debug('Logger......', [$args]);
            self::publish(
                args: $args,
                queue: $queue,
                deliveryMode: $deliveryMode,
                passive: $passive,
                durable: $durable,
                exclusive: $exclusive,
                auto_delete: $auto_delete,
                nowait: $nowait,
                arguments: $arguments,
                ticket: $ticket
            );
        } catch (\Exception $ex) {
            Logger::error($ex->getMessage(), $ex->getTrace());
            //echo $ex->getMessage();
            //echo $ex->getTraceAsString();

        }
    }


    static function publish(
        array $args,
        string $queue,
        int $deliveryMode,
        bool $passive,
        bool $durable,
        bool $exclusive,
        bool $auto_delete,
        bool $nowait,
        $arguments,
        $ticket
    ): void {


        $connection = new \PhpAmqpLib\Connection\AMQPStreamConnection(
            $_ENV['RABBITMQ_HOST'],
            $_ENV['RABBITMQ_PORT'],
            $_ENV['RABBITMQ_USERNAME'],
            $_ENV['RABBITMQ_PASSWORD']
        );

        // $connection = new \PhpAmqpLib\Connection\AMQPStreamConnection(
        //     "rabbitmq-goenterprise",
        //     5672,
        //     "guest",
        //     "guest"
        // );

        $channel = $connection->channel();

        # Create the queue if it does not already exist.
        $channel->queue_declare(
            $queue,
            $passive,
            $durable,
            $exclusive,
            $auto_delete,
            $nowait,
            $arguments,
            $ticket
        );


        $msg = new \PhpAmqpLib\Message\AMQPMessage(
            json_encode($args, JSON_UNESCAPED_SLASHES),
            array('delivery_mode' => $deliveryMode) # make message persistent
        );

        $channel->basic_publish($msg, '', $queue);
        $channel->close();
        $connection->close();
    }
    static function getTenantName()
    {

        // extract username
        if (!empty($_SERVER['HTTP_HOST'])) {
            $hostInfo = explode('.', $_SERVER['HTTP_HOST']);
            return array_shift($hostInfo);
        }
    }
}
