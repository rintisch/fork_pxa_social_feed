<?php

declare(strict_types=1);

namespace Pixelant\PxaSocialFeed\Feed\Source;

use Pixelant\PxaSocialFeed\Domain\Model\Configuration;
use Pixelant\PxaSocialFeed\Event\YoutubeEndPointRequestFieldsEvent;
use Pixelant\PxaSocialFeed\Exception\BadResponseException;
use Pixelant\PxaSocialFeed\Exception\InvalidFeedSourceData;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class YoutubeSource
 */
class YoutubeSource extends BaseSource
{
    public const API_URL = 'https://www.googleapis.com/youtube/v3/';

    /**
     * Load feed source
     *
     * @return array Feed items
     */
    public function load(): array
    {
        $fields = $this->getFields($this->getConfiguration());

        $endPointUrl = $this->addFieldsAsGetParametersToUrl(
            $this->generateEndPointUrl('search'),
            $fields
        );

        $response = $this->requestYoutubeApi($endPointUrl);

        $body = (string)$response->getBody();
        $data = json_decode($body, true);

        if (!is_array($data) || !isset($data['items'])) {
            // @codingStandardsIgnoreStart
            throw new InvalidFeedSourceData(sprintf("Youtube response doesn't appear to be a valid json. Items are missing. Response return '%s'.", $body), 1562910457024);
            // @codingStandardsIgnoreEnd
        }

        return $data['items'];
    }

    /**
     * Request youtube api
     *
     * @throws BadResponseException
     */
    protected function requestYoutubeApi(string $url): ResponseInterface
    {
        return $this->performApiGetRequest($url);
    }

    /**
     * Youtube api endpoint url
     */
    protected function generateEndPointUrl(string $endPoint): string
    {
        return $this->getUrl() . $endPoint;
    }

    /**
     * Get api url
     */
    protected function getUrl(): string
    {
        return self::API_URL;
    }

    /**
     * Get youtube fields for request
     */
    protected function getFields(Configuration $configuration): array
    {
        $fields = [
            'order' => 'date',
            'part' => 'snippet',
            'type' => 'video',
            'maxResults' => $configuration->getMaxItems(),
            'channelId' => $configuration->getSocialId(),
            'key' => $configuration->getToken()->getApiKey(),
        ];

        $eventDispatcher = GeneralUtility::makeInstance(EventDispatcherInterface::class);
        $event           = $eventDispatcher->dispatch(new YoutubeEndPointRequestFieldsEvent($fields));

        return $event->getFields();
    }
}
