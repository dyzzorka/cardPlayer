<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221009054231 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE card (id INT AUTO_INCREMENT NOT NULL, value INT NOT NULL, family VARCHAR(255) NOT NULL, image VARCHAR(500) NOT NULL, status TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE card_game_mod (card_id INT NOT NULL, game_mod_id INT NOT NULL, INDEX IDX_B59F8E0E4ACC9A20 (card_id), INDEX IDX_B59F8E0E4EBB8C05 (game_mod_id), PRIMARY KEY(card_id, game_mod_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE game_mod (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, player_limit INT NOT NULL, status TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE party (id INT AUTO_INCREMENT NOT NULL, gamemod_id INT NOT NULL, token VARCHAR(500) NOT NULL, run TINYINT(1) NOT NULL, status TINYINT(1) NOT NULL, INDEX IDX_89954EE08B85B4DE (gamemod_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE party_player (party_id INT NOT NULL, player_id INT NOT NULL, INDEX IDX_DE6F013C213C1059 (party_id), INDEX IDX_DE6F013C99E6F5DF (player_id), PRIMARY KEY(party_id, player_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE player (id INT AUTO_INCREMENT NOT NULL, pseudo VARCHAR(255) NOT NULL, password VARCHAR(510) NOT NULL, status TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rank (id INT AUTO_INCREMENT NOT NULL, player_id INT NOT NULL, gamemod_id INT NOT NULL, mmr INT DEFAULT NULL, status TINYINT(1) NOT NULL, INDEX IDX_8879E8E599E6F5DF (player_id), INDEX IDX_8879E8E58B85B4DE (gamemod_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE card_game_mod ADD CONSTRAINT FK_B59F8E0E4ACC9A20 FOREIGN KEY (card_id) REFERENCES card (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE card_game_mod ADD CONSTRAINT FK_B59F8E0E4EBB8C05 FOREIGN KEY (game_mod_id) REFERENCES game_mod (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE party ADD CONSTRAINT FK_89954EE08B85B4DE FOREIGN KEY (gamemod_id) REFERENCES game_mod (id)');
        $this->addSql('ALTER TABLE party_player ADD CONSTRAINT FK_DE6F013C213C1059 FOREIGN KEY (party_id) REFERENCES party (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE party_player ADD CONSTRAINT FK_DE6F013C99E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE rank ADD CONSTRAINT FK_8879E8E599E6F5DF FOREIGN KEY (player_id) REFERENCES player (id)');
        $this->addSql('ALTER TABLE rank ADD CONSTRAINT FK_8879E8E58B85B4DE FOREIGN KEY (gamemod_id) REFERENCES game_mod (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE card_game_mod DROP FOREIGN KEY FK_B59F8E0E4ACC9A20');
        $this->addSql('ALTER TABLE card_game_mod DROP FOREIGN KEY FK_B59F8E0E4EBB8C05');
        $this->addSql('ALTER TABLE party DROP FOREIGN KEY FK_89954EE08B85B4DE');
        $this->addSql('ALTER TABLE party_player DROP FOREIGN KEY FK_DE6F013C213C1059');
        $this->addSql('ALTER TABLE party_player DROP FOREIGN KEY FK_DE6F013C99E6F5DF');
        $this->addSql('ALTER TABLE rank DROP FOREIGN KEY FK_8879E8E599E6F5DF');
        $this->addSql('ALTER TABLE rank DROP FOREIGN KEY FK_8879E8E58B85B4DE');
        $this->addSql('DROP TABLE card');
        $this->addSql('DROP TABLE card_game_mod');
        $this->addSql('DROP TABLE game_mod');
        $this->addSql('DROP TABLE party');
        $this->addSql('DROP TABLE party_player');
        $this->addSql('DROP TABLE player');
        $this->addSql('DROP TABLE rank');
        $this->addSql('DROP TABLE user');
    }
}
