<?php

namespace Drupal\pcda\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Drupal\captcha\CaptchaPointInterface;

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

        $form['captcha'] = [
            '#type' => 'captcha',
            '#captcha_type' => 'default',
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

        // Validate that Claimed Amount is greater than or equal to Passed Amount
        $claimed_amount = $form_state->getValue('claimed_amount');
        $passed_amount = $form_state->getValue('passed_or_rejection');

        // If passed_or_rejection contains a numeric value (not a rejection reason)
        if (is_numeric($passed_amount) && $claimed_amount < $passed_amount) {
            $form_state->setErrorByName('claimed_amount', $this->t('Claimed amount must be greater than or equal to Passed Amount.'));
        }
        // If passed_or_rejection contains a rejection reason (not a numeric value)
        elseif (!is_numeric($passed_amount)) {
            $form_state->setErrorByName('passed_or_rejection', $this->t('Please enter a valid passed amount or rejection reason.'));
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

        // Generate the PDF.
        $html = $this->buildPdfContent($values);
        $this->generatePdf($html, $values['bill_id']);

        // Clear specific fields after form submission
        $form_state->setValue('bill_id', '');  // Clear Bill ID
        $form_state->setValue('pan_number', '');  // Clear PAN Number
        $form_state->setValue('claimed_amount', '');  // Clear Claimed Amount
        $form_state->setValue('passed_or_rejection', '');

        // After PDF generation, redirect to the default page (home page).
        $form_state->setRedirect('pcda.payment_advice.form');
    }

    /**
     * Build the HTML content for the PDF.
     */
    private function buildPdfContent(array $values) {
        return '
            <h1>Payment Advice</h1>
            <p><strong>Bill ID:</strong> ' . $values['bill_id'] . '</p>
            <p><strong>PAN Number:</strong> ' . $values['pan_number'] . '</p>
            <p><strong>Claimed Amount:</strong> ' . $values['claimed_amount'] . '</p>
            <p><strong>Passed Amount / Rejection Reason:</strong> ' . $values['passed_or_rejection'] . '</p>
            <p><strong>Date Created:</strong> ' . date('Y-m-d H:i:s', time()) . '</p>
        ';
    }

    /**
     * Generate a PDF and trigger download.
     */
    private function generatePdf($html, $file_name) {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Output the PDF to the browser for download.
        $dompdf->stream($file_name . '.pdf', ['Attachment' => true]);
    }
}
