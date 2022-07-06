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

use Austral\EntitySeoBundle\Entity\Interfaces\EntityRobotInterface;
use Doctrine\ORM\Mapping as ORM;
use Exception;

/**
 * Austral Translate Entity Seo Robot Trait.
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
trait EntityRobotTrait
{

  /**
   * @var string
   * @ORM\Column(name="status", type="string", length=50, nullable=false )
   */
  protected string $status = "unpublished"; // draft // published
  
  /**
   * @var boolean
   * @ORM\Column(name="is_index", type="boolean")
   */
  protected bool $isIndex = true;
  
  /**
   * @var boolean
   * @ORM\Column(name="is_follow", type="boolean")
   */
  protected bool $isFollow = true;
  
  /**
   * @var boolean
   * @ORM\Column(name="in_sitemap", type="boolean")
   */
  protected bool $inSitemap = true;

  /**
   * @var string[]
   */
  protected array $statusAccepted = array("unpublished", "draft", "published");

  /**
   * Set status
   *
   * @param string $status
   *
   * @return EntityRobotInterface|EntityRobotTrait
   * @throws Exception
   */
  public function setStatus(string $status): EntityRobotInterface
  {
    if(!in_array($status, $this->statusAccepted))
    {
      throw new Exception("Status {$status} is not accepted : ".implode(" // ", $this->statusAccepted));
    }
    $this->status = $status;
    return $this;
  }

  /**
   * Get status
   *
   * @return string
   */
  public function getStatus(): string
  {
    return $this->status;
  }

  /**
   * @return bool
   */
  public function isPublished(): bool
  {
    return $this->status === "published";
  }
  
  /**
   * Set isIndex
   *
   * @param bool $isIndex
   *
   * @return EntityRobotInterface|EntityRobotTrait
   */
  public function setIsIndex(bool $isIndex): EntityRobotInterface
  {
    $this->isIndex = $isIndex;
    return $this;
  }

  /**
   * Get isIndex
   *
   * @return bool
   */
  public function getIsIndex(): bool
  {
    return $this->isIndex;
  }

  /**
   * Set isFollow
   *
   * @param bool $isFollow
   *
   * @return EntityRobotInterface|EntityRobotTrait
   */
  public function setIsFollow(bool $isFollow): EntityRobotInterface
  {
    $this->isFollow = $isFollow;
    return $this;
  }

  /**
   * Get isFollow
   *
   * @return bool
   */
  public function getIsFollow(): bool
  {
    return $this->isFollow;
  }

  /**
   * Set inSitemap
   *
   * @param bool $inSitemap
   *
   * @return EntityRobotInterface|EntityRobotTrait
   */
  public function setInSitemap(bool $inSitemap): EntityRobotInterface
  {
    $this->inSitemap = $inSitemap;
    return $this;
  }

  /**
   * Get inSitemap
   *
   * @return bool
   */
  public function getInSitemap(): bool
  {
    return $this->inSitemap;
  }
  
  
}
