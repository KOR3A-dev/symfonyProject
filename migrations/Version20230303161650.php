<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230303161650 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_detail RENAME COLUMN product_id TO customer_id');
        $this->addSql('ALTER TABLE order_detail ADD CONSTRAINT FK_ED896F469395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE order_detail ADD CONSTRAINT FK_ED896F468D9F6D38 FOREIGN KEY (order_id) REFERENCES "order" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_ED896F469395C3F3 ON order_detail (customer_id)');
        $this->addSql('CREATE INDEX IDX_ED896F468D9F6D38 ON order_detail (order_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE order_detail DROP CONSTRAINT FK_ED896F469395C3F3');
        $this->addSql('ALTER TABLE order_detail DROP CONSTRAINT FK_ED896F468D9F6D38');
        $this->addSql('DROP INDEX IDX_ED896F469395C3F3');
        $this->addSql('DROP INDEX IDX_ED896F468D9F6D38');
        $this->addSql('ALTER TABLE order_detail RENAME COLUMN customer_id TO product_id');
    }
}
