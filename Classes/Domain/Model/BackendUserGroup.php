<?php

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Pixelant\PxaSocialFeed\Domain\Model;

use TYPO3\CMS\Extbase\Annotation as Extbase;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * This model represents a backend usergroup.
 */
class BackendUserGroup extends AbstractEntity
{
    public const FILE_OPPERATIONS             = 1;

    public const DIRECTORY_OPPERATIONS        = 4;

    public const DIRECTORY_COPY               = 8;

    public const DIRECTORY_REMOVE_RECURSIVELY = 16;

    #[Extbase\Validate(['validator' => 'NotEmpty'])]
    protected string $title = '';

    protected string $description = '';

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Pixelant\PxaSocialFeed\Domain\Model\BackendUserGroup>
     */
    protected ObjectStorage $subGroups;

    protected string $modules = '';

    protected string $tablesListening = '';

    protected string $tablesModify = '';

    protected string $pageTypes = '';

    protected string $allowedExcludeFields = '';

    protected string $explicitlyAllowAndDeny = '';

    protected string $allowedLanguages = '';

    protected bool $workspacePermission = false;

    protected string $databaseMounts = '';

    protected int $fileOperationPermissions = 0;

    protected string $tsConfig = '';

    /**
     * Constructs this backend usergroup
     */
    public function __construct()
    {
        $this->subGroups = new ObjectStorage();
    }

    /**
     * Setter for title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * Getter for title
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Setter for description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * Getter for description
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Setter for the sub groups
     */
    public function setSubGroups(ObjectStorage $subGroups): void
    {
        $this->subGroups = $subGroups;
    }

    /**
     * Adds a sub group to this backend user group
     */
    public function addSubGroup(\Pixelant\PxaSocialFeed\Domain\Model\BackendUserGroup $beGroup): void
    {
        $this->subGroups->attach($beGroup);
    }

    /**
     * Removes sub group from this backend user group
     */
    public function removeSubGroup(\Pixelant\PxaSocialFeed\Domain\Model\BackendUserGroup $groupToDelete): void
    {
        $this->subGroups->detach($groupToDelete);
    }

    /**
     * Remove all sub groups from this backend user group
     */
    public function removeAllSubGroups(): void
    {
        $subGroups = clone $this->subGroups;
        $this->subGroups->removeAll($subGroups);
    }

    /**
     * Getter of sub groups
     */
    public function getSubGroups(): \TYPO3\CMS\Extbase\Persistence\ObjectStorage
    {
        return $this->subGroups;
    }

    /**
     * Setter for modules
     */
    public function setModules(string $modules): void
    {
        $this->modules = $modules;
    }

    /**
     * Getter for modules
     */
    public function getModules(): string
    {
        return $this->modules;
    }

    /**
     * Setter for tables listening
     */
    public function setTablesListening(string $tablesListening): void
    {
        $this->tablesListening = $tablesListening;
    }

    /**
     * Getter for tables listening
     */
    public function getTablesListening(): string
    {
        return $this->tablesListening;
    }

    /**
     * Setter for tables modify
     */
    public function setTablesModify(string $tablesModify): void
    {
        $this->tablesModify = $tablesModify;
    }

    /**
     * Getter for tables modify
     */
    public function getTablesModify(): string
    {
        return $this->tablesModify;
    }

    /**
     * Setter for page types
     */
    public function setPageTypes(string $pageTypes): void
    {
        $this->pageTypes = $pageTypes;
    }

    /**
     * Getter for page types
     */
    public function getPageTypes(): string
    {
        return $this->pageTypes;
    }

    /**
     * Setter for allowed exclude fields
     */
    public function setAllowedExcludeFields(string $allowedExcludeFields): void
    {
        $this->allowedExcludeFields = $allowedExcludeFields;
    }

    /**
     * Getter for allowed exclude fields
     */
    public function getAllowedExcludeFields(): string
    {
        return $this->allowedExcludeFields;
    }

    /**
     * Setter for explicitly allow and deny
     */
    public function setExplicitlyAllowAndDeny(string $explicitlyAllowAndDeny): void
    {
        $this->explicitlyAllowAndDeny = $explicitlyAllowAndDeny;
    }

