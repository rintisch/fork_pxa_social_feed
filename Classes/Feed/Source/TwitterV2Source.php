<?php

declare(strict_types=1);

namespace Pixelant\PxaSocialFeed\Feed\Source;

use Pixelant\PxaSocialFeed\Event\BeforeReturnTwitterQueryFieldsEvent;
use Pixelant\PxaSocialFeed\Exception\BadResponseException;
use Pixelant\PxaSocialFeed\Exception\InvalidFeedSourceData;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class TwitterSource
 */
class TwitterV2Source extends BaseSource
{
    /**
     * Twitter api
     */
    public const API_URL = 'https://api.twitter.com/2/';

    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {}

    /**
     * Load feed source
     *
     * @return array Feed items
     */
    public function load(): array
    {
        $endPointUrl = $this->generateEndPointUrl(
            'users/:id/tweets',
            [':id' => $this->configuration->getSocialId()]
        );
        $fields = $this->getFields();

        $authHeader = $this->getAuthHeader();

        $response = $this->requestTwitterApi(
            $this->addFieldsAsGetParametersToUrl($endPointUrl, $fields),
            $authHeader
        );

        $body = $response->getBody()->getContents();
        $data = json_decode($body, true);

        if (!is_array($data)) {
            throw new InvalidFeedSourceData(
                sprintf("Twitter v2 response doesn't appear to be a valid json. Response return '%s'.", $body),
                1684850941
            );
        }

        return $data;
    }

    /**
     * Request twitter api
     *
     * @throws BadResponseException
     */
    protected function requestTwitterApi(string $url, string $autHeader): ResponseInterface
    {
        $additionalOptions = [
            'headers' => [
                'Authorization' => $autHeader,
            ],
        ];

        return $this->performApiGetRequest($url, $additionalOptions);
    }

    /**
     * Generate url for request
     *
     * @return string
     */
    protected function generateEndPointUrl(string $endPoint, array $pathVariables = []): string|array
    {
        $url = $this->getApiUrl() . $endPoint;

        foreach ($pathVariables as $key => $value) {
            $url = str_replace($key, rawurlencode((string) $value), $url);
        }

        return $url;
    }

    /**
     * Get API url
     */
    protected function getApiUrl(): string
    {
        return self::API_URL;
    }

    /**
     * Query fields
     */
    protected function getFields(): array
    {
        $configuration = $this->getConfiguration();

        // Important to pass field value as string, because it's encoded with rawurlencode
        $fields = [
            'max_results' => (string)$configuration->getMaxItems(),
            'expansions' => 'attachments.media_keys,author_id',
            'tweet.fields' => 'created_at,public_metrics',
            'media.fields' => 'url,preview_image_url',
            'user.fields' => 'profile_image_url',
            'exclude' => 'replies',
        ];

        [ $fields ] = $this->eventDispatcher->dispatch(new BeforeReturnTwitterQueryFieldsEvent($fields));

        return $fields;
    }

    /**
     * Get Authorization header
     */
    protected function getAuthHeader(): string
    {
        $token = $this->getConfiguration()->getToken()->getBearerToken();

        return 'Bearer ' . $token;
    }
}
