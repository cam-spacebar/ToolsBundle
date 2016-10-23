<?php

namespace VisageFour\Bundle\ToolsBundle\Fixtures;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Lexik\Bundle\MailerBundle\Entity\Email;
use Lexik\Bundle\MailerBundle\Entity\EmailTranslation;
use Lexik\Bundle\MailerBundle\Entity\Layout;
use Lexik\Bundle\MailerBundle\Entity\LayoutTranslation;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

// this class provides useful methods for creating lexik email fixtures
class LexikEmailFixtureGeneric implements ContainerAwareInterface
{

    /*
    === FIXTURES USAGE ===
    commandline:
    - delete from emails from all lexik databases first:
    --- ??
    --- ??
    - load email fixtures
    --- sudo php app/console doctrine:fixtures:load --fixtures=src/PlatypusPie/AnchorcardsBundle/DataFixtures/ORM/LoadAppData.php --append

    //*/

    /**
     * @var Container
     */
    protected $container;
    protected $locator;
    protected $parser;

    protected $em;

    protected $fromAddress;
    protected $fromName;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->em = $this->container->get('doctrine.orm.default_entity_manager');       // needed, as for some reason, the $manager obj passed to load doesn't work (it behaves like it;s save an obj but hasn't)
    }

    public function __construct()
    {
    }

    // delete all records from a DB table
    public function purgeEntityTable ($entityPath) {
        $cmd = $this->em->getClassMetadata($entityPath);
        $connection = $this->em->getConnection();
        $connection->beginTransaction();

        try {
            $connection->query('SET FOREIGN_KEY_CHECKS=0');
            $connection->query('DELETE FROM '.$cmd->getTableName());
            // Beware of ALTER TABLE here--it's another DDL statement and will cause
            // an implicit commit.
            $connection->query('SET FOREIGN_KEY_CHECKS=1');
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollback();
        }

        print 'Deleted all records from: "'. $entityPath ."\"\n";
    }

    // used to remove lexik layouts, layout translations, email templates and email template translations before beginning fixture population
    public function deleteLexikLayoutsAndEmailTemplates () {
        $this->purgeEntityTable('Lexik\Bundle\MailerBundle\Entity\Email');
        $this->purgeEntityTable('Lexik\Bundle\MailerBundle\Entity\EmailTranslation');
        $this->purgeEntityTable('Lexik\Bundle\MailerBundle\Entity\Layout');
        $this->purgeEntityTable('Lexik\Bundle\MailerBundle\Entity\LayoutTranslation');

        print "===\n";

        return true;
    }

    // create Lexik Layout and Layout translation DB records
    public function createLexikLayout ($layoutParams) {
        // create lexik layout
        $lexikLayout = new Layout();
        $lexikLayout->setReference($layoutParams['reference']);
        $lexikLayout->setDescription($layoutParams['description']);

        // create lexik layout translation
        $lexikLayoutTranslation = new LayoutTranslation($layoutParams['locale']);
        $lexikLayoutTranslation->setBody($layoutParams['body']);
        $lexikLayoutTranslation->setLayout($lexikLayout);

        $this->em->persist($lexikLayout);
        $this->em->persist($lexikLayoutTranslation);

        print 'Created Lexik Layout with reference: "'. $layoutParams['reference'] ."\"\n";

        return $lexikLayout;
    }

    // create lexik Email Template (both email + email translation db records, but not layout)
    public function createNewEmailTemplate ($emailArr, $lexikLayout) {
        if (empty($this->fromAddress) || empty($this->fromName)) {
            throw new \Exception('both $this->fromAddress and $this->fromName must be set');
        }

        if (empty($emailArr['htmlPath']) || empty($emailArr['txtPath']) || empty($emailArr['description']) || empty($emailArr['reference']) || empty($emailArr['subject'])) {
            throw new \Exception('missing required parameters for $emailArr parameters in: createNewEmailTemplate()');
        }

        // get classes required to resolve twig/symfony file notation
        $this->parser = $this->container->get('templating.name_parser');
        $this->locator = $this->container->get('templating.locator');

        // create new lexik email
        $lexikEmail = new Email();
        $lexikEmail->setReference($emailArr['reference']);
        $lexikEmail->setDescription($emailArr['description']);
        $lexikEmail->setSpool(0);
        $lexikEmail->setLayout($lexikLayout);

        // get paths of emails from symfony filepath notation
        $NewPersonRegistrationHTMLPath = $this->locator->locate($this->parser->parse($emailArr['htmlPath']));
        $NewPersonRegistrationTXTPath  = $this->locator->locate($this->parser->parse($emailArr['txtPath']));

        // create lexik email translation
        $lexikEmailTranslation = new EmailTranslation('en');
        $lexikEmailTranslation->setSubject($emailArr['subject']);
        $lexikEmailTranslation->setFromAddress($this->fromAddress);
        $lexikEmailTranslation->setFromName($this->fromName);
        $lexikEmailTranslation->setBody(file_get_contents($NewPersonRegistrationHTMLPath));
        $lexikEmailTranslation->setBodyText(file_get_contents($NewPersonRegistrationTXTPath));
        $lexikEmailTranslation->setEmail($lexikEmail);

        $this->em->persist($lexikEmailTranslation);
        $this->em->persist($lexikEmail);

        print 'Created Lexik Email with reference: "'. $emailArr['reference'] ."\"\n";

        return $lexikEmail;
    }
}
?>