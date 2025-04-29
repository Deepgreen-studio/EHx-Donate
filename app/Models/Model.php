<?php

namespace EHxDonate\Models;

/**
 * Base ORM Model class for WordPress database operations
 * 
 * Provides ActiveRecord-style query building and CRUD operations with fluent interface
 */
class Model
{
    /** @var string $model Table name with prefix */
    protected $model = '';
    
    /** @var \wpdb $db WordPress database instance */
    protected $db;
    
    /** @var array $wheres WHERE conditions */
    protected $wheres = [];
    
    /** @var array $whereBetween BETWEEN conditions */
    protected $whereBetween = [];
    
    /** @var array $selects SELECT fields */
    protected $selects = [];
    
    /** @var mixed $limit LIMIT value */
    protected $limit = false;
    
    /** @var mixed $offset OFFSET value */
    protected $offset = false;
    
    /** @var mixed $orderBy ORDER BY clause */
    protected $orderBy = false;
    
    /** @var array $joins JOIN clauses */
    protected $joins = [];
    
    /** @var string $tableAlias Table alias */
    protected $tableAlias = '';
    
    /** @var array $groupBy GROUP BY fields */
    protected $groupBy = [];

    /**
     * Constructor - initializes the model with optional table name
     *
     * @param string $table Optional table name (without prefix)
     */
    public function __construct($table = '')
    {
        global $wpdb;
        if ($table) {
            $this->model = $wpdb->prefix.$table;
        }
        $this->db = $wpdb;
    }

    /**
     * Set the table name with optional alias
     *
     * @param string $table Table name (without prefix)
     * @param string $alias Optional table alias
     * @return self
     */
    public function table($table, $alias = '')
    {
        $this->model = $this->db->prefix.$table;
        $this->tableAlias = $alias;
        return $this;
    }

    /**
     * Get the database instance
     *
     * @return \wpdb WordPress database instance
     */
    public function getDB()
    {
        return $this->db;
    }

    /**
     * Add fields to select
     *
     * @param string|array $selects Field(s) to select
     * @return self
     */
    public function select($selects)
    {
        if (is_array($selects)) {
            $selects = array_merge($selects, $this->selects);
        } else {
            $this->selects[] = $selects;
            $selects = $this->selects;
        }
        $this->selects = array_unique($selects);
        return $this;
    }

    /**
     * Add a WHERE condition
     *
     * @param string $column Column name
     * @param mixed $operator Comparison operator
     * @param mixed $value Comparison value
     * @return self
     */
    public function where($column, $operator, $value = '')
    {
        if (func_num_args() == 2) {
            $value = $operator;
            $operator = '=';
        }
        $this->wheres[] = array($column, $operator, $value);
        return $this;
    }
    
    /**
     * Add a OR WHERE condition
     *
     * @param string $column Column name
     * @param mixed $operator Comparison operator
     * @param mixed $value Comparison value
     * @return self
     */
    public function orWhere($column, $operator, $value = '')
    {
        if (func_num_args() == 2) {
            $value = $operator;
            $operator = '=';
        }
        $this->wheres[] = [$column, $operator, $value, 'OR'];
        return $this;
    }

    /**
     * Add a WHERE BETWEEN condition
     *
     * @param string $column Column name
     * @param mixed $firstVal Start value
     * @param mixed $lastVal End value
     * @return self
     */
    public function whereBetween($column, $firstVal, $lastVal = '')
    {
        $this->whereBetween[] = array($column, $firstVal, $lastVal, '');
       return $this;
    }

    /**
     * Add an OR WHERE BETWEEN condition
     *
     * @param string $column Column name
     * @param mixed $firstVal Start value
     * @param mixed $lastVal End value
     * @return self
     */
    public function orWhereBetween($column, $firstVal, $lastVal = '')
    {
        $this->whereBetween[] = array($column, $firstVal, $lastVal, 'OR');
       return $this;
    }

    /**
     * Add an AND WHERE BETWEEN condition
     *
     * @param string $column Column name
     * @param mixed $firstVal Start value
     * @param mixed $lastVal End value
     * @return self
     */
    public function andWhereBetween($column, $firstVal, $lastVal = '')
    {
        $this->whereBetween[] = array($column, $firstVal, $lastVal, ' AND ');
       return $this;
    }

    /**
     * Add a WHERE IN condition
     *
     * @param string $column Column name
     * @param array $values Array of values
     * @return self
     */
    public function whereIn($column, $values)
    {
        if (!$values) {
            return $this;
        }
        if (count($values) == 1) {
            $this->wheres[] = array($column, '=', reset($values));
        } else {
            array_walk($values, function (&$x) {
                $x = "'$x'";
            });
            $this->wheres[] = array($column, 'IN', '('.implode(',', $values).')');
        }

        return $this;
    }

    /**
     * Add GROUP BY clause
     *
     * @param string $column Column to group by
     * @return self
     */
    public function groupBy($column)
    {
        $this->groupBy[] = $column;
        return $this;
    }

