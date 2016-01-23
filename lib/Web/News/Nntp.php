<?php
namespace Web\News;

/**
 * NNTP server connectivity and commands
 */
class Nntp
{
	/**
	 * @var resource
	 */
	protected $connection;

	/**
	 * Constructs an Nntp object
	 *
	 * @param string $hostname
	 * @param int $port
	 */
	public function __construct($hostname, $port = 119)
	{
		$errno = $errstr = null;
		$this->connection = @fsockopen($hostname, $port, $errno, $errstr, 30);

		if (!$this->connection) {
			throw new \RuntimeException(
				"Unable to connect to {$hostname} on port {$port}: {$errstr}"
			);
		}

		$hello = fgets($this->connection);
		$responseCode = substr($hello, 0, 3);

		switch ($responseCode) {
			case 400:
			case 502:
				throw new \RuntimeException('Service unavailable');
				break;
			case 200:
			case 201:
			default:
				// Successful connection
				break;
		}
	}

	/**
	 * Closes the NNTP connection when the object is destroyed
	 */
	public function __destruct()
	{
		$this->sendCommand('QUIT', 205);
		fclose($this->connection);
		$this->connection = null;
	}

	/**
	 * Sends the LIST command to the server and returns an array of newsgroups
	 *
	 * @return array
	 */
	public function listGroups()
	{
		$list = [];
		$response = $this->sendCommand('LIST', 215);

		if ($response !== false) {
			while ($line = fgets($this->connection)) {
				if ($line == ".\r\n") {
					break;
				}

				$line = rtrim($line);
				list($group, $high, $low, $status) = explode(' ', $line);

				$list[$group] = [
					'high' => $high,
					'low' => $low,
					'status' => $status,
				];
			}
		}

		return $list;
	}

	/**
	 * Sets the active group at the server and returns details about the group
	 *
	 * @param string $group Name of the group to set as the active group
	 * @return array
	 * @throws \RuntimeException
	 */
	public function selectGroup($group)
	{
		$response = $this->sendCommand("GROUP {$group}", 211);
		
		if ($response !== false) {
			list($number, $low, $high, $group) = explode(' ', $response);

			return [
				'group' => $group,
				'articlesCount' => $number,
				'low' => $low,
				'high' => $high,
			];
		}

		throw new \RuntimeException('Failed to get info on group');
	}

	/**
	 * Returns an overview of the selected articles from the specified group
	 *
	 * @param string $group The name of the group to select
	 * @param int $start The number of the article to start from
	 * @param int $pageSize The number of articles to return
	 * @return array
	 */
	public function getArticlesOverview($group, $start, $pageSize = 20)
	{
		$groupDetails = $this->selectGroup($group);

		$pageSize = $pageSize - 1;
		$high = $groupDetails['high'];
		$low = $groupDetails['low'];

		if (!$start || $start > $high - $pageSize || $start < $low) {
			$start = $high - $low > $pageSize ? $high - $pageSize : $low;
		}

		$end = min($high, $start + $pageSize);

		$overview = [
			'group' => $groupDetails + ['start' => $start],
			'articles' => [],
		];

		$response = $this->sendCommand("XOVER {$start}-{$end}", 224);

		while ($line = fgets($this->connection)) {
			if ($line == ".\r\n") {
				break;
			}

			$line = rtrim($line);
			list($n, $subject, $author, $date, $messageId, $references, $bytes, $lines, $extra) = explode("\t", $line, 9);

			$overview['articles'][$n] = [
				'subject' => $subject,
				'author' => $author,
				'date' => $date,
				'messageId' => $messageId,
				'references' => $references,
				'bytes' => $bytes,
				'lines' => $lines,
				'extra' => $extra,
			];
		}

		return $overview;
	}

	/**
	 * Returns the full content of the specified article (headers and body)
	 *
	 * @param int $articleId
	 * @param string|null $group
	 * @return string
	 */
	public function readArticle($articleId, $group = null)
	{
		if ($group) {
			$groupDetails = $this->selectGroup($group);
		}

		$article = '';

		try {
			$response = $this->sendCommand("ARTICLE {$articleId}", 220);
		} catch (\RuntimeException $e) {
			return null;
		}

		while ($line = fgets($this->connection)) {
			if ($line == ".\r\n") {
				break;
			}

			$article .= $line;
		}

		return $article;
	}

	/**
	 * Performs a lookup on the $messageId to find its group and article ID
	 *
	 * @param string $messageId
	 * @return array
	 */
	public function xpath($messageId)
	{
		$response = $this->sendCommand("XPATH {$messageId}", 223);
		list($group, $articleId) = explode('/', $response);

		return [
			'messageId' => $messageId,
			'group' => $group,
			'articleId' => $articleId,
		];
	}

	/**
	 * Sends a command to the server and checks the expected response code
	 *
	 * @param string $command
	 * @param int $expected The successful response code expected
	 * @return string
	 */
	protected function sendCommand($command, $expected)
	{
		fwrite($this->connection, "$command\r\n");
		$result = fgets($this->connection);
		list($code, $response) = explode(' ', $result, 2);

		if ($code == $expected) {
			return rtrim($response);
		}

		throw new \RuntimeException(
			"Expected response code of {$expected} but received {$code} for command `{$command}'"
		);
	}
}
