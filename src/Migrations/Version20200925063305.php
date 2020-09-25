<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200925063305 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql(<<<'SQL'
CREATE TABLE rememberme_token (
    series char(88) UNIQUE PRIMARY KEY NOT NULL,
    value varchar(88) NOT NULL ,
    lastUsed datetime NOT NULL ,
    class varchar(100) NOT NULL ,
    username varchar(200) NOT NULL 
    )
SQL

        );

    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP TABLE rememberme_token');

    }
}
