<?php
/**
 * This model class performs common database operations.
 *
 * @category Common Methods.
 * @package  Model.
 * @author   Mangesh Sutar <sutarbmangesh@gmail.com>
 * @license  https://www.fancode.com/   Fancode
 * @link     https://www.fancode.com/Books
 * Developed Date : 11-07-2023.
 */
class CommonMethods extends CI_Model {
/**
     * Retrieves all details.
     *
     * @param string $tableName table name to be fetched.
     * @param string $fields column names to be retrieved.
     * @param array $extraClause extra clause like where, limit, offset, orderBy.
     * @param boolean $singleRow whether to fetch single record.
     * @param string $format return type of the records.
     *
     * @return boolean true: object contains all details else false.
     */
    public function getAll(
        $tableName,
        $fields,
        $extraClause = [],
        $singleRow = true,
        $format = 'object',
        $printQuery = false
    ) {
        if (!empty($fields) && !empty($tableName)) {
            $this->db->select($fields);
            $this->db->from($tableName);

            if (!empty($extraClause['join'])) {
                foreach ($extraClause['join'] as $joinTbl => $joinData) {
                    $this->db->join($joinTbl,
                        $joinData['condition'],
                        (array_key_exists('type', $joinData)) ? $joinData['type'] : 'left'
                    );
                }
            }

            if (!empty($extraClause['where'])) {
                foreach ($extraClause['where'] as $key => $value) {
                    is_array($value)
                    ? $this->db->where_in($key, $value)
                    : (is_int($key)
                            ? $this->db->where($value)
                            : $this->db->where($key, $value)
                        );
                }
            }

            if (!empty($extraClause['whereNotInClause'])) {
                foreach ($extraClause['whereNotInClause'] as $key => $value) {
                    $this->db->where_not_in($key, $value);
                }
            }

            if (!empty($extraClause['whereString'])) {
                $this->db->where($extraClause['whereString']);
            }

            if (!empty($extraClause['groupBy'])) {
                $this->db->group_by($extraClause['groupBy']);
            }

            if (!empty($extraClause['havingClause'])) {
                $this->db->having($extraClause['havingClause']);
            }

            if (!empty($extraClause['orderBy'])) {
                $this->db->order_by($extraClause['orderBy']);
            }

            if (!empty($extraClause['limit'])) {
                $this->db->limit($extraClause['limit'],
                    !empty($extraClause['offset'])
                    ? $extraClause['offset']
                    : FCCommonConstants::DEFAULT_ZERO
                );
            }
            $result = $this->db->get();

            if ($printQuery) echo $this->db->last_query()."<br>";

            if ($singleRow) {
                if ($format == 'array') {
                    return $result->row_array();
                }

                return $result->row();
            }

            if ($format == 'array') {
                return $result->result_array();
            }

            return $result->result();
        }

        return false;
    } /* End of getAll() */


    /**
     * Insert a new data.
     *
     * @param string $tableName table name to be fetched.
     * @param array $data details to be inserted.
     * @param boolean $bulkUpdate insert data in batch.
     * @param array $eventData audit event data.
     *
     * @return int/false success: last inserted id, fail: false.
     *
     */
    public function insert($tableName, $data, $bulkInsert = false,
        $eventData = array()
    ) {
        $result = false;

        if (!empty($tableName) && !empty($data)) {
            if ($bulkInsert) {

                $result = $this->db->insert_batch(
                    $tableName,
                    $data
                );
            } else {
                if ($this->db->insert($tableName, $data)) {
                    $result = $this->db->insert_id();
                }
            }

            if (!empty($eventData)) {

                /* Add audit event here. */
            }
        }

        return $result;
    }

    /**
     * Update the details for given id / bulk update.
     *
     * @param string $tableName table name to be fetched.
     * @param array $data details to be updated.
     * @param array $whereClause where to be updated.
     * @param boolean $bulkUpdate update data in batch.
     * @param array $eventData audit event data.
     *
     * @return boolean true/false.
     *
     */
    public function update($tableName, $data, $whereClause, $bulkUpdate = false,
        $eventData = array()
    ) {
        $result = false;

        if (!empty($tableName) && !empty($data) && !empty($whereClause)) {
            if ($bulkUpdate) {
                $columnName = $whereClause;

                if (is_array($whereClause) && !empty($whereClause['CONDITION'])) {
                    $this->db->where($whereClause['CONDITION']);
                    $columnName = $whereClause['COLUMNNAME'];
                }

                $result = $this->db->update_batch(
                    $tableName,
                    $data,
                    $columnName
                );
            } else {

                if ($this->db->update(
                    $tableName,
                    $data,
                    $whereClause
                )) {
                    $result = true;

                    /* We can return information like uid here if any. */
                };
            }

            if (!empty($eventData)) {

                /* Add audit event here. */
            }
        }

        return $result;
    }

    /**
     * Delete the details for given id.
     *
     * @param string $tableName table name to be fetched.
     * @param array $whereClause where to be updated.
     * @param array $eventData audit event data.
     *
     * @return boolean true/false.
     *
     */
    public function delete($tableName, $whereClause, $eventData = array()) {
        if (!empty($tableName) && !empty($whereClause)) {

            if (!empty($whereClause)) {
                foreach ($whereClause as $key => $value) {
                    is_array($value)
                    ? $this->db->where_in($key, $value)
                    : (is_int($key)
                            ? $this->db->where($value)
                            : $this->db->where($key, $value)
                        );
                }
            }

            $this->db->delete($tableName);

            if ($this->db->affected_rows() > 0 ) {
                if (!empty($eventData)) {
                    /* Add audit event here. */
                }

                return true;
            }
        }

        return false;
    }
}
