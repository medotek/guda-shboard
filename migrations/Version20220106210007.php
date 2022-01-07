<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220106210007 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE discord_embed_message (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, discord_grouped_messages_id INT NOT NULL, embeds LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', date DATETIME NOT NULL, message_id VARCHAR(255) NOT NULL, message VARCHAR(1000) DEFAULT NULL, channel_name VARCHAR(255) DEFAULT NULL, channel_id VARCHAR(255) DEFAULT NULL, INDEX IDX_59CA11B2A76ED395 (user_id), INDEX IDX_59CA11B2AB49DEE9 (discord_grouped_messages_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE discord_grouped_messages (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, INDEX IDX_A2A81D8FF675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, creation_date DATE DEFAULT NULL, roles LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE discord_embed_message ADD CONSTRAINT FK_59CA11B2A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE discord_embed_message ADD CONSTRAINT FK_59CA11B2AB49DEE9 FOREIGN KEY (discord_grouped_messages_id) REFERENCES discord_grouped_messages (id)');
        $this->addSql('ALTER TABLE discord_grouped_messages ADD CONSTRAINT FK_A2A81D8FF675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE discord_embed_message DROP FOREIGN KEY FK_59CA11B2AB49DEE9');
        $this->addSql('ALTER TABLE discord_embed_message DROP FOREIGN KEY FK_59CA11B2A76ED395');
        $this->addSql('ALTER TABLE discord_grouped_messages DROP FOREIGN KEY FK_A2A81D8FF675F31B');
        $this->addSql('DROP TABLE discord_embed_message');
        $this->addSql('DROP TABLE discord_grouped_messages');
        $this->addSql('DROP TABLE user');
    }
}
