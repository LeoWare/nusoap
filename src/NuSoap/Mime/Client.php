<?php

namespace NuSoap\Mime;

use Mail_mimePart;
use Mail_mimeDecode;
use NuSoap\Client as BaseClient;

/**
 * NuSoap\Mime\Client client supporting MIME attachments defined at
 * http://www.w3.org/TR/SOAP-attachments.  It depends on the PEAR Mail_MIME library.
 *
 * @author    Scott Nichol <snichol@users.sourceforge.net>
 * @author    Thanks to Guillaume and Henning Reich for posting great attachment code to the mail list
 * @version   $Id$
 * @access    public
 */
class Client extends BaseClient
{
    /**
     * @var array Each array element in the return is an associative array with keys
     * data, filename, contenttype, cid
     * @access private
     */
    var $requestAttachments = [];
    /**
     * @var array Each array element in the return is an associative array with keys
     * data, filename, contenttype, cid
     * @access private
     */
    var $responseAttachments;
    /**
     * @var string
     * @access private
     */
    var $mimeContentType;

    /**
     * adds a MIME attachment to the current request.
     *
     * If the $data parameter contains an empty string, this method will read
     * the contents of the file named by the $filename parameter.
     *
     * If the $cid parameter is false, this method will generate the cid.
     *
     * @param string $data        The data of the attachment
     * @param string $filename    The filename of the attachment (default is empty string)
     * @param string $contenttype The MIME Content-Type of the attachment (default is application/octet-stream)
     * @param string $cid         The content-id (cid) of the attachment (default is false)
     *
     * @return string The content-id (cid) of the attachment
     * @access public
     */
    function addAttachment($data, $filename = '', $contenttype = 'application/octet-stream', $cid = false)
    {
        if (!$cid) {
            $cid = md5(uniqid(time()));
        }

        $info['data'] = $data;
        $info['filename'] = $filename;
        $info['contenttype'] = $contenttype;
        $info['cid'] = $cid;

        $this->requestAttachments[] = $info;

        return $cid;
    }

    /**
     * clears the MIME attachments for the current request.
     *
     * @access public
     */
    function clearAttachments()
    {
        $this->requestAttachments = [];
    }

    /**
     * gets the MIME attachments from the current response.
     *
     * Each array element in the return is an associative array with keys
     * data, filename, contenttype, cid.  These keys correspond to the parameters
     * for addAttachment.
     *
     * @return array The attachments.
     * @access public
     */
    function getAttachments()
    {
        return $this->responseAttachments;
    }

    /**
     * gets the HTTP body for the current request.
     *
     * @param string $soapmsg The SOAP payload
     *
     * @return string The HTTP body, which includes the SOAP payload
     * @access private
     */
    function getHTTPBody($soapmsg)
    {
        if (count($this->requestAttachments) > 0) {
            $params['content_type'] = 'multipart/related; type="text/xml"';
            $mimeMessage = new Mail_mimePart('', $params);
            unset($params);

            $params['content_type'] = 'text/xml';
            $params['encoding'] = '8bit';
            $params['charset'] = $this->soap_defencoding;
            $mimeMessage->addSubpart($soapmsg, $params);

            foreach ($this->requestAttachments as $att) {
                unset($params);

                $params['content_type'] = $att['contenttype'];
                $params['encoding'] = 'base64';
                $params['disposition'] = 'attachment';
                $params['dfilename'] = $att['filename'];
                $params['cid'] = $att['cid'];

                if ($att['data'] == '' && $att['filename'] <> '') {
                    if ($fd = fopen($att['filename'], 'rb')) {
                        $data = fread($fd, filesize($att['filename']));
                        fclose($fd);
                    } else {
                        $data = '';
                    }
                    $mimeMessage->addSubpart($data, $params);
                } else {
                    $mimeMessage->addSubpart($att['data'], $params);
                }
            }

            $output = $mimeMessage->encode();
            $mimeHeaders = $output['headers'];

            foreach ($mimeHeaders as $k => $v) {
                $this->debug("MIME header $k: $v");
                if (strtolower($k) == 'content-type') {
                    // PHP header() seems to strip leading whitespace starting
                    // the second line, so force everything to one line
                    $this->mimeContentType = str_replace("\r\n", " ", $v);
                }
            }

            return $output['body'];
        }

        return parent::getHTTPBody($soapmsg);
    }

    /**
     * gets the HTTP content type for the current request.
     *
     * Note: getHTTPBody must be called before this.
     *
     * @return string the HTTP content type for the current request.
     * @access private
     */
    function getHTTPContentType()
    {
        if (count($this->requestAttachments) > 0) {
            return $this->mimeContentType;
        }
        return parent::getHTTPContentType();
    }

    /**
     * gets the HTTP content type charset for the current request.
     * returns false for non-text content types.
     *
     * Note: getHTTPBody must be called before this.
     *
     * @return string the HTTP content type charset for the current request.
     * @access private
     */
    function getHTTPContentTypeCharset()
    {
        if (count($this->requestAttachments) > 0) {
            return false;
        }
        return parent::getHTTPContentTypeCharset();
    }

    /**
     * processes SOAP message returned from server
     *
     * @param array  $headers The HTTP headers
     * @param string $data    unprocessed response data from server
     *
     * @return    mixed    value of the message, decoded into a PHP type
     * @access   private
     */
    function parseResponse($headers, $data)
    {
        $this->debug('Entering parseResponse() for payload of length ' . strlen($data) . ' and type of ' . $headers['content-type']);
        $this->responseAttachments = [];
        if (strstr($headers['content-type'], 'multipart/related')) {
            $this->debug('Decode multipart/related');
            $input = '';
            foreach ($headers as $k => $v) {
                $input .= "$k: $v\r\n";
            }
            $params['input'] = $input . "\r\n" . $data;
            $params['include_bodies'] = true;
            $params['decode_bodies'] = true;
            $params['decode_headers'] = true;

            $structure = Mail_mimeDecode::decode($params);

            foreach ($structure->parts as $part) {
                if (!isset($part->disposition) && (strstr($part->headers['content-type'], 'text/xml'))) {
                    $this->debug('Have root part of type ' . $part->headers['content-type']);
                    $root = $part->body;
                    $return = parent::parseResponse($part->headers, $part->body);
                } else {
                    $this->debug('Have an attachment of type ' . $part->headers['content-type']);
                    $info['data'] = $part->body;
                    $info['filename'] = isset($part->d_parameters['filename']) ? $part->d_parameters['filename'] : '';
                    $info['contenttype'] = $part->headers['content-type'];
                    $info['cid'] = $part->headers['content-id'];
                    $this->responseAttachments[] = $info;
                }
            }

            if (isset($return)) {
                $this->responseData = $root;
                return $return;
            }

            $this->setError('No root part found in multipart/related content');
            return '';
        }
        $this->debug('Not multipart/related');
        return parent::parseResponse($headers, $data);
    }
}