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
     * @return array
     */
    public function find(string $field): array
    {
        $result = [];

        foreach ($this->parsedRows as $row) {
            if ($row['field'] === $this->getKey($field)) {
                $result[] = $row['value'];
            }
        }

        return $result;
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

            switch (count($result[$field])) {
                case 0:
                    $result[$field] = null;
                    break;
                case 1:
                    $result[$field] = $result[$field][0];
                    break;
            }
        }

        return $result;
    }

    public function all()
    {
        $result = [];

        foreach ($this->parsedRows as $row) {
            $field = array_search($row['field'], $this->fieldsMap) ?: $row['field'];
            $result[$field] = $this->find($field);

            switch (count($result[$field])) {
                case 0:
                    $result[$field] = null;
                    break;
                case 1:
                    $result[$field] = $result[$field][0];
                    break;
            }
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
