<?php
/*
 * This file is part of the Austral EntitySeo Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\EntitySeoBundle\Repository;

use Austral\EntityBundle\Repository\EntityRepository;

use Doctrine\ORM\NonUniqueResultException;

/**
 * Austral Redirection Repository.
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class RedirectionRepository extends EntityRepository
{

  /**
   * @param $entityName
   * @param $entityId
   * @param $language
   *
   * @return int|mixed|string|null
   * @throws NonUniqueResultException
   */
  public function retreiveByEntity($entityName, $entityId, $language)
  {
    $query = $this->createQueryBuilder('root')
      ->where('root.entityName = :entityName')
      ->andWhere('root.entityId = :entityId')
      ->andWhere('root.language = :language')
      ->setParameters(
        array(
          "entityName"  => $entityName,
          "entityId"    => $entityId,
          "language"    => $language
        )
      )
      ->setMaxResults(1)
      ->orderBy("root.id", "ASC")
      ->getQuery();
    try {
      $object = $query->getSingleResult();
    } catch (\Doctrine\Orm\NoResultException $e) {
      $object = null;
    }
    return $object;
  }

  /**
   * @param $urlSource
   * @param string|null $language
   *
   * @return int|mixed|string|null
   * @throws NonUniqueResultException
   */
  public function retreiveByUrlSource($urlSource, string $language = null)
  {
    $query = $this->createQueryBuilder('root')
      ->where('root.urlSource = :urlSource')
      ->andWhere('root.language = :language')
      ->andWhere('root.isActive = :isActive')
      ->setParameters(
        array(
          "urlSource"   => $urlSource,
          "language"    => $language,
          "isActive"    =>  true
        )
      )
      ->setMaxResults(1)
      ->orderBy("root.updated", "DESC")
      ->getQuery();
    try {
      $object = $query->getSingleResult();
    } catch (\Doctrine\Orm\NoResultException $e) {
      $object = null;
    }
    return $object;
  }

  /**
   * @param string $language
   *
   * @return int|mixed[]|string
   */
  public function selectArrayResultAllByUrlDestination(string $language)
  {
    $dql = "SELECT redirection.id, redirection.urlDestination, redirection.urlSource, redirection.language, redirection.entityName, redirection.entityId, redirection.isActive
          FROM Austral\WebsiteBundle\Entity\Redirection redirection
          INDEX BY redirection.urlDestination
          
          WHERE redirection.language = '$language'
          
          GROUP BY redirection.urlDestination
          ORDER BY redirection.urlDestination ASC";

    $query = $this->createQueryBuilder("redirection")->getQuery()->setDql($dql);
    return $query->getArrayResult();
  }

  /**
   * @param string $language
   *
   * @return int|mixed[]|string
   */
  public function selectArrayResultAllByUrlSource(string $language)
  {
    $dql = "SELECT redirection.id, redirection.urlDestination, redirection.urlSource, redirection.language, redirection.entityName, redirection.entityId, redirection.isActive
          FROM Austral\WebsiteBundle\Entity\Redirection redirection
          INDEX BY redirection.urlSource
          WHERE redirection.language = '$language'
          ORDER BY redirection.urlSource ASC";
    $query = $this->createQueryBuilder("redirection")->getQuery()->setDql($dql);
    return $query->getArrayResult();
  }

}
