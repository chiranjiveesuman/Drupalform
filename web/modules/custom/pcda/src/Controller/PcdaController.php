<?php
namespace Drupal\pcda\Controller;

// use Drupal\Core\Controller\ControllerBase;
// use Drupal\Core\Url;
// use Drupal\Core\Link;
// use Drupal\Core\Database\PagerSelectExtender;

// class PcdaController extends ControllerBase {

//   /**
//    * Load data from the database.
//    *
//    * @param string $bill_id
//    *   Optional: Filter results by Bill ID.
//    *
//    * @return array|null
//    *   Array of rows to render in the table or null if an error occurs.
//    */
//   protected function load($bill_id = '') {
//     try {
//       $database = \Drupal::database();
//       $select_query = $database->select('pcda_form', 'p')
//         ->extend(PagerSelectExtender::class)
//         ->limit(10); // Adjust the limit as needed

//       $select_query->fields('p', ['bill_id', 'pan_number', 'claimed_amount', 'passed_or_rejection']);

//       if (!empty($bill_id)) {
//         $select_query->condition('p.bill_id', $bill_id);
//       }

//       $entries = $select_query->execute()->fetchAll();
//       $rows = [];

//       foreach ($entries as $row) {
//         $download = Url::fromRoute('pcda.download', ['id' => $row->id]);
//         $download_link = Link::fromTextAndUrl($this->t('Download'), $download)->toString();

//         $rows[] = [
//           'bill_id' => $row->bill_id,
//           'pan_number' => $row->pan_number,
//           'claimed_amount' => $row->claimed_amount,
//           'passed_or_rejection' => $row->passed_or_rejection ?: $this->t('N/A'),
//           'action' => $download_link,
//         ];
//       }

//       return $rows;
//     } catch (\Exception $e) {
//       \Drupal::messenger()->addError($this->t('Unable to access the database. Please try again later.'));
//       return null;
//     }
//   }

//   /**
//    * Display the Payment Advice List report page.
//    *
//    * @return array
//    *   Render array for the Payment Advice List output.
//    */
//   public function getPaymentAdviceList() {
//     $content = [];

//     $bill_id = \Drupal::request()->query->get('bill_id');

//     // Table headers
//     $headers = [
//       $this->t('Bill ID'),
//       $this->t('PAN Number'),
//       $this->t('Claimed Amount'),
//       $this->t('Passed Amount / Rejection Reason'),
//       $this->t('Action'),
//     ];

//     // Table rows
//     $table_rows = $this->load($bill_id);

//     $content['payment_advice_list'] = [
//       '#theme' => 'table',
//       '#header' => $headers,
//       '#rows' => $table_rows,
//       '#empty' => $this->t('No entries available.'),
//     ];

//     $content['pager'] = [
//       '#type' => 'pager',
//     ];

//     $content['#cache']['max-age'] = 0;

//     return $content;
//   }

//   /**
//    * Download a payment advice file.
//    *
//    * @param int $id
//    *   The ID of the payment advice.
//    */
//   public function downloadPaymentAdvice($id) {
//     $database = \Drupal::database();
//     $query = $database->select('pcda_form', 'p')
//       ->fields('p', ['bill_id'])
//       ->execute()
//       ->fetchObject();

//     if ($query) {
//       $file_path = 'C:\\Downloads\\' . $query->bill_id . '.pdf'; // Adjust path as needed
//       if (file_exists($file_path)) {
//         \Drupal::service('http_kernel')->sendFile($file_path, TRUE);
//       } else {
//         \Drupal::messenger()->addError($this->t('The file could not be found.'));
//       }
//     } else {
//       \Drupal::messenger()->addError($this->t('Payment advice record not found.'));
//     }
//   }
// }
