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
use Austral\ToolsBundle\AustralTools;

use Doctrine\ORM\Mapping as ORM;

/**
 * Austral Entity Seo Trait.
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
trait EntitySeoTrait
{

  /**
   * @var string|null
   * @ORM\Column(name="ref_h1", type="string", length=255, nullable=true)
   */
  protected ?string $refH1 = null;

  /**
   * @var string|null
   * @ORM\Column(name="ref_title", type="string", length=255, nullable=true)
   */
  protected ?string $refTitle = null;

  /**
   * @var string|null
   * @ORM\Column(name="ref_description", type="text", nullable=true)
   */
  protected ?string $refDescription = null;

  /**
   * @var string|null
   * @ORM\Column(name="ref_url", type="string", length=255, nullable=true)
   */
  protected ?string $refUrl = null;

  /**
   * @var string|null
   * @ORM\Column(name="ref_url_last", type="string", length=255, nullable=true)
   */
  protected ?string $refUrlLast = null;

  /**
   * @var string|null
   * @ORM\Column(name="canonical", type="string", length=255, nullable=true)
   */
  protected ?string $canonical = null;
  
  /**
   * @var string|null
   * @ORM\Column(name="homepage_id", type="string", length=255, nullable=true )
   */
  protected ?string $homepageId = null;

  /**
   * @var string|null
   */
  protected ?string $bodyClass = null;

  /**
   * Set refH1
   *
   * @param string|null $refH1
   *
   * @return EntitySeoInterface|EntitySeoTrait
   */
  public function setRefH1(?string $refH1):EntitySeoInterface
  {
    $this->refH1 = $refH1;
    return $this;
  }

  /**
   * Get refH1
   *
   * @return string|null
   */
  public function getRefH1(): ?string
  {
    return $this->refH1;
  }

  /**
   * @return string|null
   */
  public function getRefH1OrDefault(): ?string
  {
    return $this->refH1 ? : $this->__toString();
  }

  /**
   * Set refTitle
   *
   * @param string|null $refTitle
   *
   * @return EntitySeoInterface|EntitySeoTrait
   */
  public function setRefTitle(?string $refTitle): EntitySeoInterface
  {
    $this->refTitle = $refTitle;
    return $this;
  }

  /**
   * Get refTitle
   *
   * @return string|null
   */
  public function getRefTitle(): ?string
  {
    return $this->refTitle;
  }
  
  /**
   * Set refUrl
   *
   * @param string|null $refUrl
   *
   * @return EntitySeoInterface|EntitySeoTrait
   */
  public function setRefUrl(?string $refUrl): EntitySeoInterface
  {
    $this->refUrl = $refUrl;
    return $this;
  }

  /**
   * Get refUrl
   *
   * @return string|null
   */
  public function getRefUrl(): ?string
  {
    return $this->refUrl;
  }

  /**
   * Set refUrlLast
   *
   * @param string|null $refUrlLast
   *
   * @return EntitySeoInterface|EntitySeoTrait
   */
  public function setRefUrlLast(?string $refUrlLast): EntitySeoInterface
  {
    $this->refUrlLast = AustralTools::slugger($refUrlLast, true, true);
    return $this;
  }

  /**
   * Get refUrlLast
   *
   * @return string|null
   */
  public function getRefUrlLast(): ?string
  {
    return $this->refUrlLast;
  }

  /**
   * Set refDescription
   *
   * @param string|null $refDescription
   *
   * @return EntitySeoInterface|EntitySeoTrait
   */
  public function setRefDescription(?string $refDescription): EntitySeoInterface
  {
    $this->refDescription = $refDescription;
    return $this;
  }

  /**
   * Get refDescription
   *
   * @return string|null
   */
  public function getRefDescription(): ?string
  {
    return $this->refDescription;
  }
  
  /**
   * Set canonical
   *
   * @param string|null $canonical
   *
   * @return EntitySeoInterface|EntitySeoTrait
   */
  public function setCanonical(?string $canonical): EntitySeoInterface
  {
    $this->canonical = $canonical;
    return $this;
  }

  /**
   * Get canonical
   *
   * @return string|null
   */
  public function getCanonical(): ?string
  {
    return $this->canonical;
  }

  /**
   * @return string
   * @throws \Exception
   */
  public function getBaseUrl(): string
  {
    if($this->getRefUrl())
    {
      $url = str_replace($this->getRefUrlLast(), "",  $this->getRefUrl());
      $urls = explode("/", $url);
      if(count($urls) > 1)
      {
        return sprintf('/%s/', trim(implode("/", $urls), "/"));
      }
      return "/";
    }
    else
    {
      return $this->getPageParent() ? sprintf('/%s/', trim($this->getPageParent()->getRefUrl(), "/")) : "/";
    }
  }

  /**
   * @return EntitySeoInterface|EntitySeoTrait
   */
  public function getHomepage(): ?EntitySeoInterface
  {
    if(method_exists($this, "getIsHomepage") && $this->getIsHomepage())
    {
      return $this;
    }
    return $this->getPageParent() ? $this->getPageParent()->getHomepage() : null;
  }

  /**
   * @return string|null
   */
  public function getBodyClass(): ?string
  {
    return $this->bodyClass;
  }

  /**
   * @param string|null $bodyClass
   *
   * @return EntitySeoInterface|EntitySeoTrait
   */
  public function setBodyClass(?string $bodyClass): EntitySeoInterface
  {
    $this->bodyClass = $bodyClass;
    return $this;
  }

}
