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
    private const SIGNATURE = '4ef624acedb83c1bde128eddbaec248e2b0eddc0bf8587d240d351e121a7dabd';

    public function testSimpleParse(): void
    {
        $content = file_get_contents(__DIR__.'/data/payload.json');
        $this->assertJson($content);

        $parser = new Parser(self::SECRET);
        $payload = $parser->parse($content);
        $this->assertEquals(Payload::class, $payload::class);
        $this->assertCount(2, $payload->dispatch);
    }

    public function testSignatureIntegrity(): void
    {
        $content = file_get_contents(__DIR__.'/data/payload.json');
        $this->assertJson($content);
        $parser = new Parser(self::SECRET);

        $parser->verifyIntegrity($content, self::SIGNATURE);

        $this->expectException(InvalidSignatureException::class);
        $parser->verifyIntegrity($content, 'invalid-signature');
        $parser->verifyIntegrity($content, self::SIGNATURE . '0');
    }

    public function testExtract(): void
    {
        $content = file_get_contents(__DIR__.'/data/payload.json');
        $this->assertJson($content);

        $s = $this->createStub(StreamInterface::class);
        $s->method('getContents')->willReturn($content);

        $r = $this->createStub(RequestInterface::class);
        $r->method('getBody')->willReturn($s);
        $r->method('getHeader')
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
