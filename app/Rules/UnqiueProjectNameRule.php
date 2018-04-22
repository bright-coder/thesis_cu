<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Model\Project;
use App\Model\User;

class UnqiueProjectNameRule implements Rule
{
    private $userId = null;
    private $method = null;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(string $method, string $accessToken)
    {
        $this->method = $method;
        $this->userId = User::select('id')->where('accessToken', $accessToken)->first()->id;
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
        if($this->method == '')
        if (Project::where([
            ['userId','=', $this->userId],
            ['name', '=' ,$value]
        ])->first()) {
            return false;
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
        return ':attribute already exist.';
    }
}
