<?php

namespace VisageFour\Bundle\ToolsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

// do not include ORM mapping here as the sub-classed slug cannot inherit the OneToOne relationship
// some reason, the ORM doesn't allow this to be a mapped superclass if itself is an entity.
// plus I don't need an extra table in the DB that an super-entity would have created.
/**
 * @ORM\MappedSuperclass
 */
class Slug extends Code
{
}