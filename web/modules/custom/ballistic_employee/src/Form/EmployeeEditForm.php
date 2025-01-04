<?php

namespace Drupal\ballistic_employee\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form for editing an employee.
 */
class EmployeeEditForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'employee_edit_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $id = NULL) {
    // Load the employee details based on $id
    $employee_details = $this->loadEmployeeDetails($id);

    // // Check if employee details are available.
    // if (!$employee_details) {
    //   $this->messenger()->addError($this->t('Employee not found.'));
    //   return $form;
    // }

    // Add form elements for editing employee details.
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#default_value' => $employee_details->name,
      '#required' => TRUE,
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#default_value' => $employee_details->mail,
      '#required' => TRUE,
    ];

    $form['phone'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Your phone number'),
      '#default_value' => $employee_details->phone,
    ];

    $form['gender'] = [
      '#type' => 'radios',
      '#title' => $this->t('Gender'),
      '#options' => [
        'male' => $this->t('Male'),
        'female' => $this->t('Female'),
        'other' => $this->t('Other'),
      ],
      '#default_value' => $employee_details->gender,
    ];

    $form['marital_status'] = [
      '#type' => 'select',
      '#title' => $this->t('Marital Status'),
      '#options' => [null => $this->t('None'),
        'single' => $this->t('Single'),
        'married' => $this->t('Married'),
      ],
      '#default_value' => $employee_details->marital_status,
    ];

    $form['date_of_birth'] = [
      '#type' => 'date',
      '#title' => $this->t('Date of Birth'),
      '#default_value' => $employee_details->date_of_birth,
    ];

    $form['about_employee'] = [
      '#type' => 'textarea',
      '#title' => $this->t('About Employee'),
      '#default_value' => $employee_details->about_employee,
    ];

    $form['nationality'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Nationality'),
      '#default_value' => $employee_details->nationality,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Update'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   *//**
 * {@inheritdoc}
 */
public function submitForm(array &$form, FormStateInterface $form_state) {
  // Get the edited values from the form state.
  $name = $form_state->getValue('name');
  $mail = $form_state->getValue('email');
  $phone = $form_state->getValue('phone');
  $gender = $form_state->getValue('gender');
  $marital_status = $form_state->getValue('marital_status');
  $date_of_birth = $form_state->getValue('date_of_birth');
  $about_employee = $form_state->getValue('about_employee');
  $nationality = $form_state->getValue('nationality');

  // Get the employee ID from the route parameters.
  $id = $this->getRequest()->attributes->get('id');

  // Update the employee details in the database.
  try {
    $database = \Drupal::database();
    $database->update('employee_details')
      ->fields([
        'name' => $name,
        'mail' => $mail,
        'phone' => $phone,
        'gender' => $gender,
        'marital_status' => $marital_status,
        'date_of_birth' => $date_of_birth,
        'about_employee' => $about_employee,
        'nationality' => $nationality,
      ])
      ->condition('id', $id)
      ->execute();

    $this->messenger()->addStatus($this->t('Employee details updated successfully.'));
  } catch (\Exception $e) {
    $this->messenger()->addError($this->t('Unable to update employee details. Please try again later.'));
  }

  // Redirect to the employee list page after update.
  $form_state->setRedirect('employee.getEmployeeList');
}

    /**
   * Helper function to load employee details based on ID.
   *
   * @param int $id
   *   The employee ID.
   *
   * @return object|null
   *   The employee details object or NULL if not found.
   */
  protected function loadEmployeeDetails($id) {
    try {
      $database = \Drupal::database();
      $result = $database->select('employee_details', 'e')
        ->fields('e')
        ->condition('e.id', $id)
        ->execute()
        ->fetchObject();

      return $result;
    } catch (\Exception $e) {
      \Drupal::logger('ballistic_employee')->error('Error loading employee details: @error', ['@error' => $e->getMessage()]);
      return null;
    }
  }
}


