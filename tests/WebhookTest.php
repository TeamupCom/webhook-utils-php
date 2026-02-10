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
    private const SIGNATURE = '4ef624acedb83c1bde128eddbaec248e2b0eddc0bf8587d240d351e121a7dabd';

    /**
     * @throws InvalidSignatureException
     * @throws JsonException
     * @throws ReflectionException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testWebhook(): void
    {
        $content = file_get_contents(__DIR__.'/data/payload.json');
        $this->assertJson($content);
        $s = $this->createStub(StreamInterface::class);
        $s->method('getContents')->willReturn($content);

        $r = $this->createStub(RequestInterface::class);
        $r->method('getBody')->willReturn($s);
        $r->method('getHeader')
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
