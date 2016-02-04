<?php
/**
 * This is a heavily-trimmed version of Will Bond's Flourish library fMailbox
 * class. It is based on the version of the file located here:
 * <https://github.com/flourishlib/flourish-classes/blob/7f95a67/fMailbox.php>
 *
 * This class parses mail messages retreived from the NNTP server.
 *
 * All headers, text and html content returned by this class are encoded in
 * UTF-8. Please see http://flourishlib.com/docs/UTF-8 for more information.
 *
 * @copyright  Copyright (c) 2010-2012 Will Bond
 * @author     Will Bond [wb] <will@flourishlib.com>
 * @license    http://flourishlib.com/license
 *
 * @package    Flourish
 * @link       http://flourishlib.com/fMailbox
 *
 * Copyright (c) 2010-2012 Will Bond <will@flourishlib.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
class fMailbox
{
	/**
	 * Takes a date, removes comments and cleans up some common formatting inconsistencies
	 *
	 * @param string $date  The date to clean
	 * @return string  The cleaned date
	 */
	private static function cleanDate($date)
	{
		$date = preg_replace('#\([^)]+\)#', ' ', trim($date));
		$date = preg_replace('#\s+#', ' ', $date);
		$date = preg_replace('#(\d+)-([a-z]+)-(\d{4})#i', '\1 \2 \3', $date);
		$date = preg_replace('#^[a-z]+\s*,\s*#i', '', trim($date));
		return trim($date);
	}

	/**
	 * Decodes encoded-word headers of any encoding into raw UTF-8
	 *
	 * @param string $text  The header value to decode
	 * @return string  The decoded UTF-8
	 */
	private static function decodeHeader($text)
	{
		$parts = preg_split('#(=\?[^\?]+\?[QB]\?[^\?]+\?=)#i', $text, -1, PREG_SPLIT_DELIM_CAPTURE);

		$part_with_encoding = array();
		$output = '';
		foreach ($parts as $part) {
			if ($part === '') {
				continue;
			}

			if (preg_match_all('#=\?([^\?]+)\?([QB])\?([^\?]+)\?=#i', $part, $matches, PREG_SET_ORDER)) {
				foreach ($matches as $match) {
					if (strtoupper($match[2]) == 'Q') {
						$part_string = rawurldecode(strtr(
							$match[3],
							array(
								'=' => '%',
								'_' => ' '
							)
						));
					} else {
						$part_string = base64_decode($match[3]);
					}
					$lower_encoding = strtolower($match[1]);
					$last_key = count($part_with_encoding) - 1;
					if (isset($part_with_encoding[$last_key]) && $part_with_encoding[$last_key]['encoding'] == $lower_encoding) {
						$part_with_encoding[$last_key]['string'] .= $part_string;
					} else {
						$part_with_encoding[] = array('encoding' => $lower_encoding, 'string' => $part_string);
					}
				}

			} else {
				$last_key = count($part_with_encoding) - 1;
				if (isset($part_with_encoding[$last_key]) && $part_with_encoding[$last_key]['encoding'] == 'iso-8859-1') {
					$part_with_encoding[$last_key]['string'] .= $part;
				} else {
					$part_with_encoding[] = array('encoding' => 'iso-8859-1', 'string' => $part);
				}
			}
		}

		foreach ($part_with_encoding as $part) {
			$output .= self::iconv($part['encoding'], 'UTF-8', $part['string']);
		}

		return $output;
	}

	/**
	 * Handles an individual part of a multipart message
	 *
	 * @param array  $info       An array of information about the message
	 * @param array  $structure  An array describing the structure of the message
	 * @return array  The modified $info array
	 */
	private static function handlePart($info, $structure)
	{
		if ($structure['type'] == 'multipart') {
			foreach ($structure['parts'] as $part) {
				$info = self::handlePart($info, $part);
			}
			return $info;
		}

		if ($structure['type'] == 'application' && in_array($structure['subtype'], array('pkcs7-mime', 'x-pkcs7-mime'))) {
			$to = null;
			if (isset($info['headers']['to'][0])) {
				$to = $info['headers']['to'][0]['mailbox'];
				if (!empty($info['headers']['to'][0]['host'])) {
					$to .= '@' . $info['headers']['to'][0]['host'];
				}
			}
		}

		if ($structure['type'] == 'application' && in_array($structure['subtype'], array('pkcs7-signature', 'x-pkcs7-signature'))) {
			$from = null;
			if (isset($info['headers']['from'])) {
				$from = $info['headers']['from']['mailbox'];
				if (!empty($info['headers']['from']['host'])) {
					$from .= '@' . $info['headers']['from']['host'];
				}
			}
		}

		$data = $structure['data'];

		if ($structure['encoding'] == 'base64') {
			$content = '';
			foreach (explode("\r\n", $data) as $line) {
				$content .= base64_decode($line);
			}
		} elseif ($structure['encoding'] == 'quoted-printable') {
			$content = quoted_printable_decode($data);
		} else {
			$content = $data;
		}

		if ($structure['type'] == 'text') {
			$charset = 'iso-8859-1';
			foreach ($structure['type_fields'] as $field => $value) {
				if (strtolower($field) == 'charset') {
					$charset = $value;
					break;
				}
			}
			$content = self::iconv($charset, 'UTF-8', $content);
			if ($structure['subtype'] == 'html') {
				$content = preg_replace('#(content=(["\'])text/html\s*;\s*charset=(["\']?))' . preg_quote($charset, '#') . '(\3\2)#i', '\1utf-8\4', $content);
			}
		}

		// This indicates a content-id which is used for multipart/related
		if ($structure['content_id']) {
			if (!isset($info['related'])) {
				$info['related'] = array();
			}
			$cid = $structure['content_id'][0] == '<' ? substr($structure['content_id'], 1, -1) : $structure['content_id'];
			$info['related']['cid:' . $cid] = array(
				'mimetype' => $structure['type'] . '/' . $structure['subtype'],
				'data'     => $content
			);
			return $info;
		}


		$has_disposition = !empty($structure['disposition']);
		$is_text         = $structure['type'] == 'text' && $structure['subtype'] == 'plain';
		$is_html         = $structure['type'] == 'text' && $structure['subtype'] == 'html';

		// If the part doesn't have a disposition and is not the default text or html, set the disposition to inline
		if (!$has_disposition && ((!$is_text || !empty($info['text'])) && (!$is_html || !empty($info['html'])))) {
			$is_web_image = $structure['type'] == 'image' && in_array($structure['subtype'], array('gif', 'png', 'jpeg', 'pjpeg'));
			$structure['disposition'] = $is_text || $is_html || $is_web_image ? 'inline' : 'attachment';
			$structure['disposition_fields'] = array();
			$has_disposition = true;
		}


		// Attachments or inline content
		if ($has_disposition) {

			$filename = '';
			foreach ($structure['disposition_fields'] as $field => $value) {
				if (strtolower($field) == 'filename') {
					$filename = $value;
					break;
				}
			}
			foreach ($structure['type_fields'] as $field => $value) {
				if (strtolower($field) == 'name') {
					$filename = $value;
					break;
				}
			}

			// This automatically handles primary content that has a content-disposition header on it
			if ($structure['disposition'] == 'inline' && $filename === '') {
				if ($is_text && !isset($info['text'])) {
					$info['text'] = $content;
					return $info;
				}
				if ($is_html && !isset($info['html'])) {
					$info['html'] = $content;
					return $info;
				}
			}

			if (!isset($info[$structure['disposition']])) {
				$info[$structure['disposition']] = array();
			}

			$info[$structure['disposition']][] = array(
				'filename' => $filename,
				'mimetype' => $structure['type'] . '/' . $structure['subtype'],
				'data'     => $content,
				'description' => $structure['description'],
			);
			return $info;
		}

		if ($is_text) {
			$info['text'] = $content;
			return $info;
		}

		if ($is_html) {
			$info['html'] = $content;
			return $info;
		}
	}

	/**
	 * This works around a bug in MAMP 1.9.4+ and PHP 5.3 where iconv()
	 * does not seem to properly assign the return value to a variable, but
	 * does work when returning the value.
	 *
	 * @param string $in_charset   The incoming character encoding
	 * @param string $out_charset  The outgoing character encoding
	 * @param string $string       The string to convert
	 * @return string  The converted string
	 */
	private static function iconv($in_charset, $out_charset, $string)
	{
		return iconv($in_charset, $out_charset, $string);
	}

	/**
	 * Parses a string representation of an email into the persona, mailbox and host parts
	 *
	 * @param  string $string  The email string to parse
	 * @return array  An associative array with the key `mailbox`, and possibly `host` and `personal`
	 */
	private static function parseEmail($string)
	{
		$email_regex = '((?:[^\x00-\x20\(\)<>@,;:\\\\"\.\[\]]+|"[^"\\\\\n\r]+")(?:\.[ \t]*(?:[^\x00-\x20\(\)<>@,;:\\\\"\.\[\]]+|"[^"\\\\\n\r]+"[ \t]*))*)@((?:[a-z0-9\\-]+\.)+[a-z]{2,}|\[(?:(?:[01]?\d?\d|2[0-4]\d|25[0-5])\.){3}(?:[01]?\d?\d|2[0-4]\d|25[0-5])\])';
		$name_regex  = '((?:[^\x00-\x20\(\)<>@,;:\\\\"\.\[\]]+[ \t]*|"[^"\\\\\n\r]+"[ \t]*)(?:\.?[ \t]*(?:[^\x00-\x20\(\)<>@,;:\\\\"\.\[\]]+[ \t]*|"[^"\\\\\n\r]+"[ \t]*))*)';

		if (preg_match('~^[ \t]*' . $name_regex . '[ \t]*<[ \t]*' . $email_regex . '[ \t]*>[ \t]*$~ixD', $string, $match)) {
			$match[1] = trim($match[1]);
			if ($match[1][0] == '"' && substr($match[1], -1) == '"') {
				$match[1] = substr($match[1], 1, -1);
			}
			return array(
				'personal' => self::decodeHeader($match[1]),
				'mailbox' => self::decodeHeader($match[2]),
				'host' => self::decodeHeader($match[3]),
				'raw' => $string,
			);

		} elseif (preg_match('~^[ \t]*(?:<[ \t]*)?' . $email_regex . '(?:[ \t]*>)?[ \t]*$~ixD', $string, $match)) {
			return array(
				'mailbox' => self::decodeHeader($match[1]),
				'host' => self::decodeHeader($match[2]),
				'raw' => $string,
			);

			// This handles the outdated practice of including the personal
			// part of the email in a comment after the email address
		} elseif (preg_match('~^[ \t]*(?:<[ \t]*)?' . $email_regex . '(?:[ \t]*>)?[ \t]*\(([^)]+)\)[ \t]*$~ixD', $string, $match)) {
			$match[3] = trim($match[1]);
			if ($match[3][0] == '"' && substr($match[3], -1) == '"') {
				$match[3] = substr($match[3], 1, -1);
			}

			return array(
				'personal' => self::decodeHeader($match[3]),
				'mailbox' => self::decodeHeader($match[1]),
				'host' => self::decodeHeader($match[2]),
				'raw' => $string,
			);
		}

		if (strpos($string, '@') !== false) {
			list ($mailbox, $host) = explode('@', $string, 2);
			return array(
				'mailbox' => self::decodeHeader($mailbox),
				'host' => self::decodeHeader($host),
				'raw' => $string,
			);
		}

		return array(
			'mailbox' => self::decodeHeader($string),
			'host' => '',
			'raw' => $string,
		);
	}

	/**
	 * Parses full email headers into an associative array
	 *
	 * @param  string $headers  The header to parse
	 * @param  string $filter   Remove any headers that match this
	 * @return array  The parsed headers
	 */
	private static function parseHeaders($headers, $filter = null)
	{
		$headers = trim($headers);
		if (!strlen($headers)) {
			return array();
		}
		$header_lines = preg_split("#\r\n(?!\s)#", $headers);

		$single_email_fields    = array('from', 'sender', 'reply-to');
		$multi_email_fields     = array('to', 'cc');
		$additional_info_fields = array('content-type', 'content-disposition');

		$headers = array();
		foreach ($header_lines as $header_line) {
			$header_line = preg_replace("#\r\n\s+#", ' ', $header_line);
			$header_line = trim($header_line);

			list ($header, $value) = preg_split('#:\s*#', $header_line, 2);
			$header = strtolower($header);

			if (strpos($header, $filter) !== false) {
				continue;
			}

			$is_single_email          = in_array($header, $single_email_fields);
			$is_multi_email           = in_array($header, $multi_email_fields);
			$is_additional_info_field = in_array($header, $additional_info_fields);

			if ($is_additional_info_field) {
				$pieces = preg_split('#;\s*#', $value, 2);
				$value = $pieces[0];

				$headers[$header] = array('value' => self::decodeHeader($value));

				$fields = array();
				if (!empty($pieces[1])) {
					preg_match_all('#(\w+)=("([^"]+)"|([^\s;]+))(?=;|$)#', $pieces[1], $matches, PREG_SET_ORDER);
					foreach ($matches as $match) {
						$fields[strtolower($match[1])] = self::decodeHeader(!empty($match[4]) ? $match[4] : $match[3]);
					}
				}
				$headers[$header]['fields'] = $fields;

			} elseif ($is_single_email) {
				$headers[$header] = self::parseEmail($value);

			} elseif ($is_multi_email) {
				$strings = array();

				preg_match_all('#"[^"]+?"#', $value, $matches, PREG_SET_ORDER);
				foreach ($matches as $i => $match) {
					$strings[] = $match[0];
					$value = preg_replace('#' . preg_quote($match[0], '#') . '#', ':string' . sizeof($strings), $value, 1);
				}
				preg_match_all('#\([^)]+?\)#', $value, $matches, PREG_SET_ORDER);
				foreach ($matches as $i => $match) {
					$strings[] = $match[0];
					$value = preg_replace('#' . preg_quote($match[0], '#') . '#', ':string' . sizeof($strings), $value, 1);
				}

				$emails = explode(',', $value);
				array_map('trim', $emails);
				foreach ($strings as $i => $string) {
					$emails = preg_replace(
						'#:string' . ($i+1) . '\b#',
						strtr($string, array('\\' => '\\\\', '$' => '\\$')),
						$emails,
						1
					);
				}

				$headers[$header] = array();
				foreach ($emails as $email) {
					$headers[$header][] = self::parseEmail($email);
				}

			} elseif ($header == 'references') {
				$headers[$header] = array_map(array('fMailbox', 'decodeHeader'), preg_split('#(?<=>)\s+(?=<)#', $value));

			} elseif ($header == 'received') {
				if (!isset($headers[$header])) {
					$headers[$header] = array();
				}
				$headers[$header][] = preg_replace('#\s+#', ' ', self::decodeHeader($value));

			} else {
				$headers[$header] = self::decodeHeader($value);
			}
		}

		return $headers;
	}

	/**
	 * Parses a MIME message into an associative array of information
	 *
	 * The output includes the following keys:
	 *
	 *  - `'received'`: The date the message was received by the server
	 *  - `'headers'`: An associative array of mail headers, the keys are the header names, in lowercase
	 *
	 * And one or more of the following:
	 *
	 *  - `'text'`: The plaintext body
	 *  - `'html'`: The HTML body
	 *  - `'attachment'`: An array of attachments, each containing:
	 *   - `'filename'`: The name of the file
	 *   - `'mimetype'`: The mimetype of the file
	 *   - `'data'`: The raw contents of the file
	 *  - `'inline'`: An array of inline files, each containing:
	 *   - `'filename'`: The name of the file
	 *   - `'mimetype'`: The mimetype of the file
	 *   - `'data'`: The raw contents of the file
	 *  - `'related'`: An associative array of related files, such as embedded images, with the key `'cid:{content-id}'` and an array value containing:
	 *   - `'mimetype'`: The mimetype of the file
	 *   - `'data'`: The raw contents of the file
	 *  - `'verified'`: If the message contents were verified via an S/MIME certificate - if not verified the smime.p7s will be listed as an attachment
	 *  - `'decrypted'`: If the message contents were decrypted via an S/MIME private key - if not decrypted the smime.p7m will be listed as an attachment
	 *
	 * All values in `headers`, `text` and `body` will have been decoded to
	 * UTF-8. Files in the `attachment`, `inline` and `related` array will all
	 * retain their original encodings.
	 *
	 * @param string  $message           The full source of the email message
	 * @param boolean $convert_newlines  If `\r\n` should be converted to `\n` in the `text` and `html` parts the message
	 * @return array  The parsed email message - see method description for details
	 */
	public static function parseMessage($message, $convert_newlines = false)
	{
		$info = array();
		list ($headers, $body)   = explode("\r\n\r\n", $message, 2);
		$parsed_headers          = self::parseHeaders($headers);
		$info['received']        = self::cleanDate(preg_replace('#^.*;\s*([^;]+)$#', '\1', $parsed_headers['received'][0]));
		$info['headers']         = array();
		foreach ($parsed_headers as $header => $value) {
			if (substr($header, 0, 8) == 'content-') {
				continue;
			}
			$info['headers'][$header] = $value;
		}
		$info['raw_headers'] = $headers;
		$info['raw_message'] = $message;

		$info = self::handlePart($info, self::parseStructure($body, $parsed_headers));
		unset($info['raw_message']);
		unset($info['raw_headers']);

		if ($convert_newlines) {
			if (isset($info['text'])) {
				$info['text'] = str_replace("\r\n", "\n", $info['text']);
			}
			if (isset($info['html'])) {
				$info['html'] = str_replace("\r\n", "\n", $info['html']);
			}
		}

		if (isset($info['text'])) {
			$info['text'] = preg_replace('#\r?\n$#D', '', $info['text']);
		}
		if (isset($info['html'])) {
			$info['html'] = preg_replace('#\r?\n$#D', '', $info['html']);
		}

		return $info;
	}

	/**
	 * Takes the raw contents of a MIME message and creates an array that
	 * describes the structure of the message
	 *
	 * @param string $data     The contents to get the structure of
	 * @param string $headers  The parsed headers for the message - if not present they will be extracted from the `$data`
	 * @return array  The multi-dimensional, associative array containing the message structure
	 */
	private static function parseStructure($data, $headers = null)
	{
		if (!$headers) {
			list ($headers, $data) = preg_split("#^\r\n|\r\n\r\n#", $data, 2);
			$headers = self::parseHeaders($headers);
		}

		if (!isset($headers['content-type'])) {
			$headers['content-type'] = array(
				'value'  => 'text/plain',
				'fields' => array()
			);
		}

		list ($type, $subtype) = explode('/', strtolower($headers['content-type']['value']), 2);

		if ($type == 'multipart') {
			$structure    = array(
				'type'    => $type,
				'subtype' => $subtype,
				'parts'   => array()
			);
			$boundary     = $headers['content-type']['fields']['boundary'];
			$start_pos    = strpos($data, '--' . $boundary) + strlen($boundary) + 4;
			$end_pos      = strrpos($data, '--' . $boundary . '--') - 2;
			$sub_contents = explode("\r\n--" . $boundary . "\r\n", substr(
				$data,
				$start_pos,
				$end_pos - $start_pos
			));
			foreach ($sub_contents as $sub_content) {
				$structure['parts'][] = self::parseStructure($sub_content);
			}

		} else {
			$structure = array(
				'type'               => $type,
				'type_fields'        => !empty($headers['content-type']['fields']) ? $headers['content-type']['fields'] : array(),
				'subtype'            => $subtype,
				'content_id'         => isset($headers['content-id']) ? $headers['content-id'] : null,
				'encoding'           => isset($headers['content-transfer-encoding']) ? strtolower($headers['content-transfer-encoding']) : '8bit',
				'disposition'        => isset($headers['content-disposition']) ? strtolower($headers['content-disposition']['value']) : null,
				'disposition_fields' => isset($headers['content-disposition']) ? $headers['content-disposition']['fields'] : array(),
				'description'        => isset($headers['content-description']) ? $headers['content-description'] : null,
				'data'               => $data
			);
		}

		return $structure;
	}
}
