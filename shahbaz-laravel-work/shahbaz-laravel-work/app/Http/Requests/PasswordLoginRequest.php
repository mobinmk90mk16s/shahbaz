<?php

namespace App\Http\Requests;

class PasswordLoginRequest extends NormalizeMobileRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['mobile' => ['required','string','size:11'], 'password' => ['required','string','max:200']]; }
}
