<?php

namespace Drupal\pay_through_invoice\Plugin\Commerce\PaymentMethodType;

use Drupal\commerce_payment\Entity\PaymentMethodInterface;
use Drupal\commerce_payment\Plugin\Commerce\PaymentMethodType\PaymentMethodTypeBase;

/**
 * Provides the manual payment method type.
 *
 * @CommercePaymentMethodType(
 *   id = "manual",
 *   label = @Translation("Manual payment"),
 *   create_label = @Translation("Pay via Invoice"),
 * )
 */
class ManualPayment extends PaymentMethodTypeBase {

  /**
   * {@inheritdoc}
   */
  public function buildLabel(PaymentMethodInterface $payment_method) {
    // Build and return the label for the manual payment method type.
    return $this->t('Pay via invoice');
  }
}