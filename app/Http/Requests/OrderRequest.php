<?php

namespace App\Http\Requests;

use App\Http\Json\JsonResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class OrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
                'total_price' => 'required|numeric',
                'discount' => 'required|numeric'
            ]
            +
            ($this->isMethod('POST') ? $this->store() : $this->update());
    }

    protected function failedValidation(Validator $validator)
    {
        if ($this->expectsJson()) {
//            $errors = (new ValidationException($validator))->errors();
            throw new HttpResponseException(
                JsonResponse::validationError($validator->errors())
            );
        }
    }
    protected function store()
    {
        return [];
    }

    protected function update()
    {
        return [];
    }
}
