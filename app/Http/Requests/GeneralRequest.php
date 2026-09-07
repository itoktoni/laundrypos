<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GeneralRequest extends FormRequest
{
    public function authorize(): bool
    {
        $controller = request()->route()->getController();
        $model = $controller->model ?? null;

        // ponytail: JSON-store controllers don't set $this->model; AccessMiddleware gates by role.
        if ($model === null) {
            return true;
        }

        $method = request()->route()->getActionMethod();
        // ponytail: strip only the leading get/post prefix (str_replace would
        // also eat inner occurrences, e.g. getTarget -> tar).
        $action = strtolower(preg_replace('/^(get|post)/i', '', $method));
        $action = $action === 'index' ? 'table' : $action;

        // ponytail: custom actions (getKartuStok, getJadwal, postRusak, ...)
        // have no matching policy method, so Gate denies -> 403. Fall back
        // to the standard read/write ability instead of denying.
        $policy = policy($model);
        if ($policy === null || ! method_exists($policy, $action)) {
            $isWrite = str_starts_with(strtolower($method), 'post');
            if ($isWrite) {
                $action = request()->route()->parameter('id') !== null ? 'update' : 'create';
            } else {
                $action = request()->route()->parameter('id') !== null ? 'show' : 'table';
            }
        }

        return $this->user()->can($action, $model);
    }

    public function rules(): array
    {
        return [];
    }
}
