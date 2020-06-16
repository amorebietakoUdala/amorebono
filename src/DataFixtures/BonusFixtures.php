<?php

namespace App\DataFixtures;

use App\Entity\Bonus;
use Doctrine\Common\Persistence\ObjectManager;

class BonusFixtures extends BaseFixture
{
    protected function loadData(ObjectManager $manager)
    {
        $this->createMany(1, 'businessBonus', function ($i) use ($manager) {
            $businessBonus = new Bonus();
            $businessBonus->setType('Negozio bonoa');
            $businessBonus->setEmandakoak(0);
            $businessBonus->setGuztira(54000);
            $businessBonus->setPrice(15);

            return $businessBonus;
        });

        $this->createMany(1, 'sector1Bonus', function ($i) use ($manager) {
            $sector1Bonus = new Bonus();
            $sector1Bonus->setType('1. sektore bonoa');
            $sector1Bonus->setEmandakoak(0);
            $sector1Bonus->setGuztira(3000);
            $sector1Bonus->setPrice(15);

            return $sector1Bonus;
        });

        $manager->flush();
    }
}
