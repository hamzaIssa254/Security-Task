<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class TaskStoreRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:Bug,Feature,Improvement',
            'status' => 'required|in:Open,In Progress,Completed,Blocked',
            'priority' => 'required|in:Low,Medium,High',
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
            'dependencies' => 'nullable|array', // تحقق من صحة المدخلات لتبعية المهام
            'dependencies.*' => 'exists:tasks,id', // يجب أن تكون المهام المحددة موجودة
        ];
    }
}
