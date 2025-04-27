<?php

declare(strict_types=1);

namespace Pixelant\PxaSocialFeed\Domain\Repository;

use Doctrine\DBAL\DBALException;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class BackendUserGroupRepository
 */
class BackendUserGroupRepository
{
    /**
     * Find all BE user groups
     *
     * @param array|null $exclude Uids of groups to exclude
     * @return array
     * @throws DBALException
     */
    public function findAll(array $exclude = null)
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('be_groups');

        $queryBuilder
            ->select('*')
            ->from('be_groups');

        if ($exclude !== null && $exclude !== []) {
            $queryBuilder->where(
                $queryBuilder->expr()->notIn(
                    'uid',
                    $queryBuilder->createNamedParameter($exclude, Connection::PARAM_INT_ARRAY)
                )
            );
        }

        return $queryBuilder->execute()->fetchAllAssociative();
    }
}
