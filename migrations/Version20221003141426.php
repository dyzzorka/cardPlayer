<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221003141426 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `rank` (id INT AUTO_INCREMENT NOT NULL, player_id INT NOT NULL, gamemod_id INT NOT NULL, mmr INT DEFAULT NULL, status TINYINT(1) NOT NULL, INDEX IDX_8879E8E599E6F5DF (player_id), INDEX IDX_8879E8E58B85B4DE (gamemod_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `rank` ADD CONSTRAINT FK_8879E8E599E6F5DF FOREIGN KEY (player_id) REFERENCES player (id)');
        $this->addSql('ALTER TABLE `rank` ADD CONSTRAINT FK_8879E8E58B85B4DE FOREIGN KEY (gamemod_id) REFERENCES game_mod (id)');
        $this->addSql('ALTER TABLE card_game_mod ADD CONSTRAINT FK_B59F8E0E4ACC9A20 FOREIGN KEY (card_id) REFERENCES card (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE card_game_mod ADD CONSTRAINT FK_B59F8E0E4EBB8C05 FOREIGN KEY (game_mod_id) REFERENCES game_mod (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE party ADD CONSTRAINT FK_89954EE08B85B4DE FOREIGN KEY (gamemod_id) REFERENCES game_mod (id)');
        $this->addSql('ALTER TABLE party_player ADD CONSTRAINT FK_DE6F013C213C1059 FOREIGN KEY (party_id) REFERENCES party (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE party_player ADD CONSTRAINT FK_DE6F013C99E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `rank` DROP FOREIGN KEY FK_8879E8E599E6F5DF');
        $this->addSql('ALTER TABLE `rank` DROP FOREIGN KEY FK_8879E8E58B85B4DE');
        $this->addSql('DROP TABLE `rank`');
        $this->addSql('ALTER TABLE card_game_mod DROP FOREIGN KEY FK_B59F8E0E4ACC9A20');
        $this->addSql('ALTER TABLE card_game_mod DROP FOREIGN KEY FK_B59F8E0E4EBB8C05');
        $this->addSql('ALTER TABLE party_player DROP FOREIGN KEY FK_DE6F013C213C1059');
        $this->addSql('ALTER TABLE party_player DROP FOREIGN KEY FK_DE6F013C99E6F5DF');
        $this->addSql('ALTER TABLE party DROP FOREIGN KEY FK_89954EE08B85B4DE');
    }
}
