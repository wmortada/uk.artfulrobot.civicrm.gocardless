<?php

require_once 'gocardlessdd.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function gocardlessdd_civicrm_config(&$config) {
  _gocardlessdd_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @param array $files
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function gocardlessdd_civicrm_xmlMenu(&$files) {
  _gocardlessdd_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * We set up the payment processor type and payment instrument types here.
 * (I tried to do this with `hook_civicrm_managed()` but failed because I need to relate the entities).
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function gocardlessdd_civicrm_install() {
  _gocardlessdd_civix_civicrm_install();

  /**
   * Helper function for creating data structures.
   *
   * @param string $entity - name of the API entity.
   * @param Array $params_min parameters to use for search.
   * @param Array $params_extra these plus $params_min are used if a create call
   *              is needed.
   */
  $get_or_create = function ($entity, $params_min, $params_extra) {
    $params_min += ['sequential' => 1];
    $result = civicrm_api3($entity, 'get', $params_min);
    if (!$result['count']) {
      // Couldn't find it, create it now.
      $result = civicrm_api3($entity, 'create', $params_extra + $params_min);
    }
    return $result['values'][0];
  };

  // We need a payment instrument known as direct_debit_gc.
  $payment_instrument = $get_or_create('OptionValue',
    [ 'option_group_id' => "payment_instrument", 'name' => "direct_debit_gc", ],
    [ 'label' => ts("GoCardless Direct Debit"), ]);
  $payment_instrument_id = $payment_instrument['value'];

  $get_or_create('PaymentProcessorType',
    [
      'name' => 'GoCardless',
      'title' => 'GoCardless',
      'class_name' => 'Payment_GoCardless',
      'billing_mode' => 4,
      'is_recur' => 1,
    ],
    [
      'is_active' => 1,
      'is_default' => 0,
      'user_name_label' => 'API Access Token',
      'signature_label' => 'Webhook Secret',
      'url_api_default' => 'https://api.gocardless.com/',
      'url_api_test_default' => 'https://api-sandbox.gocardless.com/',
      'billing_mode' => 4,
      'is_recur' => 1,
      'payment_type' => $payment_instrument_id,
    ]);
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function gocardlessdd_civicrm_uninstall() {
  _gocardlessdd_civix_civicrm_uninstall();
  // @todo remove direct_debit_gc payment instrument and GoCardless PaymentProcessorType if not in use.
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function gocardlessdd_civicrm_enable() {
  _gocardlessdd_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function gocardlessdd_civicrm_disable() {
  _gocardlessdd_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed
 *   Based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function gocardlessdd_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _gocardlessdd_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function gocardlessdd_civicrm_managed(&$entities) {
  _gocardlessdd_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * @param array $caseTypes
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function gocardlessdd_civicrm_caseTypes(&$caseTypes) {
  _gocardlessdd_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function gocardlessdd_civicrm_angularModules(&$angularModules) {
_gocardlessdd_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function gocardlessdd_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _gocardlessdd_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Complete a GoCardless redirect flow before we present the thank you page.
 *
 * - call GC API to complete the mandate.
 * - find details of the contribution: how much, how often, day of month, 'name'
 * - set up a GC Subscription.
 * - set trxn_id to the subscription ID in the contribution table.
 * - if recurring: set trxn_id, "In Progress", start date in contribution_recur table.
 * - if membership: set membership end date to start date + interval.
 *
 */
function gocardlessdd_civicrm_buildForm( $formName, &$form ) {
  if ($formName != 'CRM_Contribute_Form_Contribution_ThankYou' || empty($_GET['redirect_flow_id'])) {
    // This form build has nothing to do with us.
    return;
  }

  // We have a redirect_flow_id.
  $redirect_flow_id = $_GET['redirect_flow_id'];
  $sesh = CRM_Core_Session::singleton();
  $sesh_store = $sesh->get('redirect_flows', 'GoCardless');
  if (empty($sesh_store[$redirect_flow_id])) {
    // When would this happen?
    // - Back button.
    // - Hacks?
    // - Something else that lost the session.
    //
    // Anyway, in all cases let's assume that we are unable to proceed.
    // @todo should we tell the user about this?
    return;
  }

  // Complete the redirect flow with GC.
  $params = [
    'redirect_flow_id' => $redirect_flow_id,
    'session_token' => $_GET['qfKey'],
  ] + $sesh_store[$redirect_flow_id];
  try {
    $result = CRM_GoCardlessUtils::completeRedirectFlow($params);
  }
  catch (Exception $e) {
  }
}

/**
 * Functions below this ship commented out. Uncomment as required.
 *

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function gocardlessdd_civicrm_preProcess($formName, &$form) {

} // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 *
function gocardlessdd_civicrm_navigationMenu(&$menu) {
  _gocardlessdd_civix_insert_navigation_menu($menu, NULL, array(
    'label' => ts('The Page', array('domain' => 'uk.co.vedaconsulting.payment.gocardlessdd')),
    'name' => 'the_page',
    'url' => 'civicrm/the-page',
    'permission' => 'access CiviReport,access CiviContribute',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _gocardlessdd_civix_navigationMenu($menu);
} // */
