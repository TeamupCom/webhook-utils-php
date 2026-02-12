# Teamup Webhook SDK

This library provides some auxiliary code to receive, validate and
process the data sent by Teamup in a webhook call.
You can also check our [API Docs](https://apidocs.teamup.com/) for more details related to
the API and Webhook itself.

## Requirements

To create a webhook in your Teamup calendar, start by visiting the calendar page:
``Settings`` > ``Integrations`` > ``Webhooks`` which lists all available webhooks
in the current calendar.

Use the `+ New` button to create a new webhook, and fill in the relevant information.
The `Endpoint` requries a web-accessible URL, where Teamup will send the webhook payloads
by using a `POST` request.

After the webhook configuration is saved, a secret value will be provided in the webhook settings.
The secret is used as a shared key to compute a keyed-hash (or [HMAC](https://en.wikipedia.org/wiki/HMAC))
of the webhook payload using the HMAC-SHA256 algorithm.
This enables you to validate the data-integrity of the webhook's payload.

Note: for local development you can use tools to route internet traffic to your local development
machine, such as:
- [ngrok](https://ngrok.com/docs/agent/overview)
- [LocalTunnel](https://theboroer.github.io/localtunnel-www/)
- [pyjam](https://tunnel.pyjam.as) (experimental)

## Installation

Install the latest version with [Composer](https://getcomposer.org):

```bash
$ composer require teamup/webhook-php
```

## Usage

This library provides a `Webhook` class to handle the webhook requests and helpers
to parse and validate the received payload from Teamup.com.

An example implementation may look like:

```php
$webhook = new Webhook(new Parser('my-secret'));

$webhook->registerHandler(Trigger::Any, new MyLogHandler());
$webhook->registerHandler(Trigger::EventCreated, new OnEventCreatedHandler());

$webhook->handle($request);
```

You can also use directly the parser for a simpler approach:

```php
$content = file_get_contents('php://input');

$parser = new Parser('my-secret');

// should throw InvalidSignatureException if the secret is incorrect or the data is corrupted
$parser->verifyIntegrity(
    $content,
    getRequestHeader(Header::TeamupSignature->value),
);

// If the integrity is verified, you can parse it or just process the raw payload
$payload = $parser->parse($content);
```
