<?php
/*
 * This file is part of the Austral EntitySeo Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Austral\EntitySeoBundle\EventSubscriber;

use Austral\EntitySeoBundle\Event\PagesEvent;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Austral Pages Subscriber.
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class PagesSubscriber implements EventSubscriberInterface
{

  /**
   * @return array
   */
  public static function getSubscribedEvents()
  {
    return [
      PagesEvent::EVENT_SELECT_OBJECTS  =>  ["selectObjects", 1024],
    ];
  }

  /**
   * @param PagesEvent $pagesEvent
   */
  public function selectObjects(PagesEvent $pagesEvent)
  {
  }

}