<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250611205411 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add games, publisher and reports tables';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE games (name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, description TEXT NOT NULL, imageCover VARCHAR(255) NOT NULL, isPatched BOOLEAN DEFAULT false NOT NULL, isActive BOOLEAN DEFAULT true NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT 'NOW()' NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT 'NOW()', id UUID NOT NULL, publisher_id UUID DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX games_publisher_id ON games (publisher_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX games_name_unique_constraint ON games (name)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX games_slug_unique_constraint ON games (slug)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE publishers (name VARCHAR(255) NOT NULL, website VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT 'NOW()' NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT 'NOW()', id UUID NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX publishers_name_unique_constraint ON publishers (name)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE reports (is_60fps_portable BOOLEAN DEFAULT false NOT NULL, is_switch_2_edition BOOLEAN DEFAULT false NOT NULL, has_stable_framerate_portable BOOLEAN DEFAULT false NOT NULL, is_60fps_docked BOOLEAN DEFAULT false NOT NULL, has_stable_framerate_docked BOOLEAN DEFAULT false NOT NULL, has_resolution_improved_portable BOOLEAN DEFAULT false NOT NULL, is_native_resolution_portable BOOLEAN DEFAULT false NOT NULL, has_resolution_improved_docked BOOLEAN DEFAULT false NOT NULL, is_native_resolution_improved_docked BOOLEAN DEFAULT false NOT NULL, has_improved_loading_times BOOLEAN DEFAULT false NOT NULL, upvote_count INT DEFAULT 0 NOT NULL, is_visible BOOLEAN DEFAULT true NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT 'NOW()' NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT 'NOW()', id UUID NOT NULL, game_id UUID DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX reports_game_id ON reports (game_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX reports_upvote_count ON reports (upvote_count)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE games ADD CONSTRAINT FK_FF232B3140C86FCE FOREIGN KEY (publisher_id) REFERENCES publishers (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reports ADD CONSTRAINT FK_F11FA745E48FD905 FOREIGN KEY (game_id) REFERENCES games (id) NOT DEFERRABLE INITIALLY IMMEDIATE
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
            ALTER TABLE games DROP CONSTRAINT FK_FF232B3140C86FCE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reports DROP CONSTRAINT FK_F11FA745E48FD905
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE games
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE publishers
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE reports
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE roles ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE roles ALTER created_at SET DEFAULT '2025-06-11 20:54:09.397435'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE roles ALTER updated_at SET DEFAULT '2025-06-11 20:54:09.397435'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users ALTER created_at SET DEFAULT '2025-06-11 20:54:09.397435'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users ALTER updated_at SET DEFAULT '2025-06-11 20:54:09.397435'
        SQL);
    }
}
