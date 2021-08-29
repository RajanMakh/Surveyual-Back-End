<?php
/**
 * Return a JSON recordset
 * @author Rajan Makh
 */
class JSONRecordSet extends RecordSet {
    /**
     * function to return a record set as an associative array
     * @param $query   string with sql to execute to retrieve the record set
     * @param $params  associative array of params for preparted statement
     * @return string  a json documnent
     */
    function getJSONRecordSet($query, $params = null, $type = null) {
        $stmt = $this->getRecordSet($query, $params);
        if(!isset($type) ) {
            $recordSet = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $nRecords = count($recordSet);
        }
        if(isset($type) && $type === 'DELETE') {
            $recordSet = $stmt->rowCount();
            $nRecords =  $stmt->rowCount();
        }

        return json_encode(array("count"=>$nRecords, "data"=>$recordSet));
    }
}
?>
