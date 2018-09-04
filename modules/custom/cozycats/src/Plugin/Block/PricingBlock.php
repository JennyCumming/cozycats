<?php

namespace Drupal\cozycats\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a block with a simple text.
 *
 * @Block(
 *   id = "cozycats_pricing_block",
 *   admin_label = @Translation("Pricing Block"),
 * )
 */
class PricingBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = array();

    $build['form'] = \Drupal::formBuilder()->getForm('Drupal\cozycats\Plugin\Form\PricingForm');

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['my_block_settings'] = $form_state->getValue('my_block_settings');
  }
}