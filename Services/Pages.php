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
use Austral\EntityBundle\Entity\EntityInterface;
use Austral\EntitySeoBundle\Entity\Interfaces\EntitySeoInterface;
use Austral\EntitySeoBundle\Entity\Traits\EntityRobotTrait;
use Austral\EntitySeoBundle\Event\PagesEvent;
use Austral\ToolsBundle\AustralTools;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Austral Pages service.
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
Class Pages
{

  /**
   * @var EntityManagerInterface
   */
  protected EntityManagerInterface $entityManager;

  /**
   * @var Request|null
   */
  protected ?Request $request;

  /**
   * @var EventDispatcherInterface|null
   */
  protected ?EventDispatcherInterface $dispatcher;
  
  /**
   * @var string|null
   */
  protected ?string $homepageId = null;

  /**
   * @var string|null
   */
  protected ?string $currentLanguage;

  /**
   * @var array
   */
  protected array $entities = array();

  /**
   * @var array
   */
  protected array $objects = array();

  /**
   * @var array
   */
  protected array $objectsByEntity = array();

  /**
   * @var array
   */
  protected array $objectsByCode = array();

  /**
   * @var array
   */
  protected array $urls = array();

  /**
   * @var array
   */
  protected array $urlsByEntity = array();

  /**
   * @var array
   */
  protected array $conflictUrls = array();

  /**
   * @var mixed|null
   */
  protected $queryToSelectObjectSeo = null;

  /**
   * @var bool
   */
  protected bool $byStatus = true;

  /**
   * @var AuthorizationCheckerInterface
   */
  protected AuthorizationCheckerInterface $authorizationChecker;

  /**
   * Page constructor.
   *
   * @param RequestStack $request
   * @param EntityManagerInterface $entityManager
   * @param EventDispatcherInterface $dispatcher
   * @param AuthorizationCheckerInterface $authorizationChecker
   */
  public function __construct(RequestStack $request, EntityManagerInterface $entityManager, EventDispatcherInterface $dispatcher, AuthorizationCheckerInterface $authorizationChecker)
  {
    $this->entityManager = $entityManager;
    $this->dispatcher = $dispatcher;
    $this->request = $request->getCurrentRequest();
    $this->currentLanguage = $this->request ? $this->request->attributes->get('language', $this->request->getLocale()) : null;
    $this->authorizationChecker = $authorizationChecker;
    $this->initEntity();
  }

  /**
   * @param bool $refresh
   *
   * @return $this
   */
  public function initEntity(bool $refresh = false): Pages
  {
    $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
    if((count($this->entities) == 0) || $refresh)
    {
      $this->entities = array();
      foreach($metadata as $classMeta)
      {
        $className = $classMeta->getName();
        if(strpos($className,"Entity\Base") === false && strpos($className,"Translate") === false)
        {
          if(AustralTools::usedImplements($className, EntitySeoInterface::class))
          {
            $entityName = trim(str_replace($classMeta->namespace, "", $className), "\\");
            if(!array_key_exists($entityName, $this->entities))
            {
              $this->entities[$entityName] = $className;
            }
          }
        }
      }
    }
    return $this;
  }

  /**
   * @return string|null
   */
  public function getHomepageId(): ?string
  {
    return $this->homepageId;
  }

  /**
   * @param string|null $homepageId
   *
   * @return $this
   */
  public function setHomepageId(?string $homepageId): Pages
  {
    $this->homepageId = $homepageId;
    return $this;
  }

  /**
   * @return bool
   */
  public function getByStatus(): bool
  {
    return $this->byStatus;
  }

  /**
   * @param bool $byStatus
   *
   * @return $this
   */
  public function setByStatus(bool $byStatus): Pages
  {
    $this->byStatus = $byStatus;
    return $this;
  }

  /**
   * @param $currentLanguage
   *
   * @return $this
   */
  public function setCurrentLanguage($currentLanguage): Pages
  {
    $this->currentLanguage = $currentLanguage;
    return $this;
  }

  /**
   * @return string|null
   */
  public function getCurrentLanguage(): ?string
  {
    return $this->currentLanguage;
  }

  /**
   * @return array
   */
  public function getEntities(): array
  {
    return $this->entities;
  }

  /**
   * @return $this
   */
  public function reinitObject(): Pages
  {
    $this->objects = array();
    $this->urls = array();
    $this->conflictUrls = array();
    $this->objectsByCode = array();
    $this->objectsByEntity = array();
    $this->initObjects();
    return $this;
  }

  /**
   * @return $this
   */
  protected function initObjects(): Pages
  {
    if(!$this->objects)
    {
      $this->objectsByEntity = array();
      foreach($this->entities as $entityName => $className)
      {
        $objects = $this->selectObjectsSeo($className);
        $this->objects = array_merge($this->objects, $objects);
        
        $this->objectsByEntity[$entityName] = array();
        $this->urlsByEntity[$entityName] = array();
        
        foreach($objects as $object)
        {
          $this->objectsByEntity[$entityName][$object->getId()] = $object;
          $this->objectsByCode["{$object->getClassname()}_{$object->getKeyname()}"] = $object;
          $this->urlsByEntity[$entityName][$object->getRefUrl()] = $object;
          if(array_key_exists($object->getRefUrl(), $this->urls))
          {
            if(array_key_exists($object->getRefUrl(), $this->conflictUrls))
            {
              $this->conflictUrls[$object->getRefUrl()] = array();
            }
            $this->conflictUrls[$object->getRefUrl()][] = $object;
          }
          else
          {
            $this->urls[$object->getRefUrl()] = $object;
          }
          ksort($this->urlsByEntity[$entityName]);
        }
      }
    }
    ksort($this->urls);
    ksort($this->urlsByEntity);
    ksort($this->objectsByCode);
    return $this;
  }

  /**
   * @param $className
   *
   * @return array
   */
  protected function selectObjectsSeo($className): array
  {
    $pagesEvent = new PagesEvent($this->entityManager, $className);
    $this->dispatcher->dispatch($pagesEvent, PagesEvent::EVENT_SELECT_OBJECTS);
    if(!$query = $pagesEvent->getQuery())
    {
      $queryBuilder = $this->entityManager->getRepository($className)->createQueryBuilder("pages");
      if($hasTranslate = method_exists($className, "getTranslateCurrent"))
      {
        $queryBuilder->leftJoin('pages.translates', "translates")->addSelect('translates');
      }

      if($this->byStatus)
      {
        if(!$this->authorizationChecker->isGranted("ROLE_ADMIN_ACCESS"))
        {
          if(AustralTools::usedClass($className, EntityRobotTrait::class))
          {
            $queryBuilder->andWhere("pages.status = :status")
              ->setParameter("status", "published");
          }
          elseif($hasTranslate)
          {
            $queryBuilder->andWhere("translates.status = :status")
              ->setParameter("status", "published");
          }
        }
        else
        {
          if(AustralTools::usedClass($className, EntityRobotTrait::class))
          {
            $queryBuilder->andWhere("pages.status = :status OR pages.status = :statusDraft")
              ->setParameter("status", "published")
              ->setParameter("statusDraft", "draft");
          }
          elseif($hasTranslate)
          {
            $queryBuilder->andWhere("translates.status = :status OR translates.status = :statusDraft")
              ->setParameter("status", "published")
              ->setParameter("statusDraft", "draft");
          }
        }
      }

      if($this->getHomepageId()) 
      {
        $queryBuilder->andWhere("pages.homepageId = :homepageId")
          ->setParameter("homepageId", $this->getHomepageId());
      }

      $query = $queryBuilder->getQuery();
    }

    try {
      $objects = $query->execute();
    } catch (\Doctrine\Orm\NoResultException $e) {
      $objects = array();
    }
    return $objects;
  }

  /**
   * @return array
   */
  public function getObjects(): array
  {
    $this->initObjects();
    return $this->objects;
  }

  /**
   * @param string|null $entityName
   *
   * @return array
   */
  public function getObjectsByEntity(string $entityName = null): array
  {
    $this->initObjects();
    return $entityName ? AustralTools::getValueByKey($this->objectsByEntity, $entityName, array()) : $this->objectsByEntity;
  }

  /**
   * @return array
   */
  public function getUrls(): array
  {
    $this->initObjects();
    return $this->urls;
  }

  /**
   * @return array
   */
  public function getUrlsByEntity(): array
  {
    $this->initObjects();
    return $this->urlsByEntity;
  }

  /**
   * @param $refurl
   *
   * @return EntitySeoInterface|EntityInterface|null
   */
  public function retreiveByRefUrl($refurl): ?EntitySeoInterface
  {
    return AustralTools::getValueByKey($this->getUrls(), $refurl, null);
  }

  /**
   * @param string $code
   *
   * @return EntitySeoInterface|null
   */
  public function retreiveByCode(string $code): ?EntitySeoInterface
  {
    $this->initObjects();
    return AustralTools::getValueByKey($this->objectsByCode, $code, null);
  }

  /**
   * @param string $entitName
   * @param $id
   *
   * @return EntitySeoInterface|null
   */
  public function retreiveByEntityAndId(string $entitName, $id): ?EntitySeoInterface
  {
    $this->initObjects();
    if($objectsByEntity = AustralTools::getValueByKey($this->objectsByEntity, $entitName, null))
    {
      return AustralTools::getValueByKey($objectsByEntity, $id, null);
    }
    return null;
  }

  /**
   * @param string $refUrl
   *
   * @return bool
   */
  public function isConflictUrl(string $refUrl): bool
  {
    return array_key_exists($refUrl, $this->conflictUrls);
  }

}