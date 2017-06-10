<?php

namespace MauticPlugin\KompulseNeedBundle\Entity;

use Mautic\CoreBundle\Entity\CommonRepository;
use MauticPlugin\KompulseNeedBundle\Entity\Need;

/**
 * ContactNeedRepository
 */
class ContactNeedPointLogRepository extends CommonRepository
{

    public function getEntities(array $args = array())
    {
        $q = $this
            ->createQueryBuilder('npl');

        $args['qb'] = $q;

        return parent::getEntities($args);
    }

    /**
     * @return string
     */
    public function getTableAlias()
    {
        return 'npl';
    }
}
