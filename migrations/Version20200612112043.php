<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200612112043 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE bonus (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, emandakoak INT DEFAULT NULL, guztira INT NOT NULL, price DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE person (id INT AUTO_INCREMENT NOT NULL, nan VARCHAR(255) NOT NULL, izena VARCHAR(255) NOT NULL, abizenak VARCHAR(255) NOT NULL, telefonoa VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE selling (id INT AUTO_INCREMENT NOT NULL, person_id INT NOT NULL, bonus_id INT NOT NULL, quantity INT DEFAULT NULL, total_price DOUBLE PRECISION NOT NULL, INDEX IDX_5A491BAB217BBB47 (person_id), INDEX IDX_5A491BAB69545666 (bonus_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE selling ADD CONSTRAINT FK_5A491BAB217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE selling ADD CONSTRAINT FK_5A491BAB69545666 FOREIGN KEY (bonus_id) REFERENCES bonus (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE selling DROP FOREIGN KEY FK_5A491BAB69545666');
        $this->addSql('ALTER TABLE selling DROP FOREIGN KEY FK_5A491BAB217BBB47');
        $this->addSql('DROP TABLE bonus');
        $this->addSql('DROP TABLE person');
        $this->addSql('DROP TABLE selling');
        $this->addSql('DROP TABLE user');
    }
}
