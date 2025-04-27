<?php

declare(strict_types=1);

namespace Pixelant\PxaSocialFeed\Domain\Model;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2015
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use League\OAuth2\Client\Provider\Exception\FacebookProviderException;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Pixelant\PxaSocialFeed\Feed\Source\FacebookSource;
use Pixelant\PxaSocialFeed\Provider\Facebook;
use Pixelant\PxaSocialFeed\SignalSlot\EmitSignalTrait;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Tokens
 */
class Token extends AbstractEntity
{
    // use EmitSignalTrait;

    /**
     * facebook user token
     */
    public const int FACEBOOK = 1;

    /**
     * instagram_oauth2
     */
    public const int INSTAGRAM = 2;

    /**
     * twitter token
     */
    public const int TWITTER = 3;

    /**
     * youtube token
     */
    public const int YOUTUBE = 4;

    /**
     * facebook page token
     */
    public const int FACEBOOK_PAGE = 5;

    /**
     * twitter token v2 API
     */
    public const int TWITTER_V2 = 6;

    /**
     * Default PID
     */
    protected ?int $pid = 0;

    /**
     * @var ObjectStorage<BackendUserGroup>|null
     */
    #[\TYPO3\CMS\Extbase\Annotation\ORM\Lazy]
    protected \TYPO3\CMS\Extbase\Persistence\ObjectStorage $beGroup;

    protected string $name = '';

    protected int $type = 0;

    protected string $appId = '';

    protected string $appSecret = '';

    protected string $accessToken = '';

    protected string $apiKey = '';

    protected string $apiSecretKey = '';

    protected string $accessTokenSecret = '';

    protected string $bearerToken = '';

    protected ?Facebook $fb = null;

    protected string $fbSocialId = '';

    protected ?Token $parentToken;

    /**
     * Initialize.
     */
    public function __construct()
    {
        $this->beGroup = new ObjectStorage();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): void
    {
        $this->type = $type;
    }

    public function getAppId(): string
    {
        return $this->appId;
    }

    public function setAppId(string $appId): void
    {
        $this->appId = $appId;
    }

    public function getAppSecret(): string
    {
        return $this->appSecret;
    }

