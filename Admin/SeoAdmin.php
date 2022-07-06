<?php
/*
 * This file is part of the Austral EntitySeo Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\EntitySeoBundle\Admin;

use Austral\AdminBundle\Admin\Admin;
use Austral\AdminBundle\Admin\Event\ListAdminEvent;
use Austral\AdminBundle\Module\Modules;
use Austral\EntityBundle\EntityManager\EntityManager;
use Austral\EntitySeoBundle\Configuration\EntitySeoConfiguration;
use Austral\EntitySeoBundle\Listener\FormListener;
use Austral\EntitySeoBundle\Services\Pages;
use Austral\FormBundle\Event\FormEvent;
use Austral\FormBundle\Form\Type\FormTypeInterface;
use Austral\FormBundle\Mapper\FormMapper;
use Austral\ToolsBundle\AustralTools;
use Doctrine\Common\Util\ClassUtils;
use ReflectionException;

/**
 * Seo Admin .
 * @author Matthieu Beurel <matthieu@austral.dev>
 */
class SeoAdmin extends Admin
{

  /**
   * @param ListAdminEvent $listAdminEvent
   */
  public function index(ListAdminEvent $listAdminEvent)
  {
    $this->createFormByType($listAdminEvent, "seo-title");
  }

  /**
   * @param ListAdminEvent $listAdminEvent
   */
  public function url(ListAdminEvent $listAdminEvent)
  {
    $this->createFormByType($listAdminEvent, "seo-url");
  }

  /**
   * @param ListAdminEvent $listAdminEvent
   */
  public function all(ListAdminEvent $listAdminEvent)
  {
    $this->createFormByType($listAdminEvent, "seo-all");
  }


  /**
   * @param ListAdminEvent $listAdminEvent
   * @param string $type
   *
   * @return void
   * @throws ReflectionException
   */
  protected function createFormByType(ListAdminEvent $listAdminEvent, string $type)
  {
    /** @var Pages $seoPages */
    $seoPages = $this->container->get('austral.entity_seo.pages');
    $currentLanguage = $listAdminEvent->getRequest()->query->get('language',
      $listAdminEvent->getRequest()->getLocale()
    );
    $seoPages->setCurrentLanguage($currentLanguage)->setByStatus(false)->reinitObject();

    /** @var EntityManager $entityManager */
    $entityManager = $this->container->get('austral.entity_manager');

    $mappingByEntity = array();
    foreach($seoPages->getEntities() as $classEntity)
    {
      $entityManager->setClass($classEntity);
      $mappingByEntity[$classEntity] = $entityManager->getFieldsMappingAll();
    }

    $forms = array();
    /** @var EntitySeoConfiguration $seoConfig */
    $seoConfig = $this->container->get('austral.entity_seo.config');
    $formListener = new FormListener($this->container->get('request_stack'), $seoConfig, $this->container->get('router'), $this->container->get('security.authorization_checker'));

    $formsIsValide = true;
    $request = $listAdminEvent->getRequest();

    $formMapperMaster = new FormMapper($this->container->get('event_dispatcher'));
    $formMapperMaster->setTranslateDomain("austral")->setPathToTemplateDefault("@AustralAdmin/Form/Components/Fields");
    /** @var Modules $modules */
    $modules = $this->container->get('austral.admin.modules');
    foreach($seoPages->getUrls() as $object)
    {
      $formMapper = new FormMapper($this->container->get('event_dispatcher'));
      $formMapper->setObject($object)
        ->setName("form_{$object->getId()}")
        ->setFieldsMapping(AustralTools::getValueByKey($mappingByEntity, ClassUtils::getClass($object)))
        ->setFormTypeAction("edit")
        ->setTranslateDomain("austral")
        ->setModule($modules->getModuleByEntityClassname($object->getClassname()));

      $formMapperMaster->addSubFormMapper("form_{$object->getId()}", $formMapper);

      /** @var FormTypeInterface $formType */
      $formType = clone $this->container->get('austral.form.type.master')
        ->setClass(ClassUtils::getClass($object))
        ->setFormMapper($formMapperMaster);

      $formEvent = new FormEvent($formMapper);
      $formListener->formAddAutoFields($formEvent, $type);
      $form = $this->container->get('form.factory')->createNamed("form_{$object->getId()}", get_class($formType), $formMapper->getObject());
      if($request->getMethod() == 'POST')
      {
        $form->handleRequest($request);
        if($form->isSubmitted()) {

          $formMapper->setObject($form->getData());
          if($form->isValid() && $this->module->getAdmin()->formIsValidate())
          {
            $entityManager->update($formMapper->getObject(), false);
          }
          else
          {
            $formsIsValide = false;
            $formMapper->setFormStatus("error");
          }
        }
      }
      $forms[] = array(
        "mapper"    =>  $formMapper,
        "form"      =>  $form,
        "view"      =>  $form->createView()
      );
    }

    $formSend = false;
    $formStatus = null;
    if($request->getMethod() == 'POST')
    {
      $formSend = true;
      if($formsIsValide)
      {
        $entityManager->flush();
      }
      $formStatus = ($formsIsValide ? "success" : "error");
      $listAdminEvent->getAdminHandler()->addFlash(($formsIsValide ? "success" : "error"),
        $listAdminEvent->getAdminHandler()->getTranslate()->trans(
          "form.status.".($formsIsValide ? "success" : "error"),
          array('%name%' => ""), "form"
        )
      );
    }

    $listAdminEvent->getTemplateParameters()->setPath("@AustralEntitySeo/Admin/Module/seo.html.twig");
    $listAdminEvent->getTemplateParameters()->addParameters("list", array(
      "forms"       =>  $forms,
      "formSend"    =>  $formSend,
      "formStatus"  =>  $formStatus,
      "type"        =>  $type
    ));
  }


}