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

use Austral\EntityBundle\Event\EntityManagerEvent;


use Austral\EntitySeoBundle\Entity\Interfaces\EntityRobotInterface;
use Austral\EntitySeoBundle\Entity\Interfaces\EntitySeoInterface;
use Austral\ToolsBundle\AustralTools;

/**
 * Austral EntityManager Listener.
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class EntityManagerListener
{

  /**
   * @param EntityManagerEvent $entityManagerEvent
   */
  public function duplicate(EntityManagerEvent $entityManagerEvent)
  {
    if(AustralTools::usedImplements(get_class($entityManagerEvent->getObject()), EntityRobotInterface::class))
    {
      $entityManagerEvent->getObject()->setStatus(EntityRobotInterface::STATUS_UNPUBLISHED);
    }
    if(AustralTools::usedImplements(get_class($entityManagerEvent->getObject()), EntitySeoInterface::class))
    {
      $entityManagerEvent->getObject()->setRefUrlLast($entityManagerEvent->getObject()->getRefUrlLast()."_copy-".$entityManagerEvent->getDateNow()->format('Y-m-d_h-i'));
    }
  }

}