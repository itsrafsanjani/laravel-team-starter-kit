<?php

namespace App\Http\Requests\Admin;

use App\Enums\PlanType;
use Illuminate\Foundation\Http\FormRequest;

class StorePlanRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:plans',
            'description' => 'nullable|string|max:1000',
            'type' => ['required', 'in:'.implode(',', PlanType::values())],
            'monthly_price' => 'nullable|numeric|min:0',
            'yearly_price' => 'nullable|numeric|min:0',
            'lifetime_price' => 'nullable|numeric|min:0',
            'stripe_monthly_price_id' => 'nullable|string|max:255',
            'stripe_yearly_price_id' => 'nullable|string|max:255',
            'stripe_lifetime_price_id' => 'nullable|string|max:255',
            'trial_days' => 'required|integer|min:0',
            'features' => 'nullable|array',
            'features.*' => 'string',
            'permissions' => 'nullable|array',
            'is_active' => 'boolean',
            'is_popular' => 'boolean',
            'is_legacy' => 'boolean',
            'sort_order' => 'required|integer|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The plan name is required.',
            'slug.required' => 'The plan slug is required.',
            'slug.unique' => 'This plan slug is already in use.',
            'type.required' => 'The plan type is required.',
            'type.in' => 'The plan type must be one of: '.implode(', ', PlanType::values()).'.',
            'trial_days.required' => 'The trial days field is required.',
            'trial_days.integer' => 'The trial days must be a number.',
            'trial_days.min' => 'The trial days must be at least 0.',
            'sort_order.required' => 'The sort order is required.',
            'sort_order.integer' => 'The sort order must be a number.',
            'sort_order.min' => 'The sort order must be at least 0.',
        ];
    }
}
