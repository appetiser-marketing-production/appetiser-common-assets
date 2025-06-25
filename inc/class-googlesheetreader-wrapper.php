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
		 * @var boolean debug.
		 */
		private $debug = false;
		private $logMessages = [];
		
		/**
		 * GoogleSheetReader constructor.
		 *
		 * @param string $jsonKeyFilePath Path to the service account JSON key file.
		 * @param string $spreadsheetId   ID of the Google Spreadsheet to access.
		 */
		public function __construct( $jsonKeyFilePath, $spreadsheetId, $debug = false ) {
			$this->debug = $debug;

			add_action( 'admin_notices', [ $this, 'outputLogs' ] );
			
			$this->log('🔧 [Init] Starting GoogleSheetReader constructor');

			require_once __DIR__ . '/google-api-php-client/vendor/autoload.php';
			$this->log('📦 [Step 1] Google Client Library loaded');

			$this->spreadsheetId = $spreadsheetId;
			$this->log('🆔 [Step 2] Spreadsheet ID set: ' . esc_html($spreadsheetId));

			$this->client = new \Google_Client();
			$this->log('⚙️ [Step 3] Google_Client instantiated');

			$this->client->setAuthConfig( $jsonKeyFilePath );
			$this->log('🔐 [Step 4] Auth config set using: ' . esc_html($jsonKeyFilePath));

			$this->client->setScopes( [
				\Google_Service_Sheets::SPREADSHEETS_READONLY,
			] );
			$this->log('📘 [Step 5] Scopes set (readonly)');

			$this->service = new \Google_Service_Sheets( $this->client );
			$this->log('✅ [Step 6] Sheets service initialized');
		}

		/**
		 * Reads a specified range from the spreadsheet.
		 *
		 * @param string $range A1 notation of the range to read (e.g., 'Sheet1!A1:C10').
		 * @return array|null   2D array of cell values or null if empty.
		 */
		public function readRange( $range ) {
			$this->log('🔎 [Read] Attempting to read range: ' . esc_html($range));

			try {
				$response = $this->service->spreadsheets_values->get( $this->spreadsheetId, $range );
				$this->log('📊 [Read] Response received');
				return $response->getValues();
			} catch (Exception $e) {
				$this->log('❌ [Error] Google API exception: ' . esc_html($e->getMessage()));
				return null;
			}
		}

		/**
		 * Conditional logger for debug mode.
		 *
		 * @param string $message
		 */
		private function log( $message ) {
			if ( $this->debug ) {
				$this->logMessages[] = $message;
			}
		}

		public function outputLogs() {
			if ( $this->debug && ! empty( $this->logMessages ) ) {
				echo '<div class="notice notice-info is-dismissible">
				<p><strong>Google sheet reader log:</strong></p>
				<ul style="margin: 0; padding-left: 20px;">';
				foreach ( $this->logMessages as $msg ) {
					echo '<li>' . esc_html( $msg ) . '</li>';
				}
				echo '</ul></div>';
			}
		}
		
	}
}
