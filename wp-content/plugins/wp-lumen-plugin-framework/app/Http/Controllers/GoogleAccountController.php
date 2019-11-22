<?php

namespace App\Http\Controllers;

use App\Models\Calendar;
use App\Models\GoogleAccount;
use App\Http\Services\Google as Google;

use App\Helpers\LumenHelper;
use App\Models\WpUser;
use Symfony\Component\HttpFoundation\Cookie;

class GoogleAccountController extends Controller
{
      /**
   * Create a new controller instance.
   * @param $helper LumenHelper
   * @param $post WpPost
   */
    public function __construct(LumenHelper $helper) 
    {
      $this->middleware('auth');

      $this->helper = $helper;
      $this->request = $this->helper->request();
      $this->auth = $this->helper->auth();
    }

    /**
     * Display a listing of the google accounts.
     */
    public function index()
    { 
        if (empty($this->auth->user())) {
          abort("404");
        }
        return $this->helper->view('accounts', array(
          'accounts' => $this->auth->user()->googleAccounts()->get(),
          'request'=>$this->request,
        ));
    }

    /**
     * Handle the OAuth connection which leads to 
     * the creating of a new Google Account.
     */
    public function store(Google $google)
    {
        /**
         * @var \App\Helpers\LumenHelper $helper
         */
        $cookieName = "redirect";
        $redirect = $this->helper->request()->hasCookie($cookieName);
        if (!$this->request->has('code')) {
            return $this->helper->redirect($google->createAuthUrl());
        }

        $google->authenticate($this->request->get('code'));
        $account = $google->service('Plus')->people->get('me');
        $this->auth->user()->googleAccounts()->updateOrCreate(
            [
                'google_id' => $account->id,
            ],
            [
                'name' => head($account->emails)->value,
                'token' => json_encode($google->getAccessToken()),
            ]
        );

        if($redirect){
            $url = $this->helper->request()->cookies->get("redirect");
            $this->helper->request()->cookies->remove($cookieName);
            return $this->helper->redirect()->to($url);
        }
        return $this->helper->redirect()->route('google.index');
    }

    /**
     * Revoke the account's token and delete the it locally.
     */
    public function destroy($googleAccount, Google $google)
    {
        $googleAccount = GoogleAccount::findOrFail($googleAccount);
        $googleAccount->calendars->each->delete();

        $googleAccount->delete();

        $google->revokeToken($googleAccount->token);

        return $this->helper->redirect()->route('google.index');
    }

    public function ajaxDestroy($googleAccount, Google $google)
    {
        /**
         * @var \App\Models\GoogleAccount $googleAccount
         */
        $googleAccount = GoogleAccount::findOrFail($googleAccount);
        $result = [];
        try {
            /**
             * @var \Illuminate\Database\Eloquent\Relations\HasMany $calendars
             */
            $calendars = $googleAccount->calendars;
            $calendars->each(function($calendar){
                /**
                 * @var \App\Models\Calendar $calendar
                 */
                $listingId = $calendar->listing_id;
                if($listingId != NULL){
                    $meta_key = 'homey_calendar_meta';
                    /**
                     * @Todo Supprimer le metadata qui sauvegardais l'index du calendar
                     */
                    $calendar_meta = get_post_meta($listingId, $meta_key, TRUE);
                    if(is_array($calendar_meta))
                    {
                        delete_post_meta($listingId, $meta_key, $calendar_meta);
                    }
                }
                $calendar->delete();
            });
            $googleAccount->delete();

            $google->revokeToken($googleAccount->token);

            $result = [
                "status" => "success",
                "message" => "Compte déconnecté avec succès"
            ];
        }catch (\Exception $e){
            $result = [
                "status" => "error",
                "message" => "Oups ! Une erreur est survenue lors de la déconnexion du compte!"
            ];
        }
        return $this->helper->response(json_encode($result), 200, [
            "Content-Type" => "application/json"
        ]);
    }

}
