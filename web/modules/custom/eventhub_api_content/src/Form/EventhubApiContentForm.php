<?php

namespace Drupal\eventhub_api_content\Form;

use Drupal\advancedqueue\Entity\Queue;
use Drupal\advancedqueue\Job;
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
      '#type' => 'textarea',
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
    $queue = Queue::load('default');
    // for each event item received from the API, create a node
    foreach ($data->events as $event) {
      $job = Job::create('createEvent', ['data' => $event]);
      
      $queue->enqueueJob($job);

      // $name = $event->name;
      // $address = $event->_embedded->venues[0]->address->line1;
      // $body = $event->description;
      // $city = $event->_embedded->venues[0]->city->name;
      // $country = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties(['name' => $event->_embedded->venues[0]->country->name]);
      // $date = date('Y-m-d\TH:i:s', strtotime($event->dates->start->localDate . ' ' . $event->dates->start->localTime));
      // $end_sale_date = date('Y-m-d\TH:i:s', strtotime($event->sales->public->endDateTime));
      // $genre = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties(['name' => $event->classifications[0]->genre->name]);
      // $hero_image_source = $event->images[0]->url;
      // $location = $event->_embedded->venues[0]->name;
      // $max_price = $event->priceRanges[0]->max;
      // $min_price = $event->priceRanges[0]->min;
      // $performer = $event->_embedded->attractions[0]->name;
      // $onsale = false;
      // $seatmap = null;
      // $field_start_sale_date = date('Y-m-d\TH:i:s', strtotime($event->sales->public->startDateTime));

      // // check if event has a name
      // if (empty($name)) {
      //   $name = 'No name';
      // }
      
      // // check if event has an address
      // if (empty($address)) {
      //   $address = 'No address';
      // }

      // // check if event has a description
      // if (empty($body)) {
      //   $body = 'No description';
      // }

      // // check if event has a city
      // if (empty($city)) {
      //   $city = 'No city';
      // }

      // // does the check if the taxonomy terms exist, if not, create them
      // if (empty($country)) {
      //   // taxonomy term does not exist, create it
      //   $country = \Drupal\taxonomy\Entity\Term::create([
      //     'name' => $event->_embedded->venues[0]->country->name,
      //     'vid' => 'country',
      //   ]);
      //   $country->save();
      // } else {
      //   $country = array_values($country)[0];
      // }

      // // check if event has a date
      // if (empty($date)) {
      //   $date = 'No date';
      // }

      // // check if event has an end sale date
      // if (empty($end_sale_date)) {
      //   $end_sale_date = 'No end sale date';
      // }

      // // check if event has a location
      // if (empty($genre)) {
      //   // taxonomy term does not exist, create it
      //   $genre = \Drupal\taxonomy\Entity\Term::create([
      //     'name' => $event->classifications[0]->genre->name,
      //     'vid' => 'genre',
      //   ]);
      //   $genre->save();
      // } else {
      //   $genre = array_values($genre)[0];
      // }

      // // check if event has a hero image
      // if (empty($hero_image_source)) {
      //   $hero_image_source = 'https://via.placeholder.com/300';
      // }

      // // check if event has a location
      // if (empty($location)) {
      //   $location = 'No location';
      // }

      // // check if event has a max price
      // if (empty($max_price)) {
      //   $max_price = 'No max price';
      // }

      // // check if event has a min price
      // if (empty($min_price)) {
      //   $min_price = 'No min price';
      // }

      // // check if event has a performer
      // if (empty($performer)) {
      //   $performer = 'No performer';
      // }

      // // make a bolean for the sales status
      // if ($event->dates->status->code == 'onsale') {
      //   $onsale = true;
      // }

      // // searches for the biggets image in size to use as the hero image
      // // width size is used to compare
      // $biggest = 0;
      // // key is used to get the image url
      // $biggest_key = 0;
      // foreach ($event->images as $key => $image) {
      //   if ($image->width > $biggest) {
      //     $biggest = $image->width;
      //     $biggest_key = $key;
      //   }
      // }

      // // get the url of seatmap
      // if (empty($seatmap)) {
      //   $seatmap = 'https://via.placeholder.com/300';
      // } else {
      //   $seatmap = $event->seatmap->staticUrl;
      // }

      // // check if event has a start sale date
      // if (empty($field_start_sale_date)) {
      //   $field_start_sale_date = 'No start sale date';
      // }

      // // create the node
      // $node = \Drupal\node\Entity\Node::create([
      //   'type' => 'event',
      //   'title' => $name,
      //   'field_adress' => $address,
      //   'body' => [
      //     'value' => $body,
      //     'format' => 'full_html',
      //   ],
      //   'field_city' => $city,
      //   'field_country' => [
      //     'target_id' => $country->id(),
      //   ],
      //   'field_date' => $date,
      //   'field_end_sale_date' => $end_sale_date,
      //   'field_genre' => [
      //     'target_id' => $genre->id(),
      //   ],
      //   'field_hero_image_source' => $hero_image_source,
      //   'field_location' => $location,
      //   'field_max_price' => $max_price,
      //   'field_min_price' => $min_price,
      //   'field_performer' => $performer,
      //   'field_sales_status' => $onsale,
      //   'field_seatmap_image_source' => $seatmap,
      //   'field_start_sale_date' => $field_start_sale_date,
      // ]);
      // $node->save();
    }
  }
}