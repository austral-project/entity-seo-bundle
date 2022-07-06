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


use Austral\AdminBundle\Configuration\ConfigurationChecker;
use Austral\AdminBundle\Configuration\ConfigurationCheckerValue;
use Austral\AdminBundle\Event\ConfigurationCheckerEvent;
use Austral\EntitySeoBundle\Configuration\EntitySeoConfiguration;

/**
 * Austral ConfigurationChecker Listener.
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class ConfigurationCheckerListener
{

  /**
   * @var EntitySeoConfiguration
   */
  protected EntitySeoConfiguration $entitySeoConfiguration;

  /**
   * @param EntitySeoConfiguration $entitySeoConfiguration
   */
  public function __construct(EntitySeoConfiguration $entitySeoConfiguration)
  {
    $this->entitySeoConfiguration = $entitySeoConfiguration;
  }

  /**
   * @param ConfigurationCheckerEvent $configurationCheckerEvent
   *
   * @throws \Exception
   */
  public function configurationChecker(ConfigurationCheckerEvent $configurationCheckerEvent)
  {
    $configurationCheckModules = $configurationCheckerEvent->getConfigurationChecker()->getChild("modules");

    $configurationCheckerNotify = new ConfigurationChecker("entitySeo");
    $configurationCheckerNotify->setName("configuration.check.modules.entitySeo.title")
      ->setIsTranslatable(true)
      ->setParent($configurationCheckModules);

    $configurationCheckerValue = new ConfigurationCheckerValue("redirection", $configurationCheckerNotify);
    $configurationCheckerValue->setName("configuration.check.modules.entitySeo.redirection.entitled")
      ->setIsTranslatable(true)
      ->setIsTranslatableValue(true)
      ->setType("checked")
      ->setStatus($this->entitySeoConfiguration->get('redirection.auto') ? "success" : "")
      ->setValue($this->entitySeoConfiguration->get('redirection.auto') ? "configuration.check.choices.enabled" : "configuration.check.choices.disabled");

    $configurationCheckerValue = new ConfigurationCheckerValue("ref_title", $configurationCheckerNotify);
    $configurationCheckerValue->setName("configuration.check.modules.entitySeo.refTitle.entitled")
      ->setIsTranslatable(true)
      ->setIsTranslatableValue(false)
      ->setType("string")
      ->setStatus("")
      ->setValue($this->entitySeoConfiguration->get('nb_characters.ref_title'));

    $configurationCheckerValue = new ConfigurationCheckerValue("ref_description", $configurationCheckerNotify);
    $configurationCheckerValue->setName("configuration.check.modules.entitySeo.refDescription.entitled")
      ->setIsTranslatable(true)
      ->setIsTranslatableValue(false)
      ->setType("string")
      ->setStatus("")
      ->setValue($this->entitySeoConfiguration->get('nb_characters.ref_description'));

  }
}