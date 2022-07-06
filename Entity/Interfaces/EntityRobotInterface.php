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
interface EntityRobotInterface
{
  const STATUS_PUBLISHED = "published";
  const STATUS_UNPUBLISHED = "unpublished";
  const STATUS_DRAFT = "draft";

  /**
   * Set status
   *
   * @param string $status
   *
   * @return $this
   * @throws \Exception
   */
  public function setStatus(string $status): EntityRobotInterface;

  /**
   * Get status
   *
   * @return string
   */
  public function getStatus(): string;

  /**
   * @return bool
   */
  public function isPublished(): bool;

  /**
   * Set isIndex
   *
   * @param bool $isIndex
   *
   * @return $this
   */
  public function setIsIndex(bool $isIndex): EntityRobotInterface;

  /**
   * Get isIndex
   *
   * @return bool
   */
  public function getIsIndex(): bool;

  /**
   * Set isFollow
   *
   * @param bool $isFollow
   *
   * @return $this
   */
  public function setIsFollow(bool $isFollow): EntityRobotInterface;

  /**
   * Get isFollow
   *
   * @return bool
   */
  public function getIsFollow(): bool;

  /**
   * Set inSitemap
   *
   * @param bool $inSitemap
   *
   * @return $this
   */
  public function setInSitemap(bool $inSitemap): EntityRobotInterface;

  /**
   * Get inSitemap
   *
   * @return bool
   */
  public function getInSitemap(): bool;

}
