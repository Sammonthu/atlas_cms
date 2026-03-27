<?php
// DÃƒÆ’Ã‚Â©clare le namespace des migrations Doctrine.
namespace DoctrineMigrations;

// Importe le schÃƒÆ’Ã‚Â©ma Doctrine.
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
// Importe la classe abstraite de migration.
use Doctrine\Migrations\AbstractMigration;

// DÃƒÆ’Ã‚Â©clare la migration initiale alignÃƒÆ’Ã‚Â©e sur le MCD demandÃƒÆ’Ã‚Â©.
final class Version20260326102000 extends AbstractMigration
{
    // Retourne une description de migration.
    public function getDescription(): string
    {
        // DÃƒÆ’Ã‚Â©crit le schÃƒÆ’Ã‚Â©ma crÃƒÆ’Ã‚Â©ÃƒÆ’Ã‚Â©.
        return 'CrÃƒÆ’Ã‚Â©e les tables User, Page, PageCategory, Article, ArticleCategory, Tag, Comment, Gallery, Image.';
    }

    // Applique la migration.
    public function up(Schema $schema): void
    {
        // VÃƒÆ’Ã‚Â©rifie la plateforme SQL.
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform,
            'Cette migration est prÃƒÆ’Ã‚Â©vue pour MySQL/MariaDB.'
        );

        // CrÃƒÆ’Ã‚Â©e la table des utilisateurs.
        $this->addSql('CREATE TABLE cms_user (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(160) NOT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, roles JSON NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_6FDBF946E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // CrÃƒÆ’Ã‚Â©e la table des catÃƒÆ’Ã‚Â©gories de page.
        $this->addSql('CREATE TABLE page_category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(150) NOT NULL, UNIQUE INDEX UNIQ_DA5AD6CC5E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // CrÃƒÆ’Ã‚Â©e la table des catÃƒÆ’Ã‚Â©gories dÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢article.
        $this->addSql('CREATE TABLE article_category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(150) NOT NULL, UNIQUE INDEX UNIQ_9A3A7F7A5E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // CrÃƒÆ’Ã‚Â©e la table des tags.
        $this->addSql('CREATE TABLE tag (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, UNIQUE INDEX UNIQ_389B7835E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // CrÃƒÆ’Ã‚Â©e la table des galeries.
        $this->addSql('CREATE TABLE gallery (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(190) NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // CrÃƒÆ’Ã‚Â©e la table des pages.
        $this->addSql('CREATE TABLE page (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, gallery_id INT DEFAULT NULL, title VARCHAR(190) NOT NULL, content LONGTEXT NOT NULL, slug VARCHAR(220) NOT NULL, meta_description VARCHAR(255) DEFAULT NULL, moderation_status VARCHAR(30) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_140AB620989D9B62 (slug), INDEX IDX_140AB62012469DE2 (category_id), INDEX IDX_140AB6204E7AF8F (gallery_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // CrÃƒÆ’Ã‚Â©e la table des articles.
        $this->addSql('CREATE TABLE article (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, author_id INT NOT NULL, title VARCHAR(190) NOT NULL, content LONGTEXT NOT NULL, published_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', slug VARCHAR(220) NOT NULL, meta_description VARCHAR(255) DEFAULT NULL, moderation_status VARCHAR(30) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_23A0E66989D9B62 (slug), INDEX IDX_23A0E6612469DE2 (category_id), INDEX IDX_23A0E66F675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // CrÃƒÆ’Ã‚Â©e la table de jointure article-tag.
        $this->addSql('CREATE TABLE article_tag (article_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_919694F67294869C (article_id), INDEX IDX_919694F6BAD26311 (tag_id), PRIMARY KEY(article_id, tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // CrÃƒÆ’Ã‚Â©e la table des commentaires.
        $this->addSql('CREATE TABLE cms_comment (id INT AUTO_INCREMENT NOT NULL, article_id INT NOT NULL, author_id INT NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', status VARCHAR(30) NOT NULL, INDEX IDX_2ED90B587294869C (article_id), INDEX IDX_2ED90B58F675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // CrÃƒÆ’Ã‚Â©e la table des images.
        $this->addSql('CREATE TABLE image (id INT AUTO_INCREMENT NOT NULL, gallery_id INT NOT NULL, file_path VARCHAR(255) NOT NULL, caption VARCHAR(255) DEFAULT NULL, uploaded_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_C53D045F4E7AF8F (gallery_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Ajoute les clÃƒÆ’Ã‚Â©s ÃƒÆ’Ã‚Â©trangÃƒÆ’Ã‚Â¨res des pages.
        $this->addSql('ALTER TABLE page ADD CONSTRAINT FK_140AB62012469DE2 FOREIGN KEY (category_id) REFERENCES page_category (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE page ADD CONSTRAINT FK_140AB6204E7AF8F FOREIGN KEY (gallery_id) REFERENCES gallery (id) ON DELETE SET NULL');

        // Ajoute les clÃƒÆ’Ã‚Â©s ÃƒÆ’Ã‚Â©trangÃƒÆ’Ã‚Â¨res des articles.
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E6612469DE2 FOREIGN KEY (category_id) REFERENCES article_category (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E66F675F31B FOREIGN KEY (author_id) REFERENCES cms_user (id) ON DELETE RESTRICT');

        // Ajoute les clÃƒÆ’Ã‚Â©s ÃƒÆ’Ã‚Â©trangÃƒÆ’Ã‚Â¨res de la jointure article-tag.
        $this->addSql('ALTER TABLE article_tag ADD CONSTRAINT FK_919694F67294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE article_tag ADD CONSTRAINT FK_919694F6BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');

        // Ajoute les clÃƒÆ’Ã‚Â©s ÃƒÆ’Ã‚Â©trangÃƒÆ’Ã‚Â¨res des commentaires.
        $this->addSql('ALTER TABLE cms_comment ADD CONSTRAINT FK_2ED90B587294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cms_comment ADD CONSTRAINT FK_2ED90B58F675F31B FOREIGN KEY (author_id) REFERENCES cms_user (id) ON DELETE CASCADE');

        // Ajoute la clÃƒÆ’Ã‚Â© ÃƒÆ’Ã‚Â©trangÃƒÆ’Ã‚Â¨re des images.
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045F4E7AF8F FOREIGN KEY (gallery_id) REFERENCES gallery (id) ON DELETE CASCADE');
    }

    // Annule la migration.
    public function down(Schema $schema): void
    {
        // VÃƒÆ’Ã‚Â©rifie la plateforme SQL.
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform,
            'Cette migration est prÃƒÆ’Ã‚Â©vue pour MySQL/MariaDB.'
        );

        // Supprime les tables dans l'ordre inverse des dÃƒÆ’Ã‚Â©pendances.
        $this->addSql('DROP TABLE image');
        $this->addSql('DROP TABLE cms_comment');
        $this->addSql('DROP TABLE article_tag');
        $this->addSql('DROP TABLE article');
        $this->addSql('DROP TABLE page');
        $this->addSql('DROP TABLE gallery');
        $this->addSql('DROP TABLE tag');
        $this->addSql('DROP TABLE article_category');
        $this->addSql('DROP TABLE page_category');
        $this->addSql('DROP TABLE cms_user');
    }
}

