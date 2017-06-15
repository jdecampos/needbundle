<?php

namespace MauticPlugin\KompulseNeedBundle\Controller;

use Mautic\CoreBundle\Controller\AjaxController as CommonAjaxController;
use Mautic\CoreBundle\Helper\InputHelper;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AjaxController.
 */
class AjaxController extends CommonAjaxController
{
    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    protected function getActionFormAction(Request $request)
    {
        $dataArray = [
            'success' => 0,
            'html'    => '',
        ];
        $type = InputHelper::clean($request->request->get('actionType'));

        if (!empty($type)) {
            //get the HTML for the form
            /** @var \MauticPlugin\KompulseNeedBundle\Model\NeedPointModel $model */
            $model   = $this->getModel('kompulse.need_point');
            $actions = $model->getPointActions();

            if (isset($actions['actions'][$type])) {
                $themes = ['MauticPointBundle:FormTheme\Action'];
                if (!empty($actions['actions'][$type]['formTheme'])) {
                    $themes[] = $actions['actions'][$type]['formTheme'];
                }

                $formType        = (!empty($actions['actions'][$type]['formType'])) ? $actions['actions'][$type]['formType'] : 'genericpoint_settings';
                $formTypeOptions = (!empty($actions['actions'][$type]['formTypeOptions'])) ? $actions['actions'][$type]['formTypeOptions'] : [];
                $form            = $this->get('form.factory')->create('pointaction', [], ['formType' => $formType, 'formTypeOptions' => $formTypeOptions]);
                $html            = $this->renderView('MauticPointBundle:Point:actionform.html.php', [
                    'form' => $this->setFormTheme($form, 'MauticPointBundle:Point:actionform.html.php', $themes),
                ]);

                //replace pointaction with point
                $html                 = str_replace('pointaction', 'need_point', $html);
                $dataArray['html']    = $html;
                $dataArray['success'] = 1;
            }
        }

        return $this->sendJsonResponse($dataArray);
    }

}
