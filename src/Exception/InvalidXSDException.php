<?php

namespace NFSePHP\Exception;

/**
 * Thrown when DPS XML fails XSD validation.
 */
class InvalidXSDException extends \RuntimeException
{
    /**
     * @param list<string> $errors Individual error messages (e.g. from libxml)
     */
    public function __construct(
        string $message = '',
        private readonly array $errors = [],
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return list<string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
