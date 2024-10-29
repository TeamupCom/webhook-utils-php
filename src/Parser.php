<?php

namespace Teamup\Webhook;

use InvalidArgumentException;
use JsonException;
use Psr\Http\Message\RequestInterface;
use ReflectionException;
use Teamup\Webhook\Exceptions\InvalidSignatureException;
use Teamup\Webhook\Payload\Payload;

readonly class Parser
{
    private const HASH_ALG = 'sha256';
    private const ACCEPTED_CONTENT_TYPE = 'application/json';

    public function __construct(private string $secret, private HydratorInterface $hydrator = new Hydrator())
    {
    }

    /**
     * @param string $json
     * @return Payload
     * @throws JsonException
     * @throws ReflectionException
     */
    public function parse(string $json): Payload
    {
        return $this->hydrator->hydrate($json, new Payload());
    }

    /**
     * @param RequestInterface $request
     * @return Payload
     * @throws InvalidSignatureException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function extract(RequestInterface $request): Payload
    {
        if (Header::ContentType->extract($request) !== self::ACCEPTED_CONTENT_TYPE) {
            throw new InvalidArgumentException('invalid content type');
        }

        $signature = Header::TeamupSignature->extract($request);

        $json = $request->getBody()->getContents();
        $this->verifyIntegrity($json, $signature);
        return $this->parse($json);
    }

    /**
     * @throws InvalidSignatureException
     */
    public function verifyIntegrity(string $json, string $signature): void
    {
        $hash = hash_hmac(
            self::HASH_ALG,
            $json,
            $this->secret,
        );

        if ($hash !== $signature) {
            throw new InvalidSignatureException('failed to verify payload integrity');
        }
    }
}
