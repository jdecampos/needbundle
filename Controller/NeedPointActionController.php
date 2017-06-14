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
use MauticPlugin\KompulseNeedBundle\Entity\NeedPoint;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class NeedPointActionController.
 */
class NeedPointActionController extends FormController
{
    /**
     * @param int $page
     *
     * @return JsonResponse|Response
     */
    public function indexAction($page = 1)
    {
        //set some permissions
        $permissions = $this->get('mautic.security')->isGranted([
            'kompulse:need_points:view',
            'kompulse:need_points:create',
            'kompulse:need_points:edit',
            'kompulse:need_points:delete',
            'kompulse:need_points:publish',
        ], 'RETURN_ARRAY');

        if (!$permissions['kompulse:need_points:view']) {
            return $this->accessDenied();
        }

        if ($this->request->getMethod() == 'POST') {
            $this->setListFilters();
        }

        //set limits
        $limit = $this->get('session')->get(
            'mautic.point.limit', $this->coreParametersHelper->getParameter('default_pagelimit')
        );
        $start = ($page === 1) ? 0 : (($page - 1) * $limit);
        if ($start < 0) {
            $start = 0;
        }

        $search = $this->request->get('search', $this->get('session')->get('mautic.point.filter', ''));
        $this->get('session')->set('mautic.point.filter', $search);

        $filter     = ['string' => $search, 'force' => []];
        $orderBy    = $this->get('session')->get('mautic.point.orderby', 'e.name');
        $orderByDir = $this->get('session')->get('mautic.point.orderbydir', 'ASC');

        $points = $this->getModel('kompulse.need_point')->getEntities([
            'start'      => $start,
            'limit'      => $limit,
            'filter'     => $filter,
            //    'orderBy'    => $orderBy,
            'orderByDir' => $orderByDir,
        ]);

        $count = count($points);
        if ($count && $count < ($start + 1)) {
            $lastPage = ($count === 1) ? 1 : (ceil($count / $limit)) ?: 1;
            $this->get('session')->set('mautic.point.page', $lastPage);
            $returnUrl = $this->generateUrl('kompulse_point_action_index', ['page' => $lastPage]);

            return $this->postActionRedirect([
                'returnUrl'       => $returnUrl,
                'viewParameters'  => ['page' => $lastPage],
                'contentTemplate' => 'KompulseNeedBundle:NeedPointAction:index',
                'passthroughVars' => [
                    'activeLink'    => '#kompulse_point_action_index',
                    'mauticContent' => 'need_point',
                ],
            ]);
        }

        //set what page currently on so that we can return here after form submission/cancellation
        $this->get('session')->set('mautic.point.page', $page);

        $tmpl = $this->request->isXmlHttpRequest() ? $this->request->get('tmpl', 'index') : 'index';

        //get the list of actions
        $actions = $this->getModel('kompulse.need_point')->getPointActions();

        return $this->delegateView([
            'viewParameters' => [
                'searchValue' => $search,
                'items'       => $points,
                'actions'     => $actions['actions'],
                'page'        => $page,
                'limit'       => $limit,
                'permissions' => $permissions,
                'tmpl'        => $tmpl,
            ],
            'contentTemplate' => 'KompulseNeedBundle:NeedPointAction:list.html.php',
            'passthroughVars' => [
                'activeLink'    => '#kompulse_point_action_index',
                'mauticContent' => 'need_point',
                'route'         => $this->generateUrl('kompulse_point_action_index', ['page' => $page]),
            ],
        ]);
    }

