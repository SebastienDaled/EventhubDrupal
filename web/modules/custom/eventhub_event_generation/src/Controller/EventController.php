<?php

namespace Drupal\eventhub_event_generation\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\eventhub_event_generation\EventService;
use Drupal\eventhub_event_generation\Form\EventForm;

/**
 * Class EventController.
 */
class EventController extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function content() {
    // Build the form array.
    $form = $this->formBuilder()->getForm(EventForm::class);

    // Return the form.
    return $form;
  }
}
