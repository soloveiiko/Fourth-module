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
      $table_id = 'tableCount-' . $t;
      $form[$table_id] = [
        '#type' => 'table',
        '#header' => $this->header,
        '#caption' => $this->t('№@number', ['@number' => $t]),
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
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}
