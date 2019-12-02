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

// octopus & hexagon
use remote\core\crypto\Octopus;

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
class RemoteApi extends Octopus
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
     * Sends a page to the remote site with all
     * required meta (including images) attached
     * images will be sent as base64
     * 
     * @param string $secret - the secret for the site
     * @param int $post_id - the local post id
     * 
     * @return int - the remote post
     */
    public function sendPostToRemote(string $secret, int $post_id): int
    {
        var_dump($post_id);die;
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
     * @return bool - wether the connect succeeded or not
     */
    public function connect(string $secret, int $site_id): bool
    {
        // handshake -> TODO: abstract
        $octopus = new Octopus($secret, true);
        [$mac, $msg, $saltVector] = $octopus->getHandshakeData('connect');
        $res = $this->_handshake($mac, $msg, $saltVector);
        if (false === $res->error) {
            $showHeader        = get_field('show_header', $site_id);
            $showNavigation    = get_field('show_navigation', $site_id);
            $showSlider        = get_field('show_slider', $site_id);
            $showFeaturedImage = get_field('show_featured_image', $site_id);

            // request data
            $rqData = [
                'request' => self::rhashRoute('connect'),
                'requestData' => [
                    $showHeader,
                    $showNavigation,
                    $showSlider,
                    $showFeaturedImage
                ]
            ];
            $ciphertext = $octopus->generateCiphertext(json_encode($rqData, true));
            $mac2 = $octopus->sign2ndRequest($ciphertext);
            $remoteResponse = $this->_sendConnectRequest($mac2, $ciphertext);
            return !$remoteResponse->error;
        }

        return null === $res->error ? false : $res->error;
    }

    /**
     * Update a sites settings
     * 
     * @param string $secret - the site secret
     * @param int $site_id - the site local id
     */
    public function update(string $secret, int $site_id): void
    {
        // handshake -> TODO: abstract
        $octopus = new Octopus($secret, true);
        [$mac, $msg, $saltVector] = $octopus->getHandshakeData('update');
        $res = $this->_handshake($mac, $msg, $saltVector);
        if (false === $res->error) {
            $showHeader        = get_field('show_header', $site_id);
            $showNavigation    = get_field('show_navigation', $site_id);
            $showSlider        = get_field('show_slider', $site_id);
            $showFeaturedImage = get_field('show_featured_image', $site_id);

            // request data
            $rqData = [
                'request' => self::rhashRoute('update'),
                'requestData' => [
                    $showHeader,
                    $showNavigation,
                    $showSlider,
                    $showFeaturedImage
                ]
            ];
            $ciphertext = $octopus->generateCiphertext(json_encode($rqData, true));
            $mac2 = $octopus->sign2ndRequest($ciphertext);
            $this->_sendUpdateRequest($mac2, $ciphertext);
        }
    }

    private function _sendUpdateRequest(string $mac, string $ciphertext)
    {
        $data = [
			'mac' => $mac,
			'msg' => $ciphertext
		];
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->baseUrl.self::REMOTE_END.'update');
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $resp = curl_exec($ch);
		curl_close ($ch);

		return json_decode($resp);
    }

    /**
     * Reverse string then hash it
     * TODO: move this to Octopus
     * 
     * @param string $str - the string
     * 
     * @return string - the hash
     */
    private static function rhashRoute(string $str): string
    {
        return md5(strrev($str));
    }

    /**
     * Send a connect request to FetcherRemoteApi
     * this request has been initialized with a proper handshake
     * 
     * @param string $mac - the mac used to sign the message
     * @param string $ciphertext - the cyphertext encrypted by nx params
     * which are by now stored on the client
     * 
     * @return object - the response
     */
	private function _sendConnectRequest(string $mac, string $ciphertext): object
	{
		// TODO: include initial config for site to connect it
		$data = [
			'mac' => $mac,
			'msg' => $ciphertext
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
     * Run the initial Handshake call
     */
    private function _handshake(string $mac, string $msg, string $saltVector)
    {
		$data = [
			'm' => $mac,
            'ms' => $msg,
            'sv' => $saltVector
		];
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->baseUrl.self::REMOTE_END.'handshake');
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $resp = curl_exec($ch);

        curl_close ($ch);
        
        return json_decode($resp);
    }
}