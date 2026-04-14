<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260412120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Prix produit en décimales (e-boutique)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE produit CHANGE prix prix NUMERIC(10, 2) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE produit CHANGE prix prix NUMERIC(10, 0) NOT NULL');
    }
}
