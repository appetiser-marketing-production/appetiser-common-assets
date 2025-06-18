<?php
/**
 * GoogleSheetReader wrapper class.
 *
 * A lightweight wrapper around Google Sheets API for reading spreadsheet data.
 * Source: https://github.com/googleapis/google-api-php-client
 */

if ( ! class_exists( 'GoogleSheetReader' ) ) {

	/**
	 * Class GoogleSheetReader
	 *
	 * Wraps Google Sheets API for simple read-only access to a spreadsheet.
	 */
	class GoogleSheetReader {
		/**
		 * @var \Google_Client Google API client instance.
		 */
		private $client;

		/**
		 * @var \Google_Service_Sheets Sheets service instance.
		 */
		private $service;

		/**
		 * @var string Spreadsheet ID.
		 */
		private $spreadsheetId;

		/**
		 * GoogleSheetReader constructor.
		 *
		 * @param string $jsonKeyFilePath Path to the service account JSON key file.
		 * @param string $spreadsheetId   ID of the Google Spreadsheet to access.
		 */
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

		/**
		 * Reads a specified range from the spreadsheet.
		 *
		 * @param string $range A1 notation of the range to read (e.g., 'Sheet1!A1:C10').
		 * @return array|null   2D array of cell values or null if empty.
		 */
		public function readRange( $range ) {
			$response = $this->service->spreadsheets_values->get( $this->spreadsheetId, $range );
			return $response->getValues();
		}
	}
}