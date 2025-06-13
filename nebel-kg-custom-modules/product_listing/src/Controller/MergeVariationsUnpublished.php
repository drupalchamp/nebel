<?php

namespace Drupal\product_listing\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\commerce_product\Entity\Product;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\path_alias\AliasManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\commerce_pricelist\Entity\PricelistItem;

/**
 * Class MergeVariationsUnpublished.
 *
 * Provides route responses for the Merging selected variations in commerce-product-variations.
 */

class MergeVariationsUnpublished extends ControllerBase {

        public function is_csv_file($path) {
        if(strpos($path,".CSV?")!=false || strpos($path,".csv?")!=false || strpos($path,".CSV")!=false || strpos($path,".csv")!=false){
         return true;
        }else{
        return false;
      }
      }


      public function logData($key,$data){
        \Drupal::logger($key)->notice('<pre><code>'.print_r($data,TRUE).'</code></pre>');
      }

        public function custom_product_importer_get_products_from_csv(){
       
           // Getting CSV file path from config object's custom textfield field
           $config = \Drupal::config('custom_product_importer.settings');
       
           $csv_path_value_from_config = $config->get('csv_file_path');
           // $csv_path = $csv_path_value_from_config;
       
           $randomString = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 10);
       
           $csv_path = $csv_path_value_from_config.'?'.$randomString;
        

