<?php

namespace Bionicmaster\FacturaCom\Exceptions;

use Exception;

class FacturaComException extends Exception
{

    const BAD_REQUEST         = "BAD_REQUEST";
    const NOT_AUTHORIZED      = "NOT_AUTHORIZED";
    const FORBIDDEN           = "FORBIDDEN";
    const NOT_FOUND           = "NOT_FOUND";
    const RATE_LIMIT          = "RATE_LIMIT";
    const SERVER_ERROR        = "SERVER_ERROR";
    const SERVICE_UNAVAILABLE = "SERVICE_UNAVAILABLE";

    /** @var  Errors that were returned by request */
    protected $errors;

    /**
     * FacturaComException constructor.
     * @param string $message
     * @param int $code
     * @param Exception|null $previous
     * @param $errors
     */
    public function __construct($message, $code, Exception $previous = null, $errors)
    {
        parent::__construct($message, $code, $previous);
        $this->errors = $errors;
    }

    /**
     * @return mixed
     */
    public function getErrors()
    {
        return $this->errors;
    }
}