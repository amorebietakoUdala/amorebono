<?php

namespace App\DataFixtures;

use App\Entity\Person;
use Doctrine\Common\Persistence\ObjectManager;

class PersonFixtures extends BaseFixture
{
    protected function loadData(ObjectManager $manager)
    {
        $this->createMany(20, 'person', function ($i) use ($manager) {
            $person = new \App\Entity\Person();
            $person->setIzena($this->faker->firstName);
            $person->setAbizenak($this->faker->lastName.' '.$this->faker->lastName);
            $person->setNAN(str_pad($i, 8, '0', STR_PAD_LEFT).'A');
            $person->setTelefonoa($this->faker->e164PhoneNumber);

            return $person;
        });

        $manager->flush();
    }
}
