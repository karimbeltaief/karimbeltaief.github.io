<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get currency exchange rates.
 */
function Fcc_get_exchange_rates( $currency = 'USD' ) {

	$rates = FCC_Rates::get_rates();
	if ( is_array( $rates ) && $currency != 'USD' ) :

		if ( ! Fcc_currency_exists( $currency ) ) {
			trigger_error(
				esc_html__( 'Base currency to get rates not found in database', 'favethemes-currency-converter' ),
				E_USER_WARNING
			);
			return null;
		}

		$new_rates = array();
		$base_rate = $rates[strtoupper( $currency )];

		while ( $array_key = current( $rates ) ) :
			$key = key( $rates );
			$new_rates[$key] = 1 * $rates[$key] / $base_rate;
			next( $rates );
		endwhile;

		$rates = $new_rates;

	endif;

	return $rates;
}

/**
 * Sends json object for given currency with exchange rates
 */
function Fcc_get_exchange_rates_json( $currency = 'USD' ) {
	$rates = FCC_get_exchange_rates( strtoupper( $currency ) );
	wp_send_json( $rates );
}
add_action( 'wp_ajax_nopriv_get_exchange_rates', 'Fcc_get_exchange_rates_json' );
add_action( 'wp_ajax_get_exchange_rates', 'Fcc_get_exchange_rates_json' );

/**
 * Convert from one currency to another.
 */
function Fcc_convert_currency( $amount = 1, $from = 'USD', $in = 'EUR' ) {

	$rates = FCC_Rates::get_rates();

	$error = $result = '';
	if ( $rates && is_array( $rates ) && count( $rates ) > 100 ) {

		if ( ! Fcc_currency_exists( $from ) OR ! Fcc_currency_exists( $in ) ) {
			trigger_error(
				esc_html__( 'Currency was not exist or found in database.', 'favethemes-currency-converter' ),
				E_USER_WARNING
			);
			$error = true;
		}

		if ( ! is_numeric( $amount ) ) {
			trigger_error(
				esc_html__( 'Amount to covert is not number, it must be number.', 'favethemes-currency-converter' ),
				E_USER_WARNING
			);
			$error = true;
		}

		if ( ! $error === true ) {
			$from   = strtoupper( $from );
			$in     = strtoupper( $in );
			$result = $rates[ $from ] && $rates[ $in ] ? (float) $amount * (float) $rates[ $in ] / (float) $rates[ $from ] : floatval( $amount );
		}

	} else {

		trigger_error(
			__( 'Look like your API is not valid, There was a problem to get currency data from database.', 'favethemes-currency-converter' ),
			E_USER_WARNING
		);

	}

	return $result;
}

/**
 * Get currency exchange rate from one to another.
 */
function Fcc_get_exchange_rate( $currency, $other_currency ) {
	$currency = strtoupper( $currency );
	$other_currency = strtoupper( $other_currency );
	$rate = $currency == $other_currency ? 1 : Fcc_convert_currency( 1, $currency, $other_currency );
	return $rate;
}

/**
 * Get currencies array
 */
function Fcc_get_currencies() {
	return FCC_Rates::get_currencies();
}

/**
 * Get List of currencies as json object.
 */
function Fcc_get_currencies_json() {
	$currencies = FCC_get_currencies();
	if ( $currencies && is_array( $currencies ) ) {
		wp_send_json( $currencies );
	}
}
add_action( 'wp_ajax_nopriv_fcc_get_currencies', 'Fcc_get_currencies_json' );
add_action( 'wp_ajax_fcc_get_currencies', 'Fcc_get_currencies_json' );

/**
 * Get currency data.
 */
function Fcc_get_currency( $currency_code = 'USD' ) {

	if ( ! is_string( $currency_code ) OR strlen( $currency_code ) != 3 ) {
		trigger_error(
			esc_html__( 'Please pass valid currency code for argument and it must be a string of three characters long', 'favethemes-currency-converter' ),
			E_USER_WARNING
		);
		return null;
	}

	$currency_data = Fcc_get_currencies();

	if ( ! array_key_exists( strtoupper( $currency_code ), $currency_data ) ) {
		trigger_error(
			esc_html__( 'Currency could not be found', 'favethemes-currency-converter' ),
			E_USER_WARNING
		);
		return null;
	}

	return (array) $currency_data[strtoupper( $currency_code )];
}

/**
 * Format currency
 */
function Fcc_format_currency( $amount, $currency_code, $currency_symbol = true, $sup = false ) {

	if ( ! $amount || ! $currency_code OR is_nan( $amount ) )
		return '';

	$currency = Fcc_get_currency( strtoupper( $currency_code ) );

	if ( is_null( $currency ) ){
		return '';
	}

	if ( ! $currency ) {
		$symbol = $currency_symbol == true ? strtoupper( $currency_code ) : '';
		$result = $amount . ' ' . $symbol;
	} else {
		$formatted = number_format( $amount, $currency['decimals'], $currency['decimals_sep'], $currency['thousands_sep'] );
		if ( $currency_symbol == false ) {
			$result = $formatted;
		} else {
			if($sup) {
	            $currency_symbol = '<sup>'.$currency['symbol'].'</sup>';
	        } else {
	        	$currency_symbol = $currency['symbol'];
	        }

			$result = $currency['position'] == 'before' ? $currency_symbol . '' . $formatted : $formatted . '' . $currency_symbol;
		}
	}

	return html_entity_decode( $result );
}

/**
 * Check if currency code exist
 */
function Fcc_currency_exists( $currency_code ) {

	$currencies = Fcc_get_currencies();

	$codes = array();
	if ( $currencies && is_array( $currencies ) ) {
		foreach ( $currencies as $key => $value ) {
			$codes[] = $key;
		}
	}

	return $codes && is_array( $codes ) ? in_array( strtoupper( $currency_code ), (array) $codes ) : null;
}
