<?php
/*
 * This file is part of the Austral EntitySeo Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\EntitySeoBundle\Event;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Austral Pages Event.
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class PagesEvent extends Event
{

  const EVENT_SELECT_OBJECTS = "austral.entity_seo.select_objects";

  /**
   * @var EntityManagerInterface
   */
  private EntityManagerInterface $entityManager;

  /**
   * @var string
   */
  private string $classname;

  /**
   * @var AbstractQuery|null
   */
  private ?AbstractQuery $query = null;

  /**
   * PagesEvent constructor.
   *
   * @param EntityManagerInterface $entityManager
   * @param string $classname
   */
  public function __construct(EntityManagerInterface $entityManager, string $classname)
  {
    $this->entityManager = $entityManager;
    $this->classname = $classname;
  }

  /**
   * @return string
   */
  public function getClassname(): string
  {
    return $this->classname;
  }

  /**
   * @return EntityManagerInterface
   */
  public function getEntityManager(): EntityManagerInterface
  {
    return $this->entityManager;
  }

  /**
   * @return AbstractQuery|null
   */
  public function getQuery(): ?AbstractQuery
  {
    return $this->query;
  }

  /**
   * @param AbstractQuery $query
   *
   * @return PagesEvent
   */
  public function setQuery(AbstractQuery $query): PagesEvent
  {
    $this->query = $query;
    return $this;
  }

}