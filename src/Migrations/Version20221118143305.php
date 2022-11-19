<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221118143305 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user_answer DROP FOREIGN KEY FK_BF8F5118A7C41D6F');
        $this->addSql('CREATE TABLE option_table (id INT AUTO_INCREMENT NOT NULL, question_id INT NOT NULL, body LONGTEXT NOT NULL, name VARCHAR(255) NOT NULL, is_correct TINYINT(1) NOT NULL, slug VARCHAR(255) NOT NULL COLLATE `utf8_general_ci`, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_CFC2322E989D9B62 (slug), INDEX IDX_CFC2322E1E27F6BF (question_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE option_table ADD CONSTRAINT FK_CFC2322E1E27F6BF FOREIGN KEY (question_id) REFERENCES question (id)');
        $this->addSql('DROP TABLE `option`');
        $this->addSql('ALTER TABLE user_answer DROP FOREIGN KEY FK_BF8F5118A7C41D6F');
        $this->addSql('ALTER TABLE user_answer ADD CONSTRAINT FK_BF8F5118A7C41D6F FOREIGN KEY (option_id) REFERENCES option_table (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user_answer DROP FOREIGN KEY FK_BF8F5118A7C41D6F');
        $this->addSql('CREATE TABLE `option` (id INT AUTO_INCREMENT NOT NULL, question_id INT NOT NULL, body LONGTEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, name VARCHAR(1) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, is_correct TINYINT(1) NOT NULL, slug VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_general_ci`, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_5A8600B01E27F6BF (question_id), UNIQUE INDEX UNIQ_5A8600B0989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE `option` ADD CONSTRAINT FK_5A8600B01E27F6BF FOREIGN KEY (question_id) REFERENCES question (id)');
        $this->addSql('DROP TABLE option_table');
        $this->addSql('ALTER TABLE user_answer DROP FOREIGN KEY FK_BF8F5118A7C41D6F');
        $this->addSql('ALTER TABLE user_answer ADD CONSTRAINT FK_BF8F5118A7C41D6F FOREIGN KEY (option_id) REFERENCES `option` (id)');
    }
}
