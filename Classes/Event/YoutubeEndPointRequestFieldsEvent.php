<?php

declare(strict_types=1);

namespace Pixelant\PxaSocialFeed\Event;

final class YoutubeEndPointRequestFieldsEvent
{
    public function __construct(private $fields) {}

    public function getFields()
    {
        return $this->fields;
    }

    public function setFields($fields): void
    {
        $this->fields = $fields;
    }
}
