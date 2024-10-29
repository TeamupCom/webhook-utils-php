<?php

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Teamup\Webhook\Exceptions\InvalidSignatureException;
use Teamup\Webhook\HandlerInterface;
use Teamup\Webhook\Parser;
use Teamup\Webhook\Payload\Dispatch;
use Teamup\Webhook\Trigger;
use Teamup\Webhook\Webhook;

#[CoversClass(Webhook::class)]
final class WebhookTest extends TestCase
{
    private const SECRET = '6xMtKaczFwpJoyv43KxaXSEjg2jN1Fut9uEY7xv3NKgmn5HU8qNCfBDhVruwZsuDUytaR8L8yDZP6nfPpnLw5d8ffniRKfqGnN3hNuGvMZRf7PTNVuorCcVuY62b58qG';
    private const SIGNATURE = '4d49452cab75de9852b72616376d9293b82ad137dbdb9dc2e06e6b270fe1cb10';

    /**
     * @throws InvalidSignatureException
     * @throws JsonException
     * @throws ReflectionException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testWebhook(): void
    {
        $content = file_get_contents(implode(DIRECTORY_SEPARATOR, [__DIR__, 'data', 'payload.json']));
        $s = $this->createMock(StreamInterface::class);
        $s->method('getContents')->willReturn($content);

        $r = $this->createMock(RequestInterface::class);
        $r->method('getBody')->willReturn($s);
        $r->method('getHeader')
            ->withAnyParameters()
            ->willReturn(['application/json'],
                [self::SIGNATURE],
                ['application/json'],
                [self::SIGNATURE],
            );

        $parser = new Parser(self::SECRET);

        $h1 = new class implements HandlerInterface {
            public bool $called = false;

            public function __invoke(
                RequestInterface $request,
                Dispatch $dispatch,
            ): void {
                $this->called = true;
            }
        };

        $h2 = new class implements HandlerInterface {
            public bool $called = false;

            public function __invoke(
                RequestInterface $request,
                Dispatch $dispatch,
            ): void {
                $this->called = true;
            }
        };

        $webhook = new Webhook($parser);
        $webhook->registerHandler(Trigger::Any, $h1);
        $webhook->registerHandler(Trigger::EventModified, $h2);
        $webhook->handle($r);

        $this->assertTrue($h1->called);
        $this->assertTrue($h2->called);

        $webhook = new Webhook(new Parser(self::SECRET));
        // without handlers
        $this->expectException(InvalidArgumentException::class);
        $webhook->handle($r);

        $webhook->registerHandler(Trigger::Any, $h1);
        // invalid signature
        $this->expectException(InvalidSignatureException::class);
        $webhook->handle($r);
    }
}
