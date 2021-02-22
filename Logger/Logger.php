<?php
namespace Integrai\Core\Logger;
/**
 * Integrai custom logger allows name changing to differentiate log call origin
 * Class Logger
 *
 * @package Integrai\Core\Logger
 */
class Logger
    extends \Monolog\Logger
{

    /**
     * Set logger name
     * @param $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

}