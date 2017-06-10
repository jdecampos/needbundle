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

use Mautic\CoreBundle\Controller\FormController as CommonFormController;
use MauticPlugin\KompulseNeedBundle\Entity\TriggerEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class TriggerEventController.
 */
class TriggerEventController extends CommonFormController
{
    /**
     * Generates new form and processes post data.
     *
     * @return JsonResponse
     */
    public function newAction()
    {
        $success = 0;
        $valid   = $cancelled   = false;
        $method  = $this->request->getMethod();
        $session = $this->get('session');

        if ($method == 'POST') {
            $triggerEvent = $this->request->request->get('pointtriggerevent');
            $eventType    = $triggerEvent['type'];
            $triggerId    = $triggerEvent['triggerId'];
        } else {
            $eventType = $this->request->query->get('type');
            $triggerId = $this->request->query->get('triggerId');

            $triggerEvent = [
                'type'      => $eventType,
                'triggerId' => $triggerId,
            ];
        }

        //ajax only for form fields
        if (!$eventType ||
            !$this->request->isXmlHttpRequest() ||
            !$this->get('mautic.security')->isGranted([
                'kompulse:triggers:edit',
                'kompulse:triggers:create',
            ], 'MATCH_ONE')
        ) {
            return $this->modalAccessDenied();
        }

        //fire the builder event
        $events = $this->getModel('kompulse.trigger')->getEvents();
        $form   = $this->get('form.factory')->create('pointtriggerevent', $triggerEvent, [
            'action'   => $this->generateUrl('kompulse_pointtriggerevent_action', ['objectAction' => 'new']),
            'settings' => $events[$eventType],
        ]);
        $form->get('triggerId')->setData($triggerId);
        $triggerEvent['settings'] = $events[$eventType];

        //Check for a submitted form and process it
        if ($method == 'POST') {
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    $success = 1;

                    //form is valid so process the data
                    $keyId = 'new'.hash('sha1', uniqid(mt_rand()));

                    //save the properties to session
                    $actions            = $session->get('kompulse.point.'.$triggerId.'.triggerevents.modified');
                    $formData           = $form->getData();
                    $triggerEvent       = array_merge($triggerEvent, $formData);
                    $triggerEvent['id'] = $keyId;
                    if (empty($triggerEvent['name'])) {
                        //set it to the event default
                        $triggerEvent['name'] = $this->get('translator')->trans($triggerEvent['settings']['label']);
                    }
                    $actions[$keyId] = $triggerEvent;
                    $session->set('kompulse.point.'.$triggerId.'.triggerevents.modified', $actions);
                }
            }
        }

        $viewParams = ['type' => $eventType];
        if ($cancelled || $valid) {
            $closeModal = true;
        } else {
            $form = (isset($triggerEvent['settings']['formTheme'])) ?
                $this->setFormTheme($form, 'KompulseNeedBundle:Event:form.html.php', $triggerEvent['settings']['formTheme']) :
                $form->createView();

            $closeModal                = false;
            $viewParams['form']        = $form;
            $header                    = $triggerEvent['settings']['label'];
            $viewParams['eventHeader'] = $this->get('translator')->trans($header);
        }

        $passthroughVars = [
            'mauticContent' => 'need_pointTriggerEvent',
            'success'       => $success,
            'route'         => false,
        ];

        if (!empty($keyId)) {
            //prevent undefined errors
            $entity       = new TriggerEvent();
            $blank        = $entity->convertToArray();
            $triggerEvent = array_merge($blank, $triggerEvent);

            $template = (empty($triggerEvent['settings']['template'])) ? 'KompulseNeedBundle:Event:generic.html.php'
                : $triggerEvent['settings']['template'];

            $passthroughVars['eventId']   = $keyId;
            $passthroughVars['eventHtml'] = $this->renderView($template, [
                'event'     => $triggerEvent,
                'id'        => $keyId,
                'sessionId' => $triggerId,
            ]);
        }

        if ($closeModal) {
            //just close the modal
            $passthroughVars['closeModal'] = 1;
            $response                      = new JsonResponse($passthroughVars);

            return $response;
        }

        return $this->ajaxAction([
            'contentTemplate' => 'KompulseNeedBundle:Event:form.html.php',
            'viewParameters'  => $viewParams,
            'passthroughVars' => $passthroughVars,
        ]);
    }

    /**
     * Generates edit form and processes post data.
     *
     * @param int $objectId
     *
     * @return JsonResponse
     */
    public function editAction($objectId)
    {
        $session      = $this->get('session');
        $method       = $this->request->getMethod();
        $triggerId    = ($method == 'POST') ? $this->request->request->get('pointtriggerevent[triggerId]', '', true) : $this->request->query->get('triggerId');
        $events       = $session->get('kompulse.point.'.$triggerId.'.triggerevents.modified', []);
        $success      = 0;
        $valid        = $cancelled        = false;
        $triggerEvent = (array_key_exists($objectId, $events)) ? $events[$objectId] : null;

        if ($triggerEvent !== null) {
            $eventType = $triggerEvent['type'];

            $events                   = $this->getModel('kompulse.trigger')->getEvents();
            $triggerEvent['settings'] = $events[$eventType];

            //ajax only for form fields
            if (!$eventType ||
                !$this->request->isXmlHttpRequest() ||
                !$this->get('mautic.security')->isGranted([
                    'kompulse:triggers:edit',
                    'kompulse:triggers:create',
                ], 'MATCH_ONE')
            ) {
                return $this->modalAccessDenied();
            }

            $form = $this->get('form.factory')->create('pointtriggerevent', $triggerEvent, [
                'action'   => $this->generateUrl('kompulse_pointtriggerevent_action', ['objectAction' => 'edit', 'objectId' => $objectId]),
                'settings' => $triggerEvent['settings'],
            ]);
            $form->get('triggerId')->setData($triggerId);
            //Check for a submitted form and process it
            if ($method == 'POST') {
                if (!$cancelled = $this->isFormCancelled($form)) {
                    if ($valid = $this->isFormValid($form)) {
                        $success = 1;

                        //form is valid so process the data

                        //save the properties to session
                        $session  = $this->get('session');
                        $events   = $session->get('kompulse.point.'.$triggerId.'.triggerevents.modified');
                        $formData = $form->getData();
                        //overwrite with updated data
                        $triggerEvent = array_merge($events[$objectId], $formData);
                        if (empty($triggerEvent['name'])) {
                            //set it to the event default
                            $triggerEvent['name'] = $this->get('translator')->trans($triggerEvent['settings']['label']);
                        }
                        $events[$objectId] = $triggerEvent;
                        $session->set('kompulse.point.'.$triggerId.'.triggerevents.modified', $events);

                        //generate HTML for the field
                        $keyId = $objectId;
                    }
                }
            }

            $viewParams = ['type' => $eventType];
            if ($cancelled || $valid) {
                $closeModal = true;
            } else {
                $form = (isset($triggerEvent['settings']['formTheme'])) ?
                      $this->setFormTheme($form, 'KompulseNeedBundle:Event:form.html.php', $triggerEvent['settings']['formTheme']) :
                    $form->createView();

                $closeModal                = false;
                $viewParams['form']        = $form;
                $viewParams['eventHeader'] = $this->get('translator')->trans($triggerEvent['settings']['label']);
            }

            $passthroughVars = [
                'mauticContent' => 'need_pointTriggerEvent',
                'success'       => $success,
                'route'         => false,
            ];

            if (!empty($keyId)) {
                $passthroughVars['eventId'] = $keyId;

                //prevent undefined errors
                $entity       = new TriggerEvent();
                $blank        = $entity->convertToArray();
                $triggerEvent = array_merge($blank, $triggerEvent);
                $template     = (empty($triggerEvent['settings']['template'])) ? 'KompulseNeedBundle:Event:generic.html.php'
                    : $triggerEvent['settings']['template'];

                $passthroughVars['eventId']   = $keyId;
                $passthroughVars['eventHtml'] = $this->renderView($template, [
                    'event'     => $triggerEvent,
                    'id'        => $keyId,
                    'sessionId' => $triggerId,
                ]);
            }

            if ($closeModal) {
                //just close the modal
                $passthroughVars['closeModal'] = 1;
                $response                      = new JsonResponse($passthroughVars);

                return $response;
            }

            return $this->ajaxAction([
                'contentTemplate' => 'KompulseNeedBundle:Event:form.html.php',
                'viewParameters'  => $viewParams,
                'passthroughVars' => $passthroughVars,
            ]);
        }

        $response = new JsonResponse(['success' => 0]);

        return $response;
    }

    /**
     * Deletes the entity.
     *
     * @param int $objectId
     *
     * @return JsonResponse
     */
    public function deleteAction($objectId)
    {
        $session   = $this->get('session');
        $triggerId = $this->request->get('triggerId');
        $events    = $session->get('kompulse.point.'.$triggerId.'.triggerevents.modified', []);
        $delete    = $session->get('kompulse.point.'.$triggerId.'.triggerevents.deleted', []);

        //ajax only for form fields
        if (!$this->request->isXmlHttpRequest() ||
            !$this->get('mautic.security')->isGranted([
                'kompulse:triggers:edit',
                'kompulse:triggers:create',
            ], 'MATCH_ONE')
        ) {
            return $this->accessDenied();
        }

        $triggerEvent = (array_key_exists($objectId, $events)) ? $events[$objectId] : null;

        if ($this->request->getMethod() == 'POST' && $triggerEvent !== null) {
            //add the field to the delete list
            if (!in_array($objectId, $delete)) {
                $delete[] = $objectId;
                $session->set('kompulse.point.'.$triggerId.'.triggerevents.deleted', $delete);
            }

            $template = (empty($triggerEvent['settings']['template'])) ? 'KompulseNeedBundle:Event:generic.html.php'
                : $triggerEvent['settings']['template'];

            //prevent undefined errors
            $entity       = new TriggerEvent();
            $blank        = $entity->convertToArray();
            $triggerEvent = array_merge($blank, $triggerEvent);

            $dataArray = [


                'mauticContent' => 'need_pointTriggerEvent',
                'success'       => 1,
                'target'        => '#triggerEvent'.$objectId,
                'route'         => false,
                'eventId'       => $objectId,
                'eventHtml'     => $this->renderView($template, [
                    'event'     => $triggerEvent,
                    'id'        => $objectId,
                    'deleted'   => true,
                    'sessionId' => $triggerId,
                ]),
            ];
        } else {
            $dataArray = ['success' => 0];
        }

        $response = new JsonResponse($dataArray);

        return $response;
    }

    /**
     * Undeletes the entity.
     *
     * @param int $objectId
     *
     * @return JsonResponse
     */
    public function undeleteAction($objectId)
    {
        $session   = $this->get('session');
        $triggerId = $this->request->get('triggerId');
        $events    = $session->get('kompulse.point.'.$triggerId.'.triggerevents.modified', []);
        $delete    = $session->get('kompulse.point.'.$triggerId.'.triggerevents.deleted', []);

        //ajax only for form fields
        if (!$this->request->isXmlHttpRequest() ||
            !$this->get('mautic.security')->isGranted([
                'kompulse:triggers:edit',
                'kompulse:triggers:create',
            ], 'MATCH_ONE')
        ) {
            return $this->accessDenied();
        }

        $triggerEvent = (array_key_exists($objectId, $events)) ? $events[$objectId] : null;

        if ($this->request->getMethod() == 'POST' && $triggerEvent !== null) {

            //add the field to the delete list
            if (in_array($objectId, $delete)) {
                $key = array_search($objectId, $delete);
                unset($delete[$key]);
                $session->set('kompulse.point.'.$triggerId.'.triggerevents.deleted', $delete);
            }

            $template = (empty($triggerEvent['settings']['template'])) ? 'KompulseNeedBundle:Event:generic.html.php'
                : $triggerEvent['settings']['template'];

            //prevent undefined errors
            $entity       = new TriggerEvent();
            $blank        = $entity->convertToArray();
            $triggerEvent = array_merge($blank, $triggerEvent);

            $dataArray = [
                'mauticContent' => 'need_pointTriggerEvent',
                'success'       => 1,
                'target'        => '#triggerEvent'.$objectId,
                'route'         => false,
                'eventId'       => $objectId,
                'eventHtml'     => $this->renderView($template, [
                    'event'     => $triggerEvent,
                    'id'        => $objectId,
                    'deleted'   => false,
                    'triggerId' => $triggerId,
                ]),
            ];
        } else {
            $dataArray = ['success' => 0];
        }

        $response = new JsonResponse($dataArray);

        return $response;
    }
}
