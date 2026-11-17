<?php
final class Validator {
    private array $errors = [];
    private array $data;

    public function __construct(array $data) {
        $this->data = $data;
    }

    public function required(string ...$fields): self {
        foreach ($fields as $f) {
            if (!isset($this->data[$f]) || (is_string($this->data[$f]) && trim($this->data[$f]) === '')) {
                $this->errors[$f] = "The {$this->human($f)} field is required.";
            }
        }
        return $this;
    }

    public function email(string $field): self {
        if (!empty($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = 'Please provide a valid email address.';
        }
        return $this;
    }

    public function min(string $field, int $len): self {
        if (isset($this->data[$field]) && strlen((string)$this->data[$field]) < $len) {
            $this->errors[$field] = "The {$this->human($field)} must be at least {$len} characters.";
        }
        return $this;
    }

    public function max(string $field, int $len): self {
        if (isset($this->data[$field]) && strlen((string)$this->data[$field]) > $len) {
            $this->errors[$field] = "The {$this->human($field)} must be at most {$len} characters.";
        }
        return $this;
    }

    public function regex(string $field, string $pattern, string $msg): self {
        if (isset($this->data[$field]) && !preg_match($pattern, (string)$this->data[$field])) {
            $this->errors[$field] = $msg;
        }
        return $this;
    }

    public function in(string $field, array $allowed): self {
        if (isset($this->data[$field]) && !in_array($this->data[$field], $allowed, true)) {
            $this->errors[$field] = "The {$this->human($field)} is invalid.";
        }
        return $this;
    }

    public function password(string $field): self {
        if (isset($this->data[$field])) {
            $p = (string)$this->data[$field];
            if (strlen($p) < 8) {
                $this->errors[$field] = 'Password must be at least 8 characters.';
            } elseif (!preg_match('/[A-Za-z]/', $p) || !preg_match('/[0-9]/', $p)) {
                $this->errors[$field] = 'Password must contain at least one letter and one number.';
            }
        }
        return $this;
    }

    /**
     * Final check — throws if any error. Returns clean data otherwise.
     */
    public function validate(): array {
        if (!empty($this->errors)) {
            $first = reset($this->errors);
            throw new Exception($first, 400);
        }
        return $this->data;
    }

    private function human(string $field): string {
        return strtolower(str_replace('_', ' ', $field));
    }
}