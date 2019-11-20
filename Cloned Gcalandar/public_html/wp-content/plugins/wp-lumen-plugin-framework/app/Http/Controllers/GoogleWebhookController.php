<?php

namespace App\Http\Controllers;

use App\Models\Synchronization;
use App\Helpers\LumenHelper;
use Illuminate\Support\Facades\Log;

class GoogleWebhookController extends Controller {

  /**
   * Create a new controller instance.
   * 
   * @param $helper LumenHelper
   */
  public function __construct(LumenHelper $helper) {
    $this->helper = $helper;
    $this->request = $this->helper->request();
  }

  public function __invoke() {
    if ($this->request->header('x-goog-resource-state') !== 'exists') {
      return;
    }

    try {
      Synchronization::query()
              ->where('id', $this->request->header('x-goog-channel-id'))
              ->where('resource_id', $this->request->header('x-goog-resource-id'))
              ->firstOrFail()->ping();
    } catch (\Exception $e) {
     
      Log::warning("Error ping: ". print_r([
          "x-goog-channel-id" => $this->request->header('x-goog-channel-id'),
          'x-goog-resource-id' => $this->request->header('x-goog-resource-id'),
          "Webhook warning: " => $e->getMessage()
      ], TRUE));
    }

    $response = [
      "status" => "received"
    ];
    return $this->helper->response(json_encode($response), 200, [
              "Content-Type" => "application/json"
    ]);
  }

}
