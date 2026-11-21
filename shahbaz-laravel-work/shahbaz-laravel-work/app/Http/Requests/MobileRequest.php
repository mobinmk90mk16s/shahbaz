<?php

namespace App\Http\Requests;

class MobileRequest extends NormalizeMobileRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['mobile' => ['required','string','size:11']]; }
}
