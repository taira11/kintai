<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExhibitionRequest extends FormRequest
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
            'name'          => ['required'],
            'description'   => ['required', 'max:255'],
            'image'         => ['required', 'mimetypes:image/jpeg,image/png,image/x-png'],
            'category_ids'  => ['required'],
            'status'        => ['required'],
            'price'         => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages()
    {
        return [
            'name.required'         => '商品名を入力してください',
            'description.required'  => '商品の説明を入力してください',
            'description.max'       => '商品の説明は255文字以内で入力してください',
            'image.required'        => '商品画像をアップロードしてください',
            'image.image'           => '商品画像は画像ファイルを選択してください',
            'image.mimes'           => '商品画像は.jpegもしくは.png形式でアップロードしてください',
            'category_ids.required' => '商品のカテゴリーを選択してください',
            'status.required'       => '商品の状態を選択してください',
            'price.required'        => '価格を入力してください',
            'price.numeric'         => '価格は数値で入力してください',
            'price.min'             => '価格は0円以上で入力してください',
        ];
    }
}
