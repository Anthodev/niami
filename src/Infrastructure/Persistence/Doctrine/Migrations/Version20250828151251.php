<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250828151251 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add apiId to publishers table and rename is_native_resolution_improved_docked to is_native_resolution_docked';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE publishers ADD apiId INT NOT NULL');
        $this->addSql('ALTER TABLE reports RENAME COLUMN is_native_resolution_improved_docked TO is_native_resolution_docked');
        $this->addSql('ALTER TABLE reports ALTER is_native_resolution_docked SET DEFAULT false');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE publishers DROP apiId');
        $this->addSql('ALTER TABLE reports RENAME COLUMN is_native_resolution_docked TO is_native_resolution_improved_docked');
        $this->addSql('ALTER TABLE reports ALTER is_native_resolution_improved_docked SET DEFAULT false');
    }
}