    /**
     * Set query LIMIT
     *
     * @param int $limit Number of rows to return
     * @return self
     */
    public function limit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Set query OFFSET
     *
     * @param int $offset Number of rows to skip
     * @return self
     */
    public function offset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * Add ORDER BY clause
     *
     * @param string $column Column to sort by
     * @param string $orderBy Sort direction (ASC/DESC)
     * @return self
     */
    public function orderBy($column, $orderBy = 'ASC')
    {
        $this->orderBy = array($column, $orderBy);
        return $this;
    }

    /**
     * Reset all query conditions
     *
     * @return void
     */
    public function reset()
    {
        $this->wheres = [];
        $this->selects = [];
        $this->limit = false;
        $this->offset = false;
        $this->orderBy = false;
        $this->whereBetween = [];
        $this->joins = [];
        $this->tableAlias = '';
        $this->groupBy = [];
    }

    /**
     * Generate WHERE clause from conditions
     *
     * @return string WHERE clause
     * @access private
     */
    private function getWhereStatement()
    {
        $statement = '';
        if ($this->wheres) {
            foreach ($this->wheres as $index => $where) {
                if (is_numeric($where[2])) {
                    $whereValue = $where[2];
                } 
                else {
                    if ($where[1] != 'IN') {
                        $whereValue = "'$where[2]'";
                    } 
                    else {
                        $whereValue = $where[2];
                    }
                }
                if ($index == 0) {
                    $statement = "WHERE $where[0] $where[1] $whereValue";
                } 
                else {
                    $operator = $where[3] ?? 'AND';
                    $statement .= " $operator $where[0] $where[1] $whereValue";
                }
            }
        }
        if ($this->whereBetween && !$this->wheres) {
            foreach ($this->whereBetween as $whereBetween) {
                $statement .= " WHERE $whereBetween[0] BETWEEN $whereBetween[1] AND $whereBetween[2]";
            }
        }

        if ($this->whereBetween && $this->wheres) {
            foreach ($this->whereBetween as $whereBetween) {
                $statement .= " $whereBetween[3] $whereBetween[0] BETWEEN $whereBetween[1] AND $whereBetween[2]";
            }
        }
        return $statement;
    }

    /**
     * Generate SELECT fields list
     *
     * @return string SELECT fields
     * @access private
     */
    private function getSelects()
    {
        if ($this->selects) {
            return implode(', ', $this->selects);
        }
        return '*';
    }

    /**
     * Generate FROM clause
     *
     * @return string FROM clause
     * @access private
     */
    private function getFromClause()
    {
        return $this->tableAlias ? "{$this->model} AS {$this->tableAlias}" : $this->model;
    }

    /**
     * Generate JOIN clauses
     *
     * @return string JOIN clauses
     * @access protected
     */
    protected function getJoinStatement()
    {
        $statement = '';
        foreach ($this->joins as $join) {
            if ($join['type'] === 'CROSS') {
                $statement .= " CROSS JOIN {$join['table']}";
            } else {
                $statement .= " {$join['type']} JOIN {$join['table']} ON {$join['first']} {$join['operator']} {$join['second']}";
            }
        }
        return $statement;
    }

    /**
     * Generate GROUP BY clause
     *
     * @return string GROUP BY clause
     * @access private
     */
    private function getGroupByStatement()
    {
        if (empty($this->groupBy)) {
            return '';
        }
        return 'GROUP BY ' . implode(', ', $this->groupBy);
    }

    /**
     * Generate additional clauses (ORDER BY, LIMIT)
     *
     * @return string Additional clauses
     * @access private
     */
    private function getOtherStatements()
    {
        $statement = '';

        if (!empty($this->groupBy)) {
            $statement .= $this->getGroupByStatement() . ' ';
        }

        if ($this->orderBy) {
            $statement .= 'ORDER BY '.$this->orderBy[0].' '.$this->orderBy[1]. ' ';
        }

        if ($this->limit) {
            $statement .= "LIMIT {$this->limit} OFFSET {$this->offset}";
        }

        return $statement;
    }

    /**
     * Add a JOIN clause with optional table alias
     * @param string $table Table to join
     * @param string $first First column
     * @param string $operator Operator
     * @param string $second Second column
     * @param string $alias Optional table alias
     * @param string $type Join type (INNER, LEFT, RIGHT)
     * @return $this
     */
    private function addJoin($table, $first, $operator, $second, $alias = '', $type = 'INNER')
    {
        $tableName = $this->db->prefix.$table;
        $tableWithAlias = $alias ? "$tableName AS $alias" : $tableName;
        
        $this->joins[] = [
            'type' => $type,
            'table' => $tableWithAlias,
            'first' => $first,
            'operator' => $operator,
            'second' => $second,
            'alias' => $alias
        ];
        return $this;
    }

    /**
     * Add an INNER JOIN clause
     *
     * @param string $table Table to join
     * @param string $first First column
     * @param string $operator Comparison operator
     * @param string $second Second column
     * @param string $alias Optional table alias
     * @return self
     */
    public function join($table, $first, $operator, $second, $alias = '')
    {
        return $this->addJoin($table, $first, $operator, $second, $alias, 'INNER');
    }

