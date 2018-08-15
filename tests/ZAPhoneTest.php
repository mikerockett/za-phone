<?php

use Facades\Rockett\Toolkit\ZAPhone;
use Illuminate\Validation\Rule;
use Orchestra\Testbench\TestCase;

class ZAPhoneTest extends TestCase
{
    /**
     * Phone numbers that would not pass.
     * In all cases, it fails because the length of the resulting
     * number is not correct, or because it does not begin with
     * 27 or 0 followed by a valid area or network code.
     * @var array
     */
    protected $expectedCheckInvalids = [
        '01134567',
        '113456789',
        '+113456789',
        '+270113456789',
        '(01) 345 6789',
        '27011 3455 6789',
        '011-34-6789',
        '01-345 6789',
        '011 34-67',
        '011/345/789',
        '+1/345/6789',
    ];

    /**
     * Phone numbers that would pass.
     * @var array
     */
    protected $expectedCheckValids = [
        '0113456789',
        '27113456789',
        '+27113456789',
        '(011) 345 6789',
        '011 345 6789',
        '011-345-6789',
        '011-345 6789',
        '011 345-6789',
        '011/345/6789',
        '2711/345/6789',
    ];

    /**
     * Expected format results
     * @var array
     */
    protected $expectedFormatResults = [
        'national' => '011 345 6789',
        'E164' => '+27113456789',
        'intl' => '+27 11 345 6789',
        'RFC3966' => '+27-11-345-6789',
    ];

    /**
     * Expect $phone to not be false for invalid numbers.
     */
    public function testExpectedCheckInvalids()
    {
        foreach ($this->expectedCheckInvalids as $number) {
            $phone = ZAPhone::check($number);
            $this->assertEquals($phone, false);
        }
    }

    /**
     * Expect $phone to not be an instance of ZAPhone for valid numbers.
     */
    public function testExpectedCheckValids()
    {
        foreach ($this->expectedCheckValids as $number) {
            $phone = ZAPhone::check($number);
            $this->assertTrue($phone instanceof Rockett\Toolkit\ZAPhone);
        }
    }

    /**
     * Expect formats to be correct for all valid numbers.
     */
    public function testExpectedFormats()
    {
        foreach ($this->expectedCheckValids as $number) {
            $phone = ZAPhone::check($number);
            foreach ($this->expectedFormatResults as $format => $result) {
                $format = ucfirst($format);
                $method = "format$format";
                $this->assertEquals($phone->$method(), $result);
            }
        }
    }

    /**
     * Expect the rule class to return correct validation strings.
     */
    public function testExpectedRuleStrings()
    {
        $this->assertEquals(Rule::zaphone(), 'zaphone');
        $this->assertEquals(Rule::zaphone()->format('national'), 'zaphone:national');
        $this->assertEquals(Rule::zaphone()->format('intl'), 'zaphone:intl');
        $this->assertEquals(Rule::zaphone()->format('E164'), 'zaphone:E164');
        $this->assertEquals(Rule::zaphone()->format('RFC3966'), 'zaphone:RFC3966');
    }

    /**
     * Expect validation to pass or fail on the given set.
     */
    public function testExpectedValidationResults()
    {
        // Valid numbers
        foreach ($this->expectedCheckValids as $number) {
            $validator = Validator::make(
                ['phone_number' => $number],
                ['phone_number' => 'zaphone']
            );
            $this->assertTrue($validator->passes());
        }

        // Invalid numbers
        foreach ($this->expectedCheckInvalids as $number) {
            $validator = Validator::make(
                ['phone_number' => $number],
                ['phone_number' => 'zaphone']
            );
            $this->assertTrue($validator->fails());
        }

        // Mobile & Landline
        foreach ($this->expectedCheckValids as $number) {
            $validator = Validator::make(
                ['phone_number' => $number],
                ['phone_number' => 'zaphone:landline']
            );
            $this->assertTrue($validator->passes());
        }
    }

    /**
     * Expect getters on ZAPhone to return correctly.
     */
    public function testExpectGetters()
    {
        $phone = ZAPhone::check('0115009000');
        $this->assertNotNull($phone->number);
        // $this->assertEquals($phone->number, '0115009000');
        $this->assertNotNull($phone->prefix);
        $this->assertEquals($phone->prefix, '11');
        $this->assertNotNull($phone->three);
        $this->assertEquals($phone->three, '500');
        $this->assertNotNull($phone->four);
        $this->assertEquals($phone->four, '9000');
        $this->assertNull($phone->five);
    }

    /**
     * Expect dial-ins to be correct.
     * @return [type] [description]
     */
    public function testExpectedDialInFormats()
    {
        $exitCodes = $this->readFileLines(__DIR__ . '/../data/exit-codes.ini');
        $phone = ZAPhone::check('0115009000');
        foreach ($exitCodes as $countryCode => $exitCode) {
            $this->assertEquals(
                $phone->formatDialIn($countryCode),
                "$exitCode 27 115009000"
            );
        }
    }

    /**
     * Get lines from an INI-formatted file
     * @param  string  $filePath
     * @return array
     */
    protected function readFileLines(string $filePath)
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

    /**
     * Get the providers for this test case
     * @param $app unused
     */
    protected function getPackageProviders($app)
    {
        return [
            \Rockett\Toolkit\Providers\ZAPhoneServiceProvider::class,
        ];
    }
}
