<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$view->extend('MauticCoreBundle:Default:content.html.php');
$view['slots']->set('mauticContent', 'contactneed');

$objectId = $form->vars['data']->getId();
if (!empty($objectId)) {
  $name   = $form->vars['data']->getName();
  $header = $view['translator']->trans('kompulse.contact_need.header.edit', ['%name%' => $name]);
} else {
  $header = $view['translator']->trans('kompulse.contact_need.header.new');
}
$view['slots']->set('headerTitle', $header);
?>

<?php echo $view['form']->start($form); ?>
<div class="box-layout">
  <div class="col-xs-12 bg-white height-auto">
    <!-- tabs controls -->
    <ul class="bg-auto nav nav-tabs pr-md pl-md">
      <li class="active"><a href="#details-container" role="tab" data-toggle="tab"><?php echo $view['translator']->trans('mautic.core.details'); ?></a></li>
    </ul>
    <!--/ tabs controls -->

    <div class="tab-content pa-md">
      <div class="tab-pane fade in active bdr-w-0 height-auto" id="details-container">
    <?php if (isset($form['need'])): ?>
	<div class="row">
	  <div class="pa-md">
	    <div class="col-md-6">
	      <?php echo $view['form']->row($form['need']); ?>
	    </div>
	  </div>
	</div>
    <?php endif ?>
	<div class="row">
	  <div class="col-md-6">
	    <div class="pa-md">
	      <?php echo $view['form']->row($form['points']); ?>
	    </div>
	  </div>
	</div>
      </div>
    </div>
  </div>
</div>
<?php echo $view['form']->end($form); ?>
