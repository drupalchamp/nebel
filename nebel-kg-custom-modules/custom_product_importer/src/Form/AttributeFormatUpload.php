<?php

    namespace Drupal\custom_product_importer\Form;

    use Drupal\Core\Form\FormBase;
    use Drupal\Core\Form\FormStateInterface;
    use Drupal\commerce_product\Entity\ProductAttributeValue;

    /**
     * Class AttributeFormatUpload
     *
     * This class extends FormBase and is used to import product variation format attribute from a CSV file.
     * It provides methods for building the form, handling form submission, processing the CSV file, and managing related entities.
     */
    class AttributeFormatUpload extends FormBase {

        /**
         * Returns the unique form ID for the product import form.
         *
         * @return string
         *   The form ID as a string.
         */
        public function getFormId() {
            return 'product_variation_format_upload_form';
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

            $form['field_format'] = [
                '#type' => 'textfield',
                '#title' => $this->t('Format'),
                '#required' => TRUE,
                '#attributes' => [
                    'placeholder' => 'Enter format attribute name'
                ]
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

            $format = $form_state->getValue('field_format');
      
            if(isset($format) && !empty($format)){
            
            $storage = \Drupal::entityTypeManager()->getStorage('commerce_product_attribute_value');
            $format_values = $storage->loadByProperties(['name' => $format]);

            if (!empty($format_values)) {
                \Drupal::messenger()->addMessage($this->t('Format @format already exist.', ['@format' => $format]),'warning');
            } else {
                // Create a new ProductAttributeValue entity for the type
                $format_attr = ProductAttributeValue::create([
                'attribute' => 'select_format', 
                'name' => $format,
                ]);
                $format_attr->save();
                \Drupal::messenger()->addMessage($this->t('Format @format uploaded successfully.', ['@format' => $format]));
            }
            }
        }
    }
