<?php
/*
 * Osclass - software for creating and publishing online classified advertising platforms
 * Maintained and supported by Mindstellar Community
 * https://github.com/mindstellar/Osclass
 * Copyright (c) 2021.  Mindstellar
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *                     GNU GENERAL PUBLIC LICENSE
 *                        Version 3, 29 June 2007
 *
 *  Copyright (C) 2007 Free Software Foundation, Inc. <http://fsf.org/>
 *  Everyone is permitted to copy and distribute verbatim copies
 *  of this license document, but changing it is not allowed.
 *
 *  You should have received a copy of the GNU Affero General Public
 *  License along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * Created by Mindstellar Community.
 * User: navjottomer
 * Date: 15/07/20
 * Time: 7:03 PM
 * License is provided in root directory.
 */

namespace mindstellar\upgrade;

use RuntimeException;

/**
 * Class Osclass
 *
 * @package mindstellar\upgrade
 */
abstract class UpgradePackage
{
    private $osclass_version = OSCLASS_VERSION;

    /**
     * @var string
     */
    private $s_title;
    /**
     * @var string
     */
    private $s_source_url;
    /**
     * @var string
     */
    private $s_new_version;
    /**
     * @var string
     */
    private $s_installed_version;
    /**
     * @var string
     */
    private $s_short_name;

    private $s_target_directory;

    /**
     * @var array
     */
    private $a_compatible;

    private $a_filtered_files = [];

    private $s_prerelease;

    /**
     * @var bool
     */
    private $forceUpgrade;
    /**
     * @var bool
     */
    private $enablePreRelease;

    /**
     * UpgradePackage constructor.
     *
     * @param array $package_info
     * @param bool  $force_upgrade
     * @param bool  $enable_prerelease
     */
    public function __construct(array $package_info, bool $force_upgrade = false, bool $enable_prerelease = false)
    {
        $this->setVariable($package_info);
        $this->enablePreRelease = $enable_prerelease;
        $this->forceUpgrade     = $force_upgrade;
    }

    /**
     * Ser variable from given package info
     *
     * @param array $package_info
     *                           [
     *                           's_title' => package title,
     *                           's_source_url' => package source file,
     *                           's_new_version' => package new version, "PHP-standardized" version number string
     *                           's_installed_version' => package installed version, "PHP-standardized" version number
     *                           strings
     *                           's_short_name' => package short_name,
     *                           's_target_directory => installation target directory
     *                           'a_filtered_files => array of directory/files name which shouldn't overwrite
     *                           's_compatible' => csv of compatible osclass version (optional)
     *                           's_prerelease' => true or false (Optional)
     *                           ]
     */
    private function setVariable(array $package_info)
    {

        if (isset($package_info) && !empty($package_info)) {
            $this->s_title = $package_info['s_title'];

            if (isset($package_info['s_source_url'])
                && filter_var($package_info['s_source_url'], FILTER_VALIDATE_URL)
            ) {
                $this->s_source_url = $package_info['s_source_url'];
            } else {
                throw new RuntimeException(__('s_source_file is not a valid url'));
            }
            if (isset($package_info['s_new_version'])) {
                $this->s_new_version = $package_info['s_new_version'];
            }
            if (isset($package_info['s_installed_version'])) {
                $this->s_installed_version = $package_info['s_installed_version'];
            }
            if (isset($package_info['s_short_name'])) {
                $this->s_short_name = $package_info['s_short_name'];
            }
            if (isset($package_info['s_target_directory'])) {
                $this->s_target_directory = $package_info['s_target_directory'];
            } else {
                throw new RuntimeException(__('Invalid s_target_directory.'));
            }
            if (isset($package_info['a_filtered_files']) && is_array($package_info['a_filtered_files'])) {
                $this->a_filtered_files = $package_info['a_filtered_files'];
            }
            if (isset($package_info['s_compatible'])) {
                $this->a_compatible = explode(',', $package_info['s_compatible']);
            }
            if (isset($package_info['s_prerelease'])) {
                $this->s_prerelease = $package_info['s_prerelease'];
            }
        } else {
            throw new RuntimeException(__('Invalid upgrade package info'));
        }
    }

    /**
     * Package Title
     *
     * @return string
     */
    public function getTitle()
    : string
    {
        return $this->s_title;
    }

    /**
     * Package short name( directory name identifier)
     *
     * @return string
     */
    public function getShortName()
    : string
    {
        return $this->s_short_name;
    }

    /**
     * Package Source download url
     *
     * @return string
     */
    public function getSourceUrl()
    : string
    {
        return $this->s_source_url;
    }

    /**
     * Package target directory write
     *
     * @return string
     */
    public function getTargetDirectory()
    : string
    {
        return $this->s_target_directory;
    }

    /**
     * Array of files to not overwrite them
     *
     * @return array
     */
    public function getFilteredFiles()
    : array
    {
        return $this->a_filtered_files;
    }

    /**
     * Is package is compatible
     *
     * @return bool
     */
    public function isCompatible()
    : bool
    {
        if ($this->a_compatible !== null && !$this->forceUpgrade) {
            return in_array($this->osclass_version, $this->a_compatible, false);
        }
        return true;
    }

    /**
     * Actions after upgrade process is done
     *
     */
    abstract public function afterProcessUpgrade();

    /**
     * Is package upgradable
     *
     * @return bool|int
     */
    public function isUpgradable()
    {
        if ($this->s_prerelease && !$this->enablePreRelease) {
            return false;
        }
        if ($this->forceUpgrade) {
            return true;
        }
        return version_compare($this->s_installed_version, $this->s_new_version, 'lt');
    }

    /**
     * @return string
     */
    public function getNewVersion()
    : string
    {
        return $this->s_new_version;
    }
}
