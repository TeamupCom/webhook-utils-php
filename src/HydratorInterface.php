<?php

namespace Teamup\Webhook;

interface HydratorInterface
{
    public function hydrate(string|array $json, object|string $objOrClass): object;
}
