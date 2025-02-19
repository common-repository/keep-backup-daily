<?php

namespace WPMechanic\KBFNR\Common\Http;

use WPMechanic\KBFNR\Common\Error\ErrorLog;
use WPMechanic\KBFNR\Common\Filesystem\Filesystem;
use WPMechanic\KBFNR\Common\MigrationState\MigrationStateManager;
use WPMechanic\KBFNR\Common\Properties\DynamicProperties;
use WPMechanic\KBFNR\Common\Properties\Properties;
use WPMechanic\KBFNR\Common\Settings\Settings;
use WPMechanic\KBFNR\Common\Util\Util;

class RemotePost extends Http {
	/**
	 * @var Properties
	 */
	public $properties;
	/**
	 * @var MigrationStateManager
	 */
	public $migration_state_manager;
	/**
	 * @var Settings
	 */
	public $settings;
	/**
	 * @var $error
	 */
	public $error;
	/**
	 * @var ErrorLog
	 */
	private $error_log;
	/**
	 * @var Scramble
	 */
	private $scrambler;

	public function __construct(
		Util $util,
		Filesystem $filesystem,
		MigrationStateManager $migration_state_manager,
		Settings $settings,
		ErrorLog $error_log,
		Scramble $scrambler,
		Properties $properties
	) {
		parent::__construct( $util, $filesystem, $scrambler, $properties );

		$this->util                    = $util;
		$this->filesystem              = $filesystem;
		$this->migration_state_manager = $migration_state_manager;
		$this->settings                = $settings->get_settings();
		$this->error_log               = $error_log;
		$this->scrambler               = $scrambler;
	}

	/**
	 * Post data to a remote site with  Pro and check the response.
	 *
	 * @param string $url              The URL to post to.
	 * @param array  $data             The associative array of data to be posted to the remote.
	 * @param string $scope            A string to be used in error messages defining the function that initiated the remote post.
	 * @param array  $args             An optional array of args to alter the timeout, blocking and sslverify options.
	 * @param bool   $expecting_serial Verify that the response is a serialized string (defaults to false).
	 *
	 * @return bool|string
	 */
	function post( $url, $data, $scope, $args = array(), $expecting_serial = false ) {
		$this->util->set_time_limit();
		$state_data = $this->migration_state_manager->set_post_data();

		if ( function_exists( 'fsockopen' ) && 0 === strpos( $url, 'https://' ) && 'ajax_verify_connection_to_remote_site' == $scope ) {
			$url_parts = Util::parse_url( $url );
			$host      = $url_parts['host'];
			if ( $pf = @fsockopen( $host, 443, $err, $err_string, 1 ) ) {
				// worked
				fclose( $pf );
			} else {
				// failed
				$url = substr_replace( $url, 'http', 0, 5 );
			}
		}

		$sslverify = ( 1 == $this->settings['verify_ssl'] ? true : false );

		$default_remote_post_timeout = apply_filters( 'kbfnr_default_remote_post_timeout', 60 * 20 );

		$args = wp_parse_args( $args,
			array(
				'timeout'   => $default_remote_post_timeout,
				'blocking'  => true,
				'sslverify' => $sslverify,
			) );

		$args['method'] = 'POST';

		if ( ! isset( $args['body'] ) ) {
			$args['body'] = $this->array_to_multipart( $data );
		}

		$args['headers']['Content-Type'] = 'multipart/form-data; boundary=' . $this->props->multipart_boundary;
		$args['headers']['Referer']      = $this->util->referer_from_url( $url );

		$this->dynamic_props->attempting_to_connect_to = $url;

		do_action( 'kbfnr_before_remote_post' );

		$response = wp_remote_post( $url, $args );

		if ( ! is_wp_error( $response ) ) {
			// Every response should be scrambled, but other processes may have been applied too so we use a filter.
			add_filter( 'kbfnr_after_response', array( $this->scrambler, 'unscramble' ) );
			$response['body'] = apply_filters( 'kbfnr_after_response', trim( $response['body'], "\xef\xbb\xbf" ) );
			remove_filter( 'kbfnr_after_response', array( $this->scrambler, 'unscramble' ) );
		}

		$response_status = $this->handle_remote_post_response( $response, $url, $scope, $expecting_serial, $state_data );

		if ( false === $response_status ) {
			return false;
		} else if ( true === $response_status ) {
			return $this->retry_remote_post( $url, $data, $scope, $expecting_serial );
		}

		return trim( $response['body'] );
	}

	function retry_remote_post( $url, $data, $scope, $args = array(), $expecting_serial = false ) {
		$url = substr_replace( $url, 'http', 0, 5 );
		if ( $response = $this->post( $url, $data, $scope, $args, $expecting_serial ) ) {
			return $response;
		}

		return false;
	}

