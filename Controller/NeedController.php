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
use MauticPlugin\KompulseNeedBundle\Model\NeedModel;
use MauticPlugin\KompulseNeedBundle\Entity\Need;
use Symfony\Component\HttpFoundation\JsonResponse;

class NeedController extends FormController
{
    /**
     * @param     $bundle
     * @param int $page
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function indexAction($page = 1)
    {
        $session = $this->get('session');

        if ($this->request->getMethod() == 'POST') {
            $this->setListFilters();
        }

        //set limits
        $limit = $session->get('mautic.need.limit', $this->coreParametersHelper->getParameter('default_pagelimit'));

        $start = ($page === 1) ? 0 : (($page - 1) * $limit);
        if ($start < 0) {
            $start = 0;
        }

        $orderBy    = $this->get('session')->get('mautic.need.orderby', 'n.name');
        $orderByDir = $this->get('session')->get('mautic.need.orderbydir', 'DESC');
        $filter     = $this->request->get('search', $this->get('session')->get('mautic.role.filter', ''));
        $this->get('session')->set('mautic.role.filter', $filter);
        $tmpl  = $this->request->isXmlHttpRequest() ? $this->request->get('tmpl', 'index') : 'index';
        $model = $this->getModel('kompulse.need');

        $items = $model->getEntities(
            [
                'start'      => $start,
                'limit'      => $limit,
                'filter'     => $filter,
                'orderBy'    => $orderBy,
                'orderByDir' => $orderByDir,
            ]
        );

        $count = count($items);
        if ($count && $count < ($start + 1)) {
            //the number of entities are now less then the current page so redirect to the last page
            $lastPage = ($count === 1) ? 1 : (ceil($count / $limit)) ?: 1;
            $this->get('session')->set('mautic.need.page', $lastPage);
            $returnUrl = $this->generateUrl('plugin_kompulse_index', ['page' => $lastPage]);

            return $this->postActionRedirect([
                'returnUrl'      => $returnUrl,
                'viewParameters' => [
                    'page' => $lastPage,
                    'tmpl' => $tmpl,
                ],
                'contentTemplate' => 'KompulseNeedBundle:Need:index',
                'passthroughVars' => [
                    'activeLink'    => '#kompulse_need_index',
                    'mauticContent' => 'need',
                ],
            ]);
        }

        foreach ($items as $need) {
            $needIds[] = $need->getId();
        }

        $userCounts = (!empty($needIds)) ? $model->getRepository()->getUserCount($needIds) : [];

        //set what page currently on so that we can return here after form submission/cancellation
        $this->get('session')->set('mautic.need.page', $page);

        //set some permissions
        $permissions = [
            'create' => $this->get('mautic.security')->isGranted('kompulse:need:create'),
            'edit'   => $this->get('mautic.security')->isGranted('kompulse:need:edit'),
            'delete' => $this->get('mautic.security')->isGranted('kompulse:need:delete'),
        ];

        $parameters = [
            'items'       => $items,
            'userCounts'  => $userCounts,
            'searchValue' => $filter,
            'page'        => $page,
            'limit'       => $limit,
            'permissions' => $permissions,
            'tmpl'        => $tmpl,
        ];

        return $this->delegateView([
            'viewParameters'  => $parameters,
            'contentTemplate' => 'KompulseNeedBundle:Need:list.html.php',
            'passthroughVars' => [
                'route'         => $this->generateUrl('plugin_kompulse_index', ['page' => $page]),
                'mauticContent' => 'role',
            ],
        ]);
    }

    /**
     * Generate's new need form and processes post data.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newAction()
    {
        if (!$this->get('mautic.security')->isGranted('kompulse:need:create')) {
            return $this->accessDenied();
        }

        //retrieve the entity
        $entity = new Need();
        $model  = $this->getModel('kompulse.need');

        //set the return URL for post actions
        $returnUrl = $this->generateUrl('plugin_kompulse_index');

        //set the page we came from
        $page   = $this->get('session')->get('mautic.need.page', 1);
        $action = $this->generateUrl('kompulse_need_action', ['objectAction' => 'new']);

        //get the user form factory
        $form              = $model->createForm($entity, $this->get('form.factory'), $action);

        ///Check for a submitted form and process it
        if ($this->request->getMethod() == 'POST') {
            $valid = false;
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {

                    //form is valid so process the data
                    $model->saveEntity($entity);

                    $this->addFlash('mautic.core.notice.created', [
                        '%name%'      => $entity->getName(),
                        '%menu_link%' => 'kompulse_need_index',
                        '%url%'       => $this->generateUrl('kompulse_need_action', [
                            'objectAction' => 'edit',
                            'objectId'     => $entity->getId(),
                        ]),
                    ]);
                }
            }

            if ($cancelled || ($valid && $form->get('buttons')->get('save')->isClicked())) {
                return $this->postActionRedirect([
                    'returnUrl'       => $returnUrl,
                    'viewParameters'  => ['page' => $page],
                    'contentTemplate' => 'KompulseNeedBundle:Need:index',
                    'passthroughVars' => [
                        'activeLink'    => '#kompulse_need_index',
                        'mauticContent' => 'need',
                    ],
                ]);
            } else {
                return $this->editAction($entity->getId(), true);
            }
        }

        return $this->delegateView([
            'viewParameters' => [
                'form'              => $this->setFormTheme($form, 'KompulseNeedBundle:Need:form.html.php', 'KompulseNeedBundle:FormTheme\Need'),
            ],
            'contentTemplate' => 'KompulseNeedBundle:Need:form.html.php',
            'passthroughVars' => [
                'activeLink'     => '#kompulse_need_new',
                'route'          => $this->generateUrl('kompulse_need_action', ['objectAction' => 'new']),
                'mauticContent'  => 'need',
            ],
        ]);
    }

    /**
     * Generate's need edit form and processes post data.
     *
     * @param int  $objectId
     * @param bool $ignorePost
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction($objectId, $ignorePost = true)
    {
        if (!$this->get('mautic.security')->isGranted('kompulse:needs:edit')) {
            return $this->accessDenied();
        }

        /** @var \MauticPlugin\KompulseNeedBundle\Model\NeedModel $model */
        $model  = $this->getModel('kompulse.need');
        $entity = $model->getEntity($objectId);

