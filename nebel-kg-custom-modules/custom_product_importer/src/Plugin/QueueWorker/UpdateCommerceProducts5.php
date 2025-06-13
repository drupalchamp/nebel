<?php

namespace Drupal\custom_product_importer\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;


/**
 * Processes product updates from the queue.
 *
 * @QueueWorker(
 *   id = "commerce_product_updater_queue5",
 *   title = @Translation("Product Updater Queue Worker5"),
 *   cron = {"time" = 300}
 * )
 */
class UpdateCommerceProducts5 extends QueueWorkerBase {
  /**
   * Processes each product update.
   */
  public function processItem($product_data) {
    // \Drupal::logger('Queue Worker5 processItem')->notice('Queue worker5 started the updating of products in queue');
    custom_product_importer_update_product($product_data);
  }
}
