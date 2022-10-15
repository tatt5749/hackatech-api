<?php

namespace App\Logging;

use Monolog\Logger;

/**
 * Class MySqlLogService
 *
 * @package App\Services
 * @author Liew <hanwah@e-sky.com.my>
 * @copyright 2018 E-Sky Tech Sdn. Bhd.
 */
class MySQLCustomLogger
{
    /**
     * Create a custom Monolog instance.
     *
     * @param  array  $config
     * @return \Monolog\Logger
     */
    public function __invoke(array $config)
    {
        $logger = new Logger("MySQLLoggingHandler");
        return $logger->pushHandler(new MySQLLoggingHandler());
    }
}

