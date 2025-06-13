<?php

namespace Drupal\custom_product_importer\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_pricelist\Entity\PricelistItem;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\commerce_product\Entity\ProductAttributeValue;
use Drupal\taxonomy\Entity\Term;



/**
 * Class ProductNameUpdator
 * 
 * This class extends FormBase and is used to import product name from a CSV file and update in product.
 * It provides methods for building the form, handling form submission, processing the CSV file, and managing related entities.
 * 
 * @property string $formId The form ID.
 * @property array $form The form array.
 * @property \Drupal\Core\Form\FormStateInterface $form_state The form state.
 */

class ProductNameUpdator extends FormBase
{

  /**
   * Returns the unique form ID for the product import form.
   *
   * @return string
   *   The form ID as a string.
   */
  public function getFormId()
  {
    return 'product_attributes_import_form';
  }



  /**
   * Builds the form for importing product data from a CSV file.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */

  public function buildForm(array $form, FormStateInterface $form_state)
  {
   
    $form['csv_file'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Upload CSV File'),
      '#description' => $this->t('Upload a CSV file with product data.'),
      '#upload_location' => 'public://import_products/',
      '#upload_validators' => [
        'file_validate_extensions' => ['csv'],
      ],
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Import'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * Handles the submission of the form, processes the uploaded CSV file, and imports the product data in batches.
   *
   * This method is called when the form is submitted, and it performs the following actions:
   * 1. Retrieves the uploaded CSV file and loads its contents.
   * 2. Reads the CSV file and stores its data in an array.
   * 3. Defines a batch operation to process the product data in chunks.
   * 4. Sets the batch operation and displays a success message if the import is successful.
   *
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    // Get the ID of the uploaded CSV file from the form data
    $fid = $form_state->getValue('csv_file')[0];

    // Load the file object from the file ID
    $file = File::load($fid);

    // Check if the file exists
    if ($file) {

      // Get the path to the uploaded CSV file
      $csv_path = $file->getFileUri();

      // Initialize an empty array to store the product data
      $product_data = [];

      // Open the file for reading
      if (($handle = fopen($csv_path, 'r')) !== FALSE) {
        // Get the header row (optional)
        $headers = fgetcsv($handle);

        // Loop through each row of the CSV
        while (($row = fgetcsv($handle)) !== FALSE) {
          // Combine headers with row data for associative array (optional)
          $product_data[] = array_combine($headers, $row);
        }

        // Close the file after reading
        fclose($handle);
      }

      $batch = [
        'title' => 'Importing product names from CSV...',
        'operations' => [],
        'finished' => [__CLASS__, 'batchFinished']
      ];

      //calling batchProcess for each chunk(2) of nodes
      foreach (array_chunk($product_data, 10) as $chunk) {
        $batch['operations'][] = [[__CLASS__, "batchProcess"], [$chunk]];
      }

      batch_set($batch);
      // \Drupal::messenger()->addMessage($this->t('Products images and format imported successfully.'));
    } else {
      \Drupal::messenger()->addError($this->t('File upload failed.'));
    }
  }

  /**
   * Converts the input string to UTF-8 encoding, ensuring safe use and preventing character encoding issues.
   *
   * @param string $input The input string to be converted.
   * @return string The input string in UTF-8 encoding.
   */
  public static function sanitizeInput($input)
  {
    $original_string = $input;
$detected_encoding = mb_detect_encoding($original_string, mb_detect_order(), true);

if ($detected_encoding !== 'UTF-8') {
    // $utf8_string = iconv($detected_encoding, "UTF-8", $original_string);
    $utf8_string = iconv('Windows-1252', 'UTF-8', $original_string);
} else {
    $utf8_string = $original_string; // It's already in UTF-8
}

    return $utf8_string;
  }



  /**
   * Batch process product data from a CSV file and import it into the Drupal Commerce system.
   *
   * This function iterates through each product in the CSV data, sanitizes the input data,
   * and updates or creates products and product variations in the system.
   *
   * @param array $product_data
   *   The product data from the CSV file, where each key is a product ID and each value is an array of product data.
   * @param array $context
   *   The batch context, which is used to track the progress of the batch process.
   */

  public static function batchProcess($product_data, $context)
  {
  
    foreach ($product_data as $key => $data) {
$product_custom_id = self::sanitizeInput($data['Custom Product ID']);


if(isset($product_custom_id) && !empty($product_custom_id)){
  $product_name = self::sanitizeInput($data['product_name']);
  // \Drupal::logger('name of product id '.$product_custom_id)->notice('<pre><code>'.print_r($product_name,TRUE).'</pre></code>');

$existing_product = \Drupal::entityTypeManager()->getStorage('commerce_product')->loadByProperties([
        'field_custom_product_id' => $product_custom_id
]);

// $existing_product = $product_variation->getProduct(); //loading product using variation
if (isset($existing_product) || !empty($existing_product)) {
$product = reset($existing_product);

if(isset($product_name) && !empty($product_name)){
  $product->setTitle($product_name);
  $product->save();
}
}
}

  

// \Drupal::logger('checking product data')->notice('<pre><code>'.print_r($data,TRUE).'</code></pre>');
    }
    $context['message'] = t('Processing products import');
    
  }

  /**
   * Batch finished callback.
   *
   * @param bool $success
   *   Indicates whether the batch process was successful.
   * @param array $results
   *   Results information passed from the processing callback.
   * @param array $operations
   *   If $success is FALSE, contains the operations that remained unprocessed.
   */
  public static function batchFinished($success, $results, $operations)
  {
    if ($success) {
      \Drupal::messenger()->addStatus(t('All specified products name in csv updated successfully.'));
    } else {
      \Drupal::messenger()->addError(t('An error occurred while importing products.'));
    }
  }








}

