<?php
/*
* created on: 05/11/2021 - 13:11
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Repository\Purchase;

use App\Entity\Purchase\AttributionTag;
use VisageFour\Bundle\ToolsBundle\Repository\NoAutowire\BaseRepository;

class BaseAttributionTagRepository extends BaseRepository
{
//    public function __construct (ManagerRegistry $registry, $class) {
//        parent::__construct($registry, $class);
//    }

    /**
     * @param string $name
     * @param AttributionTag|null $parentTag
     * @return AttributionTag
     *
     * Check the parent doesn't have a tag with the same name already
     */
    public function createNew (string $name, AttributionTag $parentTag = null)
    {
        $this->checkForDuplicateTag($name, $parentTag);

        $newTag = new AttributionTag($name, $parentTag);

        $this->persistAndLogEntityCreation($newTag);

        return $newTag;
    }

    // throws an exception if the parent already has a tag with the provided: $name
    public function checkForDuplicateTag(string $name, ?AttributionTag $parentTag)
    {
        if ($parentTag == null) {
            return true;
        }
        $preExistingTag = $this->findOneBy([
            'name'              => $name,
            'relatedParentTag'  => $parentTag
        ]);

        if (!empty($preExistingTag)) {
            throw new \Exception('the attributeTag name: "'. $name .'" already exists under the parent tag: "'. $parentTag->getName());
        }

        return true;
    }

    /**
     * @param array $arrayOfTags
     * @param AttributionTag $parentTag
     * @throws \Doctrine\ORM\ORMException
     *
     * Create a series of tags, and assign them (as children) to the provided $parent.
     */
    public function createFromArray(array $arrayOfTags, AttributionTag $parentTag, $outputContents = false): array
    {
        $newTags = [];
        foreach ($arrayOfTags as $curI => $curTagName) {
            $newTags[$curI] = $this->createNew($curTagName, $parentTag);
//            $this->persistAndLogEntityCreation($newTags[$curI]);

            if ($outputContents) {
                $newTags[$curI]->outputContents();
            }
        }

        return $newTags;
    }
}