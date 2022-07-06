<?php
/*
 * This file is part of the Austral EntitySeo Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
namespace Austral\EntitySeoBundle\Entity\Traits;

use Austral\EntitySeoBundle\Entity\Interfaces\EntitySeoInterface;

/**
 * Austral Entity Homepage Translate Trait.
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
trait EntityHomepageTranslateTrait
{

  /**
   * @return string|null
   */
  public function getHomepageId(): ?string
  {
    return null;
  }

  /**
   * @param string|null $homepageId
   *
   * @return EntitySeoInterface|EntityHomepageTranslateTrait
   */
  public function setHomepageId(?string $homepageId): EntitySeoInterface
  {
    return $this;
  }

}
