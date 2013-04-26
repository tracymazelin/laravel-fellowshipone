<?php 

	
	/**
	 * Helper Class for the FellowshipOne.com API in Laravel
	 * Using some methods adapted from Daniel Boorn's Helper Class:
	 * @link https://github.com/deboorn/Fellowship-One-API-Helper/blob/master/lib/com.rapiddigitalllc/FellowshipOne.php
	 * @license apache license 2.0, code is distributed "as is", use at own risk, all rights reserved
	 * @copyright 2013 Tracy Mazelin
	 * @author Tracy Mazelin tracy.mazelin@activenetwork.com 
	 * @uses PHP PECL OAuth
	 *
	 */


	class FellowshipOne{
		
		public $settings;
		public $error;
					
		/**
		 * contruct fellowship one class with settings array that contains
		 * @param unknown_type $settings
		 */
		public function __construct($settings){
			$this->settings = (object) $settings;
			return $this->login($settings['username'], $settings['password']);
		}

					
		/**
		 * Generic HTTP GET function
		 * @param string $endpoint 
		 * @return object
		 */
		public function get($endpoint){
			$url = $this->settings->baseUrl . $endpoint;
			return $this->fetchJson($url);
		}

		/**
		 * Generic HTTP POST function
		 * @param object $model 
		 * @param string $endpoint 
		 * @return object
		 */
		public function post($model, $endpoint){
			$url = $this->settings->baseUrl . $endpoint;
			$model = json_encode($model);
			return $this->fetchJson($url,$model,OAUTH_HTTP_METHOD_POST);
		}
		
		/**
		 * Generic HTTP PUT function
		 * @param object $model 
		 * @param string $endpoint 
		 * @return object
		 */
		public function put($model, $endpoint){
			$url = $this->settings->baseUrl . $endpoint;
			$model = json_encode($model);
			return $this->fetchJson($url,$model,OAUTH_HTTP_METHOD_PUT);
		}
		
		/**
		 * Generic HTTP DELETE function
		 * @param string $endpoint 
		 * @return object
		 */
		public function delete($endpoint){
			$url = $this->settings->baseUrl . $endpoint;
			return $this->fetchJson($url,$model=null,OAUTH_HTTP_METHOD_DELETE);
		}

		

		/**
		 * fetches JSON request on F1, parses and returns response
		 * @param string $url
		 * @param string|array $data
		 * @param const $method
		 * @return void
		 */
		public function fetchJson($url,$data=null,$method=OAUTH_HTTP_METHOD_GET){
			try{
				$o = new OAuth($this->settings->key, $this->settings->secret, OAUTH_SIG_METHOD_HMACSHA1);
				$o->setToken($this->accessToken->oauth_token, $this->accessToken->oauth_token_secret);
				$headers = array(
						'Content-Type' => 'application/json',
				);
				
				if($o->fetch($url, $data, $method, $headers)){
						if($this->settings->debug) 
						return array(
							'headers'=>self::http_parse_headers($o->getLastResponseHeaders()), 
							'response'=>json_decode($o->getLastResponse(),true)
						);
						return json_decode($o->getLastResponse(),true);
					}
				
			}catch(OAuthException $e){
				$this->error = array(
					'method'=>$method,
					'url'=>$url,
					'response'=>self::http_parse_headers($o->getLastResponseHeaders())
					
				);
				return $this->error;
			}
		}	
		
		
		/**
		 * parse header string to array
		 * @source http://www.php.net/manual/en/function.http-parse-message.php
		 * @param string $header
		 * @return array $retVal
		 */
		public static function http_parse_headers($header) {
	      $retVal = array();
	      $fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $header));
	      foreach ($fields as $field) {

	        // Do not process empty cubrid_num_fields(result)
	        if (empty($field)) {
	          continue;
	        }

		        if (preg_match('/([^:]+): (.+)/m', $field, $match)) {
		          $match[1] = preg_replace('/(?<=^|[\x09\x20\x2D])./e', 'strtoupper("\0")', strtolower(trim($match[1])));
		          if( isset($retVal[$match[1]]) ) {
		            if (!is_array($retVal[$match[1]])) {
		              $retVal[$match[1]] = array($retVal[$match[1]]);
		            }
		            $retVal[$match[1]][] = $match[2];
		          }
		          else {
		            $retVal[$match[1]] = trim($match[2]);
		          }
		        }
		        else {
		          if (preg_match('/HTTP\//', $field)) {
		            // Following HTTP standards which are space-separated
		            preg_match('/(.*?) (.*?) (.*)/', $field, $matches);
		            $retVal['HTTP']['version'] = $matches[1];
		            $retVal['HTTP']['code'] = $matches[2];
		            $retVal['HTTP']['reason'] = $matches[3];
		          }
		          else {
		            $retVal['Content'][] = $field;
		          }
		        }
		      }
		      return $retVal;
	  	}


		/**
		 * get access token from laravel cache
		 * @param string $username
		 * @return array|NULL
		 */
		protected function getAccessToken(){
			if (Cache::has('tokens'))
				{
				     $tokens = Cache::get('tokens');
				     return (object) $tokens;
				}
			return null;
		}
		
		
		/**
		 * save access token to laravel cache
		 * @param array $token
		 */
		protected function saveAccessToken($token){
			Cache::forever('tokens', (object) $token);
		}
		
				
		/**
		 * 2nd Party credentials based authentication
		 * @param string $username
		 * @param string $password
		 * @return boolean
		 */
		public function login($username,$password){
			$token = $this->getAccessToken();
			if(!$token){
				$token = $this->obtainAccessToken($username,$password);
				$this->saveAccessToken($token);
			}
			$this->accessToken = $token;
			return true;
		}

		/**
		 * obtain credentials based access token from API
		 * @param string $username
		 * @param string $password
		 * @return array
		 */
		protected function obtainAccessToken($username,$password){
			try{
				$message = urlencode(base64_encode("{$username} {$password}"));
				
				$url = $this->settings->baseUrl . $this->settings->accessTokenUrl .'?ec='.$message;
				$o = new OAuth($this->settings->key, $this->settings->secret, OAUTH_SIG_METHOD_HMACSHA1);
				return (object) $o->getAccessToken($url);
			}catch(OAuthException $e){
				die("Error: {$e->getMessage()}\nCode: {$e->getCode()}\nResponse: {$e->lastResponse}\n");
			}
		}
		
			
	}