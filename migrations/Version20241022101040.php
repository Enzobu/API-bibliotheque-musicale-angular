<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241022101040 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE artist_style (artist_id INT NOT NULL, style_id INT NOT NULL, INDEX IDX_53B18839B7970CF8 (artist_id), INDEX IDX_53B18839BACD6074 (style_id), PRIMARY KEY(artist_id, style_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE artist_style ADD CONSTRAINT FK_53B18839B7970CF8 FOREIGN KEY (artist_id) REFERENCES artist (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE artist_style ADD CONSTRAINT FK_53B18839BACD6074 FOREIGN KEY (style_id) REFERENCES style (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE album CHANGE title title VARCHAR(255) NOT NULL, CHANGE release_date release_date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE artist CHANGE name name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE song CHANGE title title VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE style DROP FOREIGN KEY FK_33BDB86AB7970CF8');
        $this->addSql('DROP INDEX IDX_33BDB86AB7970CF8 ON style');
        $this->addSql('ALTER TABLE style DROP artist_id, CHANGE name name VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE artist_style DROP FOREIGN KEY FK_53B18839B7970CF8');
        $this->addSql('ALTER TABLE artist_style DROP FOREIGN KEY FK_53B18839BACD6074');
        $this->addSql('DROP TABLE artist_style');
        $this->addSql('ALTER TABLE album CHANGE title title VARCHAR(50) NOT NULL, CHANGE release_date release_date DATE NOT NULL');
        $this->addSql('ALTER TABLE artist CHANGE name name VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE song CHANGE title title VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE style ADD artist_id INT DEFAULT NULL, CHANGE name name VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE style ADD CONSTRAINT FK_33BDB86AB7970CF8 FOREIGN KEY (artist_id) REFERENCES artist (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_33BDB86AB7970CF8 ON style (artist_id)');
    }
}
