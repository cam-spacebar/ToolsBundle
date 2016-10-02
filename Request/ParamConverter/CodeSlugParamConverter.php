<?php

namespace VisageFour\Bundle\ToolsBundle\Request\ParamConverter;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twencha\Bundle\EventRegistrationBundle\Entity\Code;
use Twencha\Bundle\EventRegistrationBundle\Repository\CodeRepository;

/**
 * User: Cameron Burns
 * Date: 2/10/2016
 * Time: 1:29 PM
 */

// ONLY USE THIS FOR EXAMPLE - CURRENTLY USED BY TWENCHA/EVENTREGISTRATIONBUNDLE
// MAYBE: could be setup as a configurtable service? could accept a repo and code class for classes that sub-class the code super class?
class CodeSlugParamConverter implements ParamConverterInterface
{
    /**
     * @var ManagerRegistry $registry Manager registry
     */
    private $registry;

    /**
     * @param ManagerRegistry $registry Manager registry
     */
    public function __construct(ManagerRegistry $registry = null)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     *
     * Check, if object supported by our converter
     */
    public function supports(ParamConverter $configuration)
    {
        // If there is no manager, this means that only Doctrine DBAL is configured
        // In this case we can do nothing and just return
        if (null === $this->registry || !count($this->registry->getManagers())) {
            return false;
        }

        // Check, if option class was set in configuration
        if (null === $configuration->getClass()) {
            return false;
        }

        // Get actual entity manager for class
        $em = $this->registry->getManagerForClass($configuration->getClass());

        // Check, if class provided can be converted
        if ('Twencha\Bundle\EventRegistrationBundle\Entity\Slug' !== $em->getClassMetadata($configuration->getClass())->getName()) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * Applies converting
     *
     * @throws \InvalidArgumentException When route attributes are missing
     * @throws NotFoundHttpException     When object not found
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $slugCode = $request->attributes->get('slugCode');
        if (null === $slugCode) {
            throw new \InvalidArgumentException('slugCode attribute is missing');
        }

        /** @var EntityManager $em */
        $em = $this->registry->getManagerForClass($configuration->getClass());

        // get code
        /** @var CodeRepository $codeRepo */
        $codeRepo = $em->getRepository('EventRegistrationBundle:Code');
        /** @var Code $code */
        $code = $codeRepo->findOneBy(array ('code' => $slugCode));

        if (empty($code)) {
            throw new \Exception('The slug code provided: "'. $slugCode .'" does not exist.');
            //die ('The URL provided does not exist, please check and try again.');
        }

        // get relatedSlug
        $slug = $code->getRelatedSlug();

        if (empty($slug)) {
            throw new \Exception('Could not find related slug for Code with code: "'. $code->getCode() .'"');
        }

        // Map slug to the route's parameter
        $request->attributes->set($configuration->getName(), $slug);
    }
}