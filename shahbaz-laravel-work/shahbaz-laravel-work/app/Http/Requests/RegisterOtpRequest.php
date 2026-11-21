<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class RegisterOtpRequest extends NormalizeMobileRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'name' => ['required','string','min:2','max:120'],
            'mobile' => ['required','string','size:11', Rule::unique('users','mobile')],
            'email' => ['nullable','email','max:254',Rule::unique('users','email')],
            'password' => ['required','string','min:8','confirmed','max:200'],
        ];
    }
}
