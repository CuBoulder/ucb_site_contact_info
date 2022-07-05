<?php
namespace Drupal\ucb_site_contact_info\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

class SiteInfoForm extends ConfigFormBase {

    const NUMBER_OF_GROUPS = 4;

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
        $form['seperate_departments'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('Organize site contact info as seperate departments'),
            '#default_value' => $config->get('seperate_departments') ?? '0'
        ];
        $departmentStoredValues = $config->get('department');
        $addressStoredValues = $config->get('address');
        $emailStoredValues = $config->get('email');
        $phoneStoredValues = $config->get('phone');
        for($index = 0; $index < self::NUMBER_OF_GROUPS; $index++) {
            $innerForm = [
                '#type' => 'details',
                '#title' => $this->t('Group ' . $index + 1 . ($index == 0 ? '' : ' (Optional)')),
                '#open' => true
            ];   
            $innerForm = $this->_buildFormSection('Department ' . $index + 1, 'department', 'department', $index, 'Label', 'Link (optional)', 'textfield', 255, $departmentStoredValues, $innerForm);
            $innerForm = $this->_buildFormSection('Address ' . $index + 1, 'address', 'address', $index, 'Label (optional)', 'Value (supports multiline)', 'textarea', 255, $addressStoredValues, $innerForm);
            $innerForm['address_' . $index]['address' . '_' . $index . '_map_link'] = [
                '#type' => 'textfield',
                // '#size' => 32,
                '#title' => $this->t('Map link (optional)'),
                '#default_value' => $addressStoredValues[$index]['map_link'] ?? ''    
            ];
            $innerForm = $this->_buildFormSection('Email ' . $index + 1, 'email', 'email', $index, 'Label (optional)', 'Value', 'email', 20, $emailStoredValues, $innerForm);
            $innerForm = $this->_buildFormSection('Phone ' . $index + 1, 'phone', 'phone', $index, 'Label (optional)', 'Value', 'tel', 20, $phoneStoredValues, $innerForm);
            $form[$index] = $innerForm;
        }
        return parent::buildForm($form, $form_state);
    }

    private function _buildFormSection($verboseName, $verboseNameLower, $machineName, $index, $labelFieldLabel, $valueFieldLabel, $valueFieldType, $valueFieldSize, $storedValues, array &$innerForm) {
        $isDepartment = $machineName == 'department';
        $sectionForm = [
            '#type' => 'details',
            '#title' => $this->t($verboseName),
            '#open' => $isDepartment
        ];
        $sectionForm[$machineName . '_' . $index . '_visible'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('Display this ' . $verboseNameLower . ' in the site footer'),
            '#default_value' => $storedValues[$index]['visible'] ?? ($isDepartment && $index == 0 ? '1' : '0')
        ];
        $sectionForm[$machineName . '_' . $index . '_label'] = [
            '#type' => 'textfield',
            // '#size' => 32,
            '#title' => $this->t($labelFieldLabel),
            '#default_value' => $storedValues[$index]['label'] ?? ''
        ];
        $sectionForm[$machineName . '_' . $index . '_value'] = [
            '#type' => $valueFieldType,
            // '#size' => $valueFieldSize,
            '#title' => $this->t($valueFieldLabel),
            '#default_value' => $storedValues[$index]['value'] ?? ''
        ];
        if($isDepartment) {
            $sectionForm['#states'] = [
                'visible' => [
                    ':input[name="seperate_departments"]' => [ 'checked' => true ]
                ]
            ];     
        } else {
            $sectionForm[$machineName . '_' . $index . '_visible']['#states'] = [
                'visible' => [
                    [ ':input[name="seperate_departments"]' => [ 'checked' => false ] ],
                    [ ':input[name="department_' . $index . '_visible"]' => [ 'checked' => true ] ]
                ]
            ];
        }
        $innerForm[$machineName . '_' . $index] = $sectionForm;
        return $innerForm;
    }

    /**
     * @param array $form
     * @param FormStateInterface $form_state
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        $config = $this->config('ucb_site_info.settings');
        $formValues = $form_state->getValues();
        $config->set('seperate_departments', $formValues['seperate_departments'])->save();
        $this->_saveFormSection($formValues, $config, 'department', ['visible', 'label', 'value'], self::NUMBER_OF_GROUPS);
        $this->_saveFormSection($formValues, $config, 'address', ['visible', 'label', 'value', 'map_link'], self::NUMBER_OF_GROUPS);
        $this->_saveFormSection($formValues, $config, 'email', ['visible', 'label', 'value'], self::NUMBER_OF_GROUPS);
        $this->_saveFormSection($formValues, $config, 'phone', ['visible', 'label', 'value'], self::NUMBER_OF_GROUPS);
        \Drupal::service('cache.render')->invalidateAll();
    }

    private static function _saveFormSection($formValues, $config, $sectionName, $fieldNames, $count) {
        $values = [];
        $visibleForCategory = '0';
        for($index = 0; $index < $count; $index++) {
            $visible = $formValues[$sectionName . '_' . $index . '_visible'];
            if($visible == '1') {
                // Make visible the address, email, or phone section if at least one address, email, or phone is marked as visible
                $visibleForCategory = '1';
            }
            $fieldNameValueDict = [];
            foreach($fieldNames as $fieldName) {
                $fieldNameValueDict[$fieldName] = $formValues[$sectionName . '_' . $index . '_' . $fieldName];
            }
            $values[] = $fieldNameValueDict;
        }
        $config->set($sectionName . '_visible', $visibleForCategory)->save();
        $config->set($sectionName, $values)->save();
    }
}
?>