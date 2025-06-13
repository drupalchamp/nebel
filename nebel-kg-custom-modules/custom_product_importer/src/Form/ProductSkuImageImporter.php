<?php

    namespace Drupal\custom_product_importer\Form;

    use Drupal\Core\Form\FormBase;
    use Drupal\Core\Form\FormStateInterface;
    use Drupal\file\Entity\File;

    /**
     * Class ProductSkuImageImporter
     *
     * This class extends FormBase and is used to import product images and attributes from a CSV file.
     * It provides methods for building the form, handling form submission, processing the CSV file, and managing related entities.
     */
    class ProductSkuImageImporter extends FormBase {

        /**
         * Returns the unique form ID for the product import form.
         *
         * @return string
         *   The form ID as a string.
         */
        public function getFormId() {
            return 'product_sku_image_import_form';
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
        public function buildForm(array $form, FormStateInterface $form_state) {

            $form['field_sku'] = [
                '#type' => 'textfield',
                '#title' => $this->t('SKU ID'),
                '#required' => TRUE,
            ];

            $form['img_file'] = [
                '#type' => 'managed_file',
                '#title' => $this->t('Upload SKU Image'),
                '#description' => $this->t('Upload an image for the SKU.'),
                '#upload_location' => 'public://product_images/',
                '#upload_validators' => [
                    'file_validate_extensions' => ['jpg jpeg png gif'],
                ],
                '#required' => TRUE,
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
         * Handles form submission.
         *
         * @param array $form
         *   The form structure.
         * @param \Drupal\Core\Form\FormStateInterface $form_state
         *   The current state of the form.
         */
        public function submitForm(array &$form, FormStateInterface $form_state) {
            $sku = $form_state->getValue('field_sku');
            $file_id = $form_state->getValue('img_file')[0];
    
            // Load product variation by SKU.
            $product_variations = \Drupal::entityTypeManager()
                ->getStorage('commerce_product_variation')
                ->loadByProperties(['sku' => $sku]);
            $product_variation = reset($product_variations);
    
            if (!$product_variation) {
                \Drupal::messenger()->addError($this->t('No product variation found for SKU: @sku', ['@sku' => $sku]));
                return;
            }
    
            // Load the uploaded file.
            if ($file_id) {
                $file = File::load($file_id);
                if ($file) {
                    $file->setPermanent();
                    $file->save();
                    
                    // Get color attribute reference.
                    $color_entities = $product_variation->get('attribute_color')->referencedEntities();
                    $color_attribute = reset($color_entities);
    
                    if ($color_attribute) {
                        // Set image field.
                        $color_attribute->set('field_upload_color_palette_image', [
                            'target_id' => $file->id(),
                        ]);
                        $color_attribute->save();
    
                        \Drupal::messenger()->addStatus($this->t('Image uploaded successfully for SKU: @sku', ['@sku' => $sku]));
                    } else {
                        \Drupal::messenger()->addWarning($this->t('No color attribute found for SKU: @sku', ['@sku' => $sku]));
                    }
                }
            }
    
            // Save product variation if modified.
            $product_variation->save();
    
            // Log data.
            // \Drupal::logger('custom_product_importer')->notice('Processed SKU: @sku', ['@sku' => $sku]);
        }
    }
