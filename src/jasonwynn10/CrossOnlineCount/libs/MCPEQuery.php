<?php
namespace jasonwynn10\CrossOnlineCount\libs;

/**
 * Class MCPEQuery
 * @package jasonwynn10\CrossOnlineCount\libs
 * @author m1x
 */
class MCPEQuery
{
	/**
	 * @param string $host
	 * @param int $port
	 * @param int $timeout
	 *
	 * @return array
	 */
	static public function query(string $host, int $port, int $timeout = 2)
	{
		$socket = @fsockopen('udp://' . $host, $port, $errno, $errstr, $timeout);

		if($errno || $socket === false) {
			return ['error' => $errstr];
		}

		stream_Set_Timeout($socket, $timeout);
		stream_Set_Blocking($socket, true);

		$randInt = mt_rand(1, 999999999);
		$reqPacket = "\x01";
		$reqPacket .= pack('Q*', $randInt);
		$reqPacket .= "\x00\xff\xff\x00\xfe\xfe\xfe\xfe\xfd\xfd\xfd\xfd\x12\x34\x56\x78"; // magic string
		$reqPacket .= pack('Q*', 0);

		fwrite($socket, $reqPacket, strlen($reqPacket));

		$response = fread($socket, 4096);

		fclose($socket);

		if (empty($response) || $response === false) {
			return ['error' => 'Server failed to respond'];
		}
		if (substr($response, 0, 1) !== "\x1C") {
			return ['error' => 'Unknown Error'];
		}

		//        $firstPart = substr($response, 0, 16);
		//        $magic = substr($response, 16, 16);

		$serverInfo = substr($response, 35);
		$serverInfo = preg_replace("#§.#", "", $serverInfo);
		$serverInfo = explode(';', $serverInfo);

		return [
			'motd' => $serverInfo[1],
			'num' => $serverInfo[4],
			'max' => $serverInfo[5],
			'version' =>  $serverInfo[3],
			'platform' => 'PE'
		];
	}
}