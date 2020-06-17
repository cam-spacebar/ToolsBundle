<?php

namespace VisageFour\Bundle\ToolsBundle\Services\MigrationsExtraData;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class MigrationsExtraDataManager
 * @package App\Services
 *
 * this class is used to add extra data to a dev and prod database when executing a migration file.
 * for more information, see the CRC airtable record:
 * https://bit.ly/3erJFic
 */
class MigrationsExtraDataManager
{
    /**
     * @var Container
     */
    private $container;

    public function __construct(ContainerInterface $container) {
        $this->container        = $container;
    }

    public function findAndExecuteMED($medFQCN)
    {
        echo ' == Executing Migrations Extra Data (MED CRC): "'. $medFQCN ."\" ==\n\n";
        /** @var BaseMED $med */
        $med = $this->container->get($medFQCN);

        echo 'this Migrations Extra Data (MED) is used to: '. $med->getDescription();

        $med->executeUp();

        echo " == Finished Executing Migrations Extra Data (MED) ==\n\n";

        return $this;
    }

    public function dataDown()
    {
        // remove badgeStack
        // remove
    }
}