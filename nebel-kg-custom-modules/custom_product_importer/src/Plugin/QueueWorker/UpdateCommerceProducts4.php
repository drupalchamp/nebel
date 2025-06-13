<?php

namespace Drupal\custom_product_importer\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;


/**
 * Processes product updates from the queue.
 *
 * @QueueWorker(
 *   id = "commerce_product_updater_queue4",
 *   title = @Translation("Product Updater Queue Worker4"),
 *   cron = {"time" = 300}
 * )
 */
class UpdateCommerceProducts4 extends QueueWorkerBase {
  /**
   * Processes each product update.
   */
  public function processItem($product_data) {
    // \Drupal::logger('Queue Worker4 processItem')->notice('Queue worker4 started the updating of products in queue');
    custom_product_importer_update_product($product_data);
  }
}
