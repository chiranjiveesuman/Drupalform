<?php

namespace Drupal\pcda\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Dompdf\Dompdf;
use Dompdf\Options;

class PcdaForm extends FormBase {

    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return "pcda_form";
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        $form['bill_id'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Bill ID'),
            '#maxlength' => 10,
            '#required' => TRUE,
            '#description' => $this->t('Enter the 10-digit Bill ID.'),
        ];

        $form['pan_number'] = [
            '#type' => 'textfield',
            '#title' => $this->t('PAN Number'),
            '#maxlength' => 10,
            '#required' => TRUE,
            '#description' => $this->t('Enter the 10-digit PAN number.'),
        ];

        $form['claimed_amount'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Claimed Amount'),
            '#required' => TRUE,
            '#description' => $this->t('Enter the amount you are claiming.'),
        ];

        $form['passed_or_rejection'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Passed Amount / Rejection Reason'),
            '#description' => $this->t('Enter the passed amount if approved, or rejection reason if not approved.'),
        ];

        $form['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Download Payment Advice'),
        ];

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state) {
        if (!preg_match('/^[A-Za-z0-9]{5,10}$/', $form_state->getValue('bill_id'))) {
            $form_state->setErrorByName('bill_id', $this->t('Bill ID must be alphanumeric and between 5 and 10 characters.'));
        }

        if (!preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/', $form_state->getValue('pan_number'))) {
            $form_state->setErrorByName('pan_number', $this->t('Invalid PAN number format.'));
        }

        if (!is_numeric($form_state->getValue('claimed_amount')) || $form_state->getValue('claimed_amount') <= 0) {
            $form_state->setErrorByName('claimed_amount', $this->t('Claimed amount must be a positive number.'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        $values = $form_state->getValues();

        // Save the data into the database.
        \Drupal::database()->insert('pcda_form')->fields([
            'bill_id' => $values['bill_id'],
            'pan_number' => $values['pan_number'],
            'claimed_amount' => $values['claimed_amount'],
            'passed_or_rejection' => $values['passed_or_rejection'],
            'date_created' => time(),
        ])->execute();

        \Drupal::messenger()->addMessage($this->t('The payment advice details have been saved successfully.'));

        // Handle file download (improve or customize as needed).
        $file_path = '/path/to/your/files/' . $values['bill_id'] . '.pdf'; 
        if (file_exists($file_path)) {
            \Drupal::service('file_system')->read($file_path); // Ensure secure handling.
        } else {
            \Drupal::messenger()->addError($this->t('Payment advice not found for Bill ID: @bill_id.', ['@bill_id' => $values['bill_id']]));
        }
    }
}
