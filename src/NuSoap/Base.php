<?php
/**
 * @package  NewNuSoap
 * @license  https://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt LGPL
 */

namespace NuSoap;

/**
 *
 * NuSoap\Base
 *
 * All NuSoap classes extend from this class.
 *
 * @author   Dietrich Ayala <dietrich@ganx4.com>
 * @author   Scott Nichol <snichol@users.sourceforge.net>
 * @author   Samuel Raynor <samuel@neutrondevelopment.com>
 * @version  git: $Id$
 */
class Base
{
    /**
     * Identification for HTTP headers.
     *
     * @var string
     * @access private
     */
    protected string $title = 'New NuSOAP';

    /**
     * Version for HTTP headers.
     *
     * @var string
     * @access private
     */
    protected string $version = '0.10';

    /**
     * CVS revision for HTTP headers.
     *
     * @var string
     * @access private
     */
    protected string $revision = '$Revision: 1.123 $';

    /**
     * Current error string (manipulated by getError/setError)
     *
     * @var string
     * @access private
     */
    protected string $error_str = '';

    /**
     * Current debug string (manipulated by
     * debug/appendDebug/clearDebug/getDebug/getDebugAsXMLComment)
     *
     * @see    appendDebug
     * @var    string
     * @access private
     */
    public string $debug_str = '';

    /**
     * toggles automatic encoding of special characters as entities
     * (should always be true, I think)
     *
     * @var boolean
     * @access private
     */
    protected bool $charencoding = true;

    /**
     * the debug level for this instance
     *
     * @var    integer
     * @access private
     */
    protected int $debugLevel;

    /**
     * set schema version
     *
     * @var      string
     * @access   public
     */
    public string $XMLSchemaVersion = 'http://www.w3.org/2001/XMLSchema';

    /**
     * charset encoding for outgoing messages
     *
     * @var      string
     * @access   public
     */
    //public string $soap_defencoding = 'ISO-8859-1';
    public string $soap_defencoding = 'UTF-8';

    /**
     * namespaces in an array of prefix => uri
     *
     * this is "seeded" by a set of constants, but it may be altered by code
     *
     * @var      array
     * @access   public
     */
    public array $namespaces = [
        'SOAP-ENV' => 'http://schemas.xmlsoap.org/soap/envelope/',
        'xsd' => 'http://www.w3.org/2001/XMLSchema',
        'xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
        'SOAP-ENC' => 'http://schemas.xmlsoap.org/soap/encoding/',
    ];

    /**
     * namespaces used in the current context, e.g. during serialization
     *
     * @var    array
     * @access private
     */
    protected array $usedNamespaces = [];

    /**
     * XML Schema types in an array of uri => (array of xml type => php type)
     * is this legacy yet?
     * no, this is used by the XMLSchema class to verify type => namespace mappings.
     *
     * @var    array
     * @access public
     */
    public array $typemap = [
        'http://www.w3.org/2001/XMLSchema' => [
            'string' => 'string',
            'boolean' => 'boolean',
            'float' => 'double',
            'double' => 'double',
            'decimal' => 'double',
            'duration' => '',
            'dateTime' => 'string',
            'time' => 'string',
            'date' => 'string',
            'gYearMonth' => '',
            'gYear' => '',
            'gMonthDay' => '',
            'gDay' => '',
            'gMonth' => '',
            'hexBinary' => 'string',
            'base64Binary' => 'string',

            // abstract "any" types
            'anyType' => 'string',
            'anySimpleType' => 'string',

            // derived datatypes
            'normalizedString' => 'string',
            'token' => 'string',
            'language' => '',
            'NMTOKEN' => '',
            'NMTOKENS' => '',
            'Name' => '',
            'NCName' => '',
            'ID' => '',
            'IDREF' => '',
            'IDREFS' => '',
            'ENTITY' => '',
            'ENTITIES' => '',
            'integer' => 'integer',
            'nonPositiveInteger' => 'integer',
            'negativeInteger' => 'integer',
            'long' => 'integer',
            'int' => 'integer',
            'short' => 'integer',
            'byte' => 'integer',
            'nonNegativeInteger' => 'integer',
            'unsignedLong' => '',
            'unsignedInt' => '',
            'unsignedShort' => '',
            'unsignedByte' => '',
            'positiveInteger' => '',
        ],
        'http://www.w3.org/2000/10/XMLSchema' => [
            'i4' => '',
            'int' => 'integer',
            'boolean' => 'boolean',
            'string' => 'string',
            'double' => 'double',
            'float' => 'double',
            'dateTime' => 'string',
            'timeInstant' => 'string',
            'base64Binary' => 'string',
            'base64' => 'string',
            'ur-type' => 'array',
        ],
        'http://www.w3.org/1999/XMLSchema' => [
            'i4' => '',
            'int' => 'integer',
            'boolean' => 'boolean',
            'string' => 'string',
            'double' => 'double',
            'float' => 'double',
            'dateTime' => 'string',
            'timeInstant' => 'string',
            'base64Binary' => 'string',
            'base64' => 'string',
            'ur-type' => 'array',
        ],
        'http://soapinterop.org/xsd' => [
            'SOAPStruct' => 'struct',
        ],
        'http://schemas.xmlsoap.org/soap/encoding/' => [
            'base64' => 'string',
            'array' => 'array',
            'Array' => 'array',
        ],
        'http://xml.apache.org/xml-soap' => [
            'Map',
        ],
    ];

