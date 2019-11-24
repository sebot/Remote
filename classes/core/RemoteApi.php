<?php
/**
 * Remote
 *  
 * @category Multisite
 * @package  Remote
 * @author   Sebo <sebo@42geeks.gg>
 * @license  GPLv2 https://opensource.org/licenses/gpl-2.0.php
 * @link     https://42geeks.gg/
 */
namespace remote\core;

use ParagonIE\Halite\KeyFactory;
use ParagonIE\HiddenString\HiddenString;
use ParagonIE\Halite\Symmetric\Crypto as Symmetric;

/**
 * Class RemoteApi - launch requests to remote site accessing
 * Fetcher wp-json API
 * 
 * @category Multisite
 * @package  Remote
 * @author   Sebo <sebo@42geeks.gg>
 * @license  GPLv2 https://opensource.org/licenses/gpl-2.0.php
 * @link     https://42geeks.gg/
 */
class RemoteApi
{
	const REMOTE_END = 'wp-json/fetcher/v2/remote/';

	/**
	 * The remote sites base url
	 */
	protected $baseUrl = '';

    /**
     * Empty
     */
    public function __construct()
	{
	}

	/**
	 * Set url for the api only once when the
	 * post is loaded
	 * 
	 * @param string $url - the url
	 * 
	 * @return void
	 */
	public function setUrl(string $url): void
	{
		$this->baseUrl = $url;
	}

	/**
     * Connect a Fetcher Site to this remote Network
	 * to do that a few things will happen:
	 * 
	 * 1) a secret key is generated derived from secret
	 * 2) a cryptographic key is crafted using halite
	 * 3) the secret key will be sent to the remote site using handshake
	 * where it's stored in the database, this secret key will
	 * contain the endpoint beeing opened up. The key will live their
	 * for only one request, after that the endpoint is removed
	 * 4) the original intended request containing actual data is sent signed
	 * with the handshake secret and hmac with the remote secret
	 * 
	 * @param string $secret - the secret provided (needs to match the secret on the fetcher site)
	 * @param int $site_id - the site_id to connect to
	 * 
     */
    public function connect(string $secret, int $site_id)
    {
		[$mac, $keyForNextRequest, $msg] = $this->_generateHandshakePackage($secret, 'connect');
		$this->_handshake($mac, $msg);
		$result = $this->_sendConnectRequest($keyForNextRequest);
		// site has been connected
		if (false === $result->error) {
			update_post_meta($site_id, 'isConnected', true);
			return true;
		}
	}

	/**
	 * Generate hkdf authenticated encrypted message
	 * based on provided secret and the request which will be included
	 * in the "opening" request
	 * 
	 * @param string $secret - the secret
	 * @param string $request - the request that will be called
	 * 
	 * @return array - the signed encrypted message and the opener for the next request to use
	 * remote
	 */
	private function _generateHandshakePackage(string $secret, string $request): array
	{
		// generate opener which will be sent to remote site
		$p1 = new HiddenString(str_rot13($secret)); // pepper
		$s1 = random_bytes(16); // salt
		$opener = KeyFactory::deriveEncryptionKey($p1, $s1); // soup

		// generate key for handshake, this time use reversed secret ;)
		$p2 = new HiddenString(strrev($secret)); // pepper
		$s2 = '\x63\x96\xa9\x00'; // salt
		$packageLock = KeyFactory::deriveEncryptionKey($p2, $s2); // soup

		// encrypt - TODO: include shiffre in message like strrev or str_rot13
		// and rotate them for every request cycle
		$message = new HiddenString($request.'::'.bin2hex($s1));
		$ciphertext = Symmetric::encrypt($message, $packageLock);

		// auth
		$p3 = new HiddenString(str_rot13(strrev($secret)));
		$s3 = '\x36\x69\x9a\xff';

		return [
			Symmetric::authenticate(
				$ciphertext, 
				KeyFactory::deriveAuthenticationKey($p3, $s3)
			),
			$opener,
			$ciphertext
		];
	}

	private function _sendConnectRequest($key)
	{
		// TODO: authenticate as well -> abstract this to not call the same code all over again
		$message = new HiddenString('remote_site_connect');
		$ciphertext = Symmetric::encrypt($message, $key);

		// TODO: include initial config for site to connect it
		$data = [
			// 'remoteMac' => $mac,
			'remoteMsg' => $ciphertext
		];
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->baseUrl.self::REMOTE_END.'connect');
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$resp = curl_exec($ch);
		
		curl_close ($ch);

		return json_decode($resp);
	}
	
	/**
     *
     */
    private function _handshake($mac, $msg)
    {
		$data = [
			'remoteMac' => $mac,
			'remoteMsg' => $msg
		];
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->baseUrl.self::REMOTE_END.'handshake');
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$resp = curl_exec($ch);

		curl_close ($ch);
    }
}