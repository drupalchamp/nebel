<?php

    namespace Drupal\custom_product_importer\Form;

    use Drupal\Core\Form\FormBase;
    use Drupal\Core\Form\FormStateInterface;
    use Drupal\commerce_product\Entity\ProductAttributeValue;

    /**
     * Class AttributeTypeUpload
     *
     * This class extends FormBase and is used to import product variation type attribute from a CSV file.
     * It provides methods for building the form, handling form submission, processing the CSV file, and managing related entities.
     */
    class AttributeTypeUpload extends FormBase {

        /**
         * Returns the unique form ID for the product import form.
         *
         * @return string
         *   The form ID as a string.
         */
        public function getFormId() {
            return 'product_variation_type_upload_form';
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

            $form['field_type'] = [
                '#type' => 'textfield',
                '#title' => $this->t('Type'),
                '#required' => TRUE,
                '#attributes' => [
                    'placeholder' => 'Enter type attribute name'
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

            $type = $form_state->getValue('field_type');
      
            if(isset($type) && !empty($type)){
            
            $storage = \Drupal::entityTypeManager()->getStorage('commerce_product_attribute_value');
            $type_values = $storage->loadByProperties(['name' => $type]);

            if (!empty($type_values)) {
                \Drupal::messenger()->addMessage($this->t('Type @type already exist.', ['@type' => $type]),'warning');
            } else {
                // Create a new ProductAttributeValue entity for the type
                $type_attr = ProductAttributeValue::create([
                'attribute' => 'type', 
                'name' => $type,
                ]);
                $type_attr->save();
                \Drupal::messenger()->addMessage($this->t('Type @type uploaded successfully.', ['@type' => $type]));
            }
            }
        }
    }