	/**
	 *
	 *
	 * Returns true, false or null
	 *
	 * False is an error, true triggers retry_remote_post() which tries the request on plain HTTP, and null is a successful response
	 *
	 * @param       $response
	 * @param       $url
	 * @param       $scope
	 * @param       $expecting_serial
	 * @param array $state_data
	 *
	 * @return bool|null
	 */
	public function handle_remote_post_response( $response, $url, $scope, $expecting_serial, $state_data = array() ) {
		if ( is_wp_error( $response ) ) {
			if ( 0 === strpos( $url, 'https://' ) && 'ajax_verify_connection_to_remote_site' == $scope ) {
				return true;
			} elseif ( isset( $response->errors['http_request_failed'][0] ) && strstr( $response->errors['http_request_failed'][0], 'timed out' ) ) {
				$this->error_log->setError( sprintf( __( 'The connection to the remote server has timed out, no changes have been committed.', 'wpkbd' ).' (#134 - scope: %s)', $scope ) );
			} elseif ( isset( $response->errors['http_request_failed'][0] ) && ( strstr( $response->errors['http_request_failed'][0], ''.__('Could not resolve host', 'wpkbd').'' ) || strstr( $response->errors['http_request_failed'][0], "".__("Couldn't resolve host", 'wpkbd')."" ) || strstr( $response->errors['http_request_failed'][0], "".__("couldn't connect to host", 'wpkbd')."" ) ) ) {
				$this->error_log->setError( sprintf( __( 'We could not find', 'wpkbd').': %s. '.__('Are you sure this is the correct URL?', 'wpkbd' ), $state_data['url'] ) );
				$url_bits = Util::parse_url( $state_data['url'] );

				if ( strstr( $state_data['url'], 'dev.' ) || strstr( $state_data['url'], '.dev' ) || ! strstr( $url_bits['host'], '.' ) ) {
					$this->error_log->setError( $this->error_log->getError() . '<br />' );
					if ( 'pull' == $state_data['intent'] ) {
						$this->error_log->setError( $this->error_log->getError() . __( 'It appears that you might be trying to pull from a local environment. This will not work if', 'wpkbd').' <u>this</u> '.__('website happens to be located on a remote server, it would be impossible for this server to contact your local environment.', 'wpkbd' ) );
					} else {
						$this->error_log->setError( $this->error_log->getError() . __( 'It appears that you might be trying to push to a local environment. This will not work if', 'wpkbd').' <u>this</u> '.__('website happens to be located on a remote server, it would be impossible for this server to contact your local environment.', 'wpkbd' ) );
					}
				}
			} else {
				if ( defined( 'WP_HTTP_BLOCK_EXTERNAL' ) && WP_HTTP_BLOCK_EXTERNAL ) {
					$url_parts = Util::parse_url( $url );
					$host      = $url_parts['host'];
					if ( ! defined( 'WP_ACCESSIBLE_HOSTS' ) || ( defined( 'WP_ACCESSIBLE_HOSTS' ) && ! in_array( $host, explode( ',', WP_ACCESSIBLE_HOSTS ) ) ) ) {
						$this->error_log->setError( sprintf( __( 'We\'ve detected that', 'wpkbd' ).' <code>WP_HTTP_BLOCK_EXTERNAL</code> '.__('is enabled and the host', 'wpkbd' ).' <strong>%1$s</strong> '.__('has not been added to', 'wpkbd' ).' <code>WP_ACCESSIBLE_HOSTS</code>. '.__('Please disable', 'wpkbd' ).' <code>WP_HTTP_BLOCK_EXTERNAL</code> '.__('or add', 'wpkbd' ).' <strong>%1$s</strong> '.__('to', 'wpkbd' ).' <code>WP_ACCESSIBLE_HOSTS</code> '.__('to continue.', 'wpkbd' ).' <a href="%2$s" target="_blank">'.__('More information', 'wpkbd' ).'</a>. (#147 - scope: %3$s)', esc_attr( $host ), '', $scope ) );
					}
				} elseif ( isset( $response->errors['http_request_failed'][0] ) && strstr( $response->errors['http_request_failed'][0], ''.__('port', 'wpkbd').' 443: '.__('Connection refused', 'wpkbd').'' ) ) {
					$this->error_log->setError( sprintf( __( 'Couldn\'t connect over HTTPS.', 'wpkbd').' '.__('You might want to try regular HTTP instead.', 'wpkbd').' (#121 - scope: %s)', $scope ) );
				} elseif ( isset( $response->errors['http_request_failed'][0] ) && strstr( $response->errors['http_request_failed'][0], 'SSL' ) ) { // OpenSSL/cURL/MAMP Error
					$this->error_log->setError( sprintf(  '<strong>'.__('HTTPS Connection Error').':</strong>  (#121 - scope: %s) '.__('This typically means that the version of OpenSSL that your local site is using to connect to the remote is incompatible or, more likely, being rejected by the remote server because it\'s insecure.', 'wpkbd').' <a href="%s" target="_blank">'.__('See our documentation</a> for possible solutions.', 'wpkbd' ), $scope, '' ) );
				} else {
					$this->error_log->setError( sprintf( __( 'The connection failed, an unexpected error occurred, please contact support.', 'wpkbd' ).' (#121 - scope: %s)', $scope ) );
				}
			}
			$this->error_log->log_error( $this->error_log->getError(), $response );

			return false;

			//Check response codes and respond accordingly
		} elseif ( 200 > (int) $response['response']['code'] || 399 < (int) $response['response']['code'] ) {

			$return = null;
			switch ( (int) $response['response']['code'] ) {
				case 401:
					$this->error_log->setError( __( 'The remote site is protected with Basic Authentication. Please enter the username and password above to continue. (401 Unauthorized)', 'wpkbd' ) );
					$this->error_log->log_error( $this->error_log->getError(), $response );

					$return = false;
					break;

				//Explicitly do no retry http URL if remote returns 500 error
				case 500:
					$this->error_log->setError( sprintf( __( 'Unable to connect to the remote server, the remote server responded with', 'wpkbd' ).': %1$s %2$s (scope: %3$s)', $response['response']['code'], $response['response']['message'], $scope ) );
					$this->error_log->log_error( $this->error_log->getError(), $response );

					$return = false;
					break;

				case 0 === strpos( $url, 'https://' ) && 'ajax_verify_connection_to_remote_site' == $scope:
					$return = true;
					break;

				default:
					//other status codes less than 200 or over 400
					$this->error_log->setError( sprintf( __( 'Unable to connect to the remote server, please check the connection details', 'wpkbd' ).' - %1$s %2$s (#129 - scope: %3$s)', $response['response']['code'], $response['response']['message'], $scope ) );
					$this->error_log->log_error( $this->error_log->getError(), $response );

					$return = false;
					break;
			}

			if ( ! is_null( $return ) ) {
				return $return;
			}

		} elseif ( empty( $response['body'] ) ) {
			if ( '0' === $response['body'] && 'ajax_verify_connection_to_remote_site' == $scope ) {
				if ( 0 === strpos( $url, 'https://' ) ) {
					return true;
				} else {
					$this->error_log->setError( sprintf( __( 'Pro does not seem to be installed or active on the remote site.', 'wpkbd' ).' (#131 - scope: %s)', $scope ) );
				}
			} else {

				$url = '';

				$this->error_log->setError( sprintf( __( 'A response was expected from the remote, instead we got nothing.', 'wpkbd').' (#146 - scope: %1$s) '.__('Please review', 'wpkbd').' %2$s '.__('for possible solutions.', 'wpkbd' ), $scope, sprintf( '<a href="%s" target="_blank">%s</a>', $url, __( 'our documentation', 'wpkbd' ) ) ) );
			}
			$this->error_log->log_error( $this->error_log->getError(), $response );

			return false;

		} elseif ( $expecting_serial && false == is_serialized( $response['body'] ) ) {
			if ( 0 === strpos( $url, 'https://' ) && 'ajax_verify_connection_to_remote_site' == $scope ) {
				return true;
			}
			$this->error_log->setError( __( 'There was a problem with the AJAX request, we were expecting a serialized response, instead we received', 'wpkbd' ).':<br />' . esc_html( $response['body'] ) );
			$this->error_log->log_error( $this->error_log->getError(), $response );

			return false;

		} elseif ( $expecting_serial && ( 'ajax_verify_connection_to_remote_site' == $scope || 'ajax_copy_licence_to_remote_site' == $scope ) ) {

			$unserialized_response = Util::unserialize( $response['body'], __METHOD__ );

			if ( false !== $unserialized_response && isset( $unserialized_response['error'] ) && '1' == $unserialized_response['error'] && 0 === strpos( $url, 'https://' ) ) {

				if ( stristr( $unserialized_response['message'], 'Invalid content verification signature' ) ) {

					//Check if remote address returned is the same as what was requested. Apache sometimes returns a random HTTPS site.
					if ( false === strpos( $unserialized_response['message'], sprintf( 'Remote URL: %s', $state_data['url'] ) ) ) {
						return true;
					}
				}
			}
		}

		return null;
	}

	/**
	 * Verify a remote response is valid
	 *
	 * @param mixed $response Response
	 *
	 * @return mixed Response if valid, error otherwise
	 */
	public function verify_remote_post_response( $response ) {
		if ( false === $response ) {
			$return    = array( 'kbfnr_error' => 1, 'body' => $this->error_log->getError() );
			$error_msg = ''.__('Failed attempting to verify remote post response', 'wpkbd').' (#114mf)';
			$this->error_log->log_error( $error_msg, $this->error_log->getError() );
			$result = $this->end_ajax( json_encode( $return ) );

			return $result;
		}

		if ( ! is_serialized( trim( $response ) ) ) {
			$return    = array( 'kbfnr_error' => 1, 'body' => $response );
			$error_msg = ''.__('Failed as the response is not serialized string', 'wpkbd').' (#115mf)';
			$this->error_log->log_error( $error_msg, $response );
			$result = $this->end_ajax( json_encode( $return ) );

			return $result;
		}

		$response = unserialize( trim( $response ) );

		if ( isset( $response['kbfnr_error'] ) ) {
			$this->error_log->log_error( $response['kbfnr_error'], $response );
			$result = $this->end_ajax( json_encode( $response ) );

			return $result;
		}

		return $response;
	}
}
