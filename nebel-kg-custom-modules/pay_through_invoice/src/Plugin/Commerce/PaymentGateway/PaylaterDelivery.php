<?php

  namespace Drupal\pay_through_invoice\Plugin\Commerce\PaymentGateway;

  use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OnsitePaymentGatewayBase;
  use Drupal\commerce_payment\Entity\PaymentInterface;
  use Drupal\commerce_payment\Entity\PaymentMethodInterface;
  use Drupal\commerce_order\Entity\OrderInterface;

  /**
   * Provides the Cash on Delivery payment gateway.
   *
   * @CommercePaymentGateway(
   *   id = "cash_on_delivery",
   *   label = @Translation("Pay via invoice"),
   *   display_label = @Translation("Pay via invoice"),
   *   forms = {
   *     "add-payment" = "Drupal\commerce_payment\PluginForm\OnsitePaymentGatewayForm",
   *   },
   *   payment_method_types = {"manual"},
   *   requires_billing_information = FALSE,
   * )
   */
  class PaylaterDelivery extends OnsitePaymentGatewayBase {

    /**
     * {@inheritdoc}
     */
    public function createPayment(PaymentInterface $payment, $capture = TRUE) {
      // Set the payment to pending, as COD is paid on delivery.
      $order = $payment->getOrder();
      $amount = $order->getTotalPrice();
      
      // Set the payment state to "pending" since the cash is collected on delivery.
      $payment->setState('pending');
      $payment->setAmount($amount);
      $payment->save();
    }

    /**
     * {@inheritdoc}
     */
    public function capturePayment(PaymentInterface $payment, $amount = NULL) {
      // Mark the payment as completed when cash is received.
      $payment->setState('completed');
      $payment->save();
    }

    /**
     * {@inheritdoc}
     */
    public function deletePaymentMethod(PaymentMethodInterface $payment_method) {
      // Implement deletion logic if necessary.
    }

    /**
   * {@inheritdoc}
   */
    public function createPaymentMethod(PaymentMethodInterface $payment_method, array $payment_details = NULL) {
      // If $payment_details is NULL, initialize it as an empty array.
      if ($payment_details === NULL) {
        $payment_details = [];
      }
        
      // Implement creation logic for a payment method if necessary.
      // For COD, you typically do not create a payment method, so this could be empty.
      return NULL; // Return null or handle as needed.
    }
  }