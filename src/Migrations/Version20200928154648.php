<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200928154648 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TEMPORARY TABLE __temp__suggestion AS SELECT id, summary, content, current_state, comments FROM suggestion');
        $this->addSql('DROP TABLE suggestion');
        $this->addSql('CREATE TABLE suggestion (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, summary VARCHAR(255) NOT NULL COLLATE BINARY, content CLOB NOT NULL COLLATE BINARY, current_state VARCHAR(255) NOT NULL COLLATE BINARY, comments CLOB NOT NULL COLLATE BINARY)');
        $this->addSql('INSERT INTO suggestion (id, summary, content, current_state, comments) SELECT id, summary, content, current_state, comments FROM __temp__suggestion');
        $this->addSql('DROP TABLE __temp__suggestion');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');
        $this->addSql('ALTER TABLE suggestion ADD COLUMN name VARCHAR(255) NOT NULL COLLATE BINARY');
    }
}
