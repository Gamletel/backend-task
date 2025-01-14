<?php

declare(strict_types = 1);

namespace Raketa\BackendTestTask\Domain;

final readonly class CartItem
{
    public function __construct(
        readonly private string $uuid,
        readonly private string $productUuid,
        readonly private float $price,
        readonly private int $quantity,
    ) {
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getProductUuid(): string
    {
        return $this->productUuid;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }
}