           $valid_csv_path = false;
           // Check if the path is a valid file and has a .csv extension
           if (filter_var($csv_path, FILTER_VALIDATE_URL)) {
             // If it's a URL, check the extension
             if (is_csv_file($csv_path)) {
               $valid_csv_path = true;
             } else {
               $valid_csv_path = false;
             }
           }else{
             $valid_csv_path = false;
           }
       
       
           // If csv path is valid then extract the data from it in product_data
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
                 $product_data[] = $combinedData; //stored product records from csv in $product_data[]
               }
               // Close the file after reading
               fclose($handle);
             }
        
             return $product_data;
         }
         else{
           return 'Wrong CSV path name';
         }
       }


       public function sanitizeInput($input) {
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

       
  public function merge() {

        if(isset($_GET['skus'])){
          
          $all_selected_skus = explode(' ',explode('+',$_GET['skus'])[0]);
      
            $product_sku = $all_selected_skus[0];

            $mergableSKUs = array_slice($all_selected_skus, 1, );

            $products = $this->custom_product_importer_get_products_from_csv(); // Reading CSV and getting product data containing chunks of product_data  
            
            // Updating stock and pricelisting of selected variations
            foreach($all_selected_skus as $sku){
                $product_variation = \Drupal::entityTypeManager()
                ->getStorage('commerce_product_variation')
                ->loadByProperties(['sku' => $sku]);
                $product_variation = reset($product_variation);

                 if ($product_variation) {
                foreach($products as $p){
                        $data_chunk = explode(';',$p);
                        $sku_in_sheet =  sanitizeInput($data_chunk[0]);
                        $inventory_status =  sanitizeInput($data_chunk[21]);
                        $stock =  (float) sanitizeInput($data_chunk[22]); 
                        // Getting Prices and quantities from sheet
                        $unit_1 = $price_1 = $unit_2 = $price_2 = $unit_3 = $price_3 = $unit_4 = $price_4 = $unit_5 = $price_5 = 0;
                        if(sanitizeInput($data_chunk[11]) != 0){
                          $price_1 =  (float) sanitizeInput($data_chunk[11]); 
                          
                        }
                        if(sanitizeInput($data_chunk[12]) != 0){
                          $unit_1 =  (float) sanitizeInput($data_chunk[12]);
                        }
                        if(sanitizeInput($data_chunk[13]) != 0){
                          $price_2 =  (float) sanitizeInput($data_chunk[13]);
                      
                        }
                        if(sanitizeInput($data_chunk[14]) != 0){
                          $unit_2 =  (float) sanitizeInput($data_chunk[14]);
                        }
                        if(sanitizeInput($data_chunk[15]) != 0){
                          $price_3 = (float) sanitizeInput($data_chunk[15]);
                        }
                        if(sanitizeInput($data_chunk[16]) != 0){
                          $unit_3 =  (float) sanitizeInput($data_chunk[16]);
                        }
                        if(sanitizeInput($data_chunk[17]) != 0){
                          $price_4 = (float) sanitizeInput($data_chunk[17]);
                        }
                        if(sanitizeInput($data_chunk[18]) != 0){
                          $unit_4 =  (float) sanitizeInput($data_chunk[18]);
                        }
                        if(sanitizeInput($data_chunk[19]) != 0){
                          $price_5 = (float) sanitizeInput($data_chunk[19]);
                        }
                        if(sanitizeInput($data_chunk[20]) != 0){
                          $unit_5 =  (float) sanitizeInput($data_chunk[20]);
                        }
                        if($unit_2 == $unit_1){
                          $unit_2 = 0;
                          $price_2 = 0;
                        }
                        if($unit_3 == $unit_1){
                          $unit_3= 0;
                          $price_3 = 0;
                        } else {
                          if($unit_3 == $unit_2){
                            $unit_3 = 0;
                            $price_3 = 0;
                          } 
                        }
                        if($unit_4 == $unit_1){
                          $unit_4= 0;
                          $price_4 = 0;
                        } else {
                          if($unit_4 == $unit_2){
                            $unit_4= 0;
                            $price_4 = 0;
                          } else {
                            if($unit_4 == $unit_3){
                              $unit_4= 0;
                              $price_4 = 0;
                            }
                          }
                        }
                        if($unit_5 == $unit_1){
                          $unit_5 = 0;
                          $price_5 = 0;
                        } else {
                          if($unit_5 == $unit_2){
                            $unit_5 = 0;
                            $price_5 = 0;
                          } else {
                            if($unit_5 == $unit_3){
                              $unit_5 = 0;
                              $price_5 = 0;
                            } else {
                              if($unit_5 == $unit_4){
                                $unit_5 = 0;
                                $price_5 = 0;
                              }
                            }
                          }
                        }

                        if($sku_in_sheet == $sku){
                           // Checking and updating price of variation
                            $price_list = \Drupal::entityTypeManager()
                            ->getStorage('commerce_pricelist')
                            ->loadByProperties(['name' => 'Price table']);
                    $pricelist = reset($price_list);

                    $all_price_item = \Drupal::entityTypeManager()
                        ->getStorage('commerce_pricelist_item')
                        ->loadByProperties([
                              'type' => 'commerce_product_variation',
                              'purchasable_entity' => $product_variation->id(),
                              'price_list_id' => $pricelist->id()
                        ]);

                    if(isset($all_price_item) || !empty($all_price_item)){
                      foreach($all_price_item as $p){
                        $p->delete();
                      }
                    }
                            
                    for ($i = 1; $i != 6; $i++) {
                      if(isset(${'unit_' . $i}) && isset(${'price_' . $i})){
                        $unit_variable = ${'unit_' . $i}; // Equivalent to $unit_1, $unit_2, etc.
                        $price_variable = ${'price_' . $i}; // Equivalent to $price_1, $price_2, etc.

                        if (isset($unit_variable) && isset($price_variable)) {
                          if($unit_variable!=0 && $price_variable!=0){
                            $price_item = PriceListItem::create([
                                    'type' => 'commerce_product_variation',
                                    'pricelist' => $pricelist->id(),
                                    'quantity' => $unit_variable,
                                    'purchasable_entity' => $product_variation->id(),
                                    // 'purchasable_entity' => $variation->id(),
                                    'price' => new \Drupal\commerce_price\Price($price_variable, 'EUR'),
                                    'variation' => $product_variation->id(),
                                    'price_list_id' => $pricelist->id()
                            ]);
                            $price_item->save();
                          }
                        }
                      }
                    } 

                          // Updating Stock
                        $database = \Drupal::database();
        
                        //Preparing the raw sql query for stock value update
                        $query = "
                        INSERT INTO commerce_stock_transaction (entity_id, entity_type, qty, location_id, transaction_time, transaction_type_id, related_uid)
                        SELECT :variation_id, 'commerce_product_variation', (:stock - stock), 1, UNIX_TIMESTAMP(), 1, :related_uid
                        FROM (
                                SELECT COALESCE(SUM(qty), 0) AS stock 
                                FROM commerce_stock_transaction 
                                WHERE entity_id = :variation_id
                        ) AS current_stock
                        WHERE (:stock - stock) <> 0;
                        ";
                        
                        $increased_stock = 1000000.00 + $stock;
                        \Drupal::logger('increased_stock')->notice(print_r($increased_stock,TRUE));
                        // Executing the query with dynamic values
                        $database->query($query, [
                        ':variation_id' => $product_variation->id(),
                        ':stock' => (!empty($inventory_status)) ? (($inventory_status == 'O' || $inventory_status == 'H') ? $increased_stock : $stock): $stock,
                        ':related_uid' => \Drupal::currentUser()->id(),
                        ]);

                        $product_variation->save();
                        break;
                        } 
                } 
        }
            }

            // Loading first product using first sku from selected sku and adding all else product into first product
           $variation = \Drupal::entityTypeManager()
          ->getStorage('commerce_product_variation')
          ->loadByProperties(['sku' => $product_sku]);

          if (!empty($variation)) {
            $variation = reset($variation);
           
            $product = $variation->getProduct();
           
            if (isset($product) || !empty($product)) {
            $product_id = $product->id();

                foreach($mergableSKUs as $sku){
                        $variation = \Drupal::entityTypeManager()
                        ->getStorage('commerce_product_variation')
                        ->loadByProperties(['sku' => $sku]);
                         $variation = reset($variation);  
                         
                         if (!$variation) {
                                continue; 
                            }

                        // Checking if the variation is already attached to the target product.
                          if ((int) $variation->get('product_id')->target_id === (int) $product_id) {
                            continue;
                        }
                        $variation->set('product_id', $product_id );
                        $variation->save();
                }
               

                foreach($mergableSKUs as $sku){
                        $variation_data = \Drupal::entityTypeManager()
                        ->getStorage('commerce_product_variation')
                        ->loadByProperties(['sku' => $sku]);
        
                        foreach($variation_data as $v){
                                $product_ids = \Drupal::entityQuery('commerce_product')
                                ->condition('variations.target_id', $v->id())
                                ->accessCheck(true)
                                ->execute();
                                
                                foreach($product_ids as $pid){
                                        if($pid!=$product_id){
                                                $product = \Drupal::entityTypeManager()
                                                ->getStorage('commerce_product')
                                                ->load($pid);
                                                
                                                if ($product) {
                                                        // Get all attached variations.
                                                        $variations = $product->get('variations')->referencedEntities();
                                                       
                                                        if (count($variations) === 1 && $variations[0]->id() == $v->id()) {

                                                                foreach ($variations as $index => $variation) {

                                                                  $product->get('variations')->removeItem($index);
                                                                  $product->save();
                                                                  $product->delete();
                                                                }
                                                        }
                                                        else {
                                                                foreach ($variations as $index => $variation) {
                                                                if ($variation->id() == $v->id()) {
                                                                $product->get('variations')->removeItem($index);
                                                                break;
                                                                }
                                                                }
                                                                $product->save();
                                                        }
                                                }
                                               
                                        }
                                }
                        }
            }
            
        }
        
} 
return new RedirectResponse('/admin/unpublished-product-variations');
        }
    return [
        '#markup' => "<h3>Product Merge Failed, Because of Unknown SKUs</h3>"
    ];
  }
}
