<?php
/*
 * This file is part of the Austral EntitySeo Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\EntitySeoBundle\Services;

use Austral\EntitySeoBundle\Configuration\EntitySeoConfiguration;
use Austral\EntitySeoBundle\Entity\Interfaces\EntitySeoInterface;
use Austral\EntitySeoBundle\Entity\Interfaces\RedirectionInterface;
use Austral\EntitySeoBundle\EntityManager\RedirectionEntityManager;
use Austral\EntityTranslateBundle\Entity\Interfaces\EntityTranslateChildInterface;
use Austral\EntityTranslateBundle\Entity\Interfaces\EntityTranslateMasterInterface;
use Austral\WebsiteBundle\Entity\Interfaces\PageInterface;

use Austral\EntityBundle\Entity\EntityInterface;

use Doctrine\Common\EventArgs;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;

/**
 * Austral Page url generator service.
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class PageUrlGenerator
{

  /**
   * @var EntityManagerInterface
   */
  protected EntityManagerInterface $entityManager;

  /**
   * @var array
   */
  protected array $objects = array();

  /**
   * @var string|null
   */
  protected ?string $homepageId = null;

  /**
   * @var EntitySeoConfiguration
   */
  protected EntitySeoConfiguration $entitySeoConfiguration;

  /**
   * @var RedirectionEntityManager
   */
  protected RedirectionEntityManager $redirectionManager;

  /**
   * PageUrlGenerator constructor.
   *
   * @param EntityManagerInterface $entityManager
   * @param EntitySeoConfiguration $entitySeoConfiguration
   * @param RedirectionEntityManager $redirectionManager
   */
  public function __construct(EntityManagerInterface $entityManager, EntitySeoConfiguration $entitySeoConfiguration, RedirectionEntityManager $redirectionManager)
  {
    $this->entityManager = $entityManager;
    $this->entitySeoConfiguration = $entitySeoConfiguration;
    $this->redirectionManager = $redirectionManager;
  }

  /**
   * @param EntityInterface|object $object
   * @param EventArgs|null $eventArgs
   *
   * @return $this
   * @throws NonUniqueResultException
   */
  public function generateUrl(EntityInterface $object, EventArgs $eventArgs = null): PageUrlGenerator
  {
    if($object instanceof EntityTranslateChildInterface)
    {
      $objectMaster = $object->getMaster();
    }
    else
    {
      $objectMaster = $object;
    }

    $generateUrl = false;
    if($objectMaster instanceof EntitySeoInterface)
    {
      $generateUrl = true;
      if($objectMaster instanceof PageInterface && $objectMaster->getIsHomepage())
      {
        $objectMaster->setRefUrl(null);
        $objectMaster->setRefUrlLast(null);
        $objectMaster->setHomepageId($objectMaster->getId());
        $generateUrl = false;
      }
      elseif(!$this->homepageId && ($homepage = $objectMaster->getHomepage())) {
        $this->homepageId = $homepage->getId();
      }

      if($this->homepageId != $objectMaster->getHomepageId())
      {
        $this->homepageAttachement($objectMaster);
      }

    }

    if($generateUrl && (!$eventArgs || !method_exists($eventArgs, "hasChangedField") || (method_exists($eventArgs, "hasChangedField") && $eventArgs->hasChangedField('refUrlLast'))))
    {
      $oldRefUrl = $object->getRefUrl();
      if(!$objectMaster->getRefUrlLast())
      {
        $objectMaster->setRefUrlLast($object->__toString());
      }
      $this->generateUrlWithParent($objectMaster);
      if(method_exists($objectMaster, "getChildren"))
      {
        foreach($objectMaster->getChildren() as $child)
        {
          $this->generateUrl($child);
        }
      }
      if(method_exists($objectMaster, "getChildrenEntities"))
      {
        foreach($objectMaster->getChildrenEntities() as $child)
        {
          $this->generateUrl($child);
        }
      }
      $this->objects[] = array(
        "object"          =>  $objectMaster,
        "newRefUrl"       =>  $object->getRefUrl(),
        "oldRefUrl"       =>  $oldRefUrl,
        "redirections"    =>  $this->generateRedirectionAuto($objectMaster, $object->getRefUrl(), $oldRefUrl)
      );
    }
    return $this;
  }

  /**
   * @return array
   */
  public function getObjects(): array
  {
    return $this->objects;
  }

  /**
   * @param EntitySeoInterface $object
   *
   * @return $this
   */
  protected function homepageAttachement(EntitySeoInterface $object): PageUrlGenerator
  {
    if($this->homepageId) {
      $object->setHomepageId($this->homepageId);
    }
    if(method_exists($object, "getChildren"))
    {
      foreach($object->getChildren() as $child)
      {
        $this->homepageAttachement($child);
      }
    }
    if(method_exists($object, "getChildrenEntities"))
    {
      foreach($object->getChildrenEntities() as $child)
      {
        $this->homepageAttachement($child);
      }
    }
    return $this;
  }


  /**
   * @param EntitySeoInterface $object
   *
   * @return EntitySeoInterface
   */
  protected function generateUrlWithParent(EntitySeoInterface $object): EntitySeoInterface
  {
    if($parentPage = $object->getPageParent())
    {
      $url = trim("{$parentPage->getRefUrl()}/{$object->getRefUrlLast()}", "/");
    }
    else
    {
      $url = $object->getRefUrlLast();
    }
    $object->setRefUrl($url);
    return $object;
  }

  /**
   * @param EntitySeoInterface $object
   * @param string|null $newRefUrl
   * @param string|null $oldRefUrl
   *
   * @return array
   * @throws NonUniqueResultException
   */
  public function generateRedirectionAuto(EntitySeoInterface $object, string $newRefUrl = null, string $oldRefUrl = null): array
  {
    $redirections = array();
    if($this->entitySeoConfiguration->get('redirection.auto') == true)
    {
      if(($oldRefUrl !== $newRefUrl) && $oldRefUrl && $newRefUrl)
      {
        $currentLanguage = null;
        if($object instanceof EntityTranslateMasterInterface)
        {
          $currentLanguage = $object->getLanguageCurrent();
        }
        /** @var RedirectionInterface|null $redirection */
        $redirection = $this->redirectionManager->retreiveByUrlSource($newRefUrl, $currentLanguage);
        if($redirection && ($redirection->getRelationEntityName() !== get_class($object) || $redirection->getRelationEntityId() == $object->getId()))
        {
          $redirectionOther = $redirection;
          $redirectionOther->setIsActive(false);
          $redirections[] = $redirectionOther;
          $redirection = null;
        }

        if(!$redirection)
        {
          $redirection = $this->redirectionManager->create();
          $redirection->setIsActive(true);
          $redirection->setIsAutoGenerate(true);
        }
        $redirection->setRelationEntityName(get_class($object));
        $redirection->setRelationEntityId($object->getId());
        $redirection->setUrlSource($oldRefUrl);
        $redirection->setUrlDestination($newRefUrl);
        if($object instanceof EntityTranslateMasterInterface)
        {
          $redirection->setLanguage($currentLanguage);
        }
        $redirections[] = $redirection;
      }
    }
    return $redirections;
  }


}