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

use Austral\EntitySeoBundle\Configuration\EntitySeoConfiguration;
use Austral\EntitySeoBundle\Entity\Interfaces\EntityRobotInterface;
use Austral\EntitySeoBundle\Entity\Interfaces\EntitySeoInterface;
use Austral\EntitySeoBundle\Form\Field\PathField;
use Austral\FormBundle\Event\FormEvent;
use Austral\FormBundle\Field as Field;
use Austral\FormBundle\Mapper\GroupFields;
use Austral\ListBundle\Column\Action;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Austral FormListener Listener.
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class FormListener
{

  /**
   * @var Request|null
   */
  protected ?Request $request = null;

  /**
   * @var Router
   */
  protected Router $router;

  /**
   * @var EntitySeoConfiguration
   */
  protected EntitySeoConfiguration $entitySeoConfiguration;

  /**
   * @var AuthorizationCheckerInterface
   */
  protected AuthorizationCheckerInterface $authorizationChecker;

  /**
   * FormListener constructor.
   *
   * @param RequestStack $request
   * @param EntitySeoConfiguration $entitySeoConfiguration
   * @param Router $router
   * @param AuthorizationCheckerInterface $authorizationChecker
   */
  public function __construct(RequestStack $request, EntitySeoConfiguration $entitySeoConfiguration, Router $router, AuthorizationCheckerInterface $authorizationChecker)
  {
    $this->request = $request->getCurrentRequest();
    $this->entitySeoConfiguration = $entitySeoConfiguration;
    $this->authorizationChecker = $authorizationChecker;
    $this->router = $router;
  }

  /**
   * @param FormEvent $formEvent
   * @param string|null $type
   *
   * @throws Exception
   */
  public function formAddAutoFields(FormEvent $formEvent, ?string $type = null)
  {
    if(($formEvent->getFormMapper()->getObject() instanceof EntitySeoInterface) && ($type === FormEvent::EVENT_AUSTRAL_FORM_ADD_AUTO_FIELDS_AFTER || strpos($type, "seo") !== false))
    {
      /** @var EntitySeoInterface $object */
      $object = $formEvent->getFormMapper()->getObject();

      $seoParametersFieldset = $formEvent->getFormMapper()->addFieldset("fieldset.seoParameters");
      if($type === FormEvent::EVENT_AUSTRAL_FORM_ADD_AUTO_FIELDS_AFTER)
      {
        try {
          $formEvent->getFormMapper()->addAction(new Action("goTo", "actions.goTo",
            $this->router->generate("austral_website_page", array("slug" => $object->getRefUrl())),
            "austral-picto-corner-forward",
            array(
              "class"   =>  "button-picto",
              "attr"    =>  array(
                "target"    =>  "_blank",
              ),
            )
          ), 99
          );
        } catch(\Exception $e) {

        }
        $seoParametersFieldset->add(Field\TextField::create("refH1"));
      }

      if($type === FormEvent::EVENT_AUSTRAL_FORM_ADD_AUTO_FIELDS_AFTER || $type === "seo-all")
      {
        $seoParametersFieldset->add(Field\TemplateField::create("googleVisualisation",
            "@AustralEntitySeo/Form/_Components/Field/google-visualisation.html.twig",
            array(),
            array("domain"=>$this->request->headers->get('host'))
          )
        );
      }
      if($type === FormEvent::EVENT_AUSTRAL_FORM_ADD_AUTO_FIELDS_AFTER || $type === "seo-all" || $type === "seo-url")
      {
        $seoParametersFieldset->add(new PathField("refUrlLast", array(
              "isView"  => $object->getRefUrlLastEnabled()
            )
          )
        );
      }
      if($type === FormEvent::EVENT_AUSTRAL_FORM_ADD_AUTO_FIELDS_AFTER || $type === "seo-all" || $type === "seo-title")
      {
        $seoParametersFieldset->add(Field\TextField::create("refTitle", array(
          "attr"  => array(
            'data-characters-max'  =>  $this->entitySeoConfiguration->get("nb_characters.ref_title")
          )
        )))
        ->add(Field\TextareaField::create("refDescription", Field\TextareaField::SIZE_AUTO, array(
          "attr"  => array(
            'data-characters-max'  =>  $this->entitySeoConfiguration->get("nb_characters.ref_description")
          )
        )));
      }
      if($type === FormEvent::EVENT_AUSTRAL_FORM_ADD_AUTO_FIELDS_AFTER)
      {
        $seoParametersFieldset->add(Field\TextField::create("canonical", array('isView'=> ($type !== "seo"))));
      }
      if($type === FormEvent::EVENT_AUSTRAL_FORM_ADD_AUTO_FIELDS_AFTER || $type === "seo-all")
      {
        $formEvent->getFormMapper()->addFieldset("fieldset.dev.config")
          ->setIsView($this->authorizationChecker->isGranted("ROLE_ROOT"))
          ->add(Field\TemplateField::create("internalLink",
            "@AustralEntitySeo/Form/_Components/Field/internal-link.html.twig",
            array('isView' => function() {
                return $this->authorizationChecker->isGranted('ROLE_ROOT');
              }
            )
          ))
        ->end();
      }

    }

    if(($formEvent->getFormMapper()->getObject() instanceof EntityRobotInterface) && ($type === FormEvent::EVENT_AUSTRAL_FORM_ADD_AUTO_FIELDS_AFTER || $type == "robot"))
    {
      $formEvent->getFormMapper()->addFieldset("fieldset.robotParameters")
        ->addGroup("robots")
          ->setStyle(GroupFields::STYLE_BOOLEAN)
          ->add(Field\SwitchField::create("isIndex", array("helper"=>"fields.isIndex.information")))
          ->add(Field\SwitchField::create("isFollow", array("helper"=>"fields.isFollow.information")))
          ->add(Field\SwitchField::create("inSitemap", array("helper"=>"fields.inSitemap.information")))
        ->end()
      ->end();
    }
  }

}