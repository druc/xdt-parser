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

    /** @var bool */
    private $corrupted = false;

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
    public static function make(string $content, array $fieldsMap = []): self
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

    private function parseXdtRows(): void
    {
        foreach ($this->xdtRows as $row) {
            if ($row === '') {
                continue;
            }
            $this->parsedRows[] = $this->parseSingleXdtRow($row);
        }
    }

    /**
     * @param string $row
     * @return array
     */
    private function parseSingleXdtRow(string $row): array
    {
        $matched = preg_match('/^\\r?\\n?(\\d{3})(\\d{4})(.*?)\\r?\\n?$/', $row, $matches);

        if (!$matched) {
            $this->corrupted = true;
        }

        return [
            'length' => $matches[1] ?? null,
            'field' => $matches[2] ?? null,
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
            if ($row['field'] === $this->getKey($field)) {
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
            if ($row['field'] === $this->getKey($field)) {
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
    private function getKey(string $field): string
    {
        return $this->fieldsMap[$field] ?? $field;
    }

    /**
     * @return bool
     */
    public function isCorrupt(): bool
    {
        return $this->corrupted;
    }

    /**
     * @return array
     */
    public function getMapped(): array
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
    public function all(): array
    {
        $result = [];

        foreach ($this->parsedRows as $row) {
            $field = array_search($row['field'], $this->fieldsMap) ?: $row['field'];
            $result[$field] = $this->find($field);
        }

        return $result;
    }

    /**
     * @param array $fieldsMap
     * @return XdtParser
     */
    public function setFieldsMap(array $fieldsMap): XdtParser
    {
        $this->fieldsMap = $fieldsMap;
        return $this;
    }

    /**
     * @return array
     */
    public function getFieldsMap(): array
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
}
