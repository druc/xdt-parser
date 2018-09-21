<?php

namespace Druc\XdtParser;

class XdtParser
{
    /**
     * Array to hold mappings to different xdt keys
     * Eg: ['first_name' => '2031', 'last_name' => '2031'];
     * @var array
     */
    private $fieldsMap;

    /**
     * Holds the content unparsed rows
     * @var array
     */
    private $xdtRows = [];

    /** @var array */
    private $parsedRows = [];

    /**
     * @param string $content
     * @param array $fieldsMap
     * @return XdtParser
     */
    public static function make(string $content, array $fieldsMap = [])
    {
        return new static($content, $fieldsMap);
    }

    /**
     * XdtParser constructor.
     * @param string $content
     * @param array $fieldsMap
     */
    private function __construct(string $content, array $fieldsMap = [])
    {
        $this->fieldsMap = $fieldsMap;
        $this->xdtRows = explode(PHP_EOL, $content);
        $this->parseXdtRows();
    }

    private function parseXdtRows()
    {
        foreach ($this->xdtRows as $row) {
            if ($row === '') {
                continue;
            }
            $this->parsedRows[] = $this->parseSingle($row);
        }
    }

    /**
     * @param string $string
     * @return array
     */
    public function parseSingle(string $string)
    {
        $matched = preg_match('/^\\r?\\n?(\\d{3})(\\d{4})(.*?)\\r?\\n?$/', $string, $matches);

        if (!$matched) {
            throw new CorruptedXdt;
        }

        return [
            'length' => $matches[1] ? intval($matches[1]) : null,
            'key' => $matches[2] ?? null,
            'value' => $matches[3] ?? null
        ];
    }

    /**
     * @param string $field
     * @return null
     */
    public function first(string $field)
    {
        foreach ($this->parsedRows as $row) {
            if ($row['key'] === $this->getKey($field)) {
                return $row['value'];
            }
        }

        return null;
    }

    /**
     * @param string $field
     * @return array|mixed|null
     */
    public function find(string $field)
    {
        $result = [];

        foreach ($this->parsedRows as $row) {
            if ($row['key'] === $this->getKey($field)) {
                $result[] = $row['value'];
            }
        }

        switch (count($result)) {
            case 0:
                return null;
            case 1:
                return $result[0];
            default:
                return $result;
        }
    }

    /**
     * @param string $field
     * @return string
     */
    public function getKey(string $field)
    {
        return $this->fieldsMap[$field] ?? $field;
    }

    /**
     * @param string $key
     * @return string
     */
    public function getFieldName(string $key)
    {
        foreach ($this->fieldsMap as $field => $k) {
            if ($k === $key) {
                return $field;
            }
        }
        return $key;
    }

    /**
     * @return array
     */
    public function getMapped()
    {
        $result = [];

        foreach ($this->fieldsMap as $field => $key) {
            $result[$field] = $this->find($field);
        }

        return $result;
    }

    /**
     * @return array
     */
    public function all()
    {
        $result = [];

        foreach ($this->parsedRows as $row) {
            $field = array_search($row['key'], $this->fieldsMap) ?: $row['key'];
            $result[$field] = $this->find($field);
        }

        return $result;
    }

    /**
     * @param array $fieldsMap
     * @return XdtParser
     */
    public function setFieldsMap(array $fieldsMap)
    {
        $this->fieldsMap = $fieldsMap;
        return $this;
    }

    /**
     * @return array
     */
    public function getFieldsMap()
    {
        return $this->fieldsMap;
    }

    /**
     * @param array $fields
     */
    public function addFieldsMap(array $fields)
    {
        foreach ($fields as $field => $key) {
            $this->fieldsMap[$field] = $key;
        }
    }

    /**
     * @param array $fields
     */
    public function removeFields(array $fields)
    {
        foreach ($fields as $field) {
            unset($this->fieldsMap[$field]);
        }
    }

    /**
     * @return array
     */
    public function getXdtRows(): array
    {
        return $this->xdtRows;
    }
}
