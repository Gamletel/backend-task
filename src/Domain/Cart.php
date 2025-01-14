<?php

declare(strict_types = 1);

namespace Raketa\BackendTestTask\Domain;

final class Cart
{
    public function __construct(
        readonly private string $uuid,
        readonly private ?Customer $customer = null,
        readonly private ?string $paymentMethod = null,
        private ?array $items = null,
    ) {
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getCustomer(): Customer
    {
        return $this->customer;
    }

    public function getPaymentMethod(): string
    {
        return $this->paymentMethod;
    }

    /**
     * Summary of getItems
     * @return array[CartItem]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function addItem(CartItem $item): void
    {
        $this->items[] = $item;
    }
}
