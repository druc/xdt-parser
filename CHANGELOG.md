# Changelog
## v0.1.0
### Added
- `getFieldName($key)` - takes a xdt key and return the mapped field (if any)
- `getXdtRows()` - returns an array with xdt rows
- `getKey($field)` - takes a field and returns its key
- `parseSingle($xdtRow)` - parses a single xdtRow: `['length' => 8, 'key' => '3210', 'value' => 'pinsmile']`