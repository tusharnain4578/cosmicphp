<?php

namespace Core\Validator;

class Rules
{
    public static function required(array|bool|float|int|object|string|null $value = null): bool
    {
        if ($value === null)
            return false;
        if (is_object($value))
            return true;
        if (is_array($value))
            return $value !== [];
        return trim((string) $value) !== '';
    }
    public static function min(?string $value, int|float|string $lb): bool
    {
        return is_numeric($value) && ($value >= $lb);
    }
    public static function max(?string $value, int|float|string $ub): bool
    {
        return is_numeric($value) && ($value <= $ub);
    }
    public static function between(?string $value, int|float|string $lb, int|float|string $ub): bool
    {
        return is_numeric($value) && ($value >= $lb) && ($value <= $ub);
    }
    public static function equals(?string $value, string $compareTo): bool
    {
        return $value === $compareTo;
    }
    public static function min_length(?string $value, int $lb): bool
    {
        return strlen($value) >= $lb;
    }
    public static function max_length(?string $value, int $ub): bool
    {
        return strlen($value) <= $ub;
    }
    public static function exact_length(?string $value, int $length): bool
    {
        return strlen($value) === $length;
    }
    public static function in_list(?string $value, string $list): bool
    {
        $list = array_map('trim', explode(',', $list));
        return in_array($value, $list, true);
    }




    // Database rules
    public static function exists(?string $value, string $dbData): bool
    {
        if (count($db = array_map('trim', explode(',', $dbData))) < 2)
            throw new \InvalidArgumentException("Database table and column name are required for validation!");
        $data = db()->table($db[0])->select(1)->where($db[1], $value)->get()->row();
        return isset($data[1]);
    }
    public static function unique(?string $value, string $dbData): bool
    {
        return !self::exists($value, $dbData);
    }



    // formatting rules
    public static function alpha(?string $value = null): bool
    {
        return ctype_alpha($value ?? '');
    }

    /**
     * Numeric
     */
    public static function numeric(float|int|string|null $value): bool
    {
        return (bool) preg_match('/\A[\-+]?\d*\.?\d+\z/', $value ?? '');
    }


    /**
     * Alpha with spaces.
     */
    public static function alpha_space(?string $value = null): bool
    {
        return $value === null ? true : (bool) preg_match('/\A[A-Z ]+\z/i', $value);
    }

    /**
     * Alphanumeric with underscores and dashes
     */
    public static function alpha_dash(?string $value = null): bool
    {
        return $value === null ? false : (preg_match('/\A[a-z0-9_-]+\z/i', $value) === 1);
    }

    /**
     * Alphanumeric, spaces, and a limited set of punctuation characters.
     * Accepted punctuation characters are: ~ tilde, ! exclamation,
     * # number, $ dollar, % percent, & ampersand, * asterisk, - dash,
     * _ underscore, + plus, = equals, | vertical bar, : colon, . period
     * ~ ! # $ % & * - _ + = | : .
     */
    public static function alpha_numeric_punct($value): bool
    {
        return $value === null ? false : (preg_match('/\A[A-Z0-9 ~!#$%\&\*\-_+=|:.]+\z/i', $value) === 1);
    }

    /**
     * Alphanumeric
     */
    public static function alpha_numeric(?string $value = null): bool
    {
        return ctype_alnum($value ?? '');
    }

    /**
     * Alphanumeric with spaces
     */
    public static function alpha_numeric_space(?string $value = null): bool
    {
        return (bool) preg_match('/\A[A-Z0-9 ]+\z/i', $value ?? '');
    }

    /**
     * Any type of string
     */
    public static function string($value = null): bool
    {
        return is_string($value);
    }

    /**
     * Decimal number
     */
    public static function decimal(?string $value = null): bool
    {
        return (bool) preg_match('/\A[-+]?\d{0,}\.?\d+\z/', $value ?? '');
    }

    /**
     * String of hexidecimal characters
     */
    public static function hex(?string $value = null): bool
    {
        return ctype_xdigit($value ?? '');
    }


    /**
     * Compares value against a regular expression pattern.
     */
    public static function regex_match(?string $value, string $pattern): bool
    {
        if (strpos($pattern, '/') !== 0)
            $pattern = "/{$pattern}/";
        return (bool) preg_match($pattern, $value ?? '');
    }

    /**
     * Validates that the string is a valid timezone as per the
     * timezone_identifiers_list function.
     */
    public static function timezone(?string $value = null): bool
    {
        return in_array($value ?? '', timezone_identifiers_list(), true);
    }

    /**
     * Valid Base64
     */
    public static function valid_base64(?string $value = null): bool
    {
        return $value === null ? false : (base64_encode(base64_decode($value, true)) === $value);
    }

    /**
     * Valid JSON
     */
    public static function valid_json(?string $value = null): bool
    {
        json_decode($value ?? '');
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Checks for a correctly formatted email address
     */
    public static function email(?string $value = null): bool
    {
        if (function_exists('idn_to_ascii') && defined('INTL_IDNA_VARIANT_UTS46') && preg_match('#\A([^@]+)@(.+)\z#', $value ?? '', $matches))
            $value = $matches[1] . '@' . idn_to_ascii($matches[2], 0, INTL_IDNA_VARIANT_UTS46);
        return (bool) filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Validate a comma-separated list of email addresses.
     */
    public static function emails(?string $str = null): bool
    {
        foreach (explode(',', $str ?? '') as $email) {
            $email = trim($email);
            if ($email === '')
                return false;
            if (self::email($email) === false)
                return false;
        }
        return true;
    }


    /**
     * Checks a URL to ensure it's formed correctly.
     */
    public static function valid_url(?string $value = null, ?string $validSchemes = null): bool
    {
        if ($value === null || $value === '' || $value === '0')
            return false;
        $scheme = strtolower((string) parse_url($value, PHP_URL_SCHEME));
        $validSchemes = explode(',', strtolower($validSchemes ?? 'http,https'));
        return in_array($scheme, $validSchemes, true)
            && filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Checks for a valid date and matches a given date format
     */
    public static function valid_date(?string $str = null, ?string $format = null): bool
    {
        if ($str === null)
            return false;
        if ($format === null || $format === '')
            return strtotime($str) !== false;
        $date = \DateTime::createFromFormat($format, $str);
        $errors = method_exists(\DateTime::class, 'getLastErrors') ? \DateTime::getLastErrors() : false;
        if ($date === false)
            return false;
        if ($errors === false)
            return true;
        return $errors['warning_count'] === 0 && $errors['error_count'] === 0;
    }
}