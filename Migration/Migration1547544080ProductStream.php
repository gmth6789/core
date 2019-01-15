<?php declare(strict_types=1);

namespace Shopware\Core\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1547544080ProductStream extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1547544080;
    }

    public function update(Connection $connection): void
    {
        $connection->executeQuery('
            CREATE TABLE `product_stream` (
              `id` binary(16) NOT NULL,
              `name` varchar(500) NOT NULL,
              `type` VARCHAR(256) NULL,
              `description` LONGTEXT NULL,
              `priority` int(11) NOT NULL,
              `payload` JSON NULL,
              `invalid` TINYINT(1) NULL,
              `created_at` datetime(3) NOT NULL,
              `updated_at` datetime(3) NULL,
              PRIMARY KEY (`id`),
              CONSTRAINT `json.payload` CHECK (JSON_VALID(`payload`))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
