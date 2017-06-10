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
$view['slots']->set('mauticContent', 'need_pointTrigger');
$view['slots']->set('headerTitle', $view['translator']->trans('kompulse.need_point.trigger.header.index'));

$view['slots']->set(
    'actions',
    $view->render(
        'MauticCoreBundle:Helper:page_actions.html.php',
        [
            'templateButtons' => [
                'new' => $permissions['kompulse:triggers:create'],
            ],
            'actionRoute' => 'kompulse_pointtrigger_action',
            'langVar'     => 'kompulse.trigger',
        ]
    )
);
?>

<div class="panel panel-default bdr-t-wdh-0 mb-0">
    <?php echo $view->render(
        'MauticCoreBundle:Helper:list_toolbar.html.php',
        [
            'searchValue' => $searchValue,
            'searchHelp'  => 'mautic.core.help.searchcommands',
            'action'      => $currentRoute,
        ]
    ); ?>
    <div class="page-list">
        <?php $view['slots']->output('_content'); ?>
    </div>
</div>


<script type="text/javascript">
Mautic.need_pointTriggerOnLoad = function (container) {
    if (mQuery(container + ' #list-search').length) {
        Mautic.activateSearchAutocomplete('list-search', 'kompulse.trigger');
    }

    if (mQuery('#triggerEvents')) {
        //make the fields sortable
        mQuery('#triggerEvents').sortable({
            items: '.trigger-event-row',
            handle: '.reorder-handle',
            stop: function(i) {
                mQuery.ajax({
                    type: "POST",
                    url: mauticAjaxUrl + "?action=point:reorderTriggerEvents",
                    data: mQuery('#triggerEvents').sortable("serialize") + "&triggerId=" + mQuery('#pointtrigger_sessionId').val()
                });
            }
        });

        mQuery('#triggerEvents .trigger-event-row').on('mouseover.triggerevents', function() {
            mQuery(this).find('.form-buttons').removeClass('hide');
        }).on('mouseout.triggerevents', function() {
            mQuery(this).find('.form-buttons').addClass('hide');
        }).on('dblclick.triggerevents', function(event) {
            event.preventDefault();
            mQuery(this).find('.btn-edit').first().click();
        });
    }
};


Mautic.need_pointTriggerEventOnLoad = function (container, response) {
    //new action created so append it to the form
    if (response.eventHtml) {
        var newHtml = response.eventHtml;
        var eventId = '#triggerEvent_' + response.eventId;
        if (mQuery(eventId).length) {
            //replace content
            mQuery(eventId).replaceWith(newHtml);
            var newField = false;
        } else {
            //append content
            mQuery(newHtml).appendTo('#triggerEvents');
            var newField = true;
        }

        //initialize tooltips
        mQuery(eventId + " *[data-toggle='tooltip']").tooltip({html: true});

        //activate new stuff
        mQuery(eventId + " a[data-toggle='ajax']").click(function (event) {
            event.preventDefault();
            return Mautic.ajaxifyLink(this, event);
        });

        //initialize ajax'd modals
        mQuery(eventId + " a[data-toggle='ajaxmodal']").on('click.ajaxmodal', function (event) {
            event.preventDefault();

            Mautic.ajaxifyModal(this, event);
        });

        mQuery('#triggerEvents .trigger-event-row').off(".triggerevents");
        mQuery('#triggerEvents .trigger-event-row').on('mouseover.triggerevents', function() {
            mQuery(this).find('.form-buttons').removeClass('hide');
        }).on('mouseout.triggerevents', function() {
            mQuery(this).find('.form-buttons').addClass('hide');
        }).on('dblclick.triggerevents', function(event) {
            event.preventDefault();
            mQuery(this).find('.btn-edit').first().click();
        });

        //show events panel
        if (!mQuery('#events-panel').hasClass('in')) {
            mQuery('a[href="#events-panel"]').trigger('click');
        }

        if (mQuery('#triggerEventPlaceholder').length) {
            mQuery('#triggerEventPlaceholder').remove();
        }
    }
};

Mautic.getPointActionPropertiesForm = function(actionType) {
    Mautic.activateLabelLoadingIndicator('point_type');

    var query = "action=point:getActionForm&actionType=" + actionType;
    mQuery.ajax({
        url: mauticAjaxUrl,
        type: "POST",
        data: query,
        dataType: "json",
        success: function (response) {
            if (typeof response.html != 'undefined') {
                mQuery('#pointActionProperties').html(response.html);
                Mautic.onPageLoad('#pointActionProperties', response);
            }
        },
        error: function (request, textStatus, errorThrown) {
            Mautic.processAjaxError(request, textStatus, errorThrown);
        },
        complete: function() {
            Mautic.removeLabelLoadingIndicator();
        }
    });
};

</script>