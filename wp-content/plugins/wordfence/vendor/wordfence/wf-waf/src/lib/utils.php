<?php

class wfWAFUtils {

	/**
	 * Return dot or colon notation of IPv4 or IPv6 address.
	 *
	 * @param string $ip
	 * @return string|bool
	 */
	public static function inet_ntop($ip) {
		// trim this to the IPv4 equiv if it's in the mapped range
		if (wfWAFUtils::strlen($ip) == 16 && wfWAFUtils::substr($ip, 0, 12) == "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff") {
			$ip = wfWAFUtils::substr($ip, 12, 4);
		}
		return self::hasIPv6Support() ? inet_ntop($ip) : self::_inet_ntop($ip);
	}

	/**
	 * Return the packed binary string of an IPv4 or IPv6 address.
	 *
	 * @param string $ip
	 * @return string
	 */
	public static function inet_pton($ip) {
		// convert the 4 char IPv4 to IPv6 mapped version.
		$pton = str_pad(self::hasIPv6Support() ? inet_pton($ip) : self::_inet_pton($ip), 16,
			"\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff\x00\x00\x00\x00", STR_PAD_LEFT);
		return $pton;
	}

	/**
	 * Added compatibility for hosts that do not have inet_pton.
	 *
	 * @param $ip
	 * @return bool|string
	 */
	public static function _inet_pton($ip) {
		// IPv4
		if (preg_match('/^(?:\d{1,3}(?:\.|$)){4}/', $ip)) {
			$octets = explode('.', $ip);
			$bin = chr($octets[0]) . chr($octets[1]) . chr($octets[2]) . chr($octets[3]);
			return $bin;
		}

		// IPv6
		if (preg_match('/^((?:[\da-f]{1,4}(?::|)){0,8})(::)?((?:[\da-f]{1,4}(?::|)){0,8})$/i', $ip)) {
			if ($ip === '::') {
				return "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0";
			}
			$colon_count = wfWAFUtils::substr_count($ip, ':');
			$dbl_colon_pos = wfWAFUtils::strpos($ip, '::');
			if ($dbl_colon_pos !== false) {
				$ip = str_replace('::', str_repeat(':0000',
						(($dbl_colon_pos === 0 || $dbl_colon_pos === wfWAFUtils::strlen($ip) - 2) ? 9 : 8) - $colon_count) . ':', $ip);
				$ip = trim($ip, ':');
			}

			$ip_groups = explode(':', $ip);
			$ipv6_bin = '';
			foreach ($ip_groups as $ip_group) {
				$ipv6_bin .= pack('H*', str_pad($ip_group, 4, '0', STR_PAD_LEFT));
			}

			return wfWAFUtils::strlen($ipv6_bin) === 16 ? $ipv6_bin : false;
		}

		// IPv4 mapped IPv6
		if (preg_match('/^((?:0{1,4}(?::|)){0,5})(::)?ffff:((?:\d{1,3}(?:\.|$)){4})$/i', $ip, $matches)) {
			$octets = explode('.', $matches[3]);
			return "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff" . chr($octets[0]) . chr($octets[1]) . chr($octets[2]) . chr($octets[3]);
		}

		return false;
	}

	/**
	 * Added compatibility for hosts that do not have inet_ntop.
	 *
	 * @param $ip
	 * @return bool|string
	 */
	public static function _inet_ntop($ip) {
		// IPv4
		if (wfWAFUtils::strlen($ip) === 4) {
			return ord($ip[0]) . '.' . ord($ip[1]) . '.' . ord($ip[2]) . '.' . ord($ip[3]);
		}

		// IPv6
		if (wfWAFUtils::strlen($ip) === 16) {

			// IPv4 mapped IPv6
			if (wfWAFUtils::substr($ip, 0, 12) == "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff") {
				return "::ffff:" . ord($ip[12]) . '.' . ord($ip[13]) . '.' . ord($ip[14]) . '.' . ord($ip[15]);
			}

			$hex = bin2hex($ip);
			$groups = str_split($hex, 4);
			$collapse = false;
			$done_collapse = false;
			foreach ($groups as $index => $group) {
				if ($group == '0000' && !$done_collapse) {
					if (!$collapse) {
						$groups[$index] = ':';
					} else {
						$groups[$index] = '';
					}
					$collapse = true;
				} else if ($collapse) {
					$done_collapse = true;
					$collapse = false;
				}
				$groups[$index] = ltrim($groups[$index], '0');
			}
			$ip = join(':', array_filter($groups));
			$ip = str_replace(':::', '::', $ip);
			return $ip == ':' ? '::' : $ip;
		}

		return false;
	}

