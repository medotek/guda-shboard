<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220217201512 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE hoyolab_post (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, discord_message_id INT DEFAULT NULL, post_id VARCHAR(15) NOT NULL, subject VARCHAR(255) NOT NULL, creation_date DATETIME NOT NULL, post_creation_date DATETIME NOT NULL, last_reply_time DATETIME DEFAULT NULL, INDEX IDX_3115E93FA76ED395 (user_id), UNIQUE INDEX UNIQ_3115E93F4CC5DC63 (discord_message_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE hoyolab_post_stats (id INT AUTO_INCREMENT NOT NULL, hoyolab_post_id INT DEFAULT NULL, view INT NOT NULL, reply INT NOT NULL, likes INT NOT NULL, bookmark INT NOT NULL, share INT NOT NULL, UNIQUE INDEX UNIQ_4FF9B3EAAB17CBE2 (hoyolab_post_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE hoyolab_post ADD CONSTRAINT FK_3115E93FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE hoyolab_post ADD CONSTRAINT FK_3115E93F4CC5DC63 FOREIGN KEY (discord_message_id) REFERENCES discord_embed_message (id)');
        $this->addSql('ALTER TABLE hoyolab_post_stats ADD CONSTRAINT FK_4FF9B3EAAB17CBE2 FOREIGN KEY (hoyolab_post_id) REFERENCES hoyolab_post (id)');
        $this->addSql('ALTER TABLE discord_embed_message CHANGE discord_grouped_messages_id discord_grouped_messages_id INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE hoyolab_post_stats DROP FOREIGN KEY FK_4FF9B3EAAB17CBE2');
        $this->addSql('DROP TABLE hoyolab_post');
        $this->addSql('DROP TABLE hoyolab_post_stats');
        $this->addSql('ALTER TABLE discord_embed_message CHANGE discord_grouped_messages_id discord_grouped_messages_id INT NOT NULL');
    }
}