    /**
     * XML entities to convert
     *
     * @var        array
     * @access     public
     * @deprecated
     * @see        expandEntities
     */
    public array $xmlEntities = [
        'quot' => '"',
        'amp' => '&',
        'lt' => '<',
        'gt' => '>',
        'apos' => "'",
    ];

    /**
     * HTTP Content-type to be used for SOAP calls and responses
     *
     * @var string
     */
    protected string $contentType = "text/xml";

    /**
     * constructor
     *
     * @access public
     */
    public function __construct()
    {
        $this->debugLevel = $GLOBALS['_transient']['static']['NuSoap\Base']['globalDebugLevel'];
    }

    /**
     * gets the global debug level, which applies to future instances
     *
     * @return integer Debug level 0-9, where 0 turns off
     * @access public
     */
    public function getGlobalDebugLevel(): int
    {
        return $GLOBALS['_transient']['static']['NuSoap\Base']['globalDebugLevel'];
    }

    /**
     * sets the global debug level, which applies to future instances
     *
     * @param int $level Debug level 0-9, where 0 turns off
     *
     * @access    public
     */
    public function setGlobalDebugLevel($level)
    {
        $GLOBALS['_transient']['static']['NuSoap\Base']['globalDebugLevel'] = $level;
    }

    /**
     * gets the debug level for this instance
     *
     * @return    int    Debug level 0-9, where 0 turns off
     * @access    public
     */
    public function getDebugLevel()
    {
        return $this->debugLevel;
    }

    /**
     * sets the debug level for this instance
     *
     * @param int $level Debug level 0-9, where 0 turns off
     *
     * @access    public
     */
    public function setDebugLevel($level)
    {
        $this->debugLevel = $level;
    }

    /**
     * adds debug data to the instance debug string with formatting
     *
     * @param string $string debug data
     *
     * @access   private
     */
    protected function debug($string)
    {
        if ($this->debugLevel > 0) {
            $this->appendDebug($this->getMicrotime() . ' ' . get_class($this) . ": $string\n");
        }
    }

    /**
     * adds debug data to the instance debug string without formatting
     *
     * @param string $string debug data
     *
     * @access   public
     */
    public function appendDebug($string)
    {
        if ($this->debugLevel > 0) {
            // it would be nice to use a memory stream here to use
            // memory more efficiently
            $this->debug_str .= $string;
        }
    }

    /**
     * clears the current debug data for this instance
     *
     * @access   public
     */
    public function clearDebug()
    {
        // it would be nice to use a memory stream here to use
        // memory more efficiently
        $this->debug_str = '';
    }

    /**
     * gets the current debug data for this instance
     *
     * @return   string data
     * @access   public
     */
    public function &getDebug()
    {
        // it would be nice to use a memory stream here to use
        // memory more efficiently
        return $this->debug_str;
    }

