<?php

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Teamup\Webhook\Exception\InvalidSignatureException;
use Teamup\Webhook\HandlerInterface;
use Teamup\Webhook\Parser;
use Teamup\Webhook\Payload\Event;
use Teamup\Webhook\Payload\Payload;
use Teamup\Webhook\Trigger;
use Teamup\Webhook\Webhook;

#[CoversClass(Webhook::class)]
final class WebhookTest extends TestCase
{
    /**
     * @throws InvalidSignatureException
     * @throws JsonException
     * @throws ReflectionException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testWebhook(): void
    {
        $accessKey = 'abc';
        $signature = 'e37d453137799807e880aa6023ca9556e7a076567099038941b5f3ffc6af5160';
        $content = file_get_contents(implode(DIRECTORY_SEPARATOR, [__DIR__, 'data', 'payload.json']));
        $s = $this->createMock(StreamInterface::class);
        $s->method('getContents')->willReturn($content);

        $r = $this->createMock(RequestInterface::class);
        $r->method('getBody')->willReturn($s);
        $r->method('getHeader')
            ->withAnyParameters()
            ->willReturn(['application/json'], [$signature], [$accessKey], ['application/json'], [$signature], [$accessKey]);

        $parser = new Parser([
            $accessKey => 'def',
        ]);

        $h1 = new class implements HandlerInterface {
            public bool $called = false;
            public function __invoke(
                RequestInterface $request,
                Event $event,
                Payload $payload
            ): void {
                $this->called = true;
            }
        };

        $h2 = new class implements HandlerInterface {
            public bool $called = false;
            public function __invoke(
                RequestInterface $request,
                Event $event,
                Payload $payload
            ): void {
                $this->called = true;
            }
        };

        $webhook = new Webhook($parser);
        $webhook->registerHandler(Trigger::Any, $h1);
        $webhook->registerHandler(Trigger::EventRemoved, $h2);
        $webhook->handle($r);

        $this->assertTrue($h1->called);
        $this->assertTrue($h2->called);

        $webhook = new Webhook(new Parser(['key' => 'secret']));
        // without handlers
        $this->expectException(InvalidArgumentException::class);
        $webhook->handle($r);

        $webhook->registerHandler(Trigger::Any, $h1);
        // invalid signature
        $this->expectException(InvalidSignatureException::class);
        $webhook->handle($r);
    }
}
