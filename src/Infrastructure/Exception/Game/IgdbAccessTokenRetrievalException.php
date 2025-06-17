<?php

declare(strict_types=1);

namespace App\Infrastructure\Exception\Game;

class IgdbAccessTokenRetrievalException extends \Exception
{
    public function __construct(string $message = 'Error retrieving the access token for the IBDG client', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
