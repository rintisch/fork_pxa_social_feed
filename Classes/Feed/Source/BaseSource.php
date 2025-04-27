<?php

declare(strict_types=1);

namespace Pixelant\PxaSocialFeed\Feed\Source;

use Pixelant\PxaSocialFeed\Domain\Model\Configuration;
use Pixelant\PxaSocialFeed\Exception\BadResponseException;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class BaseSource
 */
abstract class BaseSource implements FeedSourceInterface
{
    public function __construct(protected \Pixelant\PxaSocialFeed\Domain\Model\Configuration $configuration) {}

    /**
     * Get configuration
     */
    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    /**
     * Append endpoint url with get parameters based on fields
     */
    protected function addFieldsAsGetParametersToUrl(string $url, array $fields): string
    {
        return $url . ($fields === [] ? '' : ('?' . http_build_query($fields)));
    }

    /**
     * Get request to api url
     *
     * @throws BadResponseException
     */
    protected function performApiGetRequest(string $url, array $additionalOptions = []): ResponseInterface
    {
        /** @var RequestFactory $requestFactory */
        $requestFactory = GeneralUtility::makeInstance(RequestFactory::class);

        /** @var ResponseInterface $response */
        $response = $requestFactory->request(
            $url,
            'GET',
            $additionalOptions
        );

        if ($response->getStatusCode() === 200) {
            return $response;
        }

        $body = (string)$response->getBody();
        // @codingStandardsIgnoreStart
        throw new BadResponseException(sprintf("Api request return status '%s' while trying to request '%s' with message '%s'", $response->getStatusCode(), $url, $body), 1562910160643);
        // @codingStandardsIgnoreEnd
    }
}