        //set the page we came from
        $page = $this->get('session')->get('mautic.need.page', 1);

        //set the return URL
        $returnUrl = $this->generateUrl('kompulse_need_index', ['page' => $page]);

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'KompulseNeedBundle:Need:index',
            'passthroughVars' => [
                'activeLink'    => '#kompulse_need_index',
                'mauticContent' => 'need',
            ],
        ];

        //need not found
        if ($entity === null) {
            return $this->postActionRedirect(
                array_merge($postActionVars, [
                    'flashes' => [
                        [
                            'type'    => 'error',
                            'msg'     => 'mautic.kompulse.need.error.notfound',
                            'msgVars' => ['%id' => $objectId],
                        ],
                    ],
                ])
            );
        } elseif ($model->isLocked($entity)) {
            //deny access if the entity is locked
            return $this->isLocked($postActionVars, $entity, 'kompulse.need');
        }

        $action            = $this->generateUrl('kompulse_need_action', ['objectAction' => 'edit', 'objectId' => $objectId]);
        $form              = $model->createForm($entity, $this->get('form.factory'), $action);

        ///Check for a submitted form and process it
        if (!$ignorePost && $this->request->getMethod() == 'POST') {
            $valid = false;
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    //set the permissions

                    //form is valid so process the data
                    $model->saveEntity($entity, $form->get('buttons')->get('save')->isClicked());

                    $this->addFlash('mautic.core.notice.updated', [
                        '%name%'      => $entity->getName(),
                        '%menu_link%' => 'kompulse_need_index',
                        '%url%'       => $this->generateUrl('kompulse_need_action', [
                            'objectAction' => 'edit',
                            'objectId'     => $entity->getId(),
                        ]),
                    ]);
                }
            } else {
                //unlock the entity
                $model->unlockEntity($entity);
            }

            if ($cancelled || ($valid && $form->get('buttons')->get('save')->isClicked())) {
                return $this->postActionRedirect($postActionVars);
            } else {
                //the form has to be rebuilt because the permissions were updated
                $permissionsConfig = $this->getPermissionsConfig($entity);
                $form              = $model->createForm($entity, $this->get('form.factory'), $action);
            }
        } else {
            //lock the entity
            $model->lockEntity($entity);
        }

        return $this->delegateView([
            'viewParameters' => [
                'form'              => $this->setFormTheme($form, 'KompulseNeedBundle:Need:form.html.php', 'KompulseNeedBundle:FormTheme\Need'),
            ],
            'contentTemplate' => 'KompulseNeedBundle:Need:form.html.php',
            'passthroughVars' => [
                'activeLink'     => '#kompulse_need_index',
                'route'          => $action,
                'mauticContent'  => 'need',
            ],
        ]);
    }

    /**
     * @param Entity\Need $need
     *
     * @return array
     */
    private function getPermissionsConfig(Need $need)
    {

    }

    /**
     * Delete's a need.
     *
     * @param int $objectId
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction($objectId)
    {
        if (!$this->get('mautic.security')->isGranted('kompulse:need:delete')) {
            return $this->accessDenied();
        }

        $page           = $this->get('session')->get('mautic.need.page', 1);
        $returnUrl      = $this->generateUrl('kompulse_need_index', ['page' => $page]);
        $success        = 0;
        $flashes        = [];
        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'KompulseNeedBundle:Need:index',
            'passthroughVars' => [
                'activeLink'    => '#kompulse_need_index',
                'success'       => $success,
                'mauticContent' => 'need',
            ],
        ];

        if ($this->request->getMethod() == 'POST') {
            try {
                $model  = $this->getModel('kompulse.need');
                $entity = $model->getEntity($objectId);

                if ($entity === null) {
                    $flashes[] = [
                        'type'    => 'error',
                        'msg'     => 'kompulse.need.error.notfound',
                        'msgVars' => ['%id%' => $objectId],
                    ];
                } elseif ($model->isLocked($entity)) {
                    return $this->isLocked($postActionVars, $entity, 'user.need');
                } else {
                    $model->deleteEntity($entity);
                    $name      = $entity->getName();
                    $flashes[] = [
                        'type'    => 'notice',
                        'msg'     => 'mautic.core.notice.deleted',
                        'msgVars' => [
                            '%name%' => $name,
                            '%id%'   => $objectId,
                        ],
                    ];
                }
            } catch (PreconditionRequiredHttpException $e) {
                $flashes[] = [
                    'type' => 'error',
                    'msg'  => $e->getMessage(),
                ];
            }
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
        $page      = $this->get('session')->get('mautic.need.page', 1);
        $returnUrl = $this->generateUrl('kompulse_need_index', ['page' => $page]);
        $flashes   = [];

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'KompulseNeedBundle:Need:index',
            'passthroughVars' => [
                'activeLink'    => '#kompulse_need_index',
                'mauticContent' => 'need',
            ],
        ];

        if ($this->request->getMethod() == 'POST') {
            $model       = $this->getModel('kompulse.need');
            $ids         = json_decode($this->request->query->get('ids', ''));
            $deleteIds   = [];

            // Loop over the IDs to perform access checks pre-delete
            foreach ($ids as $objectId) {
                $entity = $model->getEntity($objectId);
                $contactNeeds  = $this->get('doctrine.orm.entity_manager')->getRepository('KompulseNeedBundle:ContactNeed')->findByNeed($entity);

                if ($entity === null) {
                    $flashes[] = [
                        'type'    => 'error',
                        'msg'     => 'kompulse.need.error.notfound',
                        'msgVars' => ['%id%' => $objectId],
                    ];
                } elseif (count($contactNeeds)) {
                    $flashes[] = [
                        'type'    => 'error',
                        'msg'     => 'kompulse.need.error.deletenotallowed',
                        'msgVars' => ['%name%' => $entity->getName()],
                    ];
                } elseif (!$this->get('mautic.security')->isGranted('kompulse:need:delete')) {
                    $flashes[] = $this->accessDenied(true);
                } elseif ($model->isLocked($entity)) {
                    $flashes[] = $this->isLocked($postActionVars, $entity, 'kompulse.need', true);
                } else {
                    $deleteIds[] = $objectId;
                }
            }

            // Delete everything we are able to
            if (!empty($deleteIds)) {
                $entities = $model->deleteEntities($deleteIds);

                $flashes[] = [
                    'type'    => 'notice',
                    'msg'     => 'kompulse.need.notice.batch_deleted',
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
