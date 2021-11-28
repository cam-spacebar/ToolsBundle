<?php
/*
* created on: 26/11/2021 - 17:51
* by: Cameron
*/


namespace App\VisageFour\Bundle\ToolsBundle\Tests\UrlShortener;

use App\Entity\FileManager\File;
use App\Entity\UrlShortener\Hit;
use App\Entity\UrlShortener\Url;
use App\Exceptions\ApiErrorCode;
use App\Repository\UrlShortener\UrlRepository;
use App\Services\FrontendUrl;
use VisageFour\Bundle\ToolsBundle\Classes\Testing\CustomApiTestCase;
use VisageFour\Bundle\ToolsBundle\Classes\Testing\CustomKernelTestCase;
use Doctrine\ORM\EntityManager;
use VisageFour\Bundle\ToolsBundle\Services\FileManager;

/**
 * Class SecurityTest
 * @package App\Tests\Service
 *
 * === Test Case Documentation: ===
 * Run all tests:
 * - ./vendor/bin/phpunit
 * Run all the tests in this file:
 * - ./vendor/bin/phpunit src/VisageFour/Bundle/ToolsBundle/Tests/UrlShortener/UrlTest.php
 *
 * Create new test case [P-CB-087]
 * https://docs.google.com/presentation/d/1-AYb7xtRScoWsB3jxHnThsBJVgwY8DzEKLacGVCB28c/edit#slide=id.p
 *
 * (comment version: 1.02)
 */
class UrlTest extends CustomApiTestCase
{
    /**
     * @var UrlRepository
     */
    private $urlRepo;

    private function getServices($debuggingOutputOn)
    {
        $container = self::$kernel->getContainer();

        $this->urlRepo = $container->get('test.'. UrlRepository::class);
//        $this->fileManager->getFileRepo()->setOutputValuesOnCreation($debuggingOutputOn);
    }

    /**
     * Setup that is specific to this test case.
     * (this runs once prior to each test case / method())
     */
    protected function customSetUp()
    {
        $this->getServices(true);

        $this->testingHelper->truncateEntities([
            Url::class,
            Hit::class
        ]);

        return true;
    }

    protected function customTearDown(): void
    {
//        $this->outputDebugToTerminal('tearDown()');
//        $this->removeUser($this->person);
//        $this->manager->persist($this->person);
//        $this->manager->flush();
    }

    /**
     * @test
     * ./vendor/bin/phpunit src/VisageFour/Bundle/ToolsBundle/Tests/UrlShortener/UrlTest.php --filter createShortenedURL
     *
     * create a shortened URL (DB record) and then visit it (creating a HIit)
     */
    public function createShortenedURL(): void
    {

        $this->setCurrentMethod(__METHOD__);
        $url = $this->urlRepo->createNewShortenedUrl('www.NewToMelbourne.org/product1?coupon=11');

        $this->em->flush();
        $this->testingHelper->assertNumberOfDBTableRecords(1, Url::class, $this);

        $params = [
            'code' => $url->getCode()
        ];
        $this->setTargetRoutePairConstant(FrontendUrl::SHORTENED_URL_LP, $params);
        $this->setExpectedResponse(ApiErrorCode::REDIRECT_301);
//        $this->buildUrlWithParams($data);
        $crawler = $this->sendJSONRequest('GET');
//        dump($crawler->getcontent());

        $this->assertResponseHeaderSame('content-type', 'text/html; charset=UTF-8');
//        $this->assertResponseIsSuccessful();
//        $this->assertSelectorTextContains('h1', 'Hello World');./vendor/bin/phpunit --colors
//        $this->assertEquals(42, 42);




    }
}