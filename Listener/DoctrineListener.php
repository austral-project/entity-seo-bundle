<?php
/*
 * This file is part of the Austral EntitySeo Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\EntitySeoBundle\Listener;

use Austral\EntitySeoBundle\Entity\Redirection;
use Austral\EntitySeoBundle\Services\PageUrlGenerator;
use Doctrine\Common\EventArgs;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\EntityManager;

/**
 * Austral Doctrine Listener.
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class DoctrineListener implements EventSubscriber
{

  /**
   * @var mixed
   */
  protected $name;

  /**
   * @var EntityManager
   */
  protected EntityManager $entityManager;

  /**
   * @var bool
   */
  protected bool $postFlush = false;

  /**
   * @var PageUrlGenerator
   */
  protected PageUrlGenerator $pageUrlGenerator;

  /**
   * DoctrineListener constructor.
   */
  public function __construct(PageUrlGenerator $pageUrlGenerator)
  {
    $parts = explode('\\', $this->getNamespace());
    $this->name = end($parts);
    $this->pageUrlGenerator = $pageUrlGenerator;
  }

  /**
   * @return array
   */
  public function getSubscribedEvents()
  {
    return array(
      'postLoad',
      'prePersist',
      'preUpdate',
      'postRemove',
      "postFlush"
    );
  }

  /**
   * @param EventArgs $args
   */
  public function postLoad(EventArgs $args)
  {
  }

  /**
   * @return bool
   */
  public static function initUrl()
  {
    return !(isset($_SERVER['AUSTRAL_INIT_URL_DISABLED']) && $_SERVER['AUSTRAL_INIT_URL_DISABLED']);
  }

  /**
   * @param LifecycleEventArgs $args
   *
   * @throws NonUniqueResultException
   */
  public function prePersist(LifecycleEventArgs $args)
  {
    $this->pageUrlGenerator->generateUrl($args->getObject(), $args);
  }

  /**
   * @param LifecycleEventArgs $args
   *
   * @throws NonUniqueResultException
   */
  public function preUpdate(LifecycleEventArgs $args)
  {
    if(!$this->postFlush)
    {
      $this->pageUrlGenerator->generateUrl($args->getObject(), $args);
    }
  }

  /**
   * @param LifecycleEventArgs $args
   *
   */
  public function postRemove(LifecycleEventArgs $args)
  {
  }

  /**
   * @param PostFlushEventArgs $args
   *
   */
  public function postFlush(PostFlushEventArgs $args)
  {
    if(!$this->postFlush)
    {
      $this->postFlush = true;
      $entityManager = $args->getEntityManager();
      foreach($this->pageUrlGenerator->getObjects() as $object)
      {
        $entityManager->persist($object['object']);
        if(array_key_exists("redirections", $object))
        {
          /** @var Redirection $redirection */
          foreach($object["redirections"] as $redirection)
          {
            if($redirection->getIsActive())
            {
              $entityManager->persist($redirection);
            }
            else
            {
              $entityManager->remove($redirection);
            }
          }
        }
      }
      $entityManager->flush();
    }
  }

  /**
   * Get an event adapter to handle event specific
   * methods
   *
   * @param EventArgs $args
   *
   * @return EventArgs
   */
  protected function getEventAdapter(EventArgs $args): EventArgs
  {
    return $args;
  }

  /**
   * @return string
   */
  protected function getNamespace(): string
  {
    return __NAMESPACE__;
  }

}