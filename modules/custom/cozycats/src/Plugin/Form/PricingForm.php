<?php

/**
* @file
* Contains \Drupal\cozycats\Plugin\Form\PricingForm.
*/

namespace Drupal\cozycats\Plugin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
* Pricing Form.
*/
class PricingForm extends FormBase {
	/**
	* {@inheritdoc}
	*/
	public function getFormId() {
		return 'pricing_form';
	}

	/**
	* {@inheritdoc}
	*/
	public function buildForm(array $form, FormStateInterface $form_state) {
		$vid = 'calculated_field_nu';
		$term_data = array();
		$terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid);

		foreach ($terms as $term) {
			$single_term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($term->tid);
			$price = $single_term->field_current_price->value;
			$term_data[$price] = $term->name;
		} 
		$form['cats'] = array(
		 '#type' => 'select',
		 '#title' => $this->t('Number of Cats'),
		 '#options' => $term_data,
		);

		$form['days'] = array(
		'#type' => 'number',
		'#title' => t('Number of Days'),
		'#default_value' => 1,
		);	

		$form['submit'] = array(
		'#type' => 'submit',
		'#value' => t('Submit'),
		);

		$form['surchage'] = array(
			'#markup' => '<p><h4>Please note: This does not include the bank holiday surcharge.</h4></p>',
		);

		return $form;
	}

	/**
	* {@inheritdoc}
	*/
	public function validateForm(array &$form, FormStateInterface $form_state) {
		if ($form_state->getValue('days') == '' ) {
			$form_state->setErrorByName('days', t('This is a required field.'));
		}
	}

	/**
	* {@inheritdoc}
	*/
	public function submitForm(array &$form, FormStateInterface $form_state) {
		$price = $form_state->getValue('cats');
		$days = $form_state->getValue('days');

		$message = t('Your price is £' . number_format(($price * $days), 2) . '.');
		drupal_set_message($message);
	}
}

?>