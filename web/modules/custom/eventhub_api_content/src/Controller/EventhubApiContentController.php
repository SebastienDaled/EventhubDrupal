<?php

namespace Drupal\eventhub_api_content\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\eventhub_api_content\EventhubApiContentService;
use Drupal\eventhub_api_content\Form\EventhubApiContentForm;

/**
 * Class EventhubApiContentController.
 */
class EventhubApiContentController extends ControllerBase {
  /**
   * Content.
   *
   * @return string
   *   Return Hello string.
   */
  public function content() {
    $form = \Drupal::formBuilder()->getForm(EventhubApiContentForm::class);
    return $form;
  }

}