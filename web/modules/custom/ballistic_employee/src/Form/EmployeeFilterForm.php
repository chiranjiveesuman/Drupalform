<?php

/**
 * @file
 * A form to collect an email address for filter at Employee report.
 */

namespace Drupal\ballistic_employee\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Routing;

class EmployeeFilterForm extends FormBase {

    /**
     * {@inheritDoc}
     */
    public function getFormId() {
        return 'employee_filter_form';
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
      $form['filters'] = [
        '#type'  => 'fieldset',
        '#title' => $this->t('Filter'),
        '#open'  => true,
      ];
      $form['filters']['email'] = [
        '#type' => 'search',
        '#title' => $this->t('Email address'),
      ];
      $form['filters']['actions'] = [
        '#type' => 'actions',
      ];
      $form['filters']['actions']['submit'] = [
        '#type'  => 'submit',
        '#value' => $this->t('Filter')
      ];
      return $form;
    }

    /**
     * {@inheritDoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state) {
    }

    /**
     * {@inheritDoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        // Obtain values as entered into the filter form.
        $email = $form_state->getValue('email');

        // Obtain the Employee List url from route
        $url = Url::fromRoute('employee.getEmployeeList')->setRouteParameters(array('email' => $email));

        // Redirect to Employee Details page on submit
        $form_state->setRedirectUrl($url);
    }
}
