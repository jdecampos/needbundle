<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
if ($tmpl == 'index') {
    $view->extend('KompulseNeedBundle:ContactNeed:index.html.php');
}
?>

<ul class="notes" id="ContactNeeds">
    <?php foreach ($contactNeeds as $contactNeed): ?>
        <?php echo $view->render('KompulseNeedBundle:ContactNeed:contact_need.html.php', [
            'contactNeed'        => $contactNeed,
            'lead'        => $lead,
            'permissions' => $permissions,
        ]); ?>
<?php //echo $contactNeed->getNeed()->getName();   ?>
    <?php endforeach; ?>
</ul>
