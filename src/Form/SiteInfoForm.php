<?php
namespace Drupal\ucb_site_contact_info\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

class SiteInfoForm extends ConfigFormBase {

/**
   * @return string
   */
    public function getFormId() {
        return 'ucb_site_info_form';
    }

    /**
     * Gets the configuration names that will be editable.
     *
     * @return array
     *   An array of configuration object names that are editable if called in
     *   conjunction with the trait's config() method.
     */
    protected function getEditableConfigNames() {
        return [ 'ucb_site_info.settings' ];
    }

    /**
     * @param array $form
     * @param FormStateInterface $form_state
     * @return array
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        $config = $this->config('ucb_site_info.settings');
        $form['address_1'] = [
            '#type' => 'textfield',
            '#size' => 255,
            '#title' => $this->t('Address Line 1'),
            '#default_value' => $config->get('address_1') ?? ''
        ];
        $form['address_2'] = [
            '#type' => 'textfield',
            '#size' => 255,
            '#title' => $this->t('Address Line 2'),
            '#default_value' => $config->get('address_2') ?? ''
        ];
        $form['zip_code'] = [
            '#type' => 'number',
            '#min' => 11111,
            '#max' => 99999,
            '#title' => $this->t('Building Zip Code'),
            '#default_value' => $config->get('zip_code') ?? ''
        ];
        $form['email'] = [
            '#type' => 'email',
            '#size' => 255,
            '#title' => $this->t('Email'),
            '#default_value' => $config->get('email') ?? ''
        ];
        $form['fax'] = [
            '#type' => 'tel',
            '#size' => 255,
            '#title' => $this->t('Fax'),
            '#default_value' => $config->get('fax') ?? ''
        ];
        $form['phone'] = [
            '#type' => 'tel',
            '#size' => 255,
            '#title' => $this->t('Phone'),
            '#default_value' => $config->get('phone') ?? ''
        ];
        return parent::buildForm($form, $form_state);
    }

    /**
     * @param array $form
     * @param FormStateInterface $form_state
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        $config = $this->config('ucb_site_info.settings');
        $config->set('address_1', $form_state->getValue('address_1'))->save();
        $config->set('address_2', $form_state->getValue('address_2'))->save();
        $config->set('zip_code', $form_state->getValue('zip_code'))->save();
        $config->set('email', $form_state->getValue('email'))->save();
        $config->set('fax', $form_state->getValue('fax'))->save();
        $config->set('phone', $form_state->getValue('phone'))->save();
        \Drupal::service('cache.render')->invalidateAll();
    }
}
?>