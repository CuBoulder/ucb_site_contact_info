<?php

namespace Drupal\ucb_site_contact_info\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * @Block(
 *   id = "site_info",
 *   admin_label = @Translation("Site Contact Info Footer"),
 * )
 */
class SiteInfoBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = \Drupal::config('ucb_site_info.settings');
    return [
      '#data' => [
        'address_1' => $config->get('address_1') ?? '',
        'address_2' => $config->get('address_2') ?? '',
        'zip_code' => $config->get('zip_code') ?? '',
        'email' => $config->get('email') ?? '',
        'fax' => $config->get('fax') ?? '',
        'phone' => $config->get('phone') ?? ''
      ]
    ];
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
    return parent::blockForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    return parent::blockSubmit($form, $form_state);
  }
}