<?php

namespace App\Http\Controllers;

use App\Helpers\LumenHelper;
use App\Models\WpPost;
use App\Models\WpUser;

class SettingsController extends Controller {

  protected $post, $request, $auth, $helper, $settings;

  /**
   * Create a new controller instance.
   * @param $helper LumenHelper
   * @param $post WpPost
   * @throws \Exception
   */
  public function __construct(LumenHelper $helper, WpPost $post) {
    $this->post = $post;
    $this->helper = $helper;
    $this->request = $this->helper->request();
    $this->auth = $this->helper->auth();
    $this->settings = $this->helper->make('settings');
    if ($this->request->filled('action')) {
      $this->handlerSave($this->request->get('action'));
    }
  }

  /**
   * Process the Request Data
   * @throws \Exception
   */
  private function handlerSave($action) {

    $this->helper->validateCSRF();

    if ($action == 'save') {
      if (!current_user_can('manage_options')) {
        wp_die('Unauthorized user');
      }

      if ($this->request->get('oo_gsa_client_id')) {
        $oo_gsa_client_id_value = $this->request->get('oo_gsa_client_id');
        update_option('oo_gsa_client_id', $oo_gsa_client_id_value);
      }

      if ($this->request->get('oo_gsa_api_key')) {
        $oo_gsa_api_key_value = $this->request->get('oo_gsa_api_key');
        update_option('oo_gsa_api_key', $oo_gsa_api_key_value);
      }

      if ($this->request->get("god_email")) {
        $god_email = $this->request->get("god_email");
        update_option("god_email", $god_email);
      }

      if ($this->request->get('oo_gsa_pattern_indisponible')) {
        $oo_gsa_pattern_indisponible_value = $this->request->get('oo_gsa_pattern_indisponible');
        update_option('oo_gsa_pattern_indisponible', $oo_gsa_pattern_indisponible_value);
      }

      if ($this->request->get('oo_gsa_pattern_reservation')) {
        $oo_gsa_pattern_reservation_value = $this->request->get('oo_gsa_pattern_reservation');
        update_option('oo_gsa_pattern_reservation', $oo_gsa_pattern_reservation_value);
      }
    }
  }

  /**
   * Show the view
   * @throws \Exception
   */
  public function template() {
    $settings = [
     'god_email' => get_option("god_email", ""),
     'oo_gsa_client_id_value' => get_option('oo_gsa_client_id', ''),
     'oo_gsa_api_key_value' => get_option('oo_gsa_api_key', ''),
     'oo_gsa_pattern_indisponible_value' => get_option('oo_gsa_pattern_indisponible', ''),
     'oo_gsa_pattern_reservation_value' => get_option('oo_gsa_pattern_reservation', ''),
    ];
    
    return $this->helper->view('admin-settings', ['settings' => $settings]);
  }

}
