<?php

namespace Drupal\ballistic_employee\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class EmployeeDeleteForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'employee_delete_form';
  }

  /**
   * {@inheritdoc}
   */

  public function buildForm(array $form, FormStateInterface $form_state, $id = NULL) {

    // Add a confirmation message
    $form['confirmation_message'] = [
      '#markup' => $this->t('Are you sure you want to delete this employee?'),
    ];

    $form['id'] = [
      '#type' => 'hidden',
      '#value' => $id,
    ];

    // Add a "Cancel" button that redirects to the employee list
    $form['cancel'] = [
      '#type' => 'submit',
      '#value' => $this->t('Cancel'),
      '#submit' => ['::cancelForm'],
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Delete'),
    ];

    return $form;
  }

  /**
   * Submit callback for the "Cancel" button.
   */
  public function cancelForm(array &$form, FormStateInterface $form_state) {
    // Redirect to the employee list page
    $form_state->setRedirect('employee.getEmployeeList');
}
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get the ID from the form state
    $id = $form_state->getValue('id');

    // Perform the delete operation
    $deleted = $this->deleteEmployee($id);

    // Display a message based on the delete result
    if ($deleted) {
      \Drupal::messenger()->addStatus($this->t('Employee with ID @id has been deleted.', ['@id' => $id]));
    } else {
      \Drupal::messenger()->addError($this->t('Unable to delete employee with ID @id.', ['@id' => $id]));
    }

    // Redirect to the employee list page after deletion
    $form_state->setRedirect('employee.getEmployeeList');
  }

  /**
   * Delete an employee based on the given ID.
   *
   * @param int $id
   *   The ID of the employee to be deleted.
   *
   * @return bool
   *   TRUE if the deletion is successful, FALSE otherwise.
   */
  protected function deleteEmployee($id) {
    try {
      $database = \Drupal::database();
      $database->delete('employee_details')
        ->condition('id', $id)
        ->execute();
      return TRUE;
    } catch (\Exception $e) {
      return FALSE;
    }
  }
}
