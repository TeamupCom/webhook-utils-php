<?php

namespace Teamup\Webhook\Payload;

use Teamup\Webhook\Attributes\PayloadItem;
use Teamup\Webhook\Trigger;

class Dispatch
{
    #[PayloadItem]
    public Trigger $trigger;

    #[PayloadItem(discriminator: ['event' => Event::class])]
    public Event $data;
}
