<?php


namespace Ybren\Codis\Exception;


use Throwable;

class ConnException extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
{
    parent::__construct($message, $code, $previous);
}
}