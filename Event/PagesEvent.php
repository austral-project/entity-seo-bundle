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

use Austral\EntitySeoBundle\Entity\Interfaces\EntitySeoInterface;
use Austral\EntitySeoBundle\Services\Pages;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Austral Pages Event.
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class PagesEvent extends Event
{

  const EVENT_PAGE_INIT = "austral.entity_seo.page.init";
  const EVENT_PAGE_OBJECT_PUSH = "austral.entity_seo.page.object.push";
  const EVENT_PAGE_FINISH = "austral.entity_seo.page.finish";

  /**
   * @var Pages
   */
  private Pages $pages;

  /**
   * @var EntitySeoInterface|null
   */
  private ?EntitySeoInterface $object = null;

  /**
   * PagesEvent constructor.
   *
   * @param Pages $pages
   * @param EntitySeoInterface|null $object
   */
  public function __construct(Pages $pages, ?EntitySeoInterface $object = null)
  {
    $this->pages = $pages;
    $this->object = $object;
  }

  /**
   * @return Pages
   */
  public function getPages(): Pages
  {
    return $this->pages;
  }

  /**
   * @param Pages $pages
   *
   * @return $this
   */
  public function setPages(Pages $pages): PagesEvent
  {
    $this->pages = $pages;
    return $this;
  }

  /**
   * @return EntitySeoInterface|null
   */
  public function getObject(): ?EntitySeoInterface
  {
    return $this->object;
  }

  /**
   * @param EntitySeoInterface|null $object
   *
   * @return $this
   */
  public function setObject(?EntitySeoInterface $object): PagesEvent
  {
    $this->object = $object;
    return $this;
  }

}