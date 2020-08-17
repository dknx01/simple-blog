<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200812134409 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql(
            'INSERT INTO user (password, username, roles) VALUES(:password, :username, :roles)',
            [
                'password' => '$argon2id$v=19$m=65536,t=4,p=1$KgZ7WVBvF3kNdN9DOpYb1g$K+WXfYj5MDGjsp42K/g2+rNvNaKrahcTNuxQVLj7W9I', // parteiadmin
                'username' => 'admin',
                'roles' => \json_encode(['ROLE_ADMIN'])
            ]
        );

    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DELETE FROM user WHERE username = :username', ['username' => 'admin']);
    }
}
