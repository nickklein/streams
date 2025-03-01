<?php

namespace NickKlein\Streams\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StreamerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'platform' => 'required',
            'name' => 'required',
            'channel_id' => 'required',
            'channel_url' => 'required',
        ];
    }
}
