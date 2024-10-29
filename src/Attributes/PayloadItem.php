<?php

namespace Teamup\Webhook\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class PayloadItem
{
    public function __construct(
        public ?string $key = null,
        public bool $required = false,
        public ?string $type = null,
        public ?array $discriminator = null,
    ) {
    }
}
