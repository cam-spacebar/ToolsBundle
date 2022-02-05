<?php
/*
* created on: 04/02/2022 - 14:24
* by: Cameron
*/


namespace VisageFour\Bundle\ToolsBundle\Fixtures;


use App\Entity\Person;

class PersonDummyData
{
    public function getPersonCameron()
    {
        $person = new Person();

        $person->setEmail('cameronrobertburns@gmail.com')
            ->setFirstName('Cameron')
            ->setLastName('Burns');

        return $person;
    }
}