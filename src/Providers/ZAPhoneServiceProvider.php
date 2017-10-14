<?php

namespace Rockett\Toolkit\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Validation\Rule;
use Rockett\Toolkit\Providers\Rules;
use Rockett\Toolkit\Providers\Validators;

class ZAPhoneServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the provider.
     */
    public function boot()
    {
        // Register the translation files
        $this->loadTranslationsFrom(__DIR__ . '/../../lang', 'zaphone');

        // Register the validator and rule
        $this->registerValidator();
        $this->registerRule();
    }

    /**
     * Determine whether we can register a dependent validator.
     * @return bool
     */
    public static function canUseDependentValidation()
    {
        $validator = new \ReflectionClass('\Illuminate\Validation\Factory');

        return $validator->hasMethod('extendDependent');
    }

    /**
     * Register the "zaphone" rule macro.
     */
    protected function registerRule()
    {
        if (class_exists('Illuminate\Validation\Rule') && class_uses(Rule::class, Macroable::class)) {
            Rule::macro('zaphone', function () {
                return new Rules\ZAPhoneRule();
            });
        }
    }

    /**
     * Register the "zaphone" validator.
     */
    protected function registerValidator()
    {
        $extend = static::canUseDependentValidation() ? 'extendDependent' : 'extend';
        $this->app['validator']->{$extend}(
            'zaphone',
            Validators\ZAPhoneValidator::class . '@validate',
            trans('zaphone::messages.validation')
        );
    }
}
