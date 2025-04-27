<?php

declare(strict_types=1);

namespace Pixelant\PxaSocialFeed\Event;

final class FacebookEndPointEvent
{
    public function __construct(private $endPoint) {}

    /**
     * @return mixed
     */
    public function getEndPoint()
    {
        return $this->endPoint;
    }

    public function setEndPoint(mixed $endPoint): self
    {
        $this->endPoint = $endPoint;
        return $this;
    }
}
