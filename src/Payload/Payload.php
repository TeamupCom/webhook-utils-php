<?php

namespace Teamup\Webhook\Payload;

use Teamup\Webhook\Attributes\PayloadItem;
use Teamup\Webhook\Attributes\PayloadObject;

#[PayloadObject]
class Payload
{
    public string $id;
    public string $calendar;
    /**
     * @var Dispatch[]
     */
    #[PayloadItem(type: Dispatch::class)]
    public array $dispatch;
}
