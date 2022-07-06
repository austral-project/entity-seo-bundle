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

use Doctrine\ORM\Mapping as ORM;

/**
 * Austral Entity Homepage Trait.
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
trait EntityHomepageTrait
{

  /**
   * @var string|null
   * @ORM\Column(name="homepage_id", type="string", length=255, nullable=true )
   */
  protected ?string $homepageId = null;

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
   * @return EntitySeoInterface|EntityHomepageTrait
   */
  public function setHomepageId(?string $homepageId): EntitySeoInterface
  {
    $this->homepageId = $homepageId;
    return $this;
  }

}
