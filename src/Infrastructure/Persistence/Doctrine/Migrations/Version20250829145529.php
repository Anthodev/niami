<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250829145529 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add developers table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE developers (name VARCHAR(255) NOT NULL, website VARCHAR(255) DEFAULT NULL, apiId INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT \'NOW()\' NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT \'NOW()\', id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX developers_name_unique_constraint ON developers (name)');

        $this->addSql('ALTER TABLE games ADD developer_id UUID DEFAULT NULL');
        $this->addSql('CREATE INDEX games_developer_id ON games (developer_id)');

        $this->addSql('ALTER TABLE games ADD CONSTRAINT FK_FF232B3164DD9267 FOREIGN KEY (developer_id) REFERENCES developers (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE games DROP CONSTRAINT FK_FF232B3164DD9267');
        $this->addSql('DROP INDEX games_developer_id');
        $this->addSql('ALTER TABLE games DROP developer_id');
        $this->addSql('DROP TABLE developers');
    }
}
