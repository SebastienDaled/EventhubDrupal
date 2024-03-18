<?php 

namespace Drupal\eventhub_api_content\Plugin\AdvancedQueue\JobType;

use Drupal\advancedqueue\Job;
use Drupal\advancedqueue\JobResult;
use Drupal\advancedqueue\Plugin\AdvancedQueue\JobType\JobTypeBase;
use Drupal\Core\Site\Settings;

/**
 * @AdvancedQueueJobType(
 *   id = "createEvent",
 *   label = @Translation("Create Event"),
 *   description = @Translation("Create Event")
 * )
 */

//  
class createEvent extends JobTypeBase {

  public function process($data) {
    $event = $data->getPayload();
    // make $event->data go from an array to an object
    $event = json_decode(json_encode($event));

    // do a check if the event already exists
    // if it does, update the node
    // if it does not, create a new node
    $all_nodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple();
  

    $name = $event->data->name;
    $address = $event->data->_embedded->venues[0]->address->line1;
    $body = $event->data->description;
    $city = $event->data->_embedded->venues[0]->city->name;
    $country = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties(['name' => $event->data->_embedded->venues[0]->country->name]);
    $date = date('Y-m-d\TH:i:s', strtotime($event->data->dates->start->localDate . ' ' . $event->data->dates->start->localTime));
    $end_sale_date = date('Y-m-d\TH:i:s', strtotime($event->data->sales->public->endDateTime));
    $genre = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties(['name' => $event->data->classifications[0]->genre->name]);
    $hero_image_source = $event->data->images[0]->url;
    $location = $event->data->_embedded->venues[0]->name;
    $max_price = $event->data->priceRanges[0]->max;
    $min_price = $event->data->priceRanges[0]->min;
    // $performer = $event->data->_embedded->attractions[0]->name || 'No performer';
    $performer = 'No performer';
    $onsale = false;
    $seatmap = null;
    $field_start_sale_date = date('Y-m-d\TH:i:s', strtotime($event->data->sales->public->startDateTime));

    // check if event has a name
    if ($event->data->name) {
      $name = 'No name';
    }
    
    // check if event has an address
    if (empty($address)) {
      $address = 'No address';
    }

    // check if event has a description
    if (empty($body)) {
      $body = 'No description';
    }

    // check if event has a city
    if (empty($city)) {
      $city = 'No city';
    }

    // does the check if the taxonomy terms exist, if not, create them
    if (empty($country)) {
      // taxonomy term does not exist, create it
      $country = \Drupal\taxonomy\Entity\Term::create([
        'name' => $event->data->_embedded->venues[0]->country->name,
        'vid' => 'country',
      ]);
      $country->save();
    } else {
      $country = array_values($country)[0];
    }

    // check if event has a date
    if (empty($date)) {
      $date = 'No date';
    }

    // check if event has an end sale date
    if (empty($end_sale_date)) {
      $end_sale_date = 'No end sale date';
    }

    // check if event has a location
    if (empty($genre)) {
      // taxonomy term does not exist, create it
      $genre = \Drupal\taxonomy\Entity\Term::create([
        'name' => $event->data->classifications[0]->genre->name,
        'vid' => 'genre',
      ]);
      $genre->save();
    } else {
      $genre = array_values($genre)[0];
    }

    // check if event has a hero image
    if (empty($hero_image_source)) {
      $hero_image_source = 'https://via.placeholder.com/300';
    }

    // check if event has a location
    if (empty($location)) {
      $location = 'No location';
    }

    // check if event has a max price
    if (empty($max_price)) {
      $max_price = 'No max price';
    }

    // check if event has a min price
    if (empty($min_price)) {
      $min_price = 'No min price';
    }

    // check if event has a performer
    if (empty($performer)) {
      $performer = 'No performer';
    }

    // make a bolean for the sales status
    if ($event->data->dates->status->code == 'onsale') {
      $onsale = true;
    }

    // searches for the biggets image in size to use as the hero image
    // width size is used to compare
    $biggest = 0;
    // key is used to get the image url
    $biggest_key = 0;
    foreach ($event->data->images as $key => $image) {
      if ($image->width > $biggest) {
        $biggest = $image->width;
        $biggest_key = $key;
      }
    }

    // get the url of seatmap
    if (empty($seatmap)) {
      $seatmap = 'https://via.placeholder.com/300';
    } else {
      $seatmap = $event->data->seatmap->staticUrl;
    }

    // check if event has a start sale date
    if (empty($field_start_sale_date)) {
      $field_start_sale_date = 'No start sale date';
    }

    $exists = false;
    // foreach ($all_nodes as $node) {
    //   if ($node->field_id->value == $event->data->id) {
    //     $exists = true;
    //     $node= \Drupal\node\Entity\Node::load($node->id());
    //     $node->set('title', $name);
    //     $node->set('field_adress', $address);
    //     $node->set('body', $body);
    //     $node->set('field_city', $city);
    //     $node->set('field_country', $country->id());
    //     $node->set('field_date', $date);
    //     $node->set('field_end_sale_date', $end_sale_date);
    //     $node->set('field_genre', $genre->id());
    //     $node->set('field_hero_image_source', $hero_image_source);
    //     $node->set('field_location', $location);
    //     $node->set('field_max_price', $max_price);
    //     $node->set('field_min_price', $min_price);
    //     $node->set('field_performer', $performer);
    //     $node->set('field_sales_status', $onsale);
    //     $node->set('field_seatmap_image_source', $seatmap);
    //     $node->set('field_start_sale_date', $field_start_sale_date);
    //     $node->save();
    //   }
    // }

    // create the node
    if (!$exists) {
      $node = \Drupal\node\Entity\Node::create([
        'type' => 'event',
        'title' => $name,
        'field_adress' => $address,
        'body' => [
          'value' => $body,
          'format' => 'full_html',
        ],
        'field_city' => $city,
        'field_country' => [
          'target_id' => $country->id(),
        ],
        'field_date' => $date,
        'field_end_sale_date' => $end_sale_date,
        'field_genre' => [
          'target_id' => $genre->id(),
        ],
        'field_hero_image_source' => $hero_image_source,
        'field_location' => $location,
        'field_max_price' => $max_price,
        'field_min_price' => $min_price,
        'field_performer' => $performer,
        'field_sales_status' => $onsale,
        'field_seatmap_image_source' => $seatmap,
        'field_start_sale_date' => $field_start_sale_date,
      ]);
      $node->save();

      return JobResult::success();
    }
    
  }
}