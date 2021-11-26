<?php
/*
* created on: 26/11/2021 - 14:00
* by: Cameron
*/

namespace App\VisageFour\Bundle\ToolsBundle\Repository\UrlShortener;

use App\Entity\UrlShortener\Url;
use App\VisageFour\Bundle\ToolsBundle\Entity\UrlShortener\BaseUrl;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use VisageFour\Bundle\ToolsBundle\Repository\CodeRepository;
use VisageFour\Bundle\ToolsBundle\Repository\NoAutowire\BaseRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use VisageFour\Bundle\ToolsBundle\Services\CodeGenerator;

/**
 * @method Url|null find($id, $lockMode = null, $lockVersion = null)
 * @method Url|null findOneBy(array $criteria, array $orderBy = null)
 * @method Url[]    findAll()
 * @method Url[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BaseUrlRepository extends CodeRepository
{

    /**
     * @var string
     * e.g. http://api.NewToMelbourne.org (note: no trailing slash).
     * this is ussually set via a .env var and services.yaml bind
     */
    private $backendBaseUrl;

    public function __construct(ManagerRegistry $registry, CodeGenerator $codeGen, string $backend_base_url)
    {
        parent::__construct($registry, Url::class, $codeGen);
        $this->backendBaseUrl   = $backend_base_url;
    }

    /**
     * @param string $destinationUrl
     * @return Url
     * @throws \Exception
     */
    public function createNewShortenedUrl (string $destinationUrl): Url
    {
        $shortenedCode = $this->createNewUniqueCode(BaseUrl::$codeNoOfChars);

        $new = new Url($destinationUrl, $shortenedCode);

        $this->persistAndLogEntityCreation($new);

        return $new;
    }


    // /**
    //  * @return UrlShortener[] Returns an array of UrlShortener objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?UrlShortener
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}