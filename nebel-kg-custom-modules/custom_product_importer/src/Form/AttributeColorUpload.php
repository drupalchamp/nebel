<?php

    namespace Drupal\custom_product_importer\Form;

    use Drupal\Core\Form\FormBase;
    use Drupal\Core\Form\FormStateInterface;
    use Drupal\commerce_product\Entity\ProductAttributeValue;
    use Drupal\file\Entity\File;

    /**
     * Class AttributeColorUpload
     *
     * This class extends FormBase and is used to import product variation color attribute from a CSV file.
     * It provides methods for building the form, handling form submission, processing the CSV file, and managing related entities.
     */
    class AttributeColorUpload extends FormBase {

        /**
         * Returns the unique form ID for the product import form.
         *
         * @return string
         *   The form ID as a string.
         */
        public function getFormId() {
            return 'product_variation_color_upload_form';
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

            $form['field_color'] = [
                '#type' => 'textfield',
                '#title' => $this->t('Color'),
                '#required' => TRUE,
                '#attributes' => [
                    'placeholder' => 'Enter color code'
                ]
            ];
            
            $form['img_file'] = [
                '#type' => 'managed_file',
                '#title' => $this->t('Upload Image'),
                '#description' => $this->t('Upload an image for the color.'),
                '#upload_location' => 'public://product_images/',
                '#upload_validators' => [
                    'file_validate_extensions' => ['jpg jpeg png gif'],
                ],
            ];
           
            $form['actions'] = [
                '#type' => 'actions',
            ];
            $form['actions']['submit'] = [
                '#type' => 'submit',
                '#value' => $this->t('Add'),
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

            $full_color_name = $form_state->getValue('field_color');

            if(isset($full_color_name) && !empty($full_color_name)){

            $storage = \Drupal::entityTypeManager()->getStorage('commerce_product_attribute_value');
            $color_values = $storage->loadByProperties(['name' => $full_color_name]);

            if (!empty($color_values)) {
                \Drupal::messenger()->addMessage($this->t('Color @full_color_name already exist.', ['@full_color_name' => $full_color_name]),'warning');
            } else {
                // Create a new ProductAttributeValue entity for the color.
                $color = ProductAttributeValue::create([
                'attribute' => 'color',
                'name' => $full_color_name,
                ]);
                $color->save();
                \Drupal::messenger()->addMessage($this->t("Color @full_color_name uploaded successfully.", ['@full_color_name' => $full_color_name]),'notice');
            }

            // UPLOADING IMAGE FOR COLOR
            $file_id = $form_state->getValue('img_file')[0];

            // Load the uploaded file.
            if ($file_id) {
                $file = File::load($file_id);
                if ($file) {
                    $file->setPermanent();
                    $file->save();
                    

                    // Get color attribute reference.
                    $values = \Drupal::entityTypeManager()
                    ->getStorage('commerce_product_attribute_value')
                    ->loadByProperties([
                      'name' => $full_color_name,
                      'attribute' => 'color',
                  ]);
                  $color_attribute = reset($values);
                    if ($color_attribute) {
                        // Set image field.
                        $color_attribute->set('field_upload_color_palette_image', [
                            'target_id' => $file->id(),
                        ]);
                        $color_attribute->save();  
                        \Drupal::messenger()->addMessage($this->t("Color's @full_color_name image uploaded successfully.", ['@full_color_name' => $full_color_name]),'notice');
                    } 
                }
                
            } 


            }
        }
    }
