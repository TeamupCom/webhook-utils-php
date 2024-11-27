<?php

namespace Teamup\Webhook\Payload;

use Teamup\Webhook\Attributes\PayloadObject;

#[PayloadObject]
class Notes
{
    public string $html;
}
