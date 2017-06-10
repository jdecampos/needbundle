<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\KompulseNeedBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use MauticPlugin\KompulseNeedBundle\Entity\ContactNeed;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LoadNeedData.
 */
class LoadContactNeedData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        for ($i = 1; $i <= 50; $i++) {
            $needCount = rand(1, 10);
            for ($j = 1; $j <= $needCount; $j++) {
                $contactNeed = new ContactNeed();
                $contactNeed->setLead($this->getReference('lead-'.$i));
                $contactNeed->setNeed($this->getReference('need-'.rand(1, 20) ));
            }
        }
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return 50;
    }
}
