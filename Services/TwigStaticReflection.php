<?php

namespace VisageFour\Bundle\ToolsBundle\Services;

/**
 * Class TwigStaticReflection
 * @package VisageFour\Bundle\ToolsBundle\Services
 *
 * NOTE: CLASS CONSTANTS CAN BE ACCESSED DIRECTLY THROUGH TWIG.
 *
 * Allows the twig file to call static members and functions from a twig template - which is otherwise not possible.
 * based on this tutorial/script: http://blog.alterphp.com/2016/02/access-static-methodsproperties-from.html
 *
 * Service definition:
 *services:
    twig.static_reflection_extension:
        class: VisageFour\Bundle\ToolsBundle\Services\TwigStaticReflection
        tags:
        - { name: twig.extension }
 *
 *
 * usage:
 * {{ get_static("Twencha\\Bundle\\EventRegistrationBundle\\Entity\\Badge", 'propertyName') }}
 *
 */
class TwigStaticReflection extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('call_static', [$this, 'callStaticMethod']),
            new \Twig_SimpleFunction('get_static', [$this, 'getStaticProperty']),
        );
    }

    public function callStaticMethod($class, $method, array $args = [])
    {
        $refl = new \reflectionClass($class);

        // Check that method is static AND public
        if ($refl->hasMethod($method) && $refl->getMethod($method)->isStatic() && $refl->getMethod($method)->isPublic()) {
            return call_user_func_array($class.'::'.$method, $args);
        }

        throw new \RuntimeException(sprintf('Invalid static method call for class %s and method %s', $class, $method));
    }

    public function getStaticProperty($class, $property)
    {
        $refl = new \reflectionClass($class);

        // Check that property is static AND public
        if ($refl->hasProperty($property) && $refl->getProperty($property)->isStatic() && $refl->getProperty($property)->isPublic()) {
            return $refl->getProperty($property)->getValue();
        }

        throw new \RuntimeException(sprintf('Invalid static property get for class %s and property %s', $class, $property));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'reflection';
    }
}