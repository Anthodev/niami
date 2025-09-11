<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250911173049 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add report_comments table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE report_comments (comment TEXT NOT NULL, ip TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT \'NOW()\' NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT \'NOW()\', id UUID NOT NULL, report_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX report_comment_report_id ON report_comments (report_id)');
        $this->addSql('ALTER TABLE report_comments ADD CONSTRAINT FK_5FFE0F574BD2A4C0 FOREIGN KEY (report_id) REFERENCES reports (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE report_comments DROP CONSTRAINT FK_5FFE0F574BD2A4C0');
        $this->addSql('DROP TABLE report_comments');
    }
}
