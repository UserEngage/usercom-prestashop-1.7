<?php

namespace PShow\Core\Database\Migrations;

class MigrationTool
{

    /**
     * @var string
     */
    private $moduleName;

    /**
     * @param $moduleName
     * @return MigrationTool
     */
    public static function getInstance($moduleName)
    {
        /** @var MigrationTool[] $instance */
        static $instance;
        if ($instance === null) {
            $instance = array();
        }

        if (!isset($instance[$moduleName])) {
            $instance[$moduleName] = new static($moduleName);
        }

        return $instance[$moduleName];
    }

    /**
     * MigrationTool constructor.
     *
     * @param $moduleName
     */
    private function __construct($moduleName)
    {
        $this->moduleName = $moduleName;
    }

    /**
     * @param $version
     * @return bool
     */
    public function setCurrentVersion($version)
    {
        return \Configuration::updateValue(strtoupper($this->moduleName) . '_DB_MIGRATION', $version);
    }

    /**
     * @return int|string
     */
    public function getCurrentVersion()
    {
        return \Configuration::get(strtoupper($this->moduleName) . '_DB_MIGRATION');
    }

    /**
     * Migrate to $targetVersion
     *
     * @param $targetVersion
     */
    public function migrate($targetVersion)
    {
        while ($targetVersion != $this->getCurrentVersion()) {
            $current = $this->getCurrentVersion();

            if ($targetVersion < $current) {
                $this->migrateDown();
            } else {
                $this->migrateUp();
            }
        }
    }

    /**
     * Migrate down
     *
     * @param bool $full
     * @return bool|mixed|null
     */
    public function migrateDown($full = false)
    {
        $available = $this->getAvailableMigrations();
        $availableVersions = array_keys($available);

        $current = $this->getCurrentVersion();
        if (!$current) {
            // unable to migrate down
            return false;
        }

        $nextVersions = array();

        for ($i = 0; $i < count($availableVersions); ++$i) {
            if ($availableVersions[$i] == $current) {
                $nextVersions = array_reverse(array_slice($availableVersions, 0, $i + 1));
                break;
            }
        }

        $newVersion = null;
        foreach ($nextVersions as $nextVersion) {
            if ($newVersion) {
                $this->setCurrentVersion($nextVersion);

                if (!$full) {
                    break;
                }
            }

            $classPath = $available[$nextVersion];

            /** @var AbstractMigration $migration */
            $migration = new $classPath();
            $migration->down();

            $newVersion = $nextVersion;
        }

        if (!$newVersion) {
            return false;
        }

        return $newVersion;
    }

    /**
     * Migrate up
     *
     * @param bool $full
     * @return bool|mixed|null
     */
    public function migrateUp($full = false)
    {
        $available = $this->getAvailableMigrations();
        $availableVersions = array_keys($available);

        $nextVersions = array();

        $current = $this->getCurrentVersion();
        if (!$current) {
            if (!count($availableVersions)) {
                return false;
            }
            $nextVersions = $availableVersions;
        } else {
            for ($i = 0; $i < count($availableVersions); ++$i) {
                if ($availableVersions[$i] == $current) {
                    $nextVersions = array_slice($availableVersions, $i + 1);
                    break;
                }
            }
        }

        $newVersion = null;
        foreach ($nextVersions as $nextVersion) {
            $classPath = $available[$nextVersion];

            /** @var AbstractMigration $migration */
            $migration = new $classPath();
            $migration->up();

            $newVersion = $nextVersion;
            $this->setCurrentVersion($newVersion);

            if (!$full) {
                break;
            }
        }

        if (!$newVersion) {
            return false;
        }

        return $newVersion;
    }

    /**
     * Get all available migrations
     *
     * @return array
     */
    public function getAvailableMigrations()
    {
        $path = _PS_MODULE_DIR_ . $this->moduleName . '/migrations/';

        $versions = glob($path . '*.php');

        $result = array(
            'Version0' => '\\PShow\\Core\\Database\\Migrations\\Version0',
        );
        foreach ($versions as $version) {
            $class = ucfirst(pathinfo($version, PATHINFO_FILENAME));

            $namespace = null;
            $file = file($version);
            foreach ($file as $line) {
                if (preg_match('/^namespace ([^;]+);$/s', $line)) {
                    $namespace = trim(preg_replace('/^namespace ([^;]+);$/s', '$1', $line));
                    $namespace = '\\' . str_replace('\\', '\\', $namespace);
                }
            }

            if (!$namespace) {
                continue;
            }

            $result[$class] = $namespace . '\\' . $class;

            require_once $version;
        }

        return $result;
    }

    /**
     * @param $separator
     * @param $string
     * @return mixed
     */
    protected function getLastItemFromExplode($separator, $string)
    {
        $explode = explode($separator, $string);
        return end($explode);
    }

}