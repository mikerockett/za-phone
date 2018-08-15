<?php

namespace Rockett\Toolkit\Providers\Validators;

use Facades\Rockett\Toolkit\ZAPhone;
use Rockett\Toolkit\Exceptions;

class ZAPhoneValidator
{
    /**
     * Validate a phone number. By default, it uses the
     * check() method as it's the most lenient. However,
     * additional parameters may be used to force the
     * input to match a specific format.
     * @param  string       $attribute
     * @param  string       $value
     * @param  array        $parameters
     * @param  $validator
     * @return bool
     */
    public function validate($attribute, $value, array $parameters, $validator)
    {
        // Returning false here means that the number provided is
        // not parseable in any way. Any false returns after this
        // point means that stricter validation is required.
        if (!$phone = ZAPhone::check($value)) {
            return false;
        }

        // Check for mobile, landline constraint.
        if (isset($parameters[0]) && $typeConstraint = $parameters[0]) {
            if (preg_match('/(mobile|landline)/i', $typeConstraint)) {
                $typeConstraint = $phone->is($typeConstraint);
            }
        }

        // Check for formatting requirements, or pass validation.
        $formatConstraint = isset($typeConstraint) ? 1 : 0;
        if (isset($parameters[$formatConstraint]) && $format = $parameters[$formatConstraint]) {
            switch ($format) {
                case 'E164':
                case 'intl':
                case 'national':
                case 'RFC3966':
                    $formatter = 'format' . ucfirst($format);
                    $formatConstraint = $value === $phone->$formatter();
                    break;
                default:
                    // The format doesn't exist, so throw an exception
                    throw new Exceptions\InvalidArgumentException(sprintf("zaphone rule: the format '%s' does not exist. Note that formats are case-sensitive.", $format));
                    break;
            }
        } else {
            $formatConstraint = true;
        }

        return isset($typeConstraint) ? ($typeConstraint && $formatConstraint) : $formatConstraint;
    }
}
