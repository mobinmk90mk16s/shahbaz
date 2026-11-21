<?php

namespace App\Http\Requests;

class VerifyOtpRequest extends NormalizeMobileRequest
{
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();
        if ($this->filled('code')) {
            $digits = strtr((string) $this->input('code'), [
                '۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9',
                '٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9',
            ]);
            $this->merge(['code' => $digits]);
        }
    }

    public function authorize(): bool { return true; }
    public function rules(): array { return ['mobile' => ['required','string','size:11'], 'code' => ['required','digits:6']]; }
}