    /**
     * gets the current debug data for this instance as an XML comment
     * this may change the contents of the debug data
     *
     * @return   string debug data as an XML comment
     * @access   public
     */
    public function &getDebugAsXMLComment()
    {
        // it would be nice to use a memory stream here to use
        // memory more efficiently
        while (strpos($this->debug_str, '--')) {
            $this->debug_str = str_replace('--', '- -', $this->debug_str);
        }
        $ret = "<!--\n" . $this->debug_str . "\n-->";
        return $ret;
    }

    /**
     * expands entities, e.g. changes '<' to '&lt;'.
     *
     * @param string $val The string in which to expand entities.
     *
     * @access    private
     */
    protected function expandEntities(string $val)
    {
        if ($this->charencoding) {
            $val = str_replace('&', '&amp;', $val);
            $val = str_replace("'", '&apos;', $val);
            $val = str_replace('"', '&quot;', $val);
            $val = str_replace('<', '&lt;', $val);
            $val = str_replace('>', '&gt;', $val);
        }
        return $val;
    }

    /**
     * returns error string if present
     *
     * @return   mixed error string or false
     * @access   public
     */
    public function getError()
    {
        if ($this->error_str != '') {
            return $this->error_str;
        }
        return false;
    }

    /**
     * sets error string
     *
     * @param string $str
     *
     * @access private
     */
    protected function setError(string $str)
    {
        $this->error_str = $str;
    }

    /**
     * detect if array is a simple array or a struct (associative array)
     *
     * @param mixed $val The PHP array
     *
     * @return string    (arraySimple|arrayStruct)
     * @access private
     */
    protected function isArraySimpleOrStruct($val): string
    {
        $keyList = array_keys($val);
        foreach ($keyList as $keyListValue) {
            if (!is_int($keyListValue)) {
                return 'arrayStruct';
            }
        }
        return 'arraySimple';
    }

