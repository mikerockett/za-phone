<?php

namespace Rockett\Toolkit\Providers\Rules;

class ZAPhoneRule
{
    /**
     * Store the format, if set.
     * @var string|null
     */
    protected $format = null;

    /**
     * Collapse the format into a validation rule string.
     * @return string
     */
    public function __toString()
    {
        return 'zaphone' . ($this->format !== null ? ":{$this->format}" : '');
    }

    /**
     * Set the format required (optional)
     * @param $format
     */
    public function format($format = 'national')
    {
        $this->format = $format;

        return $this;
    }
}
