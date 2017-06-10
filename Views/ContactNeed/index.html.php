<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
?>
<div class="tab-pane fade bdr-w-0" id="kompulse-container">

  <div class="box-layout mb-lg">
    <div class="col-xs-10 va-m">
    </div>
    <div class="col-xs-2 va-t">
      <a class="btn btn-primary btn-contactneed-add pull-right" href="<?php echo $view['router']->path('mautic_contactneed_action', ['leadId' => $lead->getId(), 'objectAction' => 'new']); ?>" data-toggle="ajaxmodal" data-target="#MauticSharedModal" data-header="<?php echo $view['translator']->trans('kompulse.contact_need.header.new'); ?>"><i class="fa fa-plus fa-lg"></i> <?php echo $view['translator']->trans('kompulse.contact_need.add.need'); ?></a>
    </div>
  </div>

  <div id="ContactNeedList">
    <?php $view['slots']->output('_content'); ?>
  </div>

</div>


<script type="text/javascript">
Mautic.refreshContactNeeds = function(form) {
    Mautic.postForm(mQuery(form), function (response) {
        response.target = '#ContactNeedList';
        mQuery('#ContactNeedCount').html(response.contactNeedCount);
        Mautic.processPageContent(response);
    });
};

Mautic.contactNeedOnLoad = function (container, response) {
    if (response.contactNeedHtml) {
        var el = '#ContactNeed' + response.contactNeedId;
        if (mQuery(el).length) {
            mQuery(el).replaceWith(response.contactNeedHtml);
        } else {
            mQuery('#ContactNeeds').prepend(response.contactNeedHtml);
        }

        //initialize ajax'd modals
        mQuery(el + " *[data-toggle='ajaxmodal']").off('click.ajaxmodal');
        mQuery(el + " *[data-toggle='ajaxmodal']").on('click.ajaxmodal', function (event) {
            event.preventDefault();

            Mautic.ajaxifyModal(this, event);
        });

        //initiate links
        mQuery(el + " a[data-toggle='ajax']").off('click.ajax');
        mQuery(el + " a[data-toggle='ajax']").on('click.ajax', function (event) {
            event.preventDefault();

            return Mautic.ajaxifyLink(this, event);
        });
    } else if (response.deleteId && mQuery('#ContactNeed' + response.deleteId).length) {
        mQuery('#ContactNeed' + response.deleteId).remove();
    }

    if (response.upContactNeedCount || response.contactNeedCount || response.downContactNeedCount) {
        if (response.upContactNeedCount || response.downContactNeedCount) {
            var count = parseInt(mQuery('#ContactNeedCount').html());
            count = (response.upContactNeedCount) ? count + 1 : count - 1;
        } else {
            var count = parseInt(response.contactNeedCount);
        }

        mQuery('#ContactNeedCount').html(count);
    }
};
</script>
