<?php

namespace Drupal\reclique_gxp_sync\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;

/**
 * Provides reclique_gxp_sync settings form.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'reclique_gxp_sync_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'reclique_gxp_sync.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('reclique_gxp_sync.settings');

    $form['api_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API URL'),
      '#required' => TRUE,
      '#default_value' => ($config->get('api_url')) ? $config->get('api_url') : '',
    ];

    $form['program_subcategory'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'node',
      '#required' => TRUE,
      '#selection_settings' => [
        'target_bundles' => [
          'program_subcategory',
        ]
      ],
      '#title' => $this->t('Program SubCategory'),
      '#default_value' => !empty($config->get('program_subcategory')) ? Node::load($config->get('program_subcategory')) : NULL,
      '#description' => t('Reference to Program Subcategory, typically "Group Exercises" under "Health & Fitness" program.'),
    ];

    $form['category_mapping'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Category mapping'),
      '#description' => $this->t('Mapping of categories used for classes grouping/renaming. One row for one record. Must be separated by newline and typically looks like this "Boot Camp,Cardio,19,19". Numbers(IDs) are not required. 1st title is a key in source.'),
      '#default_value' => ($config->get('category_mapping')) ? $config->get('category_mapping') : '',
    ];

    $form['class_sign_up_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Sign Up URL'),
      '#description' => $this->t('URL used for sign up link. Looks like this https://midtn.recliquecore.com/programs/register/c/CLASSID_PLACEHOLDER/DATE_PLACEHOLDER/OpenYGeneratedDateInstance'),
      '#default_value' => ($config->get('class_sign_up_url')) ? $config->get('class_sign_up_url') : '',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = \Drupal::service('config.factory')->getEditable('reclique_gxp_sync.settings');
    $config->set('api_url', $form_state->getValue('api_url'))->save();
    $config->set('program_subcategory', $form_state->getValue('program_subcategory'))->save();
    $config->set('category_mapping', $form_state->getValue('category_mapping'))->save();
    $config->set('class_sign_up_url', $form_state->getValue('class_sign_up_url'))->save();

    parent::submitForm($form, $form_state);
  }

}
