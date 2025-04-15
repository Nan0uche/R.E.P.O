<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250414225000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__weather_station AS SELECT id, user_id, name, location, description, is_active, mac_address FROM weather_station');
        $this->addSql('DROP TABLE weather_station');
        $this->addSql('CREATE TABLE weather_station (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, location VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL, is_active BOOLEAN NOT NULL, mac_address VARCHAR(17) NOT NULL, CONSTRAINT FK_3B061BFAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO weather_station (id, user_id, name, location, description, is_active, mac_address) SELECT id, user_id, name, location, description, is_active, mac_address FROM __temp__weather_station');
        $this->addSql('DROP TABLE __temp__weather_station');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3B061BFAB728E969 ON weather_station (mac_address)');
        $this->addSql('CREATE INDEX IDX_3B061BFAA76ED395 ON weather_station (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__weather_station AS SELECT id, user_id, mac_address, name, location, description, is_active FROM weather_station');
        $this->addSql('DROP TABLE weather_station');
        $this->addSql('CREATE TABLE weather_station (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, mac_address VARCHAR(17) NOT NULL, name VARCHAR(255) NOT NULL, location VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL, is_active BOOLEAN NOT NULL, CONSTRAINT FK_3B061BFAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO weather_station (id, user_id, mac_address, name, location, description, is_active) SELECT id, user_id, mac_address, name, location, description, is_active FROM __temp__weather_station');
        $this->addSql('DROP TABLE __temp__weather_station');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3B061BFAB728E969 ON weather_station (mac_address)');
        $this->addSql('CREATE INDEX IDX_3B061BFAA76ED395 ON weather_station (user_id)');
    }
}
