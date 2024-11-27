<?php

namespace Teamup\Webhook;

use Psr\Http\Message\RequestInterface;
use Teamup\Webhook\Payload\Dispatch;

interface HandlerInterface
{
    public function __invoke(RequestInterface $request, Dispatch $dispatch);
}
