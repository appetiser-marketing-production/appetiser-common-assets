<?php
/* GoogleSheetReader wrapper class
*  source: https://github.com/googleapis/google-api-php-client
*/
if ( ! class_exists( 'GoogleSheetReader' ) ) {

	class GoogleSheetReader {
		private $client;
		private $service;
		private $spreadsheetId;

		public function __construct( $jsonKeyFilePath, $spreadsheetId ) {
			require_once __DIR__ . '/google-api-php-client/vendor/autoload.php';

			$this->spreadsheetId = $spreadsheetId;

			$this->client = new \Google_Client();
			$this->client->setAuthConfig( $jsonKeyFilePath );
			$this->client->setScopes( [
				\Google_Service_Sheets::SPREADSHEETS_READONLY,
			] );

			$this->service = new \Google_Service_Sheets( $this->client );
		}

		public function readRange( $range ) {
			$response = $this->service->spreadsheets_values->get( $this->spreadsheetId, $range );
			return $response->getValues();
		}
	}
}