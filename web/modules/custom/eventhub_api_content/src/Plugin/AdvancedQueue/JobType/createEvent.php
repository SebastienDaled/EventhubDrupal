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
  

    $name = "insert Title";
    $address = "insert Address";
    $body = "insert Body";
    $city =  "insert City";
    $country = "insert Country";
    $date = "insert Date";
    $end_sale_date = "insert End Sale Date";
    $genre = "insert Genre";
    $hero_image_source = 'https://via.placeholder.com/300';
    $location = "insert Location";
    $max_price = 0;
    $min_price = 0;
    $performer = "insert Performer";
    $onsale = false;
    $seatmap = 'https://via.placeholder.com/300';
    $field_start_sale_date = "insert Start Sale Date";

    // check if event has a name
    if (isset($event->data->name)) {
      $name = $event->data->name;
    }
    
    // check if event has an address
    if (isset($event->data->_embedded->venues[0]->address->line1)) {
      $address = $event->data->_embedded->venues[0]->address->line1;
    }

    // check if event has a description
    if (isset($event->data->description)) {
      $body = $event->data->description;
    }

    // check if event has a city
    if (isset($event->data->_embedded->venues[0]->city->name)) {
      $city = $event->data->_embedded->venues[0]->city->name;
    }

    // does the check if the taxonomy terms exist, if not, create them
    if (isset($event->data->_embedded->venues[0]->country->name)) {
      // taxonomy term does not exist, create it
      $country = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties(['name' => $event->data->_embedded->venues[0]->country->name]);
      
      if (empty($country)) {
        $country = \Drupal\taxonomy\Entity\Term::create([
          'name' => $event->data->_embedded->venues[0]->country->name,
          'vid' => 'country',
        ]);
        $country->save();
      } else {
        $country = array_values($country)[0];
      }

    } else {
      $country = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties(['name' => "Belgium"]);;
      $country = array_values($country)[0];
    }
    // check if event has a date
    if (isset($event->data->dates->start->localDate) && isset($event->data->dates->start->localTime)) {
      $date = date('Y-m-d\TH:i:s', strtotime($event->data->dates->start->localDate . ' ' . $event->data->dates->start->localTime));
    }

    // check if event has an end sale date
    if (isset($event->data->sales->public->endDateTime)) {
      $end_sale_date = date('Y-m-d\TH:i:s', strtotime($event->data->sales->public->endDateTime));
    }

    // check if event has a location
    if (isset($event->data->classifications[0]->genre->name)) {

      $genre = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties(['name' => $event->data->classifications[0]->genre->name]);

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
    } else {
      $genre = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties(['name' => "Other"]);
      $genre = array_values($genre)[0];
    }

    // check if event has a hero image
    if (isset($event->data->images[0]->url)) {
      $hero_image_source = $event->data->images[0]->url;
    }

    // check if event has a location
    if (isset($event->data->_embedded->venues[0]->name)) {
      $location = $event->data->_embedded->venues[0]->name;
    }

    // check if event has a max price
    if (isset($event->data->priceRanges[0]->max)) {
      $max_price = $event->data->priceRanges[0]->max;
    }

    // check if event has a min price
    if (isset($event->data->priceRanges[0]->min)) {
      $min_price = $event->data->priceRanges[0]->min;
    }

    // check if event has a performer
    if (isset($event->data->_embedded->attractions[0]->name)) {
      $performer = $event->data->_embedded->attractions[0]->name;
    }

    // make a bolean for the sales status
    if (isset($event->data->dates->status->code) && $event->data->dates->status->code === 'onsale') {
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
    if (isset($event->data->seatmap->staticUrl)) {
      $seatmap = $event->data->seatmap->staticUrl;
    }

    // check if event has a start sale date
    if (isset($event->data->sales->public->startDateTime)) {
      $field_start_sale_date = date('Y-m-d\TH:i:s', strtotime($event->data->sales->public->startDateTime));
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
          'target_id' => intval($country->id()),
        ],
        'field_date' => $date,
        'field_end_sale_date' => $end_sale_date,
        'field_genre' => [
          'target_id' => intval($genre->id()),
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