<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200812071446 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add a basic generic user';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql(
            'INSERT INTO user (password, username, roles) VALUES(:password, :username, :roles)',
            [
                'password' => '$argon2id$v=19$m=65536,t=4,p=1$T7P1aykcbawu+2n2DEA/pg$TkyxkzeCcwiDraez0tAphuUk5f+iKgtD4PMx45liOKM', // parteigenosse
                'username' => 'genosse',
                'roles' => \json_encode(['ROLE_USER'])
            ]
        );
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DELETE FROM user WHERE username = :username', ['username' => 'genosse']);
    }
}
