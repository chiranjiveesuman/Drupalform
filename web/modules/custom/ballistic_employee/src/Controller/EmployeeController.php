<?php

/**
 * @file
 * Provide site administrators with a list of all the Employees.
 */

 namespace Drupal\ballistic_employee\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Database\Query\PagerSelectExtender;
use PhpParser\Node\Stmt\TryCatch;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Url;
use Drupal\Core\Link;

class EmployeeController extends ControllerBase {

  /**
   * @return array|null
   */
  protected function load($mail = '') {
    try {
      $database = \Drupal::database();
      $select_query = $database->select('employee_details', 'e')->extend(PagerSelectExtender::class)->limit(2);
      $select_query->fields('e', ['id', 'name', 'mail', 'phone', 'gender', 'marital_status', 'date_of_birth', 'about_employee', 'nationality']);

      if (!empty($mail)) {
        $select_query->condition('e.mail', $mail);
      }

      $entries = $select_query->execute()->fetchAll();
      $rows = [];

      foreach ($entries as $row) {


        $delete = Url::fromRoute('ballistic_employee.employee_delete_form', ['id' => $row->id]);
        $edit = Url::fromRoute('ballistic_employee.employee_edit_form', ['id' => $row->id]);

        $edit_link = Link::fromTextAndUrl($this->t('Edit'), $edit)->toString();
        $delete_link = Link::fromTextAndUrl($this->t('Delete'), $delete)->toString();

        $actionLink = $this->t('@linkEdit | @linkDelete', array('@linkEdit' => $edit_link, '@linkDelete' => $delete_link));
        $rows[] = [
          'id' => $row->id,
          'name' => $row->name,
          'mail' => $row->mail,
          'phone' => $row->phone,
          'gender' => $row->gender,
          'marital_status' => $row->marital_status,
          'date_of_birth' => $row->date_of_birth,
          'about_employee' => $row->about_employee,
          'nationality' => $row->nationality,
          'action' => $actionLink,
        ];
      }

      return $rows;
    } catch (\Exception $e) {
      // Display a user friendly error
      \Drupal::messenger()->addStatus($this->t('Unable to access database at this time. Please try again later.'));
      return null;
    }
  }

  /**
   * Create the Employee List report page
   *
   * @return array
   *
   * Render array for Employee List report output.
   */
  public function getEmployeeList() {
    $content = [];

    //Get parameter value while submitting filter form
    $email = \Drupal::request()->query->get('email');

    // load EmployeeFilterForm
    $content['filter_form'] = $this->formBuilder()->getForm('Drupal\ballistic_employee\Form\EmployeeFilterForm');


    $content['message'] = [
      '#markup' => $this->t('Below is the list of all employees.')
    ];
    // Creating headers for the list
    $headers = [
      $this->t('Id'),
      $this->t('Name'),
      $this->t('Email'),
      $this->t('Phone'),
      $this->t('Gender'),
      $this->t('Marital Status'),
      $this->t('DOB'),
      $this->t('About Employee'),
      $this->t('Nationality'),
      $this->t('Action'),
    ];

    // Because load() returns an associative array with each table row
    // as its own array, we can simply define the HTML table rows like this:

    $table_rows = $this->load($email);

    // Create the pager array for rendering an HTML table with pagination.

    $content ['pager'] = [
      'table' => [
        '#theme' => 'table',
        '#header' => $headers,
        '#rows' => $table_rows,
        '#empty' => $this->t('No entries available.'),
      ],
      'pager' => [
        '#type' =>'pager'
      ],
    ];

    // Do not cache this page by setting the max-age to 0.
    $content['#cache']['max-age'] = 0;

    // Return the populated render array.
    return $content;
  }
}