    public function setAppSecret(string $appSecret): void
    {
        $this->appSecret = $appSecret;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function setAccessToken(string $accessToken): void
    {
        $this->accessToken = $accessToken;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function setApiKey(string $apiKey): void
    {
        $this->apiKey = $apiKey;
    }

    public function getApiSecretKey(): string
    {
        return $this->apiSecretKey;
    }

    public function setApiSecretKey(string $apiSecretKey): void
    {
        $this->apiSecretKey = $apiSecretKey;
    }

    public function getAccessTokenSecret(): string
    {
        return $this->accessTokenSecret;
    }

    public function getFbSocialId(): string
    {
        return $this->fbSocialId;
    }

    public function setAccessTokenSecret(string $accessTokenSecret): void
    {
        $this->accessTokenSecret = $accessTokenSecret;
    }

    public function getBearerToken(): string
    {
        return $this->bearerToken;
    }

    public function setBearerToken(string $bearerToken): void
    {
        $this->bearerToken = $bearerToken;
    }

    public function getBeGroup(): ?ObjectStorage
    {
        return $this->beGroup;
    }

    public function setBeGroup(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $beGroup): void
    {
        $this->beGroup = $beGroup;
    }

    /**
     * Check if facebook token is valid
     */
    public function isValidFacebookAccessToken(): bool
    {
        $isValid = true;
        if ($this->accessToken === '' || $this->accessToken === '0') {
            $isValid = false;
        } else {
            try {
                $token = new AccessToken([
                    'access_token' => $this->getAccessToken(),
                ]);
                $this->getFb(
                    $this->getAppId(),
                    $this->getAppSecret()
                )->getLongLivedAccessToken((string)$token->getToken());
            } catch (FacebookProviderException|IdentityProviderException) {
                $isValid = false;
            }
        }

        return $isValid;
    }

    /**
     * Check how much it left for facebook access token
     *
     * @throws \Exception
     */
    public function getFacebookAccessTokenValidPeriod(string $format = '%R%a'): string
    {
        $expireAt = $this->getFacebookAccessTokenMetadataExpirationDate();
        if ($expireAt instanceof \DateTime) {
            $today = new \DateTime();
            $interval = $today->diff($expireAt);

            return $interval->format($format);
        }

        return 'Could not get expire date of token';
    }

    /**
     * Get date when facebook token expire
     */
    public function getFacebookAccessTokenMetadataExpirationDate(): ?\DateTime
    {
        try {
            $expireAt = new \DateTime('+60 days');
            $token = new AccessToken([
                'access_token' => $this->getAccessToken(),
            ]);
            $this->getFb($this->getAppId(), $this->getAppSecret())->getLongLivedAccessToken($token);
        } catch (FacebookProviderException|IdentityProviderException) {
            return null;
        }

        return $expireAt;
    }

    /**
     * Facebook login url
     */
    public function getFacebookLoginUrl(string $clientId, string $clientSecret, string $redirectUrl, array $permissions): string
    {
        // required by SDK login
        session_start();

        $fb = $this->getFb($clientId, $clientSecret, $redirectUrl);
        return $fb->getAuthorizationUrl([
            'scope' => $permissions,
        ]) . '&bypass=1';
    }

    /**
     * Fetch all available pages from facebook
     */
    public function getFacebookPagesIds(): array
    {
        $token = new AccessToken([
            'access_token' => $this->getAccessToken(),
        ]);

        try {
            $body = $this->getFb($this->getAppId(), $this->getAppSecret())->getResourceOwner($token);
        } catch (\Exception) {
            $body = null;
        }

        if (isset($body)) {
            $accounts = [
                'me' => LocalizationUtility::translate('module.source_id_me', 'PxaSocialFeed'),
            ];
            $accounts[$body->getId()] = sprintf(
                '%s (ID: %s)',
                $body->getName(),
                $body->getId()
            );
        } else {
            $accounts = ['0' => 'Invalid data. Could not fetch accounts(pages) list from facebook'];
        }

        return $accounts;
    }

    public function getParentToken(): ?Token
    {
        return $this->parentToken;
    }

    /**
     * Get value for select box.
     */
    public function getTitle(): string
    {
        $type = LocalizationUtility::translate('module.type.' . $this->getType(), 'PxaSocialFeed') ?? '';
        if ($this->getName() !== '' && $this->getName() !== '0') {
            return sprintf('%s (%s)', $type, $this->getName());
        }

        return $type;
    }

    /**
     * Check if is facebook token type
     */
    public function isFacebookType(): bool
    {
        return $this->type === static::FACEBOOK;
    }

    /**
     * Check if is facebook page token type
     */
    public function isFacebookPageType(): bool
    {
        return $this->type === static::FACEBOOK_PAGE;
    }

    /**
     * Check if it's of type instagram
     */
    public function isInstagramType(): bool
    {
        return $this->type === static::INSTAGRAM;
    }

    /**
     * Check if it's of type twitter
     */
    public function isTwitterType(): bool
    {
        return $this->type === static::TWITTER;
    }

    /**
     * Check if it's of type twitter
     */
    public function isTwitterV2Type(): bool
    {
        return $this->type === static::TWITTER_V2;
    }

    /**
     * Check if it's of type youtube
     */
    public function isYoutubeType(): bool
    {
        return $this->type === static::YOUTUBE;
    }

    /**
     * Get FB
     */
    public function getFb(string $clientId = '', string $clientSecret = '', string $redirectUri = ''): Facebook
    {
        if (!$this->fb instanceof \Pixelant\PxaSocialFeed\Provider\Facebook) {
            $this->fb = new Facebook(
                [
                    'clientId'          => $clientId,
                    'clientSecret'      => $clientSecret,
                    'redirectUri'       => $redirectUri,
                    'graphApiVersion'   => FacebookSource::GRAPH_VERSION,
                ]
            );
        }

        return $this->fb;
    }

    /**
     * Return all available types
     */
    public static function getAvailableTokensTypes(): array
    {
        return [
            static::FACEBOOK,
            static::INSTAGRAM,
            static::TWITTER,
            static::TWITTER_V2,
            static::YOUTUBE,
        ];
    }
}