    /**
     * Add a LEFT JOIN clause
     *
     * @param string $table Table to join
     * @param string $first First column
     * @param string $operator Comparison operator
     * @param string $second Second column
     * @param string $alias Optional table alias
     * @return self
     */
    public function leftJoin($table, $first, $operator, $second, $alias = '')
    {
        return $this->addJoin($table, $first, $operator, $second, $alias, 'LEFT');
    }

    /**
     * Add a RIGHT JOIN clause
     *
     * @param string $table Table to join
     * @param string $first First column
     * @param string $operator Comparison operator
     * @param string $second Second column
     * @param string $alias Optional table alias
     * @return self
     */
    public function rightJoin($table, $first, $operator, $second, $alias = '')
    {
        return $this->addJoin($table, $first, $operator, $second, $alias, 'RIGHT');
    }

    /**
     * Add a CROSS JOIN clause
     *
     * @param string $table Table to join
     * @param string $alias Optional table alias
     * @return self
     */
    public function crossJoin($table, $alias = '')
    {
        $tableName = $this->db->prefix.$table;
        $tableWithAlias = $alias ? "$tableName AS $alias" : $tableName;
        
        $this->joins[] = [
            'type' => 'CROSS',
            'table' => $tableWithAlias
        ];
        return $this;
    }

    /**
     * Build SQL query with proper escaping
     */
    protected function buildSqlQuery(bool $countOnly = false): string
    {
        $select = $countOnly ? 'COUNT(*)' : $this->getSelects();
        $from = $this->getFromClause();
        $joins = $this->getJoinStatement();
        $where = $this->getWhereStatement();
        $others = $this->getOtherStatements();

        return $this->db->prepare("SELECT $select FROM %1s %1s $where %1s", $from, $joins, $others);
    }

    /**
     * Execute query and get first matching row
     *
     * @return object|null First result or null
     */
    public function first()
    {
        $data = $this->db->get_row($this->buildSqlQuery());

        $this->reset();

        return $data;
    }

    /**
     * Get the generated SQL query string
     *
     * @return string The prepared SQL query
     */
    public function getSQL()
    {
        // Build the base query with placeholders
        $query = $this->buildSqlQuery();

        $this->reset();

        return $query;
    }

    /**
     * Execute query and get all results
     *
     * @param string $output Output format (OBJECT/ARRAY_A/ARRAY_N)
     * @return array Query results
     */
    public function get($output = OBJECT, $plainSelect = false)
    {
        $data = $this->db->get_results($this->buildSqlQuery(), $output);

        $this->reset();

        return $data;
    }

    /**
     * Insert new record
     *
     * @param array $data Column-value pairs
     * @return int|string Inserted row ID
     */
    public function insert(array $data): int|string
    {
        try {
            $this->db->insert($this->model, array_merge(
                $data,
                [
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql'),
                ]
            ));

            return $this->db->insert_id;

        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    /**
     * Update records matching current conditions
     *
     * @param array $data Column-value pairs
     * @return bool True on success
     */
    public function update($data)
    {
        $whereArray = array();
        $whereFormatArray = array();
        foreach ($this->wheres as $where) {
            $whereArray[$where[0]] = $where[2];
            if (is_numeric($where[2])) {
                $whereFormatArray[] = '%d';
            } else {
                $whereFormatArray[] = '%s';
            }
        }

        $formatArray = [];
        foreach ($data as $datum) {
            if (is_numeric($datum)) {
                $formatArray[] = '%d';
            } else {
                $formatArray[] = '%s';
            }
        }
        
        $data['updated_at'] = current_time('mysql');

        $this->reset();

        return $this->db->update($this->model, $data, $whereArray, $formatArray, $whereFormatArray);
    }

    /**
     * Delete records matching current conditions
     *
     * @return bool True on success
     */
    public function delete()
    {
        $whereArray = array();
        $whereFormatArray = array();
        foreach ($this->wheres as $where) {
            $whereArray[$where[0]] = $where[2];
            if (is_numeric($where[2])) {
                $whereFormatArray[] = '%d';
            } else {
                $whereFormatArray[] = '%s';
            }
        }
        return $this->db->delete($this->model, $whereArray, $whereFormatArray);
    }

    /**
     * Get count of matching records
     *
     * @return string|null Number of matching rows
     */
    public function getCount()
    {
        return $this->db->get_var($this->buildSqlQuery(true));
    }

    /**
     * Get distinct values for a column
     *
     * @param string $row Column name
     * @return array Distinct values
     */
    public function getDISTINCT($row)
    {
        $query = $this->db->prepare("SELECT DISTINCT %1s FROM %1s", $row, $this->getFromClause());
        $this->reset();
        return $this->db->get_results($query);
    }

    /**
     * Get sum of matching records
     *
     * @return float|int Number of matching rows sum
     */
    public function getSum($column)
    {
        // Build the base query with placeholders
        $query = $this->db->prepare("SELECT SUM(%1s) FROM %1s", $column, $this->getFromClause());


        if (!empty($this->getWhereStatement())) {
            $query .= " {$this->getWhereStatement()}";
        }

        $total_sum = $this->db->get_var($query);

        return (float) $total_sum ?? 0;
    }
}