    /**
     * serializes PHP values in accordance w/ section 5. Type information is
     * not serialized if $use == 'literal'.
     *
     * @param mixed   $val        The value to serialize
     * @param string  $name       The name (local part) of the XML element
     * @param string  $type       The XML schema type (local part) for the element
     * @param string  $name_ns    The namespace for the name of the XML element
     * @param string  $type_ns    The namespace for the type of the element
     * @param mixed   $attributes The attributes to serialize as name=>value pairs
     * @param string  $use        The WSDL "use" (encoded|literal)
     * @param boolean $soapval    Whether this is called from soapval.
     *
     * @return string    The serialized element, possibly with child elements
     * @access public
     */
    public function serialize_val(
        $val,
        string $name = '',
        string $type = '',
        string $name_ns = '',
        string $type_ns = '',
        $attributes = [],
        string $use = 'encoded',
        bool $soapval = false
    ): string {
        $this->debug("in serialize_val: name=$name, type=$type, name_ns=$name_ns, type_ns=$type_ns, use=$use, soapval=$soapval");
        $this->appendDebug('value=' . $this->varDump($val));
        $this->appendDebug('attributes=' . $this->varDump($attributes));

        if (is_object($val) && get_class($val) == 'soapval' && (!$soapval)) {
            $this->debug("serialize_val: serialize soapval");
            $xml = $val->serialize($use);
            $this->appendDebug($val->getDebug());
            $val->clearDebug();
            $this->debug("serialize_val of soapval returning $xml");
            return $xml;
        }
        // force valid name if necessary
        if (is_numeric($name)) {
            $name = '__numeric_' . $name;
        } elseif (!$name) {
            $name = 'noname';
        }
        // if name has ns, add ns prefix to name
        $xmlns = '';
        if ($name_ns) {
            $prefix = 'nu' . rand(1000, 9999);
            $name = $prefix . ':' . $name;
            $xmlns .= " xmlns:$prefix=\"$name_ns\"";
        }
        // if type is prefixed, create type prefix
        if ($type_ns != '' && $type_ns == $this->namespaces['xsd']) {
            // need to fix this. shouldn't default to xsd if no ns specified
            // w/o checking against typemap
            $type_prefix = 'xsd';
        } elseif ($type_ns) {
            $type_prefix = 'ns' . rand(1000, 9999);
            $xmlns .= " xmlns:$type_prefix=\"$type_ns\"";
        }
        // serialize attributes if present
        $atts = '';
        if ($attributes) {
            foreach ($attributes as $k => $v) {
                $atts .= " $k=\"" . $this->expandEntities($v) . '"';
            }
        }
        // serialize null value
        if (is_null($val)) {
            $this->debug("serialize_val: serialize null");
            if ($use == 'literal') {
                // TODO: depends on minOccurs
                $xml = "<$name$xmlns$atts/>";
                $this->debug("serialize_val returning $xml");
                return $xml;
            } else {
                if (isset($type) && isset($type_prefix)) {
                    $type_str = " xsi:type=\"$type_prefix:$type\"";
                } else {
                    $type_str = '';
                }
                $xml = "<$name$xmlns$type_str$atts xsi:nil=\"true\"/>";
                $this->debug("serialize_val returning $xml");
                return $xml;
            }
        }
        // serialize if an xsd built-in primitive type
        if ($type != '' && isset($this->typemap[$this->XMLSchemaVersion][$type])) {
            $this->debug("serialize_val: serialize xsd built-in primitive type");
            if (is_bool($val)) {
                if ($type == 'boolean') {
                    $val = $val ? 'true' : 'false';
                } elseif (!$val) {
                    $val = 0;
                }
            } elseif (is_string($val)) {
                $val = $this->expandEntities($val);
            }
            if ($use == 'literal') {
                $xml = "<$name$xmlns$atts>$val</$name>";
                $this->debug("serialize_val returning $xml");
                return $xml;
            } else {
                $xml = "<$name$xmlns xsi:type=\"xsd:$type\"$atts>$val</$name>";
                $this->debug("serialize_val returning $xml");
                return $xml;
            }
        }
        // detect type and serialize
        $xml = '';
        switch (true) {
            case (is_bool($val) || $type == 'boolean'):
                $this->debug("serialize_val: serialize boolean");
                if ($type == 'boolean') {
                    $val = $val ? 'true' : 'false';
                } elseif (!$val) {
                    $val = 0;
                }
                if ($use == 'literal') {
                    $xml .= "<$name$xmlns$atts>$val</$name>";
                } else {
                    $xml .= "<$name$xmlns xsi:type=\"xsd:boolean\"$atts>$val</$name>";
                }
                break;
            case (is_int($val) || is_long($val) || $type == 'int'):
                $this->debug("serialize_val: serialize int");
                if ($use == 'literal') {
                    $xml .= "<$name$xmlns$atts>$val</$name>";
                } else {
                    $xml .= "<$name$xmlns xsi:type=\"xsd:int\"$atts>$val</$name>";
                }
                break;
            case (is_float($val) || is_double($val) || $type == 'float'):
                $this->debug("serialize_val: serialize float");
                if ($use == 'literal') {
                    $xml .= "<$name$xmlns$atts>$val</$name>";
                } else {
                    $xml .= "<$name$xmlns xsi:type=\"xsd:float\"$atts>$val</$name>";
                }
                break;
            case (is_string($val) || $type == 'string'):
                $this->debug("serialize_val: serialize string");
                $val = $this->expandEntities($val);
                if ($use == 'literal') {
                    $xml .= "<$name$xmlns$atts>$val</$name>";
                } else {
                    $xml .= "<$name$xmlns xsi:type=\"xsd:string\"$atts>$val</$name>";
                }
                break;
            case is_object($val):
                $this->debug("serialize_val: serialize object");
                if (get_class($val) == 'soapval') {
                    $this->debug("serialize_val: serialize soapval object");
                    $pXml = $val->serialize($use);
                    $this->appendDebug($val->getDebug());
                    $val->clearDebug();
                } else {
                    if (!$name) {
                        $name = get_class($val);
                        $this->debug("In serialize_val, used class name $name as element name");
                    } else {
                        $this->debug("In serialize_val, do not override name $name for element name for class " . get_class($val));
                    }
                    foreach (get_object_vars($val) as $k => $v) {
                        $pXml = isset($pXml)
                            ? $pXml . $this->serialize_val($v,
                                $k,
                                false,
                                false,
                                false,
                                false,
                                $use)
                            : $this->serialize_val($v,
                                $k,
                                false,
                                false,
                                false,
                                false,
                                $use);
                    }
                }
                if (isset($type) && isset($type_prefix)) {
                    $type_str = " xsi:type=\"$type_prefix:$type\"";
                } else {
                    $type_str = '';
                }
                if ($use == 'literal') {
                    $xml .= "<$name$xmlns$atts>$pXml</$name>";
                } else {
                    $xml .= "<$name$xmlns$type_str$atts>$pXml</$name>";
                }
                break;
                break;
            case (is_array($val) || $type):
                // detect if struct or array
                $valueType = $this->isArraySimpleOrStruct($val);
                if ($valueType == 'arraySimple' || preg_match('/^ArrayOf/', $type)) {
                    $this->debug("serialize_val: serialize array");
                    $i = 0;
                    if (is_array($val) && count($val) > 0) {
                        foreach ($val as $v) {
                            if (is_object($v) && get_class($v) == 'soapval') {
                                $tt_ns = $v->type_ns;
                                $tt = $v->type;
                            } elseif (is_array($v)) {
                                $tt = $this->isArraySimpleOrStruct($v);
                            } else {
                                $tt = gettype($v);
                            }
                            $array_types[$tt] = 1;
                            // TODO: for literal, the name should be $name
                            $xml .= $this->serialize_val($v,
                                'item',
                                false,
                                false,
                                false,
                                false,
                                $use);
                            ++$i;
                        }
                        if (count($array_types) > 1) {
                            $array_typename = 'xsd:anyType';
                        } elseif (isset($tt) && isset($this->typemap[$this->XMLSchemaVersion][$tt])) {
                            if ($tt == 'integer') {
                                $tt = 'int';
                            }
                            $array_typename = 'xsd:' . $tt;
                        } elseif (isset($tt) && $tt == 'arraySimple') {
                            $array_typename = 'SOAP-ENC:Array';
                        } elseif (isset($tt) && $tt == 'arrayStruct') {
                            $array_typename = 'unnamed_struct_use_soapval';
                        } else {
                            // if type is prefixed, create type prefix
                            if ($tt_ns != '' && $tt_ns == $this->namespaces['xsd']) {
                                $array_typename = 'xsd:' . $tt;
                            } elseif ($tt_ns) {
                                $tt_prefix = 'ns' . rand(1000, 9999);
                                $array_typename = "$tt_prefix:$tt";
                                $xmlns .= " xmlns:$tt_prefix=\"$tt_ns\"";
                            } else {
                                $array_typename = $tt;
                            }
                        }
                        $array_type = $i;
                        if ($use == 'literal') {
                            $type_str = '';
                        } elseif (isset($type) && isset($type_prefix)) {
                            $type_str = " xsi:type=\"$type_prefix:$type\"";
                        } else {
                            $type_str = " xsi:type=\"SOAP-ENC:Array\" SOAP-ENC:arrayType=\"" . $array_typename . "[$array_type]\"";
                        }
                        // empty array
                    } else {
                        if ($use == 'literal') {
                            $type_str = '';
                        } elseif (isset($type) && isset($type_prefix)) {
                            $type_str = " xsi:type=\"$type_prefix:$type\"";
                        } else {
                            $type_str = " xsi:type=\"SOAP-ENC:Array\" SOAP-ENC:arrayType=\"xsd:anyType[0]\"";
                        }
                    }
                    // TODO: for array in literal, there is no wrapper here
                    $xml = "<$name$xmlns$type_str$atts>" . $xml . "</$name>";
                } else {
                    // got a struct
                    $this->debug("serialize_val: serialize struct");
                    if (isset($type) && isset($type_prefix)) {
                        $type_str = " xsi:type=\"$type_prefix:$type\"";
                    } else {
                        $type_str = '';
                    }
                    if ($use == 'literal') {
                        $xml .= "<$name$xmlns$atts>";
                    } else {
                        $xml .= "<$name$xmlns$type_str$atts>";
                    }
                    foreach ($val as $k => $v) {
                        // Apache Map
                        if ($type == 'Map' && $type_ns == 'http://xml.apache.org/xml-soap') {
                            $xml .= '<item>';
                            $xml .= $this->serialize_val($k,
                                'key',
                                false,
                                false,
                                false,
                                false,
                                $use);
                            $xml .= $this->serialize_val($v,
                                'value',
                                false,
                                false,
                                false,
                                false,
                                $use);
                            $xml .= '</item>';
                        } else {
                            $xml .= $this->serialize_val($v, $k, false, false, false, false, $use);
                        }
                    }
                    $xml .= "</$name>";
                }
                break;
            default:
                $this->debug("serialize_val: serialize unknown");
                $xml .= 'not detected, got ' . gettype($val) . ' for ' . $val;
                break;
        }
        $this->debug("serialize_val returning $xml");
        return $xml;
    }

