<?php

namespace Drupal\bonnie\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Create form.
 */
class TableForm extends FormBase {

  /**
   * Table header.
   *
   * @var string[]
   */
  protected $header;

  /**
   * Inactive table cells.
   *
   * @var string[]
   */
  protected $inactiveCells;

  /**
   * Number of constructed tables.
   *
   * @var int
   */
  protected int $tableCount = 1;

  /**
   * Number of constructed rows.
   *
   * @var int
   */
  protected int $rowCount = 1;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'table_form';
  }

  /**
   * Table header.
   */
  public function addHeader() {
    $this->header = [
      'year' => $this->t('Year'),
      'jan' => $this->t('Jan'),
      'feb' => $this->t('Feb'),
      'mar' => $this->t('Mar'),
      'q1' => $this->t('Q1'),
      'apr' => $this->t('Apr'),
      'may' => $this->t('May'),
      'jun' => $this->t('Jun'),
      'q2' => $this->t('Q2'),
      'jul' => $this->t('Jul'),
      'aug' => $this->t('Aug'),
      'sep' => $this->t('Sep'),
      'q3' => $this->t('Q3'),
      'oct' => $this->t('Oct'),
      'nov' => $this->t('Nov'),
      'dec' => $this->t('Dec'),
      'q4' => $this->t('Q4'),
      'ytd' => $this->t('YTD'),
    ];
  }

  /**
   * Inactive cells of table.
   */
  protected function inactiveCells() {
    $this->inactiveCells = [
      'year' => $this->t('Year'),
      'q1' => $this->t('Q1'),
      'q2' => $this->t('Q2'),
      'q3' => $this->t('Q3'),
      'q4' => $this->t('Q4'),
      'ytd' => $this->t('YTD'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#prefix'] = '<div id = "form_wrapper">';
    $form['#suffix'] = '</div>';
    $form['#attached'] = ['library' => ['bonnie/global']];
    // Add buttons.
    $form['addRow'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add Year'),
      '#submit' => ['::addRows'],
      '#limit_validation_errors' => [],
      '#ajax' => [
        'callback' => '::submitAjaxForm',
        'event' => 'click',
        'wrapper' => 'form_wrapper',
        'progress' => [
          'type' => 'none',
        ],
      ],
    ];
    $form['addTable'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add Table'),
      '#submit' => ['::addTable'],
      '#limit_validation_errors' => [],
      '#ajax' => [
        'callback' => '::submitAjaxForm',
        'event' => 'click',
        'wrapper' => 'form_wrapper',
        'progress' => [
          'type' => 'none',
        ],
      ],
    ];
    $this->buildTable($this->tableCount, $form, $form_state);
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#ajax' => [
        'callback' => '::submitAjaxForm',
        'event' => 'click',
        'wrapper' => 'form_wrapper',
      ],
    ];
    return $form;
  }

  /**
   * Build table.
   */
  public function buildTable(int $tableCount, array &$form, FormStateInterface $form_state) {
    $this->addHeader();
    for ($t = 1; $t <= $tableCount; $t++) {
      $table_id = "tableCount-{$t}";
      $form[$table_id] = [
        '#type' => 'table',
        '#header' => $this->header,
        '#caption' => $this->t('Table №@number', ['@number' => $t]),
      ];
      $this->buildRow($table_id, $form[$table_id], $form_state);
    }
  }

  /**
   * Build rows for table.
   */
  protected function buildRow($table_id, array &$table, FormStateInterface $form_state): void {
    $this->inactiveCells();
    for ($r = $this->rowCount; $r > 0; $r--) {
      foreach ($this->header as $id => $value) {
        $table[$r][$id] = [
          '#type' => 'number',
          '#step' => '0.01',
        ];
        // Values for inactive cells.
        if (array_key_exists($id, $this->inactiveCells)) {
          $form_state->getValue([$table_id, $r, $id]);
          $table[$r][$id]['#disabled'] = TRUE;
        }
      }
      // Default value for year.
      $table[$r]['year']['#default_value'] = date('Y') - $r + 1;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $this->inactiveCells();
    // Start and end points.
    $start = NULL;
    $end = NULL;
    // Storing values from each table.
    $values = [];
    // Storing all the table with cell values.
    $active_val = [];
    for ($t = 1; $t <= $this->tableCount; $t++) {
      $tables = $form_state->getValue("tableCount-{$t}");
      foreach ($tables as $row_id) {
        foreach ($row_id as $cell_id => $cells) {
          if (!array_key_exists($cell_id, $this->inactiveCells)) {
            $active_val["tableCount-{$t}"][] = $cells;
          }
        }
      }
      // Saving values.
      foreach ($active_val as $cells_val) {
        $values = $cells_val;
      }
      // Validation different tables.
      foreach ($values as $id => $value) {
        for ($f = 0; $f < count($values); $f++) {
          if (empty($active_val['tableCount-1'][$f]) !== empty($active_val["tableCount-{$t}"][$f])) {
            $form_state->setErrorByName("tableCount-{$t}", 'Tables are different.');
          }
        }
        // Validation of start point.
        if (!empty($value) || $value == '0') {
          $start = $id;
          break;
        }
      }
      // Start point is not empty.
      if ($start !== NULL) {
        for ($f = $start; $f < count($values); $f++) {
          if (($values[$f] == NULL)) {
            $end = $f;
            break;
          }
        }
      }
      // End point is not empty.
      if ($end !== NULL) {
        for ($c = $end; $c < count($values); $c++) {
          if (($values[$c]) != NULL) {
            $form_state->setErrorByName('tableCount-', $this->t('Invalid.'));
          }
        }
      }
    }

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    for ($t = 0; $t <= $this->tableCount; $t++) {
      for ($r = 1; $r <= $this->rowCount; $r++) {
        // Value setting.
        $value = $form_state->getValue(["tableCount-{$t}", $r]);
        // Default value for inactive cells.
        $q1 = 0;
        $q2 = 0;
        $q3 = 0;
        $q4 = 0;
        $ytd = 0;
        // Validation for inactive cells.
        if (!empty($value['jan']) || !empty($value['feb']) || !empty($value['mar'])) {
          $q1 = round(($value['jan'] + $value['feb'] + $value['mar'] + 1) / 3, 2);
        }
        if (!empty($value['apr']) || !empty($value['may']) || !empty($value['jun'])) {
          $q2 = round((($value['apr'] + $value['may'] + $value['jun']) + 1) / 3, 2);
        }
        if (!empty($value['jul']) || !empty($value['aug']) || !empty($value['sep'])) {
          $q3 = round((($value['jul'] + $value['aug'] + $value['sep']) + 1) / 3, 2);
        }
        if (!empty($value['oct']) || !empty($value['nov']) || !empty($value['dec'])) {
          $q4 = round((($value['oct'] + $value['nov'] + $value['dec']) + 1) / 3, 2);
        }
        if ($q1 !== 0 || $q2 !== 0 || $q3 !== 0 || $q4 !== 0) {
          $ytd = round((($q1 + $q2 + $q3 + $q4) + 1) / 4, 2);
        }
        // Set values for inactive cells.
        $form["tableCount-{$t}"][$r]['q1']['#value'] = $q1;
        $form["tableCount-{$t}"][$r]['q2']['#value'] = $q2;
        $form["tableCount-{$t}"][$r]['q3']['#value'] = $q3;
        $form["tableCount-{$t}"][$r]['q4']['#value'] = $q4;
        $form["tableCount-{$t}"][$r]['ytd']['#value'] = $ytd;
      }
    }
    $this->messenger()->addStatus('Valid.');

  }

  /**
   * Function adding a new row.
   */
  public function addRows(array $form, FormStateInterface $form_state): array {
    // Increase by 1 the number of rows.
    $this->rowCount++;
    // Rebuild form with 1 extra row.
    $form_state->setRebuild();
    return $form;
  }

  /**
   * Function adding a new table.
   */
  public function addTable(array $form, FormStateInterface $form_state): array {
    // Increase by 1 the number of tables.
    $this->tableCount++;
    // Create new tables.
    $form_state->setRebuild();
    return $form;
  }

  /**
   * Refreshing the page.
   */
  public function submitAjaxForm(array $form, FormStateInterface $form_state): array {
    return $form;
  }

}
