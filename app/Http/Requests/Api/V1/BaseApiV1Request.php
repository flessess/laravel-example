<?php

namespace App\Http\Requests\Api\V1;

use App\Helpers\BytesHelper;
use DateTime;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

abstract class BaseApiV1Request extends FormRequest
{
    protected $dateFormatWithTimeZone = 'date_format:' . DateTime::ATOM;

    protected array $uuidFields = [];

    protected function prepareForValidation(): void
    {
        foreach ($this->uuidFields as $uuid) {
            $data = $this->toArray();
            if ($this->input($uuid)) {
                if (Str::endsWith($uuid, '*')) {
                    $key = rtrim($uuid, '\.\*');
                } else {
                    $key = $uuid;
                }
                data_set($data, $key, $this->getValidUuid($uuid));
                $this->replace($data);
            }
        }
    }

    private function getValidUuid($key)
    {
        $is_array = Str::endsWith($key, '*');
        $value = $this->input($key);

        if (is_array($value)) {
            $result = collect($value)->filter()->map(
                function ($item) {
                    if (is_array($item)) {
                        foreach ($item as &$v) {
                            if (empty($v)) {
                                continue;
                            }
                            $v = BytesHelper::getValidUiid($v);
                        }
                        return $item;
                    }
                    return BytesHelper::getValidUiid($item);
                }
            )->toArray();
            return $is_array ? $result : ($result[0] ?? null);
        }

        return (is_int($value) || empty($value)) ? $value : BytesHelper::getValidUiid($value);
    }

    public function uuid(): array
    {
        return ['required', 'string', 'regex:/^[A-Fa-f0-9]{8}-?[A-Fa-f0-9]{4}-?[A-Fa-f0-9]{4}-?[A-Fa-f0-9]{4}-?[A-Fa-f0-9]{12}$/i'];
    }

    public function uuidMessage(): string
    {
        return 'The :attribute must be a valid UUID or HEX format';
    }

    /**
     * @return \Closure
     */
    private function getClosure(): \Closure
    {
        return function ($item) {
            return $this->extracted($item, 0);
        };
    }

    /**
     * @param $item
     */
    private function extracted($item, $k = null)
    {
        if ((is_array($item))) {
            foreach ($item as $k => $v) {
                return $this->extracted($v, $k);
            }
        }

        if (!empty($k)) {
            return [$k => BytesHelper::getValidUiid($item)];
        }
    }
}
