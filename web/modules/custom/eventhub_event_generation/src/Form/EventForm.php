<?php

namespace Drupal\eventhub_event_generation\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;

/**
 * Class EventForm.
 */
class EventForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'event_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['event_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Event Name'),
      '#description' => $this->t('The name of the event.'),
      '#required' => TRUE,
    ];
    $form['event_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Event Date'),
      '#description' => $this->t('The date of the event.'),
      '#required' => TRUE,
    ];
    $form['event_location'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Event Location'),
      '#description' => $this->t('The location of the event.'),
      '#required' => TRUE,
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#ajax' => [
        'callback' => '::setMessage',
        'event' => 'click',
      ],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function setMessage(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $response->addCommand(new HtmlCommand('#event_form', 'Form submitted successfully.'));
    return $response;
  }

}