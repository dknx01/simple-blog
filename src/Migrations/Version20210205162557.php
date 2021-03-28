<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210205162557 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add table Memo';
    }

    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TABLE memo (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title TEXT NOT NULL, content TEXT DEFAULT NULL, date DATETIME NOT NULL, location TEXT NOT NULL, type TEXT NOT NULL, file_name TEXT NOT NULL, extension TEXT NOT NULL, on_disk BOOLEAN NOT NULL, uuid TEXT NOT NULL --(DC2Type:uuid))');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP TABLE memo');
    }
}
