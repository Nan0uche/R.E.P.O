<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250414233415 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE weather_data ADD COLUMN type VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE weather_data ADD COLUMN value CLOB DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__weather_data AS SELECT id, station_id, temperature, humidity, pressure, timestamp FROM weather_data');
        $this->addSql('DROP TABLE weather_data');
        $this->addSql('CREATE TABLE weather_data (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, station_id INTEGER NOT NULL, temperature DOUBLE PRECISION NOT NULL, humidity DOUBLE PRECISION NOT NULL, pressure DOUBLE PRECISION NOT NULL, timestamp DATETIME NOT NULL, CONSTRAINT FK_3370691A21BDB235 FOREIGN KEY (station_id) REFERENCES weather_station (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO weather_data (id, station_id, temperature, humidity, pressure, timestamp) SELECT id, station_id, temperature, humidity, pressure, timestamp FROM __temp__weather_data');
        $this->addSql('DROP TABLE __temp__weather_data');
        $this->addSql('CREATE INDEX IDX_3370691A21BDB235 ON weather_data (station_id)');
    }
}