    /**
     * serializes a message
     *
     * @param string $body          the XML of the SOAP body
     * @param mixed  $headers       optional string of XML with SOAP header content, or array of
     *                              soapval objects for SOAP headers, or associative array
     * @param array  $namespaces    optional the namespaces used in generating the body and headers
     * @param string $style         optional (rpc|document)
     * @param string $use           optional (encoded|literal)
     * @param string $encodingStyle optional (usually 'http://schemas.xmlsoap.org/soap/encoding/'
     *                              for encoded)
     *
     * @return string the message
     * @access public
     */
    public function serializeEnvelope(
        string $body,
        $headers = false,
        array $namespaces = [],
        string $style = 'rpc',
        string $use = 'encoded',
        string $encodingStyle = 'http://schemas.xmlsoap.org/soap/encoding/'
    ): string {
        // TODO: add an option to automatically run utf8_encode on $body and $headers
        // if $this->soap_defencoding is UTF-8.  Not doing this automatically allows
        // one to send arbitrary UTF-8 characters, not just characters that map to ISO-8859-1

        $this->debug("In serializeEnvelope length="
            . strlen($body)
            . " body (max 1000 characters)="
            . substr($body,
                0,
                1000)
            . " style=$style use=$use encodingStyle=$encodingStyle");
        $this->debug("headers:");
        $this->appendDebug($this->varDump($headers));
        $this->debug("namespaces:");
        $this->appendDebug($this->varDump($namespaces));

        // serialize namespaces
        $ns_string = '';
        foreach (array_merge($this->namespaces, $namespaces) as $k => $v) {
            $ns_string .= " xmlns:$k=\"$v\"";
        }
        if ($encodingStyle) {
            $ns_string = " SOAP-ENV:encodingStyle=\"$encodingStyle\"$ns_string";
        }

        // serialize headers
        if ($headers) {
            if (is_array($headers)) {
                $xml = '';
                foreach ($headers as $k => $v) {
                    if (is_object($v) && get_class($v) == 'soapval') {
                        $xml .= $this->serialize_val($v, false, false, false, false, false, $use);
                    } else {
                        $xml .= $this->serialize_val($v, $k, false, false, false, false, $use);
                    }
                }
                $headers = $xml;
                $this->debug("In serializeEnvelope, serialized array of headers to $headers");
            }
            $headers = "<SOAP-ENV:Header>" . $headers . "</SOAP-ENV:Header>";
        }
        // serialize envelope
        return
            '<?xml version="1.0" encoding="' . $this->soap_defencoding . '"?' . ">" .
            '<SOAP-ENV:Envelope' . $ns_string . ">" .
            $headers .
            "<SOAP-ENV:Body>" .
            $body .
            "</SOAP-ENV:Body>" .
            "</SOAP-ENV:Envelope>";
    }