    /**
     * Getter for explicitly allow and deny
     */
    public function getExplicitlyAllowAndDeny(): string
    {
        return $this->explicitlyAllowAndDeny;
    }

    /**
     * Setter for allowed languages
     */
    public function setAllowedLanguages(string $allowedLanguages): void
    {
        $this->allowedLanguages = $allowedLanguages;
    }

    /**
     * Getter for allowed languages
     */
    public function getAllowedLanguages(): string
    {
        return $this->allowedLanguages;
    }

    /**
     * Setter for workspace permission
     */
    public function setWorkspacePermissions(bool $workspacePermission): void
    {
        $this->workspacePermission = $workspacePermission;
    }

    /**
     * Getter for workspace permission
     */
    public function getWorkspacePermission(): bool
    {
        return $this->workspacePermission;
    }

    /**
     * Setter for database mounts
     */
    public function setDatabaseMounts(string $databaseMounts): void
    {
        $this->databaseMounts = $databaseMounts;
    }

    /**
     * Getter for database mounts
     */
    public function getDatabaseMounts(): string
    {
        return $this->databaseMounts;
    }

    /**
     * Getter for file operation permissions
     */
    public function setFileOperationPermissions(int $fileOperationPermissions): void
    {
        $this->fileOperationPermissions = $fileOperationPermissions;
    }

    /**
     * Getter for file operation permissions
     */
    public function getFileOperationPermissions(): int
    {
        return $this->fileOperationPermissions;
    }

    /**
     * Check if file operations like upload, copy, move, delete, rename, new and
     * edit files is allowed.
     */
    public function isFileOperationAllowed(): bool
    {
        return $this->isPermissionSet(self::FILE_OPPERATIONS);
    }

    /**
     * Set the the bit for file operations are allowed.
     *
     * @param bool $value
     */
    public function setFileOperationAllowed($value): void
    {
        $this->setPermission(self::FILE_OPPERATIONS, $value);
    }

    /**
     * Check if folder operations like move, delete, rename, and new are allowed.
     */
    public function isDirectoryOperationAllowed(): bool
    {
        return $this->isPermissionSet(self::DIRECTORY_OPPERATIONS);
    }

    /**
     * Set the the bit for directory operations are allowed.
     *
     * @param bool $value
     */
    public function setDirectoryOperationAllowed($value): void
    {
        $this->setPermission(self::DIRECTORY_OPPERATIONS, $value);
    }

    /**
     * Check if it is allowed to copy folders.
     */
    public function isDirectoryCopyAllowed(): bool
    {
        return $this->isPermissionSet(self::DIRECTORY_COPY);
    }

    /**
     * Set the the bit for copy directories.
     *
     * @param bool $value
     */
    public function setDirectoryCopyAllowed($value): void
    {
        $this->setPermission(self::DIRECTORY_COPY, $value);
    }

    /**
     * Check if it is allowed to remove folders recursively.
     */
    public function isDirectoryRemoveRecursivelyAllowed(): bool
    {
        return $this->isPermissionSet(self::DIRECTORY_REMOVE_RECURSIVELY);
    }

    /**
     * Set the the bit for remove directories recursively.
     *
     * @param bool $value
     */
    public function setDirectoryRemoveRecursivelyAllowed($value): void
    {
        $this->setPermission(self::DIRECTORY_REMOVE_RECURSIVELY, $value);
    }

    /**
     * Setter for ts config
     */
    public function setTsConfig(string $tsConfig): void
    {
        $this->tsConfig = $tsConfig;
    }

    /**
     * Getter for ts config
     */
    public function getTsConfig(): string
    {
        return $this->tsConfig;
    }

    /**
     * Helper method for checking the permissions bitwise.
     *
     * @param int $permission
     */
    protected function isPermissionSet($permission): bool
    {
        return ($this->fileOperationPermissions & $permission) == $permission;
    }

    /**
     * Helper method for setting permissions bitwise.
     *
     * @param int $permission
     * @param bool $value
     */
    protected function setPermission($permission, $value)
    {
        if ($value) {
            $this->fileOperationPermissions |= $permission;
        } else {
            $this->fileOperationPermissions &= ~$permission;
        }
    }
}
