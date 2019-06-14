<?php

namespace PShow\Core\Database\Migrations;

abstract class AbstractMigration
{

    abstract public function up();

    abstract public function down();

    /**
     * @param $sql
     * @return bool
     */
    protected function execute($sql)
    {
        try {
            return \Db::getInstance()->execute($sql);
        } catch (\Exception $e) {
            $this->logException($e);
        }
        return false;
    }

    /**
     * @param $sql
     * @return array|false|\mysqli_result|\PDOStatement|resource|null
     */
    protected function executeS($sql)
    {
        try {
            return \Db::getInstance()->executeS($sql);
        } catch (\Exception $e) {
            $this->logException($e);
        }
        return false;
    }

    /**
     * @param \Exception $exception
     */
    protected function logException($exception)
    {
        $message = sprintf(
            '%s at line %d in file %s',
            $exception->getMessage(),
            $exception->getLine(),
            ltrim(str_replace(array(_PS_ROOT_DIR_, '\\'), array('', '/'), $exception->getFile()), '/')
        );

        $filename = getModulePath(__FILE__) . '/' . date('Ymd') . '_migration.log';

        if (class_exists('\FileLogger')) {
            $logger = new \FileLogger();
            $logger->setFilename($filename);
            $logger->logError($message);
            return;
        }

        file_put_contents($filename, $message, FILE_APPEND);
    }

}