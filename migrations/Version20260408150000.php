<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

// Ajoute l'arborescence parent/enfant sur les pages.
final class Version20260408150000 extends AbstractMigration
{
    // Retourne une description lisible de la migration.
    public function getDescription(): string
    {
        // Decrit la colonne parent_id et sa cle etrangere.
        return 'Ajoute parent_id sur la table page pour organiser les pages en arborescence.';
    }

    // Applique la migration.
    public function up(Schema $schema): void
    {
        // Verifie la plateforme SQL.
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform,
            'Cette migration est prevue pour MySQL/MariaDB.'
        );

        // Ajoute la colonne parent_id et son index.
        $this->addSql('ALTER TABLE page ADD parent_id INT DEFAULT NULL, ADD INDEX IDX_140AB620727ACA70 (parent_id)');
        // Ajoute la cle etrangere auto-referencee.
        $this->addSql('ALTER TABLE page ADD CONSTRAINT FK_140AB620727ACA70 FOREIGN KEY (parent_id) REFERENCES page (id) ON DELETE SET NULL');
    }

    // Annule la migration.
    public function down(Schema $schema): void
    {
        // Verifie la plateforme SQL.
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform,
            'Cette migration est prevue pour MySQL/MariaDB.'
        );

        // Supprime la cle etrangere parent.
        $this->addSql('ALTER TABLE page DROP FOREIGN KEY FK_140AB620727ACA70');
        // Supprime la colonne et l'index parent.
        $this->addSql('ALTER TABLE page DROP parent_id');
    }
}