    /**
     * Generates new form and processes post data.
     *
     * @param \MauticPlugin\KompulseNeedBundle\Entity\NeedPoint $entity
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function newAction($entity = null)
    {
        $model = $this->getModel('kompulse.need_point');

        if (!($entity instanceof NeedPoint)) {
            /** @var \MauticPlugin\KompulseNeedBundle\Entity\NeedPoint $entity */
            $entity = $model->getEntity();
        }

        if (!$this->get('mautic.security')->isGranted('kompulse:need_points:create')) {
            return $this->accessDenied();
        }

        //set the page we came from
        $page = $this->get('session')->get('mautic.point.page', 1);

        $actionType = ($this->request->getMethod() == 'POST') ? $this->request->request->get('point[type]', '', true) : '';

        $action  = $this->generateUrl('kompulse_point_action_action', ['objectAction' => 'new']);
        $actions = $model->getPointActions();
        $form    = $model->createForm($entity, $this->get('form.factory'), $action, [
            'pointActions' => $actions,
            'actionType'   => $actionType,
        ]);
        $viewParameters = ['page' => $page];

        ///Check for a submitted form and process it
        if ($this->request->getMethod() == 'POST') {
            $valid = false;
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    //form is valid so process the data
                    $model->saveEntity($entity);

                    $this->addFlash('mautic.core.notice.created', [
                        '%name%'      => $entity->getName(),
                        '%menu_link%' => 'kompulse_point_action_index',
                        '%url%'       => $this->generateUrl('kompulse_point_action_action', [
                            'objectAction' => 'edit',
                            'objectId'     => $entity->getId(),
                        ]),
                    ]);

                    if ($form->get('buttons')->get('save')->isClicked()) {
                        $returnUrl = $this->generateUrl('kompulse_point_action_index', $viewParameters);
                        $template  = 'KompulseNeedBundle:NeedPointAction:index';
                    } else {
                        //return edit view so that all the session stuff is loaded
                        return $this->editAction($entity->getId(), true);
                    }
                }
            } else {
                $returnUrl = $this->generateUrl('kompulse_point_action_index', $viewParameters);
                $template  = 'KompulseNeedBundle:NeedPointAction:index';
            }

            if ($cancelled || ($valid && $form->get('buttons')->get('save')->isClicked())) {
                return $this->postActionRedirect([
                    'returnUrl'       => $returnUrl,
                    'viewParameters'  => $viewParameters,
                    'contentTemplate' => $template,
                    'passthroughVars' => [
                        'activeLink'    => '#kompulse_point_action_index',
                        'mauticContent' => 'need_point',
                    ],
                ]);
            }
        }

        $themes = ['KompulseNeedBundle:FormTheme\Action'];
        if ($actionType && !empty($actions['actions'][$actionType]['formTheme'])) {
            $themes[] = $actions['actions'][$actionType]['formTheme'];
        }

        return $this->delegateView([
            'viewParameters' => [
                'tmpl'    => $this->request->isXmlHttpRequest() ? $this->request->get('tmpl', 'index') : 'index',
                'entity'  => $entity,
                'form'    => $this->setFormTheme($form, 'KompulseNeedBundle:NeedPointAction:form.html.php', $themes),
                'actions' => $actions['actions'],
            ],
            'contentTemplate' => 'KompulseNeedBundle:NeedPointAction:form.html.php',
            'passthroughVars' => [
                'activeLink'    => '#kompulse_point_action_index',
                'mauticContent' => 'need_point',
                'route'         => $this->generateUrl('kompulse_point_action_action', [
                        'objectAction' => (!empty($valid) ? 'edit' : 'new'), //valid means a new form was applied
                        'objectId'     => $entity->getId(),
                    ]
                ),
            ],
        ]);
    }

    /**
     * Generates edit form and processes post data.
     *
     * @param int  $objectId
     * @param bool $ignorePost
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function editAction($objectId, $ignorePost = false)
    {
        $model  = $this->getModel('kompulse.need_point');
        $entity = $model->getEntity($objectId);

        //set the page we came from
        $page = $this->get('session')->get('mautic.point.page', 1);

        $viewParameters = ['page' => $page];

        //set the return URL
        $returnUrl = $this->generateUrl('mautic_point_index', ['page' => $page]);

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => $viewParameters,
            'contentTemplate' => 'KompulseNeedBundle:NeedPointAction:index',
            'passthroughVars' => [
                'activeLink'    => '#kompulse_point_action_index',
                'mauticContent' => 'need_point',
            ],
        ];

        //form not found
        if ($entity === null) {
            return $this->postActionRedirect(
                array_merge($postActionVars, [
                    'flashes' => [
                        [
                            'type'    => 'error',
                            'msg'     => 'mautic.need_point.error.notfound',
                            'msgVars' => ['%id%' => $objectId],
                        ],
                    ],
                ])
            );
        } elseif (!$this->get('mautic.security')->isGranted('kompulse:need_points:edit')) {
            return $this->accessDenied();
        } elseif ($model->isLocked($entity)) {
            //deny access if the entity is locked
            return $this->isLocked($postActionVars, $entity, 'need_point');
        }

        $actionType = ($this->request->getMethod() == 'POST') ? $this->request->request->get('point[type]', '', true) : $entity->getType();

        $action  = $this->generateUrl('kompulse_point_action_action', ['objectAction' => 'edit', 'objectId' => $objectId]);
        $actions = $model->getPointActions();
        $form    = $model->createForm($entity, $this->get('form.factory'), $action, [
            'pointActions' => $actions,
            'actionType'   => $actionType,
        ]);

        ///Check for a submitted form and process it
        if (!$ignorePost && $this->request->getMethod() == 'POST') {
            $valid = false;
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    //form is valid so process the data
                    $model->saveEntity($entity, $form->get('buttons')->get('save')->isClicked());

                    $this->addFlash('mautic.core.notice.updated', [
                        '%name%'      => $entity->getName(),
                        '%menu_link%' => 'kompulse_point_action_index',
                        '%url%'       => $this->generateUrl('kompulse_point_action_action', [
                            'objectAction' => 'edit',
                            'objectId'     => $entity->getId(),
                        ]),
                    ]);

                    if ($form->get('buttons')->get('save')->isClicked()) {
                        $returnUrl = $this->generateUrl('kompulse_point_action_index', $viewParameters);
                        $template  = 'KompulseNeedBundle:NeedPointAction:index';
                    }
                }
            } else {
                //unlock the entity
                $model->unlockEntity($entity);

                $returnUrl = $this->generateUrl('kompulse_point_action_index', $viewParameters);
                $template  = 'KompulseNeedBundle:NeedPointAction:index';
            }

            if ($cancelled || ($valid && $form->get('buttons')->get('save')->isClicked())) {
                return $this->postActionRedirect(
                    array_merge($postActionVars, [
                        'returnUrl'       => $returnUrl,
                        'viewParameters'  => $viewParameters,
                        'contentTemplate' => $template,
                    ])
                );
            }
        } else {
            //lock the entity
            $model->lockEntity($entity);
        }

        $themes = ['KompulseNeedBundle:FormTheme\Action'];
        if (!empty($actions['actions'][$actionType]['formTheme'])) {
            $themes[] = $actions['actions'][$actionType]['formTheme'];
        }

        return $this->delegateView([
            'viewParameters' => [
                'tmpl'    => $this->request->isXmlHttpRequest() ? $this->request->get('tmpl', 'index') : 'index',
                'entity'  => $entity,
                'form'    => $this->setFormTheme($form, 'KompulseNeedBundle:NeedPointAction:form.html.php', $themes),
                'actions' => $actions['actions'],
            ],
            'contentTemplate' => 'KompulseNeedBundle:NeedPointAction:form.html.php',
            'passthroughVars' => [
                'activeLink'    => '#kompulse_point_action_index',
                'mauticContent' => 'need_point',
                'route'         => $this->generateUrl('kompulse_point_action_action', [
                        'objectAction' => 'edit',
                        'objectId'     => $entity->getId(),
                    ]
                ),
            ],
        ]);
    }

    // /**
    //  * Clone an entity.
    //  *
    //  * @param int $objectId
    //  *
    //  * @return array|JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|Response
    //  */
    // public function cloneAction($objectId)
    // {
    //     $model  = $this->getModel('point');
    //     $entity = $model->getEntity($objectId);

    //     if ($entity != null) {
    //         if (!$this->get('mautic.security')->isGranted('point:points:create')) {
    //             return $this->accessDenied();
    //         }

    //         $entity = clone $entity;
    //         $entity->setIsPublished(false);
    //     }

    //     return $this->newAction($entity);
    // }

    /**
     * Deletes the entity.
     *
     * @param int $objectId
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction($objectId)
    {
        $page      = $this->get('session')->get('mautic.point.page', 1);
        $returnUrl = $this->generateUrl('kompulse_point_action_index', ['page' => $page]);
        $flashes   = [];

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'KompulseNeedBundle:NeedPointAction:index',
            'passthroughVars' => [
                'activeLink'    => '#kompulse_point_action_index',
                'mauticContent' => 'need_point',
            ],
        ];

        if ($this->request->getMethod() == 'POST') {
            $model  = $this->getModel('kompulse.need_point');
            $entity = $model->getEntity($objectId);

            if ($entity === null) {
                $flashes[] = [
                    'type'    => 'error',
                    'msg'     => 'mautic.point.error.notfound',
                    'msgVars' => ['%id%' => $objectId],
                ];
            } elseif (!$this->get('mautic.security')->isGranted('kompulse:need_points:delete')) {
                return $this->accessDenied();
            } elseif ($model->isLocked($entity)) {
                return $this->isLocked($postActionVars, $entity, 'kompulse.need_point');
            }

            $model->deleteEntity($entity);

            $identifier = $this->get('translator')->trans($entity->getName());
            $flashes[]  = [
                'type'    => 'notice',
                'msg'     => 'mautic.core.notice.deleted',
                'msgVars' => [
                    '%name%' => $identifier,
                    '%id%'   => $objectId,
                ],
            ];
        } //else don't do anything

        return $this->postActionRedirect(
            array_merge($postActionVars, [
                'flashes' => $flashes,
            ])
        );
    }

    /**
     * Deletes a group of entities.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function batchDeleteAction()
    {
        $page      = $this->get('session')->get('mautic.point.page', 1);
        $returnUrl = $this->generateUrl('kompulse_point_action_index', ['page' => $page]);
        $flashes   = [];

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'KompulseNeedBundle:NeedPointAction:index',
            'passthroughVars' => [
                'activeLink'    => '#kompulse_point_action_index',
                'mauticContent' => 'need_point',
            ],
        ];

        if ($this->request->getMethod() == 'POST') {
            $model     = $this->getModel('kompulse.need_point');
            $ids       = json_decode($this->request->query->get('ids', '{}'));
            $deleteIds = [];

            // Loop over the IDs to perform access checks pre-delete
            foreach ($ids as $objectId) {
                $entity = $model->getEntity($objectId);

                if ($entity === null) {
                    $flashes[] = [
                        'type'    => 'error',
                        'msg'     => 'mautic.point.error.notfound',
                        'msgVars' => ['%id%' => $objectId],
                    ];
                } elseif (!$this->get('mautic.security')->isGranted('kompulse:need_points:delete')) {
                    $flashes[] = $this->accessDenied(true);
                } elseif ($model->isLocked($entity)) {
                    $flashes[] = $this->isLocked($postActionVars, $entity, 'kompulse.need_point', true);
                } else {
                    $deleteIds[] = $objectId;
                }
            }

            // Delete everything we are able to
            if (!empty($deleteIds)) {
                $entities = $model->deleteEntities($deleteIds);

                $flashes[] = [
                    'type'    => 'notice',
                    'msg'     => 'mautic.point.notice.batch_deleted',
                    'msgVars' => [
                        '%count%' => count($entities),
                    ],
                ];
            }
        } //else don't do anything

        return $this->postActionRedirect(
            array_merge($postActionVars, [
                'flashes' => $flashes,
            ])
        );
    }
}