    /**
     * formats a string to be inserted into an HTML stream
     *
     * @param string $str The string to format
     *
     * @return string The formatted string
     * @access public
     * @deprecated
     */
    public function formatDump(string $str): string
    {
        $str = htmlspecialchars($str);
        return nl2br($str);
    }

    /**
     * contracts (changes namespace to prefix) a qualified name
     *
     * @param string $qname qname
     *
     * @return    string contracted qname
     * @access   private
     */
    protected function contractQname(string $qname): string
    {
        // get element namespace
        //$this->xdebug("Contract $qname");
        if (strrpos($qname, ':')) {
            // get unqualified name
            $name = substr($qname, strrpos($qname, ':') + 1);
            // get ns
            $ns = substr($qname, 0, strrpos($qname, ':'));
            $p = $this->getPrefixFromNamespace($ns);
            if ($p) {
                return $p . ':' . $name;
            }
            return $qname;
        } else {
            return $qname;
        }
    }

    /**
     * expands (changes prefix to namespace) a qualified name
     *
     * @param string $qname qname
     *
     * @return string expanded qname
     * @access private
     */
    protected function expandQname(string $qname): string
    {
        // get element prefix
        if (strpos($qname, ':') && !preg_match('/^http:\/\//', $qname)) {
            // get unqualified name
            $name = substr(strstr($qname, ':'), 1);
            // get ns prefix
            $prefix = substr($qname, 0, strpos($qname, ':'));
            if (isset($this->namespaces[$prefix])) {
                return $this->namespaces[$prefix] . ':' . $name;
            } else {
                return $qname;
            }
        } else {
            return $qname;
        }
    }

