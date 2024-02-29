<?php

namespace Drupal\eventhub_api_content\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;

/**
 * Class EventhubApiContentForm.
 */
class EventhubApiContentForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'eventhub_api_content_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // The form for the API URL
    $form['api_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API URL'),
      '#description' => $this->t('The URL of the API.'),
      '#required' => TRUE,
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // get the URL from the form
    $url = $form_state->getValue('api_url');

    // do a fetch of the API
    $response = \Drupal::httpClient()->get($url);
    $data = json_decode($response->getBody());
    
    $data = $data->_embedded;
  
    // for each event item received from the API, create a node
    foreach ($data->events as $event) {
      // make a bolean for the sales status
      $onsale = false;

      if ($event->dates->status->code == 'onsale') {
        $onsale = true;
      }

      // get the country and genre taxonomy terms to check of they exist
      $country = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties(['name' => $event->_embedded->venues[0]->country->name]);
      $genre = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties(['name' => $event->classifications[0]->genre->name]);

      // does the check if the taxonomy terms exist, if not, create them
      if (empty($country)) {
        // taxonomy term does not exist, create it
        $country = \Drupal\taxonomy\Entity\Term::create([
          'name' => $event->_embedded->venues[0]->country->name,
          'vid' => 'country',
        ]);
        $country->save();
      } else {
        $country = array_values($country)[0];
      }

      // does the check if the taxonomy terms exist, if not, create them
      if (empty($genre)) {
        // taxonomy term does not exist, create it
        $genre = \Drupal\taxonomy\Entity\Term::create([
          'name' => $event->classifications[0]->genre->name,
          'vid' => 'genre',
        ]);
        $genre->save();
      } else {
        $genre = array_values($genre)[0];
      }

      // searches for the biggets image in size to use as the hero image
      // width size is used to compare
      $biggest = 0;
      // key is used to get the image url
      $biggest_key = 0;
      foreach ($event->images as $key => $image) {
        if ($image->width > $biggest) {
          $biggest = $image->width;
          $biggest_key = $key;
        }
      }

      // create the node
      $node = \Drupal\node\Entity\Node::create([
        'type' => 'event',
        'title' => $event->name,
        'field_adress' => $event->_embedded->venues[0]->address->line1,
        'body' => $event->description,
        'field_city' => $event->_embedded->venues[0]->city->name,
        'field_country' => [
          'target_id' => $country->id(),
        ],
        'field_date' => date('Y-m-d\TH:i:s', strtotime($event->dates->start->localDate . ' ' . $event->dates->start->localTime)),
        'field_end_sale_date' => date('Y-m-d\TH:i:s', strtotime($event->sales->public->endDateTime)),
        'field_genre' => [
          'target_id' => $genre->id(),
        ],
        'field_hero_image_source' => $event->images[$biggest_key]->url,
        'field_location' => $event->_embedded->venues[0]->name,
        'field_max_price' => $event->priceRanges[0]->max,
        'field_min_price' => $event->priceRanges[0]->min,
        'field_performer' => $event->_embedded->attractions[0]->name,
        'field_sales_status' => $onsale,
        'field_seatmap_image_source' => $event->seatmap->staticUrl,
        'field_start_sale_date' => date('Y-m-d\TH:i:s', strtotime($event->sales->public->startDateTime)),
      ]);
      $node->save();
    }
  }
}