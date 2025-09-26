<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250925163138 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add is_patched column to reports table and removed ispatched column from games table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE games DROP ispatched');
        $this->addSql('ALTER TABLE reports ADD is_patched BOOLEAN DEFAULT false NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE games ADD ispatched BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE reports DROP is_patched');
    }
}
