<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\KompulseNeedBundle\Controller;

use Mautic\CoreBundle\Controller\FormController;
use Mautic\LeadBundle\Controller\LeadAccessTrait;
use MauticPlugin\KompulseNeedBundle\Model\NeedModel;
use MauticPlugin\KompulseNeedBundle\Entity\Need;
use MauticPlugin\KompulseNeedBundle\Entity\ContactNeed;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Controller class for managing ContactNeeds
 * (inspired from LeadBundle/Controller/NoteController)
 */
class ContactNeedController extends FormController
{
    use LeadAccessTrait;

    /**
     * Generate's new contactNeed and processes post data.
     *
     * @param $leadId
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newAction($leadId)
    {
        $lead = $this->checkLeadAccess($leadId, 'view');
        if ($lead instanceof Response) {
            return $lead;
        }

        //retrieve the entity
        $contactNeed = new ContactNeed();
        $contactNeed->setLead($lead);

        $model  = $this->getModel('kompulse.contactneed');
        $action = $this->generateUrl(
            'mautic_contactneed_action',
            [
                'objectAction' => 'new',
                'leadId'       => $leadId,
            ]
        );
        //get the user form factory
        $form       = $model->createForm($contactNeed, $this->get('form.factory'), $action);
        $closeModal = false;
        $valid      = false;
        ///Check for a submitted form and process it
        if ($this->request->getMethod() == 'POST') {
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    $closeModal = true;

                    $contactNeed->initChange();
                    //form is valid so process the data
                    $model->saveEntity($contactNeed);
                }
            } else {
                $closeModal = true;
            }
        }

        $security    = $this->get('mautic.security');
        $permissions = [
            'edit'   => $security->hasEntityAccess('kompulse:contact_need:editown', 'kompulse:contact_need:editother', $lead->getPermissionUser()),
            'delete' => $security->hasEntityAccess('kompulse:contact_need:deleteown', 'kompulse:contact_need:deleteown', $lead->getPermissionUser()),
        ];

        if ($closeModal) {
            //just close the modal
            $passthroughVars = [
                'closeModal'    => 1,
                'mauticContent' => 'contactNeed',
            ];

            if ($valid && !$cancelled) {
                $passthroughVars['upContactNeedCount'] = 1;
                $passthroughVars['contactNeedHtml']    = $this->renderView(
                    'KompulseNeedBundle:ContactNeed:contact_need.html.php',
                    [
                        'contactNeed' => $contactNeed,
                        'lead'        => $lead,
                        'permissions' => $permissions,
                    ]
                );
                $passthroughVars['contactNeedId'] = $contactNeed->getId();
            }

            $response = new JsonResponse($passthroughVars);

            return $response;
        } else {
            return $this->delegateView(
                [
                    'viewParameters' => [
                        'form'        => $form->createView(),
                        'lead'        => $lead,
                        'permissions' => $permissions,
                    ],
                    'contentTemplate' => 'KompulseNeedBundle:ContactNeed:form.html.php',
                ]
            );
        }
    }

    /**
     * Deletes the entity.
     *
     * @param   $objectId
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction($leadId, $objectId)
    {
        $lead = $this->checkLeadAccess($leadId, 'view');
        if ($lead instanceof Response) {
            return $lead;
        }

        $model = $this->getModel('kompulse.contactneed');
        $contactNeed  = $model->getEntity($objectId);

        if ($contactNeed === null) {
            return $this->notFound();
        }

        if (
            !$this->get('mautic.security')->hasEntityAccess('kompulse:contact_need:editown', 'kompulse:contact_need:editother', $lead->getPermissionUser())
            || $model->isLocked($contactNeed)
        ) {
            return $this->accessDenied();
        }

        $model->deleteEntity($contactNeed);

        $response = new JsonResponse(
            [
                'deleteId'      => $objectId,
                'mauticContent' => 'contactNeed',
                'downContactNeedCount' => 1,
            ]
        );

        return $response;
    }

    /**
     *
     */
    public function editAction($leadId, $objectId)
    {
        $lead = $this->checkLeadAccess($leadId, 'view');
        if ($lead instanceof Response) {
            return $lead;
        }

        $model      = $this->getModel('kompulse.contactneed');
        $contactNeed = $model->getEntity($objectId);
        $closeModal = false;
        $valid      = false;

        if ($contactNeed === null || !$this->get('mautic.security')->hasEntityAccess('kompulse:contact_need:editown', 'kompulse:contact_need:editother', $lead->getPermissionUser())) {
            return $this->accessDenied();
        }

        $action = $this->generateUrl(
            'mautic_contactneed_action',
            [
                'objectAction' => 'edit',
                'objectId'     => $objectId,
                'leadId'       => $leadId,
            ]
        );
        $form = $model->createForm($contactNeed, $this->get('form.factory'), $action);

        ///Check for a submitted form and process it
        if ($this->request->getMethod() == 'POST') {
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    //form is valid so process the data
                    $model->saveEntity($contactNeed);
                    $closeModal = true;
                }
            } else {
                $closeModal = true;
            }
        }

        $security    = $this->get('mautic.security');
        $permissions = [
            'edit'   => $security->hasEntityAccess('kompulse:contact_need:editown', 'kompulse:contact_need:editother', $lead->getPermissionUser()),
            'delete' => $security->hasEntityAccess('kompulse:contact_need:deleteown', 'kompulse:contact_need:deleteown', $lead->getPermissionUser()),
        ];

        if ($closeModal) {
            //just close the modal
            $passthroughVars['closeModal'] = 1;

            if ($valid && !$cancelled) {
                $passthroughVars['contactNeedHtml'] = $this->renderView(
                    'KompulseNeedBundle:ContactNeed:contact_need.html.php',
                    [
                        'contactNeed' => $contactNeed,
                        'lead'        => $lead,
                        'permissions' => $permissions,
                    ]
                );
                $passthroughVars['contactNeedId'] = $contactNeed->getId();
            }

            $passthroughVars['mauticContent'] = 'contactNeed';

            $response = new JsonResponse($passthroughVars);

            return $response;
        } else {
            return $this->delegateView(
                [
                    'viewParameters' => [
                        'form'        => $form->createView(),
                        'lead'        => $lead,
                        'permissions' => $permissions,
                        'contactNeed' => $contactNeed,
                    ],
                    'contentTemplate' => 'KompulseNeedBundle:ContactNeed:form.html.php',
                ]
            );
        }
    }

    /**
     * Executes an action defined in route.
     *
     * @param     $objectAction
     * @param int $objectId
     * @param int $leadId
     *
     * @return Response
     */
    public function executeNeedAction($objectAction, $objectId = 0, $leadId = 0)
    {
        if (method_exists($this, "{$objectAction}Action")) {
            return $this->{"{$objectAction}Action"}($leadId, $objectId);
        } else {
            return $this->accessDenied();
        }
    }

}
