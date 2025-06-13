<?php

namespace Drupal\product_listing\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\taxonomy\Entity\Term;
use Drupal\commerce_product\Entity\Product;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\path_alias\AliasManagerInterface;

/**
 * Class ProductListingByBrandController.
 *
 * Provides route responses for the Product Listing module.
 */
class ProductListingByBrandController extends ControllerBase {


  public function listProductByBrand() {
 
        // Getting Brand Name from Route
$brand_name = \Drupal::routeMatch()->getParameter('name');
$brand = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties([
        'name' => $brand_name,
        'vid' => 'brands',
      ]);
      
  $markup = "";

if(isset($brand) && !empty($brand)){

        $markup = "<div class='row child-listing'>";
        // $markup .= '<h3 class="title">' . reset($brand)->getName() . '</h3>';

        $markup .= "<div class='product-listing-banner'><div class='text'>".t('Manufactured By ').reset($brand)->getName()."</div>";

        // Getting Brand ID
        $brand_id = reset($brand)->id();

     if(isset(reset($brand)->get('field_brand_icon')[0]) && !empty(reset($brand)->get('field_brand_icon')[0])){
        $brand_image_id = reset($brand)->get('field_brand_icon')[0]->target_id;
       
        $image = \Drupal\file\Entity\File::load($brand_image_id);
        
        
                $brand_image_url = \Drupal::service('file_url_generator')->generateAbsoluteString($image->getFileUri());
                $markup.= "<div class='brand-img'><img src=".$brand_image_url."></div>";
        }
        else{
                $node = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties([
                        'title' => $brand_name
                ]);
                $node_image_id = reset($node)->get('field_upload_image')[0]->target_id;
                $node_image = \Drupal\file\Entity\File::load($node_image_id);
                $node_image_url = \Drupal::service('file_url_generator')->generateAbsoluteString($node_image->getFileUri());
                $markup.= "<div class='brand-img'><img src=".$node_image_url."></div>";  
        }
        
        $markup.="</div><div class='col-sm-12 item-in-grid'>";


        $products = \Drupal::entityTypeManager()->getStorage('commerce_product')->loadByProperties([
                'type' => 'nebel',
                'field_brands' => $brand_id
        ]);

        if(isset($products) && !empty($products)){

                usort($products, function ($a, $b) {
                        return strcmp($a->getTitle(), $b->getTitle());
                });
                
                foreach($products as $p){
                        $markup .= '<p><a href="' . $p->toUrl()->toString() . '">' . $p->getTitle() . '</a></p>';
                }
        }
        else{
                $markup.="<h4>Product doesn't exist of this brand,</h4>";    
        }
        
        $markup.="</div></div>";
}
else{
        $markup.=  "<h4> ".$brand_name." brand doesn't exist.";
}

         

    return [
        '#markup' => $markup
    ];
  }
}
