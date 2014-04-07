<?php

/**
 * sendyApi is a PHP5 wrapper for the Sendy API that intends to 
 * take advantage of core WordPress functionality.
 * @internal This wrapper is incomplete and subject to change.
 * @author Eric Binnion <ericbinnion@gmail.com>
 * @copyright Copyright (c) 
 * @link http://manofhustle.com
 * @version 0.1.1
 */

class sendyApi {
	private $installationUrl;
	private $apiKey;
	private $listId;

	public function __construct( array $config ) {
		if ( !isset($config['installationUrl']) )
			return new WP_Error( 1, 'Required config parameter [installation_url] is not set');
        if ( !isset($config['apiKey']) )
        	return new WP_Error( 1, 'Required config parameter [api_key] is not set');

        $this->listId; = $config['listId'];
        $this->installationUrl = $config['installationUrl'];
        $this->apiKey = $config['apiKey'];
	}

	public getListId() {
		return $this->listId;
	}

	public setListId( $listId ) {
		if( isset( $listId ) )
			return new WP_Error( 1, 'Must pass listId parameter to update listId');
		$this->listId = $listId;
	}

	public function subscribe( $params ) {
		if( !isset($params['email']) )
			return new WP_Error( 1, 'Subscribe requires an email.');

		$response = $this->execute( 'subscribe', $params );

		switch ($result) {
			case '1':
			case 'true':
				return array(
					'status' => true,
					'message' => 'Subscribed'
				);
				break;

			case 'Already subscribed.':
				return array(
					'status' => true,
					'message' => 'Already subscribed.'
				);
				break;

			default:
				return array(
					'status' => false,
					'message' => $result
				);
				break;
		}
	}

	public function unsubscribe( $params ) {
		if( !isset($params['email']) )
			return new WP_Error( 1, 'Subscribe requires an email.');

		$response = $this->execute( 'unsubscribe', $params );

		switch ($result) {
			case '1':
				return array(
					'status' => true,
					'message' => 'Unsubscribed'
				);
				break;

			default:
				return array(
					'status' => false,
					'message' => $result
				);
				break;
		}
	}

	public function getStatus( $email ) {
		if( !isset($params['email']) )
			return new WP_Error( 1, 'Retrieving a status requires an email.');

		$response = $this->execute( '/api/subscribers/subscription-status.php', $params );

		//Handle the results
		switch ($result) {
			case 'Subscribed':
			case 'Unsubscribed':
			case 'Unconfirmed':
			case 'Bounced':
			case 'Soft bounced':
			case 'Complained':
				return array(
					'status' => true,
					'message' => $result
				);
			break;

			default:
				return array(
					'status' => false,
					'message' => $result
				);
			break;
		}
	}

	public function getSubscriberCount( $listId = null ) {
		if( !isset($listId) && !isset($this->listId) )
			return new WP_Error( 1, 'getSubscriberCount requires listId to be set when instantiating class or when calling this method.');

		$params = array();
		if( isset($listId) )
			$params['listId'] = $listId;

		$response = $this->execute( '/api/subscribers/active-subscriber-count.php', $params );

		if( is_int($response) ) {
			return array(
				'status' => true,
				'message' => $result
			);
		} else {
			return array(
				'status' => false,
				'message' => $result
			);
		}
	}

	protected function execute( $endpoint, $params ) {
		$params['boolean'] = true;

		$request = array_merge( array('list' => $this->listId, 'boolean' => true), $params )

		$response = wp_remote_post( "{$this->installationUrl}/{$endpoint}", array( 'body' => $request ) );

		if( is_wp_error($response) ) {
			trigger_error( $response->get_error_message() );
		} else {
			return wp_remote_retrieve_body( $response );
		}
	}
}