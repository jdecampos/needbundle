<?php

namespace MauticPlugin\KompulseNeedBundle\Entity;

use Mautic\CoreBundle\Entity\CommonRepository;
use Mautic\LeadBundle\Entity\Lead;
use MauticPlugin\KompulseNeedBundle\Entity\Need;

/**
 * ContactNeedRepository
 */
class ContactNeedRepository extends CommonRepository
{

    public function getEntities(array $args = array())
    {
        $q = $this
            ->createQueryBuilder('cn');

        $args['qb'] = $q;

        return parent::getEntities($args);
    }

    /**
     * @return string
     */
    public function getTableAlias()
    {
        return 'cn';
    }

    public function findByConditions(Lead $lead, $field, $value, $operator)
    {
        $needId   = preg_replace('/\D/', '', $field);


        $qb = $this->_em->getConnection()->createQueryBuilder()
            ->select('cn.id')
            ->from(MAUTIC_TABLE_PREFIX.'plugin_kompulse_contact_need', 'cn');

        $qb->where('cn.lead_id = :lead')
            ->andWhere('cn.need_id = :need')
            ->andWhere(
                $qb->expr()->$operator('cn.points', $value)
            );
        $qb->setParameters(['lead' => $lead->getId(), 'need' => $needId]);

        $result = $qb->execute()->fetch();

        return !empty($result['id']);
    }
}
