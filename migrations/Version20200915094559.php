<?php

declare(strict_types=1);

namespace Weblike\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200915094559 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Import the base payment methods';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE IF NOT EXISTS es_payment_methods (
            id INT AUTO_INCREMENT NOT NULL, 
            pos INT DEFAULT NULL, 
            name VARCHAR(255) DEFAULT NULL, 
            code VARCHAR(255) DEFAULT NULL, 
            price DOUBLE DEFAULT NULL, 
            payment_transfer SMALLINT(6) DEFAULT NULL,
            active SMALLINT(6) DEFAULT NULL,
            PRIMARY KEY(id))');

        $methods = [
            [1,1,'{"sk":"Platobná karta"}',NULL, 0,1,1],
            [2,2,'{"sk":"Hotovosť"}',NULL,0.5,0,1],
            [3,3,'{"sk":"Platba prevodom"}',NULL,0,1,1],
            [4,4,'{"sk":"Dobierka"}',NULL,0,0,1]
        ];

        foreach ( $methods as $method )
        {
            $this->addSql('INSERT INTO es_payment_methods (
                id, pos, name, code, price, payment_transfer, active) VALUES (?, ?, ?, ?, ?, ?, ? )', $method );
        }

        $this->write("All done !");
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->throwIrreversibleMigrationException("This version can't be down-graded !");
    }
}
