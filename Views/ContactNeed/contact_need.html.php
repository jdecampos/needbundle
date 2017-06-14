<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
if ($contactNeed instanceof \MauticPlugin\KompulseNeedBundle\Entity\ContactNeed) {
    $id     = $contactNeed->getId();
    $lead   = $contactNeed->getLead();
    $need   = $contactNeed->getNeed();
    $points = $contactNeed->getPoints();
} else {
    $id     = $contactNeed['id'];
    $lead   = $contactNeed['lead'];
    $need   = $contactNeed['need'];
    $points = $contactNeeds['points'];
}

$icon = 'fa-bullseye';

?>
<li id="ContactNeed<?php echo $id; ?>">
    <div class="panel ">
        <div class="panel-body np box-layout">
            <div class="height-auto icon bdr-r bg-dark-xs col-xs-1 text-center">
                <h3><i class="fa fa-lg fa-fw <?php echo $icon; ?>"></i></h3>
            </div>
            <div class="media-body col-xs-11 pa-10">
                <div class="pull-right btn-group">
                    <?php if ($permissions['edit']): ?>
                        <a class="btn btn-default btn-xs" href="<?php echo $this->container->get('router')->generate('mautic_contactneed_action', ['leadId' => $lead->getId(), 'objectAction' => 'edit', 'objectId' => $id]); ?>" data-toggle="ajaxmodal" data-target="#MauticSharedModal" data-header="<?php echo $view['translator']->trans('kompulse.contact_need.header.edit'); ?>"><i class="fa fa-pencil"></i></a>
                    <?php endif; ?>
                     <?php if ($permissions['delete']): ?>
                         <a class="btn btn-default btn-xs"
                            data-toggle="confirmation"
                            href="<?php echo $view['router']->path('mautic_contactneed_action', ['objectAction' => 'delete', 'objectId' => $id, 'leadId' => $lead->getId()]); ?>"
                            data-message="<?php echo $view->escape($view['translator']->trans('kompulse.contact_need.confirmdelete')); ?>"
                            data-confirm-text="<?php echo $view->escape($view['translator']->trans('mautic.core.form.delete')); ?>"
                            data-confirm-callback="executeAction"
                            data-cancel-text="<?php echo $view->escape($view['translator']->trans('mautic.core.form.cancel')); ?>">
                             <i class="fa fa-trash text-danger"></i>
                         </a>
                     <?php endif; ?>
                </div>
                <?php echo $need; ?>
                <div class="mt-15 text-muted"><i class="fa fa-clock-o fa-fw"></i><span class="small"> <i class="fa fa-bullseye fa-fw"></i><span class="small"><?php echo $points; ?></span></div>
            </div>
        </div>
    </div>
</li>
