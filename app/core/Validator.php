<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Lớp kiểm tra và xác thực dữ liệu đầu vào (Input Validation)
 * Giúp ngăn chặn tấn công SQL Injection, XSS và dữ liệu sai lệch trước khi vào Database.
 */
class Validator
{
    private array $data;
    private array $rules;
    private array $errors = [];
    private array $validatedData = [];

    public function __construct(array $data, array $rules)
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->validate();
    }

    /**
     * Khởi tạo và thực thi xác thực nhanh.
     */
    public static function make(array $data, array $rules): self
    {
        return new self($data, $rules);
    }

    /**
     * Kiểm tra nhanh một ID có phải là số nguyên dương hợp lệ.
     */
    public static function validateId(mixed $id): ?int
    {
        if ($id === null || $id === '') {
            return null;
        }

        if (is_int($id) && $id > 0) {
            return $id;
        }

        if (is_string($id) && ctype_digit($id)) {
            $val = (int) $id;
            return $val > 0 ? $val : null;
        }

        return null;
    }

    /**
     * Làm sạch chuỗi cơ bản (loại bỏ ký tự điều khiển vô hình, trim khoảng trắng).
     */
    public static function sanitizeString(?string $value): string
    {
        if ($value === null) {
            return '';
        }
        $trimmed = trim($value);
        // Loại bỏ ký tự null byte
        return str_replace("\0", '', $trimmed);
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Lấy lỗi đầu tiên hoặc lỗi đầu tiên của một trường cụ thể.
     */
    public function firstError(?string $field = null): ?string
    {
        if ($field !== null) {
            return $this->errors[$field][0] ?? null;
        }

        foreach ($this->errors as $messages) {
            if (!empty($messages)) {
                return $messages[0];
            }
        }

        return null;
    }

    public function validated(): array
    {
        return $this->validatedData;
    }

    private function validate(): void
    {
        foreach ($this->rules as $field => $ruleDefinition) {
            $rules = is_array($ruleDefinition) ? $ruleDefinition : explode('|', $ruleDefinition);
            $value = $this->data[$field] ?? null;

            $isRequired = in_array('required', $rules, true);
            $isEmpty = ($value === null || $value === '' || (is_array($value) && empty($value)));

            if ($isEmpty) {
                if ($isRequired) {
                    $this->addError($field, "Trường '{$field}' không được để trống.");
                }
                // Nếu không bắt buộc và đang trống, bỏ qua các rule kiểm tra định dạng khác
                continue;
            }

            $isNumericField = in_array('int', $rules, true)
                || in_array('integer', $rules, true)
                || in_array('positive_int', $rules, true);

            foreach ($rules as $rule) {
                if ($rule === 'required') {
                    continue;
                }

                $ruleName = $rule;
                $ruleParam = null;

                if (str_contains($rule, ':')) {
                    [$ruleName, $ruleParam] = explode(':', $rule, 2);
                }

                $this->applyRule($field, $value, $ruleName, $ruleParam, $isNumericField);
            }

            if (!isset($this->errors[$field])) {
                $this->validatedData[$field] = is_string($value) ? self::sanitizeString($value) : $value;
            }
        }
    }

    private function applyRule(string $field, mixed $value, string $rule, ?string $param, bool $isNumericField = false): void
    {
        switch ($rule) {
            case 'string':
                if (!is_string($value)) {
                    $this->addError($field, "Trường '{$field}' phải là chuỗi ký tự.");
                }
                break;

            case 'int':
            case 'integer':
                if (!is_int($value) && !(is_string($value) && preg_match('/^-?\d+$/', trim($value)))) {
                    $this->addError($field, "Trường '{$field}' phải là số nguyên.");
                }
                break;

            case 'positive_int':
                if ((!is_int($value) && !(is_string($value) && ctype_digit(trim($value)))) || (int) $value <= 0) {
                    $this->addError($field, "Trường '{$field}' phải là số nguyên dương.");
                }
                break;

            case 'email':
                if (!is_string($value) || !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, "Định dạng email của '{$field}' không hợp lệ.");
                }
                break;

            case 'min':
                $min = (int) $param;
                if ($isNumericField && is_numeric($value)) {
                    if ((float) $value < $min) {
                        $this->addError($field, "Giá trị '{$field}' phải tối thiểu là {$min}.");
                    }
                } elseif (is_string($value)) {
                    if (mb_strlen($value, 'UTF-8') < $min) {
                        $this->addError($field, "Độ dài '{$field}' phải tối thiểu {$min} ký tự.");
                    }
                } elseif (is_numeric($value)) {
                    if ((float) $value < $min) {
                        $this->addError($field, "Giá trị '{$field}' phải tối thiểu là {$min}.");
                    }
                }
                break;

            case 'max':
                $max = (int) $param;
                if ($isNumericField && is_numeric($value)) {
                    if ((float) $value > $max) {
                        $this->addError($field, "Giá trị '{$field}' không được vượt quá {$max}.");
                    }
                } elseif (is_string($value)) {
                    if (mb_strlen($value, 'UTF-8') > $max) {
                        $this->addError($field, "Độ dài '{$field}' không được vượt quá {$max} ký tự.");
                    }
                } elseif (is_numeric($value)) {
                    if ((float) $value > $max) {
                        $this->addError($field, "Giá trị '{$field}' không được vượt quá {$max}.");
                    }
                }
                break;

            case 'in':
                $allowed = array_map('trim', explode(',', (string) $param));
                if (!in_array((string) $value, $allowed, true)) {
                    $this->addError($field, "Giá trị '{$field}' không nằm trong danh sách hợp lệ: " . implode(', ', $allowed));
                }
                break;

            case 'date':
                if (!is_string($value) || !$this->isValidDate($value)) {
                    $this->addError($field, "Định dạng ngày '{$field}' không hợp lệ.");
                }
                break;

            case 'array':
                if (!is_array($value)) {
                    $this->addError($field, "Trường '{$field}' phải là một mảng.");
                }
                break;

            case 'array_of_positive_int':
                if (!is_array($value)) {
                    $this->addError($field, "Trường '{$field}' phải là một mảng.");
                } else {
                    foreach ($value as $item) {
                        if ((!is_int($item) && !(is_string($item) && ctype_digit(trim((string) $item)))) || (int) $item <= 0) {
                            $this->addError($field, "Các phần tử trong '{$field}' phải là số nguyên dương.");
                            break;
                        }
                    }
                }
                break;
        }
    }

    private function isValidDate(string $date): bool
    {
        $trimmed = trim($date);
        if ($trimmed === '' || str_starts_with($trimmed, '0000-00-00')) {
            return false;
        }

        $formats = ['Y-m-d', 'Y-m-d\TH:i', 'Y-m-d\TH:i:s', 'Y-m-d H:i', 'Y-m-d H:i:s'];
        foreach ($formats as $format) {
            $d = \DateTime::createFromFormat($format, $trimmed);
            if ($d && $d->format($format) === $trimmed) {
                return true;
            }
        }

        return strtotime($trimmed) !== false;
    }

    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }
}
