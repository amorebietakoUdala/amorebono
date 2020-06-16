<?php

namespace App\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SellingFixtures extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(ObjectManager $manager)
    {
        $this->createMany(10, 'businessSellings', function ($i) use ($manager) {
            $selling = new \App\Entity\Selling();
            $selling->setPerson($this->getRandomReference('person'));
            $selling->setBonus($this->getRandomReference('businessBonus'));
            $selling->setQuantity(5);
            $selling->setTotalPrice(10);

            return $selling;
        });

        $this->createMany(10, 'sector1Sellings', function ($i) use ($manager) {
            $selling = new \App\Entity\Selling();
            $selling->setPerson($this->getRandomReference('person'));
            $selling->setBonus($this->getRandomReference('sector1Bonus'));
            $selling->setQuantity(5);
            $selling->setTotalPrice(10);

            return $selling;
        });

        $manager->flush();
    }

    public function getDependencies()
    {
        return array(
            PersonFixtures::class,
            BonusFixtures::class,
        );
    }
}