	/**
	 * Verify PHP was compiled with IPv6 support.
	 *
	 * Some hosts appear to not have inet_ntop, and others appear to have inet_ntop but are unable to process IPv6 addresses.
	 *
	 * @return bool
	 */
	public static function hasIPv6Support() {
		return defined('AF_INET6');
	}

	/**
	 * Expand a compressed printable representation of an IPv6 address.
	 *
	 * @param string $ip
	 * @return string
	 */
	public static function expandIPv6Address($ip) {
		$hex = bin2hex(self::inet_pton($ip));
		$ip = wfWAFUtils::substr(preg_replace("/([a-f0-9]{4})/i", "$1:", $hex), 0, -1);
		return $ip;
	}

	protected static $servicesJSON;

	public static function json_encode($string) {
		if (function_exists('json_encode')) {
			return json_encode($string);
		} else {
			if (!self::$servicesJSON) {
				require_once WFWAF_LIB_PATH . 'json.php';
				self::$servicesJSON = new wfServices_JSON();
			}
			return self::$servicesJSON->encodeUnsafe($string);
		}
	}

	public static function json_decode($string, $assoc_array = false) {
		if (function_exists('json_decode')) {
			return json_decode($string, $assoc_array);
		} else {
			if (!self::$servicesJSON) {
				require_once WFWAF_LIB_PATH . 'json.php';
				self::$servicesJSON = new wfServices_JSON();
			}
			$res = self::$servicesJSON->decode($string);
			if ($assoc_array)
				$res = self::_json_decode_object_helper($res);
			return $res;

		}
	}

	/**
	 * @param object $data
	 * @return array
	 */
	protected static function _json_decode_object_helper($data) {
		if (is_object($data))
			$data = get_object_vars($data);
		return is_array($data) ? array_map('wfWAFUtils::_json_decode_object_helper', $data) : $data;
	}

	/**
	 * Compare two strings in constant time. It can leak the length of a string.
	 *
	 * @param string $a Expected string.
	 * @param string $b Actual string.
	 * @return bool Whether strings are equal.
	 */
	public static function hash_equals($a, $b) {
		$a_length = wfWAFUtils::strlen($a);
		if ($a_length !== wfWAFUtils::strlen($b)) {
			return false;
		}
		$result = 0;

		// Do not attempt to "optimize" this.
		for ($i = 0; $i < $a_length; $i++) {
			$result |= ord($a[$i]) ^ ord($b[$i]);
		}

		return $result === 0;
	}

	/**
	 * @param $algo
	 * @param $data
	 * @param $key
	 * @param bool|false $raw_output
	 * @return bool|string
	 */
	public static function hash_hmac($algo, $data, $key, $raw_output = false) {
		if (function_exists('hash_hmac')) {
			return hash_hmac($algo, $data, $key, $raw_output);
		}
		return self::_hash_hmac($algo, $data, $key, $raw_output);
	}

	/**
	 * @param $algo
	 * @param $data
	 * @param $key
	 * @param bool|false $raw_output
	 * @return bool|string
	 */
	private static function _hash_hmac($algo, $data, $key, $raw_output = false) {
		$packs = array('md5' => 'H32', 'sha1' => 'H40');

		if (!isset($packs[$algo]))
			return false;

		$pack = $packs[$algo];

		if (wfWAFUtils::strlen($key) > 64)
			$key = pack($pack, $algo($key));

		$key = str_pad($key, 64, chr(0));

		$ipad = (wfWAFUtils::substr($key, 0, 64) ^ str_repeat(chr(0x36), 64));
		$opad = (wfWAFUtils::substr($key, 0, 64) ^ str_repeat(chr(0x5C), 64));

		$hmac = $algo($opad . pack($pack, $algo($ipad . $data)));

		if ($raw_output)
			return pack($pack, $hmac);
		return $hmac;
	}

