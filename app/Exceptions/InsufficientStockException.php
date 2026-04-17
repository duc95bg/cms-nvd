<?php

namespace App\Exceptions;

use RuntimeException;

class InsufficientStockException extends RuntimeException
{
    public function __construct(
        public readonly string $productName,
        public readonly int $requestedQty,
        public readonly int $availableStock
    ) {
        parent::__construct(
            "Insufficient stock for {$productName}: requested {$requestedQty}, available {$availableStock}"
        );
    }
}
