<?php
/*
* created on: 31/05/2020 at 7:30 PM
* by: cameronrobertburns
*/

namespace VisageFour\Bundle\ToolsBundle\Services\MigrationsExtraData;

use Doctrine\ORM\EntityManagerInterface;

// [CRC readme:] this is part of a Custom Reusable Component (CRC),
// you can learn more about is via it’s CRC readme here:
// https://bit.ly/2TUgpbU
// todo: ad output to screen and file for SQL statements - so the SQL can be copied intothe migrations.
abstract class BaseMED
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * explains what the MED does.
     * @var string
     */
    protected $description;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    abstract public function executeUp();

    public function getDescription()
    {
        return $this->description;
    }
}