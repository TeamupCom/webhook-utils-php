<?php

namespace Teamup\Webhook;

use Psr\Http\Message\RequestInterface;

enum Header: string
{
    case TeamupAccessKey = 'teamup-key';
    case TeamupSignature = 'teamup-signature';

    case ContentType = 'content-type';

    public function extract(RequestInterface $request): ?string
    {
        return $request->getHeader($this->value)[0] ?? null;
    }
}
