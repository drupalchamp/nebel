<?php

namespace Drupal\custom_product_importer\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class CustomConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'custom_product_importer.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'custom_product_importer_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('custom_product_importer.settings');

    $form['csv_file_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Set product file Path'),
      '#default_value' => $config->get('csv_file_path') ?: '',
      '#description' => $this->t('Please enter path to update the products.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('custom_product_importer.settings')
      ->set('csv_file_path', $form_state->getValue('csv_file_path'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}