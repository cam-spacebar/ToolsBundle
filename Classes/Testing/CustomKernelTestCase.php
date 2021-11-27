<?php
/*
* created on: 22/11/2021 - 20:57
* by: Cameron
*/

namespace VisageFour\Bundle\ToolsBundle\Classes\Testing;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use VisageFour\Bundle\ToolsBundle\Services\FileManager\FileManager;
use VisageFour\Bundle\ToolsBundle\Services\Testing\TestingHelper;

abstract class CustomKernelTestCase extends KernelTestCase
{
    /**
     * @var TestingHelper
     */
    protected $testingHelper;

    /**
     * @param array $options
     * @return \Symfony\Component\HttpKernel\KernelInterface|void
     *
     * override bootKernel, so we can add extra services
     */
    protected static function bootKernel(array $options = [])
    {
        parent::bootKernel($options);
        $container = self::$kernel->getContainer();

        $container->get('test.'. TestingHelper::class);
    }

    protected function getContainer () {
        return self::$kernel->getContainer();
    }

    protected function setUp():void {
        self::bootKernel();

        $this->getEntityManager();
        $container = $this->getContainer();

        $this->testingHelper = $container->get('test.'. TestingHelper::class);

        $this->customSetUp();
    }

    protected function tearDown():void {
        $this->customTearDown();
    }

    abstract protected function customSetup();
    abstract protected function customTearDown();
    
    /** @var EntityManager */
    protected $em;

    public function getEntityManager (): EntityManager
    {
        $this->em = self::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        return $this->em;
    }

    /**
     * @param $filepath
     * duplicates a local file and returns the new filename
     * initinially used: because the file that is used in a FileManager test is deleted (during cleanup)
     * todo: move to testinhelper zzz1
     */
    public function duplicateLocalFile($path, $filename)
    {
        FileManager::throwExceptionIfEndsWith($path, '/');

        $originalFilepath = $path.'/'.$filename;
        $newFilepath = $path.'/' .'copy_of_'. $filename;

        // copy the original, as the local file provided will be deleted
        copy( $originalFilepath, $newFilepath );

        return $newFilepath;
    }
}