<?php

namespace Pixelant\PxaSocialFeed\Tests\Unit\Feed\Update;

use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use Pixelant\PxaSocialFeed\Domain\Model\Feed;
use Pixelant\PxaSocialFeed\Domain\Repository\FeedRepository;
use Pixelant\PxaSocialFeed\Feed\Update\BaseUpdater;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class BaseUpdaterTest
 */
class BaseUpdaterTest extends UnitTestCase
{
    /**
     * @var BaseUpdater
     */
    protected $subject;

    protected function setUp(): void
    {
        $reflection = new \ReflectionProperty(GeneralUtility::class, 'singletonInstances');
        $reflection->setAccessible(true);

        $this->subject = $this->getAccessibleMock(BaseUpdater::class, ['update'], [], '', false);
    }

    protected function tearDown(): void
    {
        unset($this->subject);
    }

    /**
     * @test
     */
    public function addOrUpdateFeedItemSaveItemInFeedsStorage(): void
    {
        $feed = new Feed();
        $feedStorage = new ObjectStorage();

        $this->subject->_call('addOrUpdateFeedItem', $feed);

        self::assertEquals(1, $feedStorage->count());
    }

    /**
     * @test
     */
    public function addOrUpdateFeedItemCallAddOnNewItem(): void
    {
        $feed = new Feed();
        $mockedRepository = $this->createMock(FeedRepository::class);
        $mockedRepository
            ->expects(self::once())
            ->method('add')
            ->with($feed);

        $this->inject($this->subject, 'feedRepository', $mockedRepository);
        $this->inject($this->subject, 'feeds', $this->createMock(ObjectStorage::class));

        $this->subject->_call('addOrUpdateFeedItem', $feed);
    }

    /**
     * @test
     */
    public function addOrUpdateFeedItemCallUpdateOnExistingItem(): void
    {
        $feed = new Feed();
        $feed->_setProperty('uid', 1);

        $mockedRepository = $this->createMock(FeedRepository::class);
        $mockedRepository
            ->expects(self::once())
            ->method('update')
            ->with($feed);

        $this->inject($this->subject, 'feedRepository', $mockedRepository);
        $this->inject($this->subject, 'feeds', $this->createMock(ObjectStorage::class));

        $this->subject->_call('addOrUpdateFeedItem', $feed);
    }

    /**
     * @test
     */
    public function encodeMessageForSimpleStringReturnSameString(): void
    {
        $value = 'test string';

        self::assertEquals($value, $this->subject->_call('encodeMessage', $value));
    }
}
