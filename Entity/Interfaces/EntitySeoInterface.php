<?php
/*
 * This file is part of the Austral EntitySeo Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\EntitySeoBundle\Entity\Interfaces;

/**
 * Austral Seo Entity Interface.
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
interface EntitySeoInterface
{

  /**
   * Get refH1
   *
   * @return EntitySeoInterface|null
   */
  public function getPageParent(): ?EntitySeoInterface;

  /**
   * Set refH1
   *
   * @param string|null $refH1
   *
   * @return $this
   */
  public function setRefH1(?string $refH1): EntitySeoInterface;

  /**
   * Get refH1
   *
   * @return string|null
   */
  public function getRefH1(): ?string;

  /**
   * @return string|null
   */
  public function getRefH1OrDefault(): ?string;

  /**
   * Set refTitle
   *
   * @param string|null $refTitle
   *
   * @return $this
   */
  public function setRefTitle(?string $refTitle): EntitySeoInterface;

  /**
   * Get refTitle
   *
   * @return string|null
   */
  public function getRefTitle(): ?string;

  /**
   * Set refUrl
   *
   * @param string|null $refUrl
   *
   * @return $this
   */
  public function setRefUrl(?string $refUrl): EntitySeoInterface;

  /**
   * Get refUrl
   *
   * @return string|null
   */
  public function getRefUrl(): ?string;

  /**
   * Set refUrlLast
   *
   * @param string|null $refUrlLast
   *
   * @return $this
   */
  public function setRefUrlLast(?string $refUrlLast): EntitySeoInterface;

  /**
   * Get refUrlLast
   *
   * @return string|null
   */
  public function getRefUrlLast(): ?string;

  /**
   * @return bool
   */
  public function getRefUrlLastEnabled(): bool;

  /**
   * Set refDescription
   *
   * @param string|null $refDescription
   *
   * @return $this
   */
  public function setRefDescription(?string $refDescription): EntitySeoInterface;

  /**
   * Get refDescription
   *
   * @return string|null
   */
  public function getRefDescription(): ?string;

  /**
   * Set canonical
   *
   * @param string|null $canonical
   *
   * @return $this
   */
  public function setCanonical(?string $canonical): EntitySeoInterface;

  /**
   * Get canonical
   *
   * @return string|null
   */
  public function getCanonical(): ?string;

  /**
   * @return string
   */
  public function getBaseUrl(): string;

  /**
   * @return mixed
   */
  public function getHomepage();

  /**
   * @return string|null
   */
  public function getHomepageId(): ?string;

  /**
   * @param string|null $homepageId
   *
   * @return EntitySeoInterface
   */
  public function setHomepageId(?string $homepageId): EntitySeoInterface;

}
