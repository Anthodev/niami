<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250614134256 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add release date to games table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE games ADD releaseDate VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE games ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE games ALTER created_at SET DEFAULT 'NOW()'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE games ALTER updated_at SET DEFAULT 'NOW()'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE publishers ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE publishers ALTER created_at SET DEFAULT 'NOW()'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE publishers ALTER updated_at SET DEFAULT 'NOW()'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reports ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reports ALTER created_at SET DEFAULT 'NOW()'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reports ALTER updated_at SET DEFAULT 'NOW()'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE roles ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE roles ALTER created_at SET DEFAULT 'NOW()'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE roles ALTER updated_at SET DEFAULT 'NOW()'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users ALTER created_at SET DEFAULT 'NOW()'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users ALTER updated_at SET DEFAULT 'NOW()'
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE users ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users ALTER created_at SET DEFAULT '2025-06-11 20:54:14.619763'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users ALTER updated_at SET DEFAULT '2025-06-11 20:54:14.619763'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE games DROP releaseDate
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE games ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE games ALTER created_at SET DEFAULT '2025-06-11 20:54:14.619763'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE games ALTER updated_at SET DEFAULT '2025-06-11 20:54:14.619763'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE roles ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE roles ALTER created_at SET DEFAULT '2025-06-11 20:54:14.619763'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE roles ALTER updated_at SET DEFAULT '2025-06-11 20:54:14.619763'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE publishers ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE publishers ALTER created_at SET DEFAULT '2025-06-11 20:54:14.619763'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE publishers ALTER updated_at SET DEFAULT '2025-06-11 20:54:14.619763'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reports ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reports ALTER created_at SET DEFAULT '2025-06-11 20:54:14.619763'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reports ALTER updated_at SET DEFAULT '2025-06-11 20:54:14.619763'
        SQL);
    }
}
