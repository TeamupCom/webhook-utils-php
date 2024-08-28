<?php

namespace Teamup\Webhook;

use Psr\Http\Message\RequestInterface;
use Teamup\Webhook\Payload\Event;
use Teamup\Webhook\Payload\Payload;

interface HandlerInterface
{
    public function __invoke(RequestInterface $request, Event $event, Payload $payload);
}
