<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260326135822 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE article CHANGE published_at published_at DATETIME DEFAULT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE article_tag RENAME INDEX idx_919694f67294869c TO IDX_919694F97294869C');
        $this->addSql('ALTER TABLE article_tag RENAME INDEX idx_919694f6bad26311 TO IDX_919694F9BAD26311');
        $this->addSql('ALTER TABLE article_category RENAME INDEX uniq_9a3a7f7a5e237e06 TO UNIQ_53A4EDAA5E237E06');
        $this->addSql('ALTER TABLE cms_comment CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE cms_comment RENAME INDEX idx_2ed90b587294869c TO IDX_CA985D7D7294869C');
        $this->addSql('ALTER TABLE cms_comment RENAME INDEX idx_2ed90b58f675f31b TO IDX_CA985D7DF675F31B');
        $this->addSql('ALTER TABLE cms_user CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE cms_user RENAME INDEX uniq_6fdbf946e7927c74 TO UNIQ_4A057B34E7927C74');
        $this->addSql('ALTER TABLE gallery CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE image CHANGE uploaded_at uploaded_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE page CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE page_category RENAME INDEX uniq_da5ad6cc5e237e06 TO UNIQ_86D31EE15E237E06');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE article CHANGE published_at published_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated_at updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE article_category RENAME INDEX uniq_53a4edaa5e237e06 TO UNIQ_9A3A7F7A5E237E06');
        $this->addSql('ALTER TABLE article_tag RENAME INDEX idx_919694f9bad26311 TO IDX_919694F6BAD26311');
        $this->addSql('ALTER TABLE article_tag RENAME INDEX idx_919694f97294869c TO IDX_919694F67294869C');
        $this->addSql('ALTER TABLE cms_comment CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE cms_comment RENAME INDEX idx_ca985d7d7294869c TO IDX_2ED90B587294869C');
        $this->addSql('ALTER TABLE cms_comment RENAME INDEX idx_ca985d7df675f31b TO IDX_2ED90B58F675F31B');
        $this->addSql('ALTER TABLE cms_user CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated_at updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE cms_user RENAME INDEX uniq_4a057b34e7927c74 TO UNIQ_6FDBF946E7927C74');
        $this->addSql('ALTER TABLE gallery CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated_at updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE image CHANGE uploaded_at uploaded_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE page CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated_at updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE page_category RENAME INDEX uniq_86d31ee15e237e06 TO UNIQ_DA5AD6CC5E237E06');
    }
}
