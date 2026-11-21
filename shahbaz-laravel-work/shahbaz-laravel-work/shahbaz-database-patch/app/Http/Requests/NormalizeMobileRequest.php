<?php

namespace App\Http\Requests;

use App\Support\PhoneNormalizer;
use Illuminate\Foundation\Http\FormRequest;

abstract class NormalizeMobileRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->filled('mobile')) $this->merge(['mobile' => PhoneNormalizer::normalize((string) $this->input('mobile'))]);
    }
}
