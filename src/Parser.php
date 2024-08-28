<?php

namespace Teamup\Webhook;

use Andrey\JsonHandler\JsonHandler;
use InvalidArgumentException;
use JsonException;
use Psr\Http\Message\RequestInterface;
use ReflectionException;
use Teamup\Webhook\Exception\InvalidSignatureException;
use Teamup\Webhook\Payload\Payload;

readonly class Parser
{
    private const HASH_ALG = 'sha256';
    private const ACCEPTED_CONTENT_TYPE = 'application/json';

    /**
     * @phpstan-type accessKey string
     * @phpstan-type secretKey string
     *
     * @param array<accessKey, secretKey>  $keys
     */
    public function __construct(private array $keys)
    { }

    /**
     * @param string $json
     * @return Payload
     * @throws JsonException
     * @throws ReflectionException
     */
    public function parse(string $json): Payload
    {
        $handler = new JsonHandler();
        /** @var Payload $payload */
        $payload = $handler->hydrateObject($json, new Payload());
        return $payload;
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
        $accessKey = Header::TeamupAccessKey->extract($request);

        $json = $request->getBody()->getContents();
        $this->verifyIntegrity($accessKey, $json, $signature);
        return $this->parse($json);
    }

    /**
     * @throws InvalidSignatureException
     */
    public function verifyIntegrity(string $accessKey, string $json, string $signature): void
    {
        $hash = hash_hmac(
            self::HASH_ALG,
            $json,
            $this->keys[$accessKey] ?? throw new InvalidArgumentException(sprintf('secret key not found for access key \'%s\'', $accessKey)),
        );

        if ($hash !== $signature) {
            throw new InvalidSignatureException();
        }
    }
}
