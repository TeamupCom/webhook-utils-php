<?php

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Teamup\Webhook\Exception\InvalidSignatureException;
use Teamup\Webhook\Header;
use Teamup\Webhook\Parser;
use Teamup\Webhook\Payload\Attachment;
use Teamup\Webhook\Payload\Comment;
use Teamup\Webhook\Payload\CommentMessage;
use Teamup\Webhook\Payload\Event;
use Teamup\Webhook\Payload\Note;
use Teamup\Webhook\Payload\Payload;
use Teamup\Webhook\Payload\Signup;
use Teamup\Webhook\Payload\SubCalendar;

#[CoversClass(Parser::class)]
#[CoversClass(Payload::class)]
#[CoversClass(Header::class)]
#[CoversClass(InvalidSignatureException::class)]
#[CoversClass(Attachment::class)]
#[CoversClass(Comment::class)]
#[CoversClass(CommentMessage::class)]
#[CoversClass(Event::class)]
#[CoversClass(Note::class)]
#[CoversClass(Signup::class)]
#[CoversClass(SubCalendar::class)]
final class ParserTest extends TestCase
{
    /**
     * @throws ReflectionException
     * @throws JsonException
     */
    public function testSimpleParse(): void
    {
        $content = file_get_contents(implode(DIRECTORY_SEPARATOR, [__DIR__, 'data', 'payload.json']));

        $parser = new Parser(['abc' => 'def']);
        $payload = $parser->parse($content);
        $this->assertEquals(Payload::class, $payload::class);
        $this->assertCount(1, $payload->events);
    }

    public function testSignatureIntegrity(): void
    {
        $accessKey = 'abc';

        $content = file_get_contents(implode(DIRECTORY_SEPARATOR, [__DIR__, 'data', 'payload.json']));
        $signature = 'e37d453137799807e880aa6023ca9556e7a076567099038941b5f3ffc6af5160';
        $parser = new Parser([$accessKey => 'def']);

        $this->expectException(InvalidSignatureException::class);
        $parser->verifyIntegrity($accessKey, $content, 'invalid-signature');

        $this->expectException(InvalidSignatureException::class);
        $parser->verifyIntegrity($accessKey, $content, $signature.'0');

        $parser->verifyIntegrity($accessKey, $content, $signature);
    }

    /**
     * @throws InvalidSignatureException
     * @throws JsonException
     * @throws ReflectionException
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testExtract(): void
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
            ->willReturn(['application/json'], [$signature], [$accessKey]);

        $this->expectException(InvalidSignatureException::class);
        $parser = new Parser([$accessKey => 'other']);
        $parser->extract($r);

        // success
        $parser = new Parser([$accessKey => 'def']);
        $payload = $parser->extract($r);
        $this->assertEquals(Payload::class, $payload::class);
        $this->assertCount(1, $payload->events);
    }
}
