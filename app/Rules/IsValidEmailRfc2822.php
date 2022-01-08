<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\ImplicitRule;

class IsValidEmailRfc2822 implements ImplicitRule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return $this->validate_rfc_2822_email($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Email не прошел проверку RFC';
    }

    /**
     * Checks if an email is a valid RFC 2822 email address
     *
     * Examples:
     * <code>
     * <?php
     * validate_email('dev@felds.com.br');                // returns true
     * validate_email('Felds Liscia <dev@felds.com.br>'); // returns false
     * ?>
     * </code>
     *
     * @author  Felds Liscia <dev@felds.com.br>
     * @version 2011-03-10
     * @see     http://www.regular-expressions.info/email.html
     * @param   string $email The email to be validated
     * @return  bool
     */
    public function validate_rfc_2822_email($email)
    {
        $regexp = '/^(?:[a-z0-9!#$%\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}'.
        '~-]+)*|"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\['.
        '\x01-\x09\x0b\x0c\x0e-\x7f])*")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-'.
        '9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:25[0-5]|2[0-4][0-'.
        '9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?'.
        '|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-' .
        '\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])$/';

        return (bool) preg_match($regexp, $email);
    }
}
