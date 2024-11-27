<?php

namespace Teamup\Webhook\Payload;

use Teamup\Webhook\Attributes\PayloadObject;

#[PayloadObject]
class CommentMessage
{
    public string $html;
}
