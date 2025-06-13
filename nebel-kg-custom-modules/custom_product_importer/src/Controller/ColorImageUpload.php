<?php

namespace Drupal\custom_product_importer\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\commerce_product\Entity\Product;
use Drupal\file\Entity\File;
use Symfony\Component\HttpFoundation\Response;
use Drupal\commerce_product\Entity\ProductAttributeValue;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\Core\Url;
use Drupal\field\Entity\FieldConfig;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\commerce_product\Entity\ProductVariationType;
use Drupal\Core\Field\FieldConfigInterface;



/**
 * Class ColorImageUpload.
 */
class ColorImageUpload extends ControllerBase {


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
    $utf8_string = iconv($detected_encoding, "UTF-8", $original_string);
} else {
    $utf8_string = $original_string; // It's already in UTF-8
}
    return $utf8_string;
  }


  public static function getAttributeId($attr,$attribute_value)
  {
    // Use the entity type manager to query for ProductAttributeValue entities.
    $storage = \Drupal::entityTypeManager()->getStorage('commerce_product_attribute_value');
    $attribute_values = $storage->loadByProperties(['name' => $attribute_value]);

    if (!empty($attribute_values)) {
      // Return the first matching attribute value.
      return reset($attribute_values)->id();
    } else {
      // Create a new ProductAttributeValue entity for the color.
      $attribute = ProductAttributeValue::create([
        'attribute' => $attr, // Ensuring this is the correct attribute machine name.
        'name' => $attribute_value,
      ]);
      $attribute->save();
      return $attribute->id();
    }
  }

  public static function processProductVariations($product_data, array &$context) {

    foreach ($product_data as $key  => $data) {
      
      $data_chunk = explode(';',$data);
      
      // Extracting sku and guise from CSV record
      $sku =  self::sanitizeInput($data_chunk[0]); 
      $guise =  sanitizeInput($data_chunk[9]);
      self::logData('guise added',$guise);
      if(isset($sku) || !empty($sku)){
         // Accessing product variation using sku
           $product_variation = \Drupal::entityTypeManager()
             ->getStorage('commerce_product_variation')
             ->loadByProperties(['sku' => $sku]);
           $product_variation = reset($product_variation); //loading variation

          if($product_variation){
            $product_variation->set('field_guise',strtolower($guise));
            $product_variation->save();
          }
   }
  }
  }
  public static function logData($key,$data){
    \Drupal::logger($key)->notice('<pre><code>'.print_r($data,TRUE).'</code></pre>');
  }

  public static function batchFinished($success, $results, $operations) {
    if ($success) {
      \Drupal::messenger()->addMessage(t('All guise added to product variations successfully.'));
    }
    else {
      \Drupal::messenger()->addError(t('Batch did not complete.'));
    }
  }


  public function setImage() { 
 
    $csv_path = 'https://nebel.bright-digital.de/sites/default/files/feeds/csv/ARTIKEL.CSV';
    // // Function to check if a path points to a CSV file
    function is_csv_file($path) {
      // Get the extension of the file and convert it to lowercase
      $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
      return $extension === 'csv';
    }


    $valid_csv_path = false;
    // Check if the path is a valid file and has a .csv extension
    if (filter_var($csv_path, FILTER_VALIDATE_URL)) {

      // If it's a URL, check the extension
      if (is_csv_file($csv_path)) {
        $valid_csv_path = true;
      } else {
        $valid_csv_path = false;
      }
    } elseif (file_exists($csv_path) && is_csv_file($csv_path)) {
      // If it's a local file, check if it exists and has a .csv extension
      $valid_csv_path = true;
    } else {
      $valid_csv_path = false;
    }

    if($valid_csv_path){

      $product_data = [];

      // Open the input CSV file for reading
      if (($handle = fopen($csv_path, 'r')) !== false) {
        while (($row = fgetcsv($handle)) !== false) {
          // Remove trailing empty values
          while (end($row) === '') {
            array_pop($row);
          }
          $combinedData = implode(',', $row);
          $product_data[] = $combinedData;
        }
        // Close the file after reading
        fclose($handle);
      }



      // Configuring batch operation for product updation in chunks
      $batch = [
        'title' => 'Importing variations guise data from CSV...',
        'operations' => [],
        'finished' => [__CLASS__, 'batchFinished']
      ];

      //calling batchProcess for each chunk(1) of nodes
      foreach (array_chunk($product_data, 100) as $chunk) {
        $batch['operations'][] = [[__CLASS__, "processProductVariations"], [$chunk]];
      }
      batch_set($batch);
    }
    return batch_process(Url::fromRoute('<front>')->toString());

//   $product_images = [
//     "Canvas Art Canvas Smooth 22102.jpg",
//     "Canvas Art Canvas Smooth 22103.jpg",
//     "Canvas Canvas Artist 22106.jpg",
//     "Canvas Canvas Artist 22107.jpg",
//     "14992.JPG",
//     "20262.JPG",
//     "20277.JPG"
// ];

// $product_images = array(
//   "22102.jpg", "22103.jpg", "22106.jpg", "22107.jpg", "22117.jpg", "22118.jpg", 
//   "22110.jpg", "22111.jpg", "22104.jpg", "22105.jpg", "22092.jpg", "22096.jpg", 
//   "22085.jpg", "22086.jpg", "22088.jpg", "21908.jpg", "22060.jpg", "22061.jpg", 
//   "21891.jpg", "22048.jpg", "22049.jpg", "22053.jpg", "21917.jpg", "22073.jpg", 
//   "22076.jpg", "22318.jpg", "22324.jpg", "23028.jpg", "21921.jpg", "22078.jpg", 
//   "22066.jpg", "22069.jpg", "23110.jpg", "23116.jpg", "21952.jpg", "21953.jpg", 
//   "21889.jpg", "21890.jpg", "21978.jpg", "21979.jpg", "21982.jpg", "21987.JPG", 
//   "21997.jpg", "21998.jpg", "21895.jpg", "21960.jpg", "21961.jpg", "21962.jpg", 
//   "21989.jpg", "21990.jpg", "21885.jpg", "21898.jpg", "21899.jpg", "21964.jpg", 
//   "21964.jpg", "23106.jpg", "21973.jpg", "21975.jpg", "21977.jpg", "22229.jpg", 
//   "22230.jpg", "22269.jpg", "21894.jpg", "21949.jpg", "22491.jpg", "22492.jpg", 
//   "22602.jpg", "21897.jpg", "22479.jpg", "23087.jpg", "23088.jpg", "22480.jpg", 
//   "22481.jpg", "22600.jpg", "22819.jpg", "22821.jpg", "21881.jpg", "22206.jpg", 
//   "21945.jpg", "22063.jpg", "22162.jpg", "22167.jpg", "22168.jpg", "22173.jpg", 
//   "22176.jpg", "22177.jpg", "22178.jpg", "22153.jpg", "22157.jpg", "22161.jpg", 
//   "22150.jpg", "22151.jpg", "22152.jpg", "22062.jpg", "22195.jpg", "22199.jpg", 
//   "23120.jpg", "23128.jpg", "22006.jpg", "22007.jpg", "22021.jpg", "22026.jpg", 
//   "22038.jpg", "22042.jpg", "22045.jpg", "22046.JPG", "22014.jpg", "22018.jpg", 
//   "21999.jpg", "22002.jpg", "22029.jpg", "22033.jpg", "22036.JPG", "21929.jpg", 
//   "22137.jpg", "22609.jpg", "21883.jpg", "21925.jpg", "21926.jpg", "21927.jpg", 
//   "22868.jpg", "22869.jpg", "22871.jpg", "22872.jpg", "22873.jpg", "22874.jpg", 
//   "22125.jpg", "22126.jpg", "22128.jpg", "22129.jpg", "22131.jpg", "22134.jpg", 
//   "21931.jpg", "21932.jpg", "21933.jpg", "21934.jpg", "21935.jpg", "21936.jpg", 
//   "22208.jpg", "22209.jpg", "22501.jpg", "22502.jpg", "21054.jpg", "14992.JPG", 
//   "20262.JPG", "20277.JPG"
// );

//   foreach($product_images as $pI){
//     $sku = pathinfo($pI, PATHINFO_FILENAME);
//   if(isset($sku) || !empty($sku)){
//      // Accessing product variation using sku
//      $product_variation = \Drupal::entityTypeManager()
//      ->getStorage('commerce_product_variation')
//      ->loadByProperties(['sku' => $sku]);
//    $product_variation = reset($product_variation); //loading variation

//    if (isset($product_variation) && !empty($product_variation)) {
//      $product = $product_variation->getProduct();
    
//     if(isset($product) && !empty($product)){
//     $product_image_filename = $pI;
//            if(isset($product_image_filename) && !empty($product_image_filename)){
    
//               $product_image_destination = 'public://product_images/' . $product_image_filename;
           
//                 // Creating a new file object from the uploaded image.
//                 $file = File::create([
//                   'uri' => $product_image_destination,
//                   'status' => 1, // Set the file as permanent.
//                 ]);
                
//                 // Save the file.
//                 $file->save();
//                 $product->get('field_upload_product_image')->appendItem([
//                   'target_id' => $file->id(),
//               ]);
    
//     $product->save();           
//     }
//     }
//   }
// }
//     }

// $custom_product_id = 49;
// $color = '506 INDIA INK';
// $full_color_name = $custom_product_id . '(' . $color . ')';
// $color_attribute_id = NULL;
// if(isset($full_color_name) && !empty($full_color_name)){
//   // Use the entity type manager to query for ProductAttributeValue entities.
//   $storage = \Drupal::entityTypeManager()->getStorage('commerce_product_attribute_value');
//   $color_values = $storage->loadByProperties(['name' => $full_color_name]);

//   if (!empty($color_values)) {
//     // Return the first matching color value.
//     $color_attribute_id = reset($color_values)->id();
//   } else {
//     // Create a new ProductAttributeValue entity for the color.
//     $color = ProductAttributeValue::create([
//       'attribute' => 'color', // Ensure this is the correct attribute machine name.
//       'name' => $full_color_name,
//     ]);
//     $color->save();
//     $color_attribute_id = $color->id();
//   }
// }


// $product_variation = \Drupal::entityTypeManager()
//      ->getStorage('commerce_product_variation')
//      ->loadByProperties(['sku' => 22941]);
//    $product_variation = reset($product_variation);
// $product_variation->set('attribute_color', $color_attribute_id);
// $product_variation->save();

// $product_images = [
// '22063.jpg'
// ];

//   foreach($product_images as $pI){
//     // $sku = pathinfo($pI, PATHINFO_FILENAME);
//     $sku = 874;
//   if(isset($sku) || !empty($sku)){
//      // Accessing product variation using sku
//      $product_variation = \Drupal::entityTypeManager()
//      ->getStorage('commerce_product_variation')
//      ->loadByProperties(['sku' => $sku]);
//    $product_variation = reset($product_variation); //loading variation

//    if (isset($product_variation) && !empty($product_variation)) {
//     $color_entities = $product_variation->get('attribute_color')->referencedEntities();
//     $color_attribute = reset($color_entities);

//     if (isset($color_attribute) && !empty($color_attribute)) {
//             $color_image_destination = 'public://product_images/' . $pI;
            
//               // Creating a new file object from the uploaded image.
//               $file = File::create([
//                 'uri' => $color_image_destination,
//                 'status' => 1, // Set the file as permanent.
//               ]);
              
//               // Save the file.
//               $file->save();

//               $color_attribute->set('field_upload_color_palette_image', [
//                     'target_id' => $file->id(),
//                   ]);

//                   $color_attribute->save();
//                     }       
//    }

//   }

// }




        // return new Response("");
  }
  

}