<?php

namespace Rockett\Toolkit;

class ZAPhone
{
    const LANDLINE_SUB_EXPR = '1[0-8]|2[1-4-7-8]|3[1-69]|4[\d]|5[1346-8]';
    const MOBILE_SUB_EXPR = '6[0-6]|7[1-46-9]|6[1-3]|8[1-4]';

    /**
     * The various parts of the current phone number
     * @var mixed
     */
    protected $parts;

    /**
     * Create a new instance
     * @param string $phone
     * @param array  $parts
     */
    public function __construct($phone = null, array $parts = [])
    {
        if ($phone && $parts) {
            $this->parts = (object) array_combine([
                'number',
                'prefix',
                'three',
                'four',
            ], $parts);
        }
    }

    /**
     * A getter to fetch a part.
     * @param  string        $part
     * @return string|null
     */
    final public function __get($part)
    {
        if (property_exists($this->parts, $part)) {
            return $this->parts->$part;
        }
    }

    /**
     * Use the common national format when converting to a string
     * @return mixed
     */
    final public function __toString()
    {
        return $this->formatNational();
    }

    /**
     * Initiate a check and return this instance
     * @param  string  $phone
     * @return ZaPhone (new)
     */
    final public function check($phone)
    {
        // Sanitise the input before passing it to a regex filter for validation
        $phone = str_replace(['+', '-'], '', filter_var($phone, FILTER_SANITIZE_NUMBER_INT));
        if (!preg_match('/^
            (?:27|0) # country code
            (
                (' . self::LANDLINE_SUB_EXPR . '|' . self::MOBILE_SUB_EXPR . ") # either landline or mobile
                (\d{3})(\d{4}) # remaining digits
            )
        $/x", $phone, $parts)) {
            return false;
        }
        unset($parts[0]);

        return new static($phone, $parts);
    }

    /**
     * Check if the number is a mobile or landline.
     * @param  string  $type
     * @return bool
     */
    final public function is(string $type)
    {
        $exprType = [
            'landline' => self::LANDLINE_SUB_EXPR,
            'mobile' => self::MOBILE_SUB_EXPR,
        ][$type];

        return preg_match("/$exprType/", $this->prefix);
    }

    /**
     * Format the number for dialling in from another country
     * Ex: 011 27 821234567 for the United States
     * Format: ISO 3166-1 alpha-3
     * @param  string   $fromCountry
     * @throws mixed
     * @return string
     */
    final public function formatDialIn($fromCountry = 'USA')
    {
        if (!$fromCountry) {
            throw new Exceptions\InvalidArgumentException('To format a phone number for international dialling, you need to set a valid country code from which the call would be made. Ex: US or AU');
        }
        $exitCodes = $this->readFileLines(__DIR__ . '/../data/exit-codes.ini');
        try {
            $exitCode = $exitCodes[$fromCountry];
        } catch (\ErrorException $e) {
            throw new Exceptions\InvalidOptionException(sprintf('The country code specified (%s) is not defined.', $fromCountry));
        }
        $parts = $this->parts;

        return "$exitCode 27 {$parts->number}";
    }

    /**
     * Format the number in international format without spaces.
     * @return string
     */
    final public function formatE164()
    {
        return str_replace(' ', '', $this->formatIntl());
    }

    /**
     * Format the number in international format with spaces
     * @return string
     */
    final public function formatIntl()
    {
        return "+27 {$this->prefix} {$this->three} {$this->four}";
    }

    /**
     * Format the number in national format with spaces
     * and optional parenthises and hyphens
     * @return string
     */
    final public function formatNational($landlineParenthises = false, $hyphens = false)
    {
        $prefix = "0{$this->prefix}";
        $sep = [' ', '-'][$hyphens];
        $initialSep = $sep;
        if ($landlineParenthises && preg_match('/' . self::LANDLINE_SUB_EXPR . '/', $this->prefix)) {
            $prefix = "($prefix)";
            $initialSep = ' ';
        }

        return "$prefix$initialSep{$this->three}$sep{$this->four}";
    }

    /**
     * Format the number in international format with hyphens
     * @return string
     */
    final public function formatRFC3966()
    {
        return str_replace(' ', '-', $this->formatIntl());
    }

    /**
     * Get lines from an INI-formatted file
     * @param  string                 $filePath
     * @throws InvalidPathException
     * @return array
     */
    final protected function readFileLines($filePath)
    {
        if (!is_readable($filePath) || !is_file($filePath)) {
            throw new Exceptions\InvalidPathException(sprintf('Unable to read the INI file at %s.', $filePath));
        }

        $lineEndingsIni = 'auto_detect_line_endings';
        $lineEndings = ini_get($lineEndingsIni);
        ini_set($lineEndingsIni, '1');
        $lines = parse_ini_file($filePath);
        ini_set($lineEndingsIni, $lineEndings);

        return $lines;
    }
}
