<?php

declare(strict_types=1);

namespace Pixelant\PxaSocialFeed\Event;

final class ChangedFeedItemEvent
{
    public function __construct(private $feed)
    {
    }

    public function getFeed()
    {
        return $this->feed;
    }

    public function setFeed($feed): void
    {
        $this->feed = $feed;
    }
}
