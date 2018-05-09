<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Model\Project;
use App\Model\User;

class UnqiueProjectNameRule implements Rule
{
    
    private $bearToken;
    private $method;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(string $method,string $bearToken)
    {
        $this->bearToken = $bearToken;
        $this->method = $method;
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
        if(strcasecmp($this->method, 'post') == 0 ) {
            $userId = User::where('accessToken', $this->bearToken)->first()->id;
            if (Project::where([
                ['userId', $userId],
                ['name', $value]
            ])->first()) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'This project name already exist.';
    }
}