    /**
     * returns the local part of a prefixed string
     * returns the original string, if not prefixed
     *
     * @param string $str The prefixed string
     *
     * @return string The local part
     * @access public
     */
    public function getLocalPart(string $str): string
    {
        if ($sstr = strrchr($str, ':')) {
            // get unqualified name
            return substr($sstr, 1);
        } else {
            return $str;
        }
    }

    /**
     * returns the prefix part of a prefixed string
     * returns false, if not prefixed
     *
     * @param string $str The prefixed string
     *
     * @return string|bool The prefix or false if there is no prefix
     * @access public
     */
    public function getPrefix(string $str)
    {
        if ($pos = strrpos($str, ':')) {
            // get prefix
            return substr($str, 0, $pos);
        }
        return false;
    }

    /**
     * pass it a prefix, it returns a namespace
     *
     * @param string $prefix The prefix
     *
     * @return string|bool The namespace, false if no namespace has the specified prefix
     * @access public
     */
    public function getNamespaceFromPrefix(string $prefix)
    {
        if (isset($this->namespaces[$prefix])) {
            return $this->namespaces[$prefix];
        }
        //$this->setError("No namespace registered for prefix '$prefix'");
        return false;
    }

    /**
     * returns the prefix for a given namespace (or prefix)
     * or false if no prefixes registered for the given namespace
     *
     * @param string $ns The namespace
     *
     * @return string|bool The prefix, false if the namespace has no prefixes
     * @access public
     */
    public function getPrefixFromNamespace(string $ns)
    {
        foreach ($this->namespaces as $p => $n) {
            if ($ns == $n || $ns == $p) {
                $this->usedNamespaces[$p] = $n;
                return $p;
            }
        }
        return false;
    }

    /**
     * returns the time in ODBC canonical form with microseconds
     *
     * @return string The time in ODBC canonical form with microseconds
     * @access public
     */
    public function getMicrotime(): string
    {
        if (function_exists('gettimeofday')) {
            $tod = gettimeofday();
            $sec = $tod['sec'];
            $usec = $tod['usec'];
        } else {
            $sec = time();
            $usec = 0;
        }
        return strftime('%Y-%m-%d %H:%M:%S', $sec) . '.' . sprintf('%06d', $usec);
    }

    /**
     * Returns a string with the output of var_dump
     *
     * @param mixed $data The variable to var_dump
     *
     * @return string The output of var_dump
     * @access public
     */
    public function varDump($data): string
    {
        ob_start();
        var_dump($data);
        $ret_val = ob_get_contents();
        ob_end_clean();
        return $ret_val;
    }

    /**
     * represents the object as a string
     *
     * @return string
     * @access public
     */
    public function __toString(): string
    {
        return $this->varDump($this);
    }
}