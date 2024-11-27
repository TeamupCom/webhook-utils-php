<?php

namespace Teamup\Webhook;

enum Trigger: string
{
    case Any = '*';

    case EventCreated = 'event.created';
    case EventModified = 'event.modified';
    case EventRemoved = 'event.removed';

    case SignupCreated = 'event_signup.created';
    case SignupModified = 'event_signup.modified';
    case SignupRemoved = 'event_signup.removed';

    case CommentCreated = 'event_comment.created';
    case CommentModified = 'event_comment.modified';
    case CommentRemoved = 'event_comment.removed';
}
