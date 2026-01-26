<?php
/**
 * Database helper wrapper around wpdb
 *
 * @package Zul\Gallery
 */

namespace Zul\Gallery\Support;

class Db
{
    private \wpdb $wpdb;
    private string $prefix;

    public function __construct(?\wpdb $wpdb = null)
    {
        $this->wpdb = $wpdb ?? $GLOBALS['wpdb'];
        $this->prefix = $this->wpdb->prefix;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function getCharsetCollate(): string
    {
        return $this->wpdb->get_charset_collate();
    }

    public function prepare(string $query, ...$args): string
    {
        if (empty($args)) {
            return $query;
        }
        return $this->wpdb->prepare($query, ...$args);
    }

    public function getRow(string $query, string $output = OBJECT): ?object
    {
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        return $this->wpdb->get_row($query, $output);
    }

    public function getResults(string $query, string $output = OBJECT): array
    {
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $results = $this->wpdb->get_results($query, $output);
        return $results ?: [];
    }

    public function getVar(string $query)
    {
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        return $this->wpdb->get_var($query);
    }

    public function insert(string $table, array $data, ?array $format = null): int|false
    {
        $result = $this->wpdb->insert($table, $data, $format);
        return $result !== false ? $this->wpdb->insert_id : false;
    }

    public function update(string $table, array $data, array $where, ?array $format = null, ?array $whereFormat = null): int|false
    {
        return $this->wpdb->update($table, $data, $where, $format, $whereFormat);
    }

    public function delete(string $table, array $where, ?array $whereFormat = null): int|false
    {
        return $this->wpdb->delete($table, $where, $whereFormat);
    }

    public function query(string $query): int|bool
    {
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        return $this->wpdb->query($query);
    }

    public function insertId(): int
    {
        return $this->wpdb->insert_id;
    }

    public function lastError(): string
    {
        return $this->wpdb->last_error;
    }

    public function getTableName(string $name): string
    {
        return $this->prefix . $name;
    }
}
