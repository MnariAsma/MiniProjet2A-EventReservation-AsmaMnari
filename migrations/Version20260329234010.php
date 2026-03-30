<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260329234010 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C849553E5F2F7B FOREIGN KEY (event_id_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE webauthn_credential ADD CONSTRAINT FK_850123F9A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C849553E5F2F7B');
        $this->addSql('ALTER TABLE webauthn_credential DROP FOREIGN KEY FK_850123F9A76ED395');
    }
}
