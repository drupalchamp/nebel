<?php

namespace Drupal\product_listing\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\taxonomy\Entity\Term;
use Drupal\commerce_product\Entity\Product;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\path_alias\AliasManagerInterface;

/**
 * Class ProductListingController.
 *
 * Provides route responses for the Product Listing module.
 */
class ProductListingController extends ControllerBase {

  /**
   * The path alias manager service.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * Constructs a ProductListingController object.
   *
   * @param \Drupal\path_alias\AliasManagerInterface $alias_manager
   *   The path alias manager service.
   */
  public function __construct(AliasManagerInterface $alias_manager) {
    $this->aliasManager = $alias_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('path_alias.manager')
    );
  }

  /**
   * Lists the term based on URL alias and its direct child terms.
   *
   * @param string $url_alias
   *   The URL alias of the category (taxonomy term).
   *
   * @return array
   *   A render array containing the term and its children.
   */
  public function listTerms($url_alias) {
    // Resolve the URL alias to the internal path.
    $current_langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
   
    $path = $this->aliasManager->getPathByAlias('/' . $url_alias);

    // Extract the term ID from the internal path.
    if (preg_match('/^\/taxonomy\/term\/(\d+)$/', $path, $matches)) {
      $term_id = $matches[1];
    }
    else {
      throw new NotFoundHttpException();
    }

    // Load the term by ID.
    $term = Term::load($term_id);
    $translated_term = $term->getTranslation($current_langcode);
    $translated_name = $translated_term->getName();

    if (!$term) {
      throw new NotFoundHttpException();
    }

    // Load direct child terms of this term.
    $child_terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('product', $term->id(), 1, TRUE);


    // Prepare the output.
    $markup = '';
	
	if (!empty($term)) {
		// $markup .= '<ul class="breadcrumb"><li class="breadcrumb-item"><a href="/">Home</a></li><li class="breadcrumb-item">' . $term->getName() . '</li></ul>';
    $markup .= '<ul class="breadcrumb"><li class="breadcrumb-item"><a href="/">Home</a></li><li class="breadcrumb-item">' . $translated_name . '</li></ul>';
	}

    if (!empty($child_terms)) {
      // Description
      // $description = $translated_term->hasField('description') ? $translated_term->get('description')->value : '';
      // if ($description) {
      //   $markup .= '<h2 class="parent-title">' . t($description) . '</h2>';
      // }

      if ($translated_name) {
        $markup .= '<h2 class="parent-title term-'.$term_id.'"><p>' . t($translated_name) . '</p></h2>';
      }

      // Banner Image
      if ($term->hasField('field_banner_image') && !$term->get('field_banner_image')->isEmpty()) {
        $file = $term->get('field_banner_image')->entity;
        if ($file) {
          $parent_image = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
          $markup .= '<img src="' . $parent_image . '" alt="' . $term->getName() . '"> ';
        }
        
      }else{
        $markup .= '<img src="https://demoworksite.online/sites/default/files/2024-12/default-banner-image.png" alt="default-banner">';
      }

      // Child terms and their products
      foreach ($child_terms as $child_term) {
        $markup .= '<div class="row child-listing">';
        // Category Name
        $markup .= '<h3 class="title">' . $child_term->getName() . '</h3>';

        // Category image
        // if ($child_term->hasField('field_category_image') && !$child_term->get('field_category_image')->isEmpty()) {
        //     $markup .= '<div class="col-sm-3">';
        //         $child_file = $child_term->get('field_category_image')->entity;
        //         if ($child_file) {
        //             $child_image = \Drupal::service('file_url_generator')->generateAbsoluteString($child_file->getFileUri());
        //             $markup .= '<img src="' . $child_image . '" alt="' . $child_term->getName() . '"> ';
        //         }
        //     $markup .= '</div>';
        // }
        if ($child_term->hasField('field_category_image') && !$child_term->get('field_category_image')->isEmpty()) {
          $child_file = $child_term->get('field_category_image')->entity;
          if ($child_file) {
              $child_image = \Drupal::service('file_url_generator')->generateAbsoluteString($child_file->getFileUri());
          } else {
              // Fallback to a default image
              $child_image = '/modules/custom/product_listing/images/default.jpg';
          }
      } else {
          // Fallback to a default image
          $child_image = '/modules/custom/product_listing/images/default.jpg';
      }
      $markup .= '<img src="' . $child_image . '" alt="' . $child_term->getName() . '"> ';

        // Query for products associated with this child term
        $product_query = \Drupal::entityQuery('commerce_product')
          ->condition('field_product_category.target_id', $child_term->id())
          ->accessCheck(TRUE)  // Explicitly set access check to TRUE
          ->execute();

        if ($product_query) {
            $markup .= '<div class="col-sm-9 item-in-grid">';
                $products = Product::loadMultiple($product_query);
               
                foreach ($products as $product) {
                  if(empty($product->getTitle())){
                    $product_title = $product->getTitle();
                    $product_url = $product->toUrl()->toString();
                    $markup .= '<p><a class="no-title" href="' . $product_url . '">' . $product_title . '</a></p>';
                  }
                  else{
                    $product_title = $product->getTitle();
                    $product_url = $product->toUrl()->toString();
                    $markup .= '<p><a href="' . $product_url . '">' . $product_title . '</a></p>';
                  }
                  
                }
            $markup .= '</div>';
        }
        $markup .= '</div>';
      }

    } else {
      // Particular child term
      $description = $term->hasField('description') ? $term->get('description')->value : '';
      if ($description) {
        $markup .= '<p>' . $description . '</p>';
      }

      if ($term->hasField('field_banner_image') && !$term->get('field_banner_image')->isEmpty()) {
        $file = $term->get('field_banner_image')->entity;
        if ($file) {
          $parent_image = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
          $markup .= '<img src="' . $parent_image . '" alt="' . $term->getName() . '"> ';
        }
      }
      $markup .= '<div class="row child-listing">';
      $markup .= '<h3 class="title">' . $term->getName() . '</h3>';

      if ($term->hasField('field_category_image') && !$term->get('field_category_image')->isEmpty()) {
        $markup .= '<div class="col-sm-3">';
            $file = $term->get('field_category_image')->entity;
            if ($file) {
            $parent_image = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
            $markup .= '<img src="' . $parent_image . '" alt="' . $term->getName() . '"> ';
            }
        $markup .= '</div>';
      }
      else{
        $default_img_path = '/modules/custom/product_listing/images/default.jpg';
        $markup .= '<div class="col-sm-3"><img src="'.$default_img_path.'" alt="default-img"></div>';
      }

      // Query for products associated with this term
      $product_query = \Drupal::entityQuery('commerce_product')
        ->condition('field_product_category.target_id', $term->id())
        ->accessCheck(TRUE)  // Explicitly set access check to TRUE
        ->execute();

      if ($product_query) {
        $markup .= '<div class="col-sm-9 item-in-grid">';
            $products = Product::loadMultiple($product_query);
            foreach ($products as $product) {
              if(empty($product->getTitle())){
                $product_title = $product->getTitle();
                $product_url = $product->toUrl()->toString();
                $markup .= '<p><a class="no-title" href="' . $product_url . '">' . $product_title . '</a></p>';
              }else{
                $product_title = $product->getTitle();
                $product_url = $product->toUrl()->toString();
                $markup .= '<p><a href="' . $product_url . '">' . $product_title . '</a></p>';
              }
            }
        $markup .= '</div>';
      }
      $markup .= '</div>';
    }

    // Return the output with the title.
    return [
      '#markup' => $markup,
    ];
  }





  /**
   * Generates the page title based on the term associated with the URL alias.
   *
   * @param string $url_alias
   *   The URL alias of the category (taxonomy term).
   *
   * @return string
   *   The generated page title.
   */
  public function pageTitle($url_alias) {
    // Resolve the URL alias to the internal path.
    $path = $this->aliasManager->getPathByAlias('/' . $url_alias);

    // Extract the term ID from the internal path.
    if (preg_match('/^\/taxonomy\/term\/(\d+)$/', $path, $matches)) {
      $term_id = $matches[1];
    }
    else {
      throw new NotFoundHttpException();
    }

    // Load the term by ID.
    $term = Term::load($term_id);

    if (!$term) {
      throw new NotFoundHttpException();
    }

    // Return the term name as the page title.
    return $term->getName();
  }

}