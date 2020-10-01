<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201001133425 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql(
            'INSERT INTO user (username, roles, password) VALUES (:username, :roles, :password)',
            [
                'username' => 'kader',
                'roles' => \json_encode(['ROLE_EDITOR']),
                'password' => '$argon2id$v=19$m=65536,t=4,p=1$g8e9x3KFxeR4vXTM21WjPQ$0d0yRP7sGKM4siecar9HVY12ltkA/W/guwH8nXuvUPE', //parteikader
            ]
        );
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DELETE FROM user where  username = :username', ['username' => 'kader']);
    }
}
