<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // يمكنك التحقق من صلاحيات المستخدم هنا، على سبيل المثال:
        // return auth()->user()->can('update', Task::class);
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
            'status' => 'nullable|in:Open,In Progress,Completed,Blocked', // تحقق من أن الحالة الجديدة من بين القيم المسموح بها
            // 'task_id' => 'nullable|exists:tasks,id',
            // 'old_status' => 'nullable|string|in:Open,In Progress,Completed,Blocked'
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'new_status.required' => 'The task status is required.',
            'new_status.in' => 'The task status must be one of the following: Pending, In Progress, Completed, or Blocked.',
        ];
    }
}
