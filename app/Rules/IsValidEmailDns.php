<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\ImplicitRule;

class IsValidEmailDns implements ImplicitRule
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
        return $this->validate_dns_email($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Email не прошел проверку DNS';
    }

    /**
     * Checks if an email is a valid dns email address
     * @param string $email The email to be validated
     * @return bool
     */
    public function validate_dns_email($email)
    {
        $domain = substr(strrchr($email, "@"), 1);
        $res = getmxrr($domain, $mx_records, $mx_weight);

        $logic = (
            false == $res 
            || 
            0 == count($mx_records) 
            || 
            (
                1 == count($mx_records) && (
                    $mx_records[0] == null 
                    || 
                    $mx_records[0] == "0.0.0.0"
                )
            )
        );

        if ($logic)
        {
            return false;
        }
        else
        {
            return true;
        }
    }
}
