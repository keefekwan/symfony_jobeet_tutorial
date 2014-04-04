<?php

namespace KK\JobeetBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use KK\JobeetBundle\Entity\Job;
use KK\JobeetBundle\Form\JobType;

/**
 * Job controller.
 *
 */
class JobController extends Controller
{

    /**
     * Lists all Job entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $categories = $em->getRepository('KKJobeetBundle:Category')->getWithJobs();

        foreach($categories as $category)
        {
            $category->setActiveJobs($em->getRepository('KKJobeetBundle:Job')
                ->getActiveJobs($category->getId(), $this->container->getParameter('max_jobs_on_homepage')));
            $category->setMoreJobs($em->getRepository('KKJobeetBundle:Job')
                ->countActiveJobs($category->getId()) - $this->container->getParameter('max_jobs_on_homepage'));
        }

        $latestJob = $em->getRepository('KKJobeetBundle:Job')
            ->getLatestPost();

        if ($latestJob) {
            $lastUpdated = $latestJob->getCreatedAt()
                ->format(DATE_ATOM);
        } else {
            $lastUpdated = new \DateTime();
            $lastUpdated = $lastUpdated->format(DATE_ATOM);
        }

        $format = $this->get('request_stack')->getCurrentRequest()
            ->getRequestFormat();

        return $this->render('KKJobeetBundle:Job:index.'.$format.'.twig', array(
            'categories' => $categories,
            'lastUpdated' => $lastUpdated,
            'feedId' => sha1($this->get('router')->generate('kk_job', array('_format'=> 'atom'), true)),
        ));
    }

    /**
     * Finds and displays a Job entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()
            ->getManager();

        $entity = $em->getRepository('KKJobeetBundle:Job')
            ->getActiveJob($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Job entity.');
        }

        $session = $this->get('request_stack')->getCurrentRequest()
            ->getSession();

        // Fetch jobs already stored in the job history
        $jobs = $session->get('job_history', array());

        // Store the job as an array so we can put it in the session and avoid entity serialize errors
        $job = array(
            'id'           => $entity->getId(),
            'position'     => $entity->getPosition(),
            'company'      => $entity->getCompany(),
            'companyslug'  => $entity->getCompanySlug(),
            'locationslug' => $entity->getLocationSlug(),
            'positionslug' => $entity->getPositionSlug()
        );

        if (!in_array($job, $jobs)) {
            // Add the current job at the beginning of the array
            array_unshift($jobs, $job);

            // Store the new job history back in the session
            $session->set('job_history', array_slice($jobs, 0, 3));
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('KKJobeetBundle:Job:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView()
        ));
    }

    public function previewAction($token)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('KKJobeetBundle:Job')->findOneByToken($token);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Job entity.');
        }

        $deleteForm = $this->createDeleteForm($entity->getId());
        $publishForm = $this->createPublishForm($entity->getToken());

        return $this->render('KKJobeetBundle:Job:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
            'publish_form' => $publishForm->createView()
        ));
    }

    public function publishAction(Request $request, $token)
    {
        $form = $this->createPublishForm($token);
        $form->submit($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('KKJobeetBundle:Job')->findOneByToken($token);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Job entity.');
            }

            $entity->publish();
            $em->persist($entity);
            $em->flush();

            $this->get('session')->getFlashBag()->add('notice', 'Your job is now online for 30 days.');
        }

        return $this->redirect($this->generateUrl('kk_job_preview', array(
            'company' => $entity->getCompanySlug(),
            'location' => $entity->getLocationSlug(),
            'token' => $entity->getToken(),
            'position' => $entity->getPositionSlug()
        )));
    }

    private function createPublishForm($token)
    {
        return $this->createFormBuilder(array('token' => $token))
            ->add('token', 'hidden')
            ->getForm()
            ;
    }

    /**
     * Creates a new Job entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity  = new Job();
        $form = $this->createForm(new JobType(), $entity);
        $form->submit($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('kk_job_preview', array(
                'company' => $entity->getCompanySlug(),
                'location' => $entity->getLocationSlug(),
                'token' => $entity->getToken(),
                'position' => $entity->getPositionSlug()
            )));
        }

        return $this->render('KKJobeetBundle:Job:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Displays a form to create a new Job entity.
     *
     */
    public function newAction()
    {
        $entity = new Job();
        $entity->setType('full-time');
        $form = $this->createForm(new JobType(), $entity);

        return $this->render('KKJobeetBundle:Job:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Displays a form to edit an existing Job entity.
     *
     */
    public function editAction($token)
    {
        $em = $this->getDoctrine()
            ->getManager();

        $entity = $em->getRepository('KKJobeetBundle:Job')
            ->findOneByToken($token);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Job entity.');
        }

        $editForm = $this->createForm(new JobType(), $entity);
        $deleteForm = $this->createDeleteForm($token);

        return $this->render('KKJobeetBundle:Job:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing Job entity.
     *
     */
    public function updateAction(Request $request, $token)
    {
        $em = $this->getDoctrine()
            ->getManager();

        $entity = $em->getRepository('KKJobeetBundle:Job')
            ->findOneByToken($token);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Job entity.');
        }

        $editForm   = $this->createForm(new JobType(), $entity);
        $deleteForm = $this->createDeleteForm($token);

        $editForm->submit($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('kk_job_preview', array(
                'company' => $entity->getCompanySlug(),
                'location' => $entity->getLocationSlug(),
                'token' => $entity->getToken(),
                'position' => $entity->getPositionSlug()
            )));
        }

        return $this->render('KKJobeetBundle:Job:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Job entity.
     *
     */
    public function deleteAction(Request $request, $token)
    {
        $form = $this->createDeleteForm($token);
        $form->submit($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()
                ->getManager();
            $entity = $em->getRepository('KKJobeetBundle:Job')
                ->findOneByToken($token);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Job entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('kk_job'));
    }

    /**
     * Creates a form to delete a Job entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($token)
    {
        return $this->createFormBuilder(array('token' => $token))
            ->add('token', 'hidden')
            ->getForm()
        ;
    }
}
