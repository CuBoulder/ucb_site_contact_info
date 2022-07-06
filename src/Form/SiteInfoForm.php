<?php
namespace Drupal\ucb_site_contact_info\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

class SiteInfoForm extends ConfigFormBase {

    const NUMBER_OF_ADDRESSES = 2;
    const NUMBER_OF_EMAILS = 3;
    const NUMBER_OF_PHONE_NUMBERS = 3;

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
        return [ 'ucb_site_contact_info.configuration' ];
    }

    /**
     * @param array $form
     * @param FormStateInterface $form_state
     * @return array
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        $config = $this->config('ucb_site_contact_info.configuration');
        $addressStoredValues = $config->get('address');
        $emailStoredValues = $config->get('email');
        $phoneStoredValues = $config->get('phone');
        $form = $this->_buildFormSection(self::NUMBER_OF_ADDRESSES, 'Address', 'address', 'address', 'Label (optional)', 'Value (supports multiline)', 'textarea', 255, $addressStoredValues, $form);
        $form = $this->_buildFormSection(self::NUMBER_OF_EMAILS, 'Email address', 'email address', 'email', 'Label (optional)', 'Value', 'email', 20, $emailStoredValues, $form);
        $form = $this->_buildFormSection(self::NUMBER_OF_PHONE_NUMBERS, 'Phone number', 'phone number', 'phone', 'Label (optional)', 'Value', 'tel', 20, $phoneStoredValues, $form);
        return parent::buildForm($form, $form_state);
    }

    private function _buildFormSection($itemCount, $verboseName, $verboseNameLower, $machineName, $labelFieldLabel, $valueFieldLabel, $valueFieldType, $valueFieldSize, $storedValues, array &$form) {
        // Toggle for "Add primary x"
        $form[$machineName . '_0_visible'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('Add primary ' . $verboseNameLower),
            '#default_value' => $storedValues[0]['visible'] ?? '0'
        ];
        // Section "Primary x"
        $sectionForm = [
            '#type' => 'details',
            '#title' => 'Primary ' . $verboseNameLower,
            '#open' => true,
            '#states' => [
                'visible' => [
                    ':input[name="'. $machineName . '_0_visible"]' => [ 'checked' => true ]
                ]
            ]
        ];   
        // Fields for primary item
        $sectionForm = $this->_buildFieldSection(0, $verboseName, $verboseNameLower, $machineName, $labelFieldLabel, $valueFieldLabel, $valueFieldType, $valueFieldSize, $storedValues, $sectionForm);
        // Add secondary items
        for($index = 1; $index < $itemCount; $index++) {
            // Toggle for "Add another x"
            $sectionForm[$machineName . '_' . $index . '_visible'] = [
                '#type' => 'checkbox',
                '#title' => $this->t('Add another ' . $verboseNameLower),
                '#default_value' => $storedValues[$index]['visible'] ?? '0'
            ];
            // Section "[another] x"
            $subSectionForm = [
                '#type' => 'details',
                '#title' => $verboseName,
                '#open' => true,
                '#states' => [
                    'visible' => [
                        ':input[name="'. $machineName . '_' . $index .'_visible"]' => [ 'checked' => true ]
                    ]
                ]
            ]; 
            // Fields for secondary item
            $subSectionForm = $this->_buildFieldSection($index, $verboseName, $verboseNameLower, $machineName, $labelFieldLabel, $valueFieldLabel, $valueFieldType, $valueFieldSize, $storedValues, $subSectionForm);
            $sectionForm[$machineName . '_' . $index] = $subSectionForm;
        }
        $form[$machineName . '_' . $index] = $sectionForm;
        return $form;
    }

    private function _buildFieldSection($index, $verboseName, $verboseNameLower, $machineName, $labelFieldLabel, $valueFieldLabel, $valueFieldType, $valueFieldSize, $storedValues, array &$form) {
        $form[$machineName . '_' . $index . '_label'] = [
            '#type' => 'textfield',
            // '#size' => 32,
            '#title' => $this->t($labelFieldLabel),
            '#default_value' => $storedValues[$index]['label'] ?? ''
        ];
        $form[$machineName . '_' . $index . '_value'] = [
            '#type' => $valueFieldType,
            // '#size' => $valueFieldSize,
            '#title' => $this->t($valueFieldLabel),
            '#default_value' => $storedValues[$index]['value'] ?? ''
        ];
        return $form;
    }

    /**
     * @param array $form
     * @param FormStateInterface $form_state
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        $formValues = $form_state->getValues();
        $config = $this->config('ucb_site_contact_info.configuration');
        $fieldNames = ['visible', 'label', 'value'];
        $this->_saveFormSection($formValues, $config, 'address', $fieldNames, self::NUMBER_OF_ADDRESSES);
        $this->_saveFormSection($formValues, $config, 'email', $fieldNames, self::NUMBER_OF_EMAILS);
        $this->_saveFormSection($formValues, $config, 'phone', $fieldNames, self::NUMBER_OF_PHONE_NUMBERS);
        \Drupal::service('cache.render')->invalidateAll();
    }

    private static function _saveFormSection($formValues, $config, $sectionName, $fieldNames, $itemCount) {
        // Gather all primary / secondary fields from form into one array.
        $values = [];
        for($index = 0; $index < $itemCount; $index++) {
            $visible = $formValues[$sectionName . '_' . $index . '_visible'];
            $fieldNameValueDict = [];
            foreach($fieldNames as $fieldName) {
                $fieldNameValueDict[$fieldName] = $formValues[$sectionName . '_' . $index . '_' . $fieldName];
            }
            $values[] = $fieldNameValueDict;
        }
        // Form design necessitates hiding all items if the primary one is not shown.
        $categoryVisible = $values[0]['visible'];
        // Set the configuration.
        $config->set($sectionName . '_visible', $categoryVisible)->save();
        $config->set($sectionName, $values)->save();
    }
}
?>