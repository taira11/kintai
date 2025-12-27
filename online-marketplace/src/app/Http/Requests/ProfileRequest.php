<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        return [
            'image' => 'sometimes|file|mimes:jpeg,png|max:2048',

            'nickname' => 'required|string|max:20',
            'postal_code' => [
                'required',
                'regex:/^\d{3}-\d{4}$/',
            ],
            'address' => 'required|string|max:100',
            'building' => 'nullable|string|max:100',
        ];
    }


    public function messages()
    {
        return [
            'image.mimes' => '画像はjpegまたはpng形式でアップロードしてください。',
            'nickname.required' => 'ユーザー名を入力してください。',
            'nickname.max' => 'ユーザー名は20文字以内で入力してください。',
            'postal_code.required' => '郵便番号を入力してください。',
            'postal_code.regex' => '郵便番号は「000-0000」の形式で入力してください。',
            'address.required' => '住所を入力してください。',
        ];
    }
}
