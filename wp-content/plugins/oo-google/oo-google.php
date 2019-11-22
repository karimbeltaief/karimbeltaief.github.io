<?php
/*
Plugin Name: Oolna Laravel google sync
Description: Google api notification push with wordpress
Version:     1.0
Author:      Oolna
*/

const GOOGLE_OAUTH_ROUTE_PATH = 'google/oauth';
const WEBHOOK_ROUTE_PATH = 'google/webhook';
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action('generate_rewrite_rules', 'oo_google_rewrite_rules');

function oo_google_rewrite_rules( $wp_rewrite ) {
    $new_rules = array(GOOGLE_OAUTH_ROUTE_PATH => 'index.php?oo-google=oauth');
    $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
    $new_rules = array(WEBHOOK_ROUTE_PATH => 'index.php?oo-google=webhook');
    $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
    $wp_rewrite->flush_rules();
}

add_filter( 'query_vars', 'oo_google_query_vars' );
function oo_google_query_vars( $query_vars ){
    $query_vars[] = 'code';
    return $query_vars;
}


add_action( 'parse_request', 'oo_google_parse_request' );
function oo_google_parse_request( &$wp )
{
    $routePath = $wp->request;
    if($routePath == GOOGLE_OAUTH_ROUTE_PATH){
        if(\Illuminate\Support\Facades\Auth::check()) {
            $google = new \App\Services\Google();
            $code = isset($_GET["code"]) ? $_GET["code"] : NULL;
            if ($code!=NULL && !empty($code)) {
                $google->authenticate($code);
                $account = $google->service('Plus')->people->get('me');
                auth()->user()->googleAccounts()->updateOrCreate(
                    [
                        'google_id' => $account->id,
                    ],
                    [
                        'name' => head($account->emails)->value,
                        'token' => $google->getAccessToken(),
                    ]
                );
                exit();
            } else {
                echo $google->createAuthUrl();
                header("Location: " . $google->createAuthUrl());
                exit();
            }
        }else{
            echo "User not logged in";
            exit();
        }
    }
    elseif($routePath == WEBHOOK_ROUTE_PATH){
        //var_dump($_SERVER);

        $obj = \App\Synchronization::query()
            ->where('id', $_SERVER["HTTP_X_GOOG_CHANNEL_ID"])
            ->where('resource_id', $_SERVER["HTTP_X_GOOG_RESOURCE_ID"])
            ->firstOrFail();
        var_dump($obj);
        $obj->ping();
         exit();
    }
    return;
}