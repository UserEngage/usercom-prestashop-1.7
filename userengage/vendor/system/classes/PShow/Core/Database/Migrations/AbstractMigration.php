<?php

namespace PShow\Core\Database\Migrations;

abstract class AbstractMigration
{

    abstract public function up();

    abstract public function down();

}