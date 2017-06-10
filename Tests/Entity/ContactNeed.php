<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\KompulseNeedBundle\Tests\Entity;

use Mautic\LeadBundle\Entity\DoNotContact;
use Mautic\LeadBundle\Entity\FrequencyRule;
use MauticPlugin\KompulseNeedBundle\Entity\ContactNeed;

class ContactNeedTest extends \PHPUnit_Framework_TestCase
{
    public function testAdjustPoints()
    {
        // new lead
        $this->adjustPointsTest(5, $this->getContactNeedChangedArray(0, 5), new ContactNeed());
        $this->adjustPointsTest(5, $this->getContactNeedChangedArray(0, 5), new ContactNeed(), 'plus');
        $this->adjustPointsTest(5, $this->getContactNeedChangedArray(0, -5), new ContactNeed(), 'minus');
        $this->adjustPointsTest(5, [], new ContactNeed(), 'times');
        $this->adjustPointsTest(5, [], new ContactNeed(), 'divide');

        // // existing lead
        $contactNeed = new ContactNeed();
        $contactNeed->setPoints(5);

        $this->adjustPointsTest(5, $this->getContactNeedChangedArray(5, 10), $contactNeed);
        $this->adjustPointsTest(5, $this->getContactNeedChangedArray(10, 15), $contactNeed);
        $this->adjustPointsTest(10, $this->getContactNeedChangedArray(15, 150), $contactNeed, 'times');
        $this->adjustPointsTest(10, $this->getContactNeedChangedArray(150, 15), $contactNeed, 'divide');
    }

    /**
     * @param $points
     * @param $expected
     * @param ContactNeed $contactNeed
     * @param bool $operator
     */
    private function adjustPointsTest($points, $expected, ContactNeed $contactNeed, $operator = false)
    {
        if ($operator) {
            $contactNeed->adjustPoints($points, $operator);
        } else {
            $contactNeed->adjustPoints($points);
        }

        // var_dump($expected, $contactNeed->getChanges());die;
        $this->assertEquals($expected, $contactNeed->getChanges());
    }

    /**
     * @param int $oldValue
     * @param int $newValue
     *
     * @return array
     */
    private function getContactNeedChangedArray($oldValue = 0, $newValue = 0)
    {
        return [
            'points' => [
                0 => $oldValue,
                1 => $newValue,
            ],
        ];
    }
}
