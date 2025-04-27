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
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Feeds
 */
class Feed extends AbstractEntity
{
    /**
     * image media type
     */
    public const int IMAGE = 1;

    /**
     * video media type
     */
    public const int VIDEO = 2;

    /**
     * pid
     */
    protected ?int $pid = 0;

    /**
     * updateDate
     */
    protected ?\DateTime $updateDate = null;

    /**
     * externalIdentifier
     */
    protected string $externalIdentifier = '';

    /**
     * date
     */
    protected ?\DateTime $postDate = null;

    /**
     * postUrl
     */
    protected string $postUrl = '';

    /**
     * message
     */
    protected string $message = '';

    /**
     * image
     *
     * @deprecated will be removed in a future version
     */
    protected string $image = '';

    /**
     * small image
     *
     * @deprecated will be removed in a future version
     */
    protected string $smallImage = '';

    /**
     * likes
     */
    protected int $likes = 0;

    /**
     * title
     */
    protected string $title = '';

    /**
     * type
     */
    protected int $type = 0;

    /**
     * token
     *
     * @var Configuration
     */
    #[Lazy]
    protected $configuration;

    /**
     * Fal media items
     *
     * @var ObjectStorage<\Pixelant\PxaSocialFeed\Domain\Model\FileReference>
     */
    #[Lazy]
    protected $falMedia;

    /**
     * media type
     *
     * @var int
     */
    protected $mediaType = self::IMAGE;

    public function __construct()
    {
        // Do not remove the next line: It would break the functionality
        $this->initializeObject();
    }

    /**
     * Initializes all ObjectStorage properties when model is reconstructed from DB (where __construct is not called)
     * Do not modify this method!
     * It will be rewritten on each save in the extension builder
     * You may modify the constructor of this class instead.
     */
    public function initializeObject(): void
    {
        $this->falMedia ??= new ObjectStorage();
    }

    /**
     * Returns the date
     *
     * @return \DateTime|null $date
     */
    public function getPostDate(): ?\DateTime
    {
        return $this->postDate;
    }

    /**
     * Sets the date
     */
    public function setPostDate(\DateTime $postDate): void
    {
        $this->postDate = $postDate;
    }

    public function getPostUrl(): string
    {
        return $this->postUrl;
    }

    public function setPostUrl(string $postUrl): void
    {
        $this->postUrl = $postUrl;
    }

    /**
     * Returns the message
     *
     * @return string $message
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Returns the message decoded
     *
     * @return string $message
     */
    public function getDecodedMessage(): string
    {
        return json_decode(
            sprintf(
                '"%s"',
                $this->message
            )
        );
    }

    /**
     * Sets the message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    /**
     * Returns the image
     *
     * @deprecated will be removed in a future version
     * @return string $image
     */
    public function getImage(): string
    {
        return $this->image;
    }

    /**
     * Sets the image
     *
     * @deprecated will be removed in a future version
     */
    public function setImage(string $image): void
    {
        $this->image = $image;
    }

    /**
     * Returns small image
     *
     * @deprecated will be removed in a future version
     * @return string $smallImage
     */
    public function getSmallImage(): string
    {
        return $this->smallImage;
    }

    /**
     * Sets the image
     *
     * @deprecated will be removed in a future version
     */
    public function setSmallImage(string $smallImage): void
    {
        $this->smallImage = $smallImage;
    }

    /**
     * Returns the title
     *
     * @return string $title
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Sets the title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * Returns the config
     *
     * @return Configuration $configuration
     */
    public function getConfiguration(): ?Configuration
    {
        if ($this->configuration instanceof LazyLoadingProxy) {
            $this->configuration->_loadRealInstance();
        }

        return $this->configuration;
    }

    /**
     * Sets the token
     *
     * @param Configuration $configuration
     */
    public function setConfiguration(?Configuration $configuration): void
    {
        $this->configuration = $configuration;
    }

    public function getExternalIdentifier(): string
    {
        return $this->externalIdentifier;
    }

    public function setExternalIdentifier(string $externalIdentifier): void
    {
        $this->externalIdentifier = $externalIdentifier;
    }

    public function getUpdateDate(): ?\DateTime
    {
        return $this->updateDate;
    }

    public function setUpdateDate(\DateTime $updateDate): void
    {
        $this->updateDate = $updateDate;
    }

    public function getLikes(): int
    {
        return $this->likes;
    }

    public function setLikes(int $likes): void
    {
        $this->likes = $likes;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): void
    {
        $this->type = $type;
    }

    /**
     * Returns the mediaType
     *
     * @return int $mediaType
     */
    public function getMediaType(): int
    {
        return $this->mediaType;
    }

    /**
     * Sets the mediaType
     */
    public function setMediaType(int $mediaType): void
    {
        $this->mediaType = $mediaType;
    }

    /**
     * Get the Fal media items
     *
     * @return ObjectStorage<\Pixelant\PxaSocialFeed\Domain\Model\FileReference>|null
     */
    public function getFalMedia(): ?ObjectStorage
    {
        if ($this->falMedia instanceof LazyLoadingProxy) {
            $this->falMedia->_loadRealInstance();
        }

        return $this->falMedia;
    }

    /**
     * Set Fal media relation
     *
     * @param ObjectStorage<\Pixelant\PxaSocialFeed\Domain\Model\FileReference> $falMedia
     */
    public function setFalMedia(ObjectStorage $falMedia): void
    {
        $this->falMedia = $falMedia;
    }

    /**
     * Add a Fal media file reference
     */
    public function addFalMedia(FileReference $falMedia): void
    {
        $this->falMedia = $this->getFalMedia();
        $this->falMedia->attach($falMedia);
    }
}