	/**
	 * @param int $length
	 * @param string $chars
	 * @return string
	 */
	public static function getRandomString($length = 16, $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_ []{}<>~`+=,.;:/?|') {
		// This is faster than calling self::random_int for $length
		$bytes = self::random_bytes($length);
		$return = '';
		$maxIndex = wfWAFUtils::strlen($chars) - 1;
		for ($i = 0; $i < $length; $i++) {
			$fp = (float) ord($bytes[$i]) / 255.0; // convert to [0,1]
			$index = (int) (round($fp * $maxIndex));
			$return .= $chars[$index];
		}
		return $return;
	}

	/**
	 * Polyfill for random_bytes.
	 *
	 * @param int $bytes
	 * @return string
	 */
	public static function random_bytes($bytes) {
		$bytes = (int) $bytes;
		if (function_exists('random_bytes')) {
			try {
				$rand = random_bytes($bytes);
				if (is_string($rand) && wfWAFUtils::strlen($rand) === $bytes) {
					return $rand;
				}
			} catch (Exception $e) {
				// Fall through
			} catch (TypeError $e) {
				// Fall through
			} catch (Error $e) {
				// Fall through
			}
		}
		if (function_exists('mcrypt_create_iv')) {
			$rand = @mcrypt_create_iv($bytes, MCRYPT_DEV_URANDOM);
			if (is_string($rand) && wfWAFUtils::strlen($rand) === $bytes) {
				return $rand;
			}
		}
		if (function_exists('openssl_random_pseudo_bytes')) {
			$rand = @openssl_random_pseudo_bytes($bytes, $strong);
			if (is_string($rand) && wfWAFUtils::strlen($rand) === $bytes) {
				return $rand;
			}
		}
		// Last resort is insecure
		$return = '';
		for ($i = 0; $i < $bytes; $i++) {
			$return .= chr(mt_rand(0, 255));
		}
		return $return;
	}

	/**
	 * Polyfill for random_int.
	 *
	 * @param int $min
	 * @param int $max
	 * @return int
	 */
	public static function random_int($min = 0, $max = 0x7FFFFFFF) {
		if (function_exists('random_int')) {
			try {
				return random_int($min, $max);
			} catch (Exception $e) {
				// Fall through
			} catch (TypeError $e) {
				// Fall through
			} catch (Error $e) {
				// Fall through
			}
		}
		$diff = $max - $min;
		$bytes = self::random_bytes(4);
		if ($bytes === false || wfWAFUtils::strlen($bytes) != 4) {
			throw new RuntimeException("Unable to get 4 bytes");
		}
		$val = unpack("Nint", $bytes);
		$val = $val['int'] & 0x7FFFFFFF;
		$fp = (float) $val / 2147483647.0; // convert to [0,1]
		return (int) (round($fp * $diff) + $min);
	}

	/**
	 * @param mixed $subject
	 * @return array|string
	 */
	public static function stripMagicQuotes($subject) {
		$sybase = ini_get('magic_quotes_sybase');
		$sybaseEnabled = ((is_numeric($sybase) && $sybase) ||
			(is_string($sybase) && $sybase && !in_array(wfWAFUtils::strtolower($sybase), array(
					'off',
					'false'
				))));
		if ((function_exists("get_magic_quotes_gpc") && get_magic_quotes_gpc()) || $sybaseEnabled) {
			return self::stripslashes_deep($subject);
		}
		return $subject;
	}

	/**
	 * @param mixed $subject
	 * @return array|string
	 */
	public static function stripslashes_deep($subject) {
		if (is_array($subject)) {
			return array_map(array(
				'self', 'stripslashes_deep',
			), $subject);
		} else if (is_string($subject)) {
			return stripslashes($subject);
		}
		return $subject;
	}


	/**
	 * Set the mbstring internal encoding to a binary safe encoding when func_overload
	 * is enabled.
	 *
	 * When mbstring.func_overload is in use for multi-byte encodings, the results from
	 * strlen() and similar functions respect the utf8 characters, causing binary data
	 * to return incorrect lengths.
	 *
	 * This function overrides the mbstring encoding to a binary-safe encoding, and
	 * resets it to the users expected encoding afterwards through the
	 * `reset_mbstring_encoding` function.
	 *
	 * It is safe to recursively call this function, however each
	 * `mbstring_binary_safe_encoding()` call must be followed up with an equal number
	 * of `reset_mbstring_encoding()` calls.
	 *
	 * @see wfWAFUtils::reset_mbstring_encoding
	 *
	 * @staticvar array $encodings
	 * @staticvar bool  $overloaded
	 *
	 * @param bool $reset Optional. Whether to reset the encoding back to a previously-set encoding.
	 *                    Default false.
	 */
	public static function mbstring_binary_safe_encoding($reset = false) {
		static $encodings = array();
		static $overloaded = null;

		if (is_null($overloaded))
			$overloaded = function_exists('mb_internal_encoding') && (ini_get('mbstring.func_overload') & 2);

		if (false === $overloaded)
			return;

		if (!$reset) {
			$encoding = mb_internal_encoding();
			array_push($encodings, $encoding);
			mb_internal_encoding('ISO-8859-1');
		}

		if ($reset && $encodings) {
			$encoding = array_pop($encodings);
			mb_internal_encoding($encoding);
		}
	}

	/**
	 * Reset the mbstring internal encoding to a users previously set encoding.
	 *
	 * @see wfWAFUtils::mbstring_binary_safe_encoding
	 */
	public static function reset_mbstring_encoding() {
		self::mbstring_binary_safe_encoding(true);
	}

	/**
	 * @param callable $function
	 * @param array $args
	 * @return mixed
	 */
	protected static function callMBSafeStrFunction($function, $args) {
		self::mbstring_binary_safe_encoding();
		$return = call_user_func_array($function, $args);
		self::reset_mbstring_encoding();
		return $return;
	}

	/**
	 * Multibyte safe strlen.
	 *
	 * @param $binary
	 * @return int
	 */
	public static function strlen($binary) {
		$args = func_get_args();
		return self::callMBSafeStrFunction('strlen', $args);
	}

	/**
	 * @param $haystack
	 * @param $needle
	 * @param int $offset
	 * @return int
	 */
	public static function stripos($haystack, $needle, $offset = 0) {
		$args = func_get_args();
		return self::callMBSafeStrFunction('stripos', $args);
	}

	/**
	 * @param $string
	 * @return mixed
	 */
	public static function strtolower($string) {
		$args = func_get_args();
		return self::callMBSafeStrFunction('strtolower', $args);
	}

	/**
	 * @param $string
	 * @param $start
	 * @param $length
	 * @return mixed
	 */
	public static function substr($string, $start, $length = null) {
		$args = func_get_args();
		return self::callMBSafeStrFunction('substr', $args);
	}

	/**
	 * @param $haystack
	 * @param $needle
	 * @param int $offset
	 * @return mixed
	 */
	public static function strpos($haystack, $needle, $offset = 0) {
		$args = func_get_args();
		return self::callMBSafeStrFunction('strpos', $args);
	}

	/**
	 * @param string $haystack
	 * @param string $needle
	 * @param int $offset
	 * @param int $length
	 * @return mixed
	 */
	public static function substr_count($haystack, $needle, $offset = 0, $length = null) {
		$haystack = self::substr($haystack, $offset, $length);
		return self::callMBSafeStrFunction('substr_count', array(
			$haystack, $needle,
		));
	}

	/**
	 * @param $string
	 * @return mixed
	 */
	public static function strtoupper($string) {
		$args = func_get_args();
		return self::callMBSafeStrFunction('strtoupper', $args);
	}

	/**
	 * @param string $haystack
	 * @param string $needle
	 * @param int $offset
	 * @return mixed
	 */
	public static function strrpos($haystack, $needle, $offset = 0) {
		$args = func_get_args();
		return self::callMBSafeStrFunction('strrpos', $args);
	}
	
	/**
	 * @param string $val An ini byte size value (e.g., 20M)
	 * @return int
	 */
	public static function iniSizeToBytes($val) {
		$val = trim($val);
		$last = strtolower(substr($val, -1));
		switch ($last) {
			case 'g':
				$val *= 1024;
			case 'm':
				$val *= 1024;
			case 'k':
				$val *= 1024;
		}
		
		return intval($val);
	}
}
