<?php
// src/KK/JobeetBundle/Controller/CategoryController.php

namespace KK\JobeetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use KK\JobeetBundle\Entity\Category;

/**
 * Category Controller
 */
class CategoryController extends Controller
{
    public function showAction($slug, $page)
    {
        $em = $this->getDoctrine()
            ->getManager();

        $category = $em->getRepository('KKJobeetBundle:Category')
            ->findOneBySlug($slug);

        if (!$category) {
            throw $this->createNotFoundException('Unable to find Category entity.');
        }

        $latestJob = $em->getRepository('KKJobeetBundle:Job')
            ->getLatestPost($category->getId());

        if ($latestJob) {
            $lastUpdated = $latestJob->getCreatedAt()
                ->format(DATE_ATOM);
        } else {
            $lastUpdated = new \DateTime();
            $lastUpdated = $lastUpdated->format(DATE_ATOM);
        }

        $total_jobs = $em->getRepository('KKJobeetBundle:Job')->countActiveJobs($category->getId());
        $jobs_per_page = $this->container->getParameter('max_jobs_on_category');
        $last_page = ceil($total_jobs / $jobs_per_page);
        $previous_page = $page > 1 ? $page - 1 : 1;
        $next_page = $page < $last_page ? $page + 1 : $last_page;
        $category->setActiveJobs($em->getRepository('KKJobeetBundle:Job')->getActiveJobs($category->getId(), $jobs_per_page, ($page - 1) * $jobs_per_page));

        $format = $this->getRequest()->getRequestFormat();

        return $this->render('KKJobeetBundle:Category:show.' . $format . '.twig', array(
            'category' => $category,
            'last_page' => $last_page,
            'previous_page' => $previous_page,
            'current_page' => $page,
            'next_page' => $next_page,
            'total_jobs' => $total_jobs,
            'feedId' => sha1($this->get('router')->generate('KKJobeetBundle_category', array('slug' => $category->getSlug(), 'format' => 'atom'), true)),
            'lastUpdated' => $lastUpdated
        ));
    }
}
