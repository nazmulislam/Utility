<?php

declare(strict_types=1);

namespace NazmulIslam\Utility\Requests;


class InputSanitization
{
    public static array $sanitizedInput = [];
    public static $currentInput;
    public static $modelSchema;

    const DATA_TABLE_SCHEMA = [
        'page' => [
            'type' => 'integer',
            'length' => 11,
            'sanitizers' => [
                'FILTER_SANITIZE_NUMBER_INT' => [],
            ],
        ],

        'per_page' => [
            'type' => 'integer',
            'length' => 11,
            'sanitizers' => [
                'FILTER_SANITIZE_NUMBER_INT' => [],
            ],
        ],

        'filter	' => [
            'type' => 'varchar',
            'length' => 255,
            'sanitizers' => [
                'FILTER_SANITIZE_STRING' => [],


            ],
        ],
        'sort' => [
            'type' => 'varchar',
            'length' => 255,
            'sanitizers' => [
                'FILTER_SANITIZE_STRING' => [],


            ],
        ],
    ];

    static public function sanitize(array $schema, array $inputs): array
    {
        self::$modelSchema = array_merge($schema, self::DATA_TABLE_SCHEMA);
        if (isset($inputs) && !empty($inputs) && is_array($inputs)) {

            $fields = array_keys($inputs);

            foreach ($inputs as $key => $value) {
                if (array_key_exists($key, self::$modelSchema) && in_array($key, $fields) && isset($value)) {
                    if (is_array($value)) {
                        self::$sanitizedInput[$key] = $value;
                    } else {
                        self::$sanitizedInput[$key] = self::perform($key, $value);
                    }
                } else {
                    self::$sanitizedInput[$key] = null;
                }
            }
        }
        return self::$sanitizedInput;
    }

    static private function perform(string $field, string $value)
    {
        self::$currentInput = $value;
        if (isset(self::$modelSchema[$field])) {
            $sanitizers = self::$modelSchema[$field]['sanitizers'];
            if (isset($sanitizers)) {

                foreach ($sanitizers as $key => $value) {

                    self::$currentInput = filter_var(self::$currentInput, constant($key));
                }

                return self::$currentInput;
            }
        } else {
            self::$currentInput = null;
        }
    }
}
