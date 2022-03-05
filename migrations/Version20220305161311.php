<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220305161311 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE discord_credentials (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, access_token VARCHAR(255) NOT NULL, refresh_token VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_6C133F4FA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE discord_embed_message (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, discord_grouped_messages_id INT DEFAULT NULL, embeds LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', date DATETIME NOT NULL, message_id VARCHAR(255) NOT NULL, message VARCHAR(1000) DEFAULT NULL, channel_name VARCHAR(255) DEFAULT NULL, channel_id VARCHAR(255) DEFAULT NULL, INDEX IDX_59CA11B2A76ED395 (user_id), INDEX IDX_59CA11B2AB49DEE9 (discord_grouped_messages_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE discord_grouped_messages (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, INDEX IDX_A2A81D8FF675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE discord_webhook (id INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, name VARCHAR(255) NOT NULL, avatar_id VARCHAR(500) DEFAULT NULL, token VARCHAR(500) NOT NULL, webhook_id VARCHAR(100) NOT NULL, channel_id VARCHAR(100) NOT NULL, guild_id VARCHAR(100) NOT NULL, INDEX IDX_CEE9330D7E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE discord_webhook_user (discord_webhook_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_39F2244343246941 (discord_webhook_id), INDEX IDX_39F22443A76ED395 (user_id), PRIMARY KEY(discord_webhook_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE hoyolab_post (id INT AUTO_INCREMENT NOT NULL, discord_message_id INT DEFAULT NULL, hoyolab_post_user_id INT DEFAULT NULL, post_id VARCHAR(15) NOT NULL, subject VARCHAR(255) NOT NULL, creation_date DATETIME NOT NULL, post_creation_date DATETIME NOT NULL, last_reply_time DATETIME DEFAULT NULL, webhook_id BIGINT DEFAULT NULL, image VARCHAR(500) DEFAULT NULL, UNIQUE INDEX UNIQ_3115E93F4CC5DC63 (discord_message_id), INDEX IDX_3115E93FD6A992E3 (hoyolab_post_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE hoyolab_post_discord_notification (id INT AUTO_INCREMENT NOT NULL, hoyolab_post_id INT DEFAULT NULL, message_id VARCHAR(15) DEFAULT NULL, process_date DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_9ED79E48AB17CBE2 (hoyolab_post_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE hoyolab_post_stats (id INT AUTO_INCREMENT NOT NULL, hoyolab_post_id INT DEFAULT NULL, view INT NOT NULL, reply INT NOT NULL, likes INT NOT NULL, bookmark INT NOT NULL, share INT NOT NULL, UNIQUE INDEX UNIQ_4FF9B3EAAB17CBE2 (hoyolab_post_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE hoyolab_post_user (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, uid VARCHAR(15) NOT NULL, nickname VARCHAR(100) NOT NULL, avatar_url VARCHAR(500) DEFAULT NULL, pendant VARCHAR(500) DEFAULT NULL, webhook_url VARCHAR(500) DEFAULT NULL, UNIQUE INDEX UNIQ_8400E19E539B0606 (uid), INDEX IDX_8400E19EA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE team (id INT AUTO_INCREMENT NOT NULL, creator_id INT NOT NULL, name VARCHAR(60) NOT NULL, creation_date DATETIME NOT NULL, INDEX IDX_C4E0A61F61220EA6 (creator_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE team_user (team_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_5C722232296CD8AE (team_id), INDEX IDX_5C722232A76ED395 (user_id), PRIMARY KEY(team_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, creation_date DATETIME DEFAULT NULL, roles JSON DEFAULT NULL, last_connection_date DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), UNIQUE INDEX UNIQ_8D93D6495E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE discord_credentials ADD CONSTRAINT FK_6C133F4FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE discord_embed_message ADD CONSTRAINT FK_59CA11B2A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE discord_embed_message ADD CONSTRAINT FK_59CA11B2AB49DEE9 FOREIGN KEY (discord_grouped_messages_id) REFERENCES discord_grouped_messages (id)');
        $this->addSql('ALTER TABLE discord_grouped_messages ADD CONSTRAINT FK_A2A81D8FF675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE discord_webhook ADD CONSTRAINT FK_CEE9330D7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE discord_webhook_user ADD CONSTRAINT FK_39F2244343246941 FOREIGN KEY (discord_webhook_id) REFERENCES discord_webhook (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE discord_webhook_user ADD CONSTRAINT FK_39F22443A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE hoyolab_post ADD CONSTRAINT FK_3115E93F4CC5DC63 FOREIGN KEY (discord_message_id) REFERENCES discord_embed_message (id)');
        $this->addSql('ALTER TABLE hoyolab_post ADD CONSTRAINT FK_3115E93FD6A992E3 FOREIGN KEY (hoyolab_post_user_id) REFERENCES hoyolab_post_user (id)');
        $this->addSql('ALTER TABLE hoyolab_post_discord_notification ADD CONSTRAINT FK_9ED79E48AB17CBE2 FOREIGN KEY (hoyolab_post_id) REFERENCES hoyolab_post (id)');
        $this->addSql('ALTER TABLE hoyolab_post_stats ADD CONSTRAINT FK_4FF9B3EAAB17CBE2 FOREIGN KEY (hoyolab_post_id) REFERENCES hoyolab_post (id)');
        $this->addSql('ALTER TABLE hoyolab_post_user ADD CONSTRAINT FK_8400E19EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE team ADD CONSTRAINT FK_C4E0A61F61220EA6 FOREIGN KEY (creator_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE team_user ADD CONSTRAINT FK_5C722232296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE team_user ADD CONSTRAINT FK_5C722232A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE hoyolab_post DROP FOREIGN KEY FK_3115E93F4CC5DC63');
        $this->addSql('ALTER TABLE discord_embed_message DROP FOREIGN KEY FK_59CA11B2AB49DEE9');
        $this->addSql('ALTER TABLE discord_webhook_user DROP FOREIGN KEY FK_39F2244343246941');
        $this->addSql('ALTER TABLE hoyolab_post_discord_notification DROP FOREIGN KEY FK_9ED79E48AB17CBE2');
        $this->addSql('ALTER TABLE hoyolab_post_stats DROP FOREIGN KEY FK_4FF9B3EAAB17CBE2');
        $this->addSql('ALTER TABLE hoyolab_post DROP FOREIGN KEY FK_3115E93FD6A992E3');
        $this->addSql('ALTER TABLE team_user DROP FOREIGN KEY FK_5C722232296CD8AE');
        $this->addSql('ALTER TABLE discord_credentials DROP FOREIGN KEY FK_6C133F4FA76ED395');
        $this->addSql('ALTER TABLE discord_embed_message DROP FOREIGN KEY FK_59CA11B2A76ED395');
        $this->addSql('ALTER TABLE discord_grouped_messages DROP FOREIGN KEY FK_A2A81D8FF675F31B');
        $this->addSql('ALTER TABLE discord_webhook DROP FOREIGN KEY FK_CEE9330D7E3C61F9');
        $this->addSql('ALTER TABLE discord_webhook_user DROP FOREIGN KEY FK_39F22443A76ED395');
        $this->addSql('ALTER TABLE hoyolab_post_user DROP FOREIGN KEY FK_8400E19EA76ED395');
        $this->addSql('ALTER TABLE team DROP FOREIGN KEY FK_C4E0A61F61220EA6');
        $this->addSql('ALTER TABLE team_user DROP FOREIGN KEY FK_5C722232A76ED395');
        $this->addSql('DROP TABLE discord_credentials');
        $this->addSql('DROP TABLE discord_embed_message');
        $this->addSql('DROP TABLE discord_grouped_messages');
        $this->addSql('DROP TABLE discord_webhook');
        $this->addSql('DROP TABLE discord_webhook_user');
        $this->addSql('DROP TABLE hoyolab_post');
        $this->addSql('DROP TABLE hoyolab_post_discord_notification');
        $this->addSql('DROP TABLE hoyolab_post_stats');
        $this->addSql('DROP TABLE hoyolab_post_user');
        $this->addSql('DROP TABLE team');
        $this->addSql('DROP TABLE team_user');
        $this->addSql('DROP TABLE user');
    }
}
