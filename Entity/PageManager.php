<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Sonata\PageBundle\Entity;

use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\BlockInterface;
use Sonata\PageBundle\Model\SnapshotInterface;

use Application\Sonata\PageBundle\Entity\Page;
use Doctrine\ORM\EntityManager;

class PageManager implements PageManagerInterface
{
    protected $entityManager;

    protected $snapshotHandler;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;

        $class = 'Application\Sonata\PageBundle\Entity\Page';
        if (class_exists($class)) {
            $this->repository = $this->entityManager->getRepository($class);
        }
    }

    /**
     * return a page with the given routeName
     *
     * @param string $routeName
     * @return PageInterface|false
     */
    public function getPageByName($routeName)
    {
        $pages = $this->entityManager->createQueryBuilder()
            ->select('p, t')
            ->from('Application\Sonata\PageBundle\Entity\Page', 'p')
            ->where('p.routeName = :routeName')
            ->leftJoin('p.template', 't')
            ->setParameters(array(
                'routeName' => $routeName
            ))
            ->getQuery()
            ->execute();

        return count($pages) > 0 ? $pages[0] : false;
    }

    /**
     * return a page with the give slug
     *
     * @param string $routeName
     * @return PageInterface
     */
    public function getPageBySlug($slug)
    {
        $pages = $this->entityManager->createQueryBuilder()
            ->select('p')
            ->from('Application\Sonata\PageBundle\Entity\Page', 'p')
            ->leftJoin('p.template', 't')
            ->where('p.slug = :slug')
            ->setParameters(array(
                'slug' => $slug
            ))
            ->getQuery()
            ->execute();


        return count($pages) > 0 ? $pages[0] : false;
    }

    public function getDefaultTemplate()
    {
        $templates = $this->entityManager->createQueryBuilder()
            ->select('t')
            ->from('Application\Sonata\PageBundle\Entity\Template', 't')
            ->where('t.id = :id')
            ->setParameters(array(
                 'id' => 1
            ))
            ->getQuery()
            ->execute();

        return count($templates) > 0 ? $templates[0] : false;
    }

    public function createNewPage(array $defaults = array())
    {
        // create a new page for this routing
        $page = $this->getNewInstance();
        $page->setTemplate(isset($defaults['template']) ? $defaults['template'] : null);
        $page->setEnabled(isset($defaults['enabled']) ? $defaults['enabled'] : true);
        $page->setRouteName(isset($defaults['routeName']) ? $defaults['routeName'] : null);
        $page->setName(isset($defaults['name']) ? $defaults['name'] : null);
        $page->setLoginRequired(isset($defaults['loginRequired']) ? $defaults['loginRequired'] : null);
        $page->setSlug(isset($defaults['slug']) ? $defaults['slug'] : null);
        $page->setCreatedAt(new \DateTime);
        $page->setUpdatedAt(new \DateTime);

        return $page;
    }

    public function save(PageInterface $page)
    {
        $this->entityManager->persist($page);
        $this->entityManager->flush();

        return $page;
    }

    /**
     * @return \Sonata\PageBundle\Model\PageInterface
     */
    public function getNewInstance()
    {
        return new Page;
    }

    public function findBy(array $criteria = array())
    {
        return $this->repository->findBy($criteria);
    }

    public function findOneBy(array $criteria = array())
    {
        return $this->repository->findOneBy($criteria);
    }
}