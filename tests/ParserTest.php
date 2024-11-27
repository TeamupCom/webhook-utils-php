<?php

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Teamup\Webhook\Exceptions\InvalidSignatureException;
use Teamup\Webhook\Hydrator;
use Teamup\Webhook\Parser;
use Teamup\Webhook\Payload\Payload;

#[CoversClass(Parser::class)]
#[CoversClass(Hydrator::class)]
final class ParserTest extends TestCase
{
    private const SECRET = '6xMtKaczFwpJoyv43KxaXSEjg2jN1Fut9uEY7xv3NKgmn5HU8qNCfBDhVruwZsuDUytaR8L8yDZP6nfPpnLw5d8ffniRKfqGnN3hNuGvMZRf7PTNVuorCcVuY62b58qG';
    private const SIGNATURE = '4d49452cab75de9852b72616376d9293b82ad137dbdb9dc2e06e6b270fe1cb10';

    /**
     * @throws ReflectionException
     * @throws JsonException
     */
    public function testSimpleParse(): void
    {
        $content = file_get_contents(implode(DIRECTORY_SEPARATOR, [__DIR__, 'data', 'payload.json']));

        $parser = new Parser(self::SECRET);
        $payload = $parser->parse($content);
        $this->assertEquals(Payload::class, $payload::class);
        $this->assertCount(2, $payload->dispatch);
    }

    /**
     * @throws InvalidSignatureException
     */
    public function testSignatureIntegrity(): void
    {
        $content = file_get_contents(implode(DIRECTORY_SEPARATOR, [__DIR__, 'data', 'payload.json']));
        $parser = new Parser(self::SECRET);

        $parser->verifyIntegrity($content, self::SIGNATURE);

        $this->expectException(InvalidSignatureException::class);
        $parser->verifyIntegrity($content, 'invalid-signature');
        $parser->verifyIntegrity($content, self::SIGNATURE . '0');
    }

    /**
     * @throws InvalidSignatureException
     * @throws JsonException
     * @throws ReflectionException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testExtract(): void
    {
        $content = file_get_contents(implode(DIRECTORY_SEPARATOR, [__DIR__, 'data', 'payload.json']));
        $s = $this->createMock(StreamInterface::class);
        $s->method('getContents')->willReturn($content);

        $r = $this->createMock(RequestInterface::class);
        $r->method('getBody')->willReturn($s);
        $r->method('getHeader')
            ->withAnyParameters()
            ->willReturn(['application/json'], [self::SIGNATURE], ['application/json'], ['invalid']);

        // success
        $parser = new Parser(self::SECRET);
        $payload = $parser->extract($r);
        $this->assertEquals(Payload::class, $payload::class);
        $this->assertCount(2, $payload->dispatch);

        $this->expectException(InvalidSignatureException::class);
        $parser = new Parser(self::SECRET);
        $parser->extract($r);
    }
}
