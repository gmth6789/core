<?php declare(strict_types=1);

namespace Shopware\Core\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1547544082ProductStreamCondition extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1547544082;
    }

    public function update(Connection $connection): void
    {
        $connection->executeQuery('
            CREATE TABLE `product_stream_condition` ( 
              `id` binary(16) NOT NULL,
              `type` varchar(256) NOT NULL,
              `product_stream_id` binary(16) NOT NULL,
              `parent_id` binary(16) NULL,
              `value` JSON NULL,
              `position` INT(11) DEFAULT 0 NOT NULL,
              PRIMARY KEY (`id`),
              CONSTRAINT `json.value` CHECK (JSON_VALID (`value`)),
              CONSTRAINT `fk.condition_product_stream.product_stream_id` FOREIGN KEY (`product_stream_id`) REFERENCES `product_stream` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.condition_product_stream.parent_id` FOREIGN KEY (`parent_id`) REFERENCES `product_stream_condition` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
