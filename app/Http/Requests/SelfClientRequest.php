<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class SelfClientRequest extends FormRequest
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
     */
    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'document_number' => 'required|string|max:20',
            'contributor_type' => 'required|in:1,2,9',
            'state_registration' => 'nullable|string|max:20',
            'phone1' => 'required|string|max:20',
            'phone2' => 'nullable|string|max:20',
            'contact1' => 'nullable|string|max:255',
            'contact2' => 'nullable|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->letters()->mixedCase()->numbers()->symbols()
            ],
        ];

        // Se for edição (PUT/PATCH), ajusta regras
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['password'] = [
                'nullable',
                'confirmed',
                Password::min(8)->letters()->mixedCase()->numbers()->symbols()
            ];
            $rules['email'] = [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore(auth()->id()),
            ];
            // Não permite alterar documento na edição
            unset($rules['document_number']);
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',
            'document_number.required' => 'O documento é obrigatório.',
            'document_number.max' => 'O documento não pode ter mais de 20 caracteres.',
            'contributor_type.required' => 'O tipo de contribuinte é obrigatório.',
            'contributor_type.in' => 'Tipo de contribuinte inválido.',
            'state_registration.max' => 'A inscrição estadual não pode ter mais de 20 caracteres.',
            'phone1.required' => 'O telefone principal é obrigatório.',
            'phone1.max' => 'O telefone não pode ter mais de 20 caracteres.',
            'phone2.max' => 'O telefone secundário não pode ter mais de 20 caracteres.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'O e-mail deve ser válido.',
            'email.max' => 'O e-mail não pode ter mais de 255 caracteres.',
            'email.unique' => 'Este e-mail já está em uso.',
            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A senha deve ter pelo menos 8 caracteres.',
            'password.confirmed' => 'A confirmação de senha não confere.',
            'password.letters' => 'A senha deve conter pelo menos uma letra.',
            'password.mixed' => 'A senha deve conter letras maiúsculas e minúsculas.',
            'password.numbers' => 'A senha deve conter pelo menos um número.',
            'password.symbols' => 'A senha deve conter pelo menos um símbolo.',
        ];
    }
}
