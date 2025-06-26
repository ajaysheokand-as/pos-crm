<?php

class DatabaseConn {

    var $Error = null;
    var $Connection = null;
    var $tableNameList = array();
    var $auditFlg = false;
    var $file_log_active = false;
    var $file_database_logger_name = null;

    var $database_server;
    var $database_username;
    var $database_password;
    var $database_name;



    public function __construct() {

        $this->database_server = getenv('DB_HOST') ?: "localhost";
        $this->database_username = getenv('DB_USER') ?: "root";
        $this->database_password = getenv('DB_PASSWORD') ?: "";
        $this->database_name = getenv('DB_NAME') ?: "pos-db";
        if ($this->file_log_active) {
            $this->file_database_logger_name = "system_db_query_" . date("YmdH") . ".log";
        }

        if ($this->checkConnection()) {
//            echo "exist connection";
        } else {
//            echo "new connection";
            $this->Connection = mysqli_connect($this->database_server, $this->database_username, $this->database_password) or die("database connection failed.");
            if ($this->database_name && trim($this->database_name) <> "") {
                mysqli_select_db($this->Connection, $this->database_name) or die("\103\157\x75\154\144 \x6eot\x20s\x65\154\145\x63\164 \x64a\x74\x61\x62\141\x73e");
            }
        }

        $this->Error = "";
    }

    public function reverseSelect($str, $revTableColMapping) {

        foreach ($this->tableNameList as $k => $tableName) {
            if (isset($revTableColMapping[$tableName][$str])) {
                return $revTableColMapping[$tableName][$str];
            }
        }
        return $str;
    }

    public function makeQuery($str) {
        $convertion = true;
        if (!$convertion) {
            return $str;
        }
        $this->tableNameList = array();
        include('DatabaseConn.table.php');
        foreach ($tableMapping as $k => $v) {
            //echo $k."---".substr($v,0,10);die;
            if (preg_match("/\b$k\b/i", $str) && substr($v, 0, 10) == "RUPEEPOWER") {
                $str = preg_replace("/\b$k\b/i", $v, $str);
                $this->tableNameList[] = $k;
            }
        }
        include 'DatabaseConn.column.php';
        foreach ($this->tableNameList as $k => $tableName) {
            foreach ($tableColumnMapping[$tableName] as $k => $v) {
                $str = preg_replace("/\b$k\b/i", $v, $str);
            }
        }

        //global $ErrorLog;
        //fwrite($ErrorLog, strip_tags(str_replace("&gt;", ">", preg_replace("/<p>(.*)<\/p>/Uis", "$1\n", $str))) . "\n");
        //echo $str."<br><br>";
        //unset tablecoumnemapping
        return $str;
    }

    public function convert_date_format($fld) {
        include 'DatabaseConn.Dcolumn.php';
        if (isset($fieldData[strtolower($fld)])) {
            return $fieldData[$fld];
        }
        return false;
    }

    function version() {
        return $this->version;
    }

    function close() {
        if ($this->Connection) {
            $result = mysqli_close($this->Connection);
            return $result;
        } else {
            return false;
        }
    }

    function checkConnection() {
        if ($this->Connection) {
            $result = mysqli_ping($this->Connection);
            return $result;
        } else {
            return false;
        }
    }

    function getConnectionHandle() {
        return $this->Connection;
    }

    function insert($table, $elements, $debug = false) {


        $query = "I\x4eS\x45\122T\040\x49\x4e\x54\x4f $table \x28";
        $fields = "";
        $values = "";
        $isFirst = true;

        foreach ($elements as $key => $value) {
            if ($value != "\156o\x77\x28)") {
                $value = "'" . $value . "'";
            }

            if ($isFirst) {
                $fields .= $key;
                $values .= $value;
                $isFirst = false;
            } else {
                $fields .= "\054" . $key;
                $values .= "\x2c" . $value;
            }
        }

        $query .= $fields . "\051\040\126\x41\x4c\125\x45\123\x20(" . $values . "\x29";

        if (isset($_SESSION['debugdb'])) {
            echo("\n\n\x3c\041-\055\x20$query\x20\055-\x3e\n\n");
        }


        if (($result = mysqli_query($this->Connection, $query))) {

            $insert_id = mysqli_insert_id($this->Connection);

            return $insert_id;
        } else {

            $this->Error = mysqli_error($this->Connection);

            if ($this->file_log_active) {
                $debug_backtrace = debug_backtrace();
                $this->common_file_logger($this->file_log_active, $this->file_database_logger_name, "--Query DB Query----" . $query);
                $this->common_file_logger($this->file_log_active, $this->file_database_logger_name, "--Query DB File----" . $debug_backtrace[0]['file'] . " Line--" . $debug_backtrace[0]['line']);
                $this->common_file_logger($this->file_log_active, $this->file_database_logger_name, "--Query DB Error----" . $this->Error);
            }

            return false;
        }
    }

    function update($table, $elements, $condition, $debug = false) {

        $query = "\x55P\x44\x41TE\x20$table\040\123\105T\x20";
        $isFirst = true;
        foreach ($elements as $key => $value) {
            if ($value != "\156o\x77\x28\051") {
                $value = "'" . $value . "'";
            }
            if ($isFirst) {
                $query .= $key . "=" . $value;
                $isFirst = false;
            } else {
                $query .= "," . $key . "\x3d" . $value;
            }
        }

        if ($condition != "") {
            $query .= " W\x48\105\x52E\x20\050" . $condition . "\x29";
        }

        if (isset($_SESSION['debugdb'])) {
            echo("\n\n\074!\x2d\x2d\x20$query \055\055>\n\n");
        }

        //echo $query;
        if (($result = mysqli_query($this->Connection, $query))) {
            return true;
        } else {

            $this->Error = mysqli_error($this->Connection);

            if ($this->file_log_active) {
                $debug_backtrace = debug_backtrace();
                $this->common_file_logger($this->file_log_active, $this->file_database_logger_name, "--Query DB Query----" . $query);
                $this->common_file_logger($this->file_log_active, $this->file_database_logger_name, "--Query DB File----" . $debug_backtrace[0]['file'] . " Line--" . $debug_backtrace[0]['line']);
                $this->common_file_logger($this->file_log_active, $this->file_database_logger_name, "--Query DB Error----" . $this->Error);
            }
            return false;
        }
    }

    function delete($table, $condition) {
        if ($condition != "") {
            $query = "\x44EL\x45TE\x20F\122O\115\040$table\040W\110E\122\x45 \050" . $condition . "\x29";
        } else {
            $query = "\104\105\x4c\105TE\x20\x46R\x4f\115\x20$table";
        }

        if (isset($_SESSION['debugdb'])) {
            echo("\n\n<\041--\x20$query\040\x2d\x2d>\n\n");
        }

        $query = $this->makeQuery($query);
        if (($result = mysql_query($query, $this->Connection))) {
            return true;
        } else {
            global $ErrorLog;
            $this->Error = mysql_error($this->Connection);
            $error_string = $this->Error . " on query '" . $query . "'";
            $pretty_error = "<p><b>E_DATABASE [DB]: </b>" . $error_string . "</p><p><i>ON =&gt; LINE: , FILE: , AT: " . date("Y-M-d H:m:s U") . "</i></p>";
            if (isset($ErrorLog)) {
                fwrite($ErrorLog, strip_tags(str_replace("&gt;", ">", preg_replace("/<p>(.*)<\/p>/Uis", "$1\n", $pretty_error))) . "\n");
            }
            //echo("\074!-\055 \123\x54AR\124 D\x42 \x45\x72r\x6fr\n\n");
            //echo($this->Error . "\n");
            //echo("\x71\165\145\162\x79\072\040" . $query . "\n");
            //echo("\n\105\x4e\104\040\104\x42 \105rro\162\040\055->\n");
            return false;
        }
    }

    function get($table, $elements, $condition, $order, $groupby) {
        $query = "\x53\x45\x4c\x45\x43\124 ";
        $isFirst = true;
        if ($elements == "") {
            $query .= "*";
        } else {
            foreach ($elements as $value) {
                if ($isFirst) {
                    $query .= $value;
                    $isFirst = false;
                } else {
                    $query .= "\054" . $value;
                }
            }
        }

        $query .= " \x46\122OM\x20$table";
        if ($condition != "") {
            $query .= "\x20\127HER\x45\x20(" . $condition . ")";
        }

        if ($order != "") {
            $query .= "\x20O\x52\104\105R\x20\x42Y\040" . $order;
        }

        if ($groupby != "") {
            $query .= "\x20\107\x52\x4f\125P \102\131\x20" . $groupby;
        }

        if (isset($_SESSION['debugdb'])) {
            echo("\n\n\x3c\x21-\055\040$query\040\055\x2d>\n\n");
        }
        $query = $this->makeQuery($query);

        if (($result = mysql_query($query, $this->Connection))) {
            if ($elements == "") {
                $i = 0;
                $elements = array();
                while ($i < mysql_num_fields($result)) {
                    $meta = mysql_fetch_field($result);
                    if ($meta) {
                        array_push($elements, $meta->name);
                    }
                    $i++;
                }
            }
            $selection = array();
            $selection["\160\x61\147e\163"] = 1;
            $selection["\x63\x6f\x75\x6e\164"] = mysql_num_rows($result);
            $selection["\x73q\154"] = $query;
            $selection["\x69t\x65m\163"] = array();

            while (($row = mysql_fetch_array($result))) {
                $temp = array();
                foreach ($elements as $value) {
                    $temp[$value] = $row[$value];
                }
                array_push($selection["ite\x6d\163"], $temp);
            }

            mysql_free_result($result);
            return $selection;
        } else {
            global $ErrorLog;
            $this->Error = mysql_error($this->Connection);
            $error_string = $this->Error . " on query '" . $query . "'";
            $pretty_error = "<p><b>E_DATABASE [DB]: </b>" . $error_string . "</p><p><i>ON =&gt; LINE: , FILE: , AT: " . date("Y-M-d H:m:s U") . "</i></p>";
            if (isset($ErrorLog)) {
                fwrite($ErrorLog, strip_tags(str_replace("&gt;", ">", preg_replace("/<p>(.*)<\/p>/Uis", "$1\n", $pretty_error))) . "\n");
            }
            //echo("\074!-\055 \123\x54AR\124 D\x42 \x45\x72r\x6fr\n\n");
            //echo($this->Error . "\n");
            //echo("\x71\165\145\162\x79\072\040" . $query . "\n");
            //echo("\n\105\x4e\104\040\104\x42 \105rro\162\040\055->\n");
            return false;
        }
    }

    function getPage($table, $elements, $condition, $order, $groupby, $page, $elementsPerPage) {
        $query = "\x53\x45\114ECT\x20";
        $isFirst = true;
        if ($elements == "") {
            $query .= "\x2a";
        } else {
            foreach ($elements as $value) {
                if ($isFirst) {
                    $query .= $value;
                    $isFirst = false;
                } else {
                    $query .= "\x2c" . $value;
                }
            }
        }

        $query .= "\040\106\x52\117M\x20$table";
        if ($condition != "") {
            $query .= " \x57H\105\122\x45\x20\x28" . $condition . "\x29";
        }

        if ($order != "") {
            $query .= " O\x52\x44\x45\122\x20\102\x59\040" . $order;
        }

        if ($groupby != "") {
            $query .= "\040\x47\x52OU\120\x20B\131\040" . $groupby;
        }

        if (isset($_SESSION['debugdb'])) {
            echo("\n\n\x3c\x21-\x2d $query\040\x2d\x2d\x3e\n\n");
        }

        $query = $this->makeQuery($query);
        if (($result = mysql_query($query, $this->Connection))) {
            if ($elements == "") {
                $i = 0;
                $elements = array();

                while ($i < mysql_num_fields($result)) {
                    $meta = mysql_fetch_field($result);
                    if ($meta) {
                        array_push($elements, $meta->name);
                    }
                    $i++;
                }
            }

            $selection = array();
            $selection["\143o\x75\156t"] = mysql_num_rows($result);
            $selection["\x73\161l"] = $query;
            $selection["\151\x74\145\155s"] = array();
            $numRows = $selection["coun\164"];
            $pages = 0;
            $remainder = 0;

            if ($numRows > 0) {
                if ($numRows > $elementsPerPage) {
                    $pages = floor($numRows / $elementsPerPage);
                    $remainder = $numRows - (($pages) * $elementsPerPage);
                }

                if ($remainder > 0) {
                    $pages++;
                }
            }

            $firstItem = $page * $elementsPerPage;

            if ($numRows < $elementsPerPage) {
                $items = $numRows;
            } else {
                if (($page + 1 == $pages) && ($remainder > 0 )) {
                    $items = $remainder;
                } else {
                    $items = $elementsPerPage;
                }
            }

            if ($numRows == 0) {
                $items = $elementsPerPage;
            }

            $selection["p\x61g\x65s"] = $pages;

            if (($firstItem + $items) <= $numRows) {
                for ($i = $firstItem; $i < $firstItem + $items; $i++) {
                    if (mysql_data_seek($result, $i)) {
                        $row = mysql_fetch_array($result);
                        $temp = array();

                        foreach ($elements as $value) {
                            $temp[$value] = $row[$value];
                        }

                        array_push($selection["it\145\x6ds"], $temp);
                    }
                }
            }

            mysql_free_result($result);

            return $selection;
        } else {
            global $ErrorLog;
            $this->Error = mysql_error($this->Connection);
            $error_string = $this->Error . " on query '" . $query . "'";
            $pretty_error = "<p><b>E_DATABASE [DB]: </b>" . $error_string . "</p><p><i>ON =&gt; LINE: , FILE: , AT: " . date("Y-M-d H:m:s U") . "</i></p>";
            if (isset($ErrorLog)) {
                fwrite($ErrorLog, strip_tags(str_replace("&gt;", ">", preg_replace("/<p>(.*)<\/p>/Uis", "$1\n", $pretty_error))) . "\n");
            }
            //echo("\074!-\055 \123\x54AR\124 D\x42 \x45\x72r\x6fr\n\n");
            //echo($this->Error . "\n");
            //echo("\x71\165\145\162\x79\072\040" . $query . "\n");
            //echo("\n\105\x4e\104\040\104\x42 \105rro\162\040\055->\n");
            return false;
        }
    }

    function query($query, $debug = false) {
        $plain_query = $query;
        if (isset($_SESSION['debugdb'])) {
            echo("\n\n\074\x21-\x2d\x20$query \x2d\x2d>\n\n");
        }
        if ($this->file_log_active) {
            $this->common_file_logger($this->file_log_active, $this->file_database_logger_name, "--Plain DB Query----" . $query);
        }
//        $query = $this->makeQuery($query);

        if ($debug) {
            echo ' <br>[B]:' . $query;
        }
        if ($this->file_log_active) {
            $debug_backtrace = debug_backtrace();
            $this->common_file_logger($this->file_log_active, $this->file_database_logger_name, "--Query DB File----" . $debug_backtrace[0]['file'] . " Line--" . $debug_backtrace[0]['line']);
        }
        if (($result = mysqli_query($this->Connection, $query))) {


            if (($result !== false)) {

                $selection = array();
                $selection["\160ag\145\x73"] = 1;
                $selection["co\x75\156\164"] = mysqli_num_rows($result);
                $selection["sq\154"] = $query;
                $selection["i\164em\163"] = array();

                while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {

                    array_push($selection["\x69\x74\x65ms"], $row);
                }

                mysqli_free_result($result);

                return $selection;
            } else {
                return $result;
            }
        } else {

            $this->Error = mysqli_error($this->Connection);

            if ($this->file_log_active) {
                $debug_backtrace = debug_backtrace();
                $this->common_file_logger($this->file_log_active, $this->file_database_logger_name, "--Query DB Query----" . $query);
                $this->common_file_logger($this->file_log_active, $this->file_database_logger_name, "--Query DB File----" . $debug_backtrace[0]['file'] . " Line--" . $debug_backtrace[0]['line']);
                $this->common_file_logger($this->file_log_active, $this->file_database_logger_name, "--Query DB Error----" . $this->Error);
            }

            return false;
        }
    }

    function queryLimitedGetCount($query, $elementsPerPage) {
        if (isset($_SESSION['debugdb'])) {
            echo("\n\n\074\x21-\x2d\x20$query \x2d\x2d>\n\n");
        }

        $query = $this->makeQuery($query);
        $ret_array = $this->query($query);
        if ($elementsPerPage > 0) {
            $sql_count = "SELECT FOUND_ROWS()";
            $ret_count = $this->query($sql_count);
            $ret_count_num = $ret_count["items"][0]["FOUND_ROWS()"];
            $ret_pages_num = ceil($ret_count_num / $elementsPerPage);

            $ret_array["count"] = $ret_count_num;
            $ret_array["pages"] = $ret_pages_num;
        }
        return $ret_array;
    }

    function queryPage($query, $page, $elementsPerPage) {
        if (isset($_SESSION['debugdb'])) {
            echo("\n\n<!-\055\x20$query\x20\055\x2d\x3e\n\n");
        }
        if ($this->file_log_active) {
            $this->common_file_logger($this->file_log_active, $this->file_database_logger_name, "--Plain DB Query----" . $query);
        }
        $query = $this->makeQuery($query);

        if ($this->file_log_active) {
            $this->common_file_logger($this->file_log_active, $this->file_database_logger_name, "--Obfs DB Query----" . $query);
            $debug_backtrace = debug_backtrace();
            $this->common_file_logger($this->file_log_active, $this->file_database_logger_name, "--Query DB File----" . $debug_backtrace[0]['file'] . " Line--" . $debug_backtrace[0]['line']);
        }

        if (($result = mysql_query($query, $this->Connection))) {
            $i = 0;
            $elements = array();
            include 'DatabaseConn.Rcolumn.php';
            while ($i < mysql_num_fields($result)) {
                $meta = mysql_fetch_field($result);

                if ($meta) {
                    //array_push($elements, $meta->name);
                    $elements[] = array($meta->name, $this->reverseSelect($meta->name, $revTableColMapping));
                }

                $i++;
            }
            //print_r($elements);
            unset($revTableColMapping);

            $selection = array();
            $selection["\x63\157\x75\x6et"] = mysql_num_rows($result);
            $selection["\163\x71\154"] = $query;
            $selection["\x69\x74e\155\x73"] = array();
            $numRows = $selection["\143o\x75nt"];
            $pages = 1;
            $remainder = 0;

            if ($numRows > 0) {
                if ($numRows > $elementsPerPage) {
                    $pages = floor($numRows / $elementsPerPage);
                    $remainder = $numRows - (($pages) * $elementsPerPage);
                }

                if ($remainder > 0) {
                    $pages++;
                }
            }

            $firstItem = $page * $elementsPerPage;

            if ($numRows < $elementsPerPage) {
                $items = $numRows;
            } else {
                if (($page + 1 == $pages) && ($remainder > 0 )) {
                    $items = $remainder;
                } else {
                    $items = $elementsPerPage;
                }
            }

            if ($numRows == 0) {
                $items = $elementsPerPage;
            }

            $selection["\x70\x61g\x65\163"] = $pages;

            if (($firstItem + $items) <= $numRows) {
                for ($i = $firstItem; $i < $firstItem + $items; $i++) {
                    if (mysql_data_seek($result, $i)) {
                        $row = mysql_fetch_array($result);
                        $temp = array();

                        foreach ($elements as $value) {
                            //$temp[$value] = $row[$value];
                            $temp[$value[1]] = $row[$value[0]];
                        }

                        array_push($selection["ite\x6d\x73"], $temp);
                    }
                }
            }

            mysql_free_result($result);

            return $selection;
        } else {
            global $ErrorLog;
            $this->Error = mysql_error($this->Connection);
            $error_string = $this->Error . " on query '" . $query . "'";
            $pretty_error = "<p><b>E_DATABASE [DB]: </b>" . $error_string . "</p><p><i>ON =&gt; LINE: , FILE: , AT: " . date("Y-M-d H:m:s U") . "</i></p>";
            if (isset($ErrorLog)) {
                fwrite($ErrorLog, strip_tags(str_replace("&gt;", ">", preg_replace("/<p>(.*)<\/p>/Uis", "$1\n", $pretty_error))) . "\n");
            }
            if ($this->file_log_active) {
                $debug_backtrace = debug_backtrace();
                $this->common_file_logger($this->file_log_active, $this->file_database_logger_name, "--Query DB Query----" . $query);
                $this->common_file_logger($this->file_log_active, $this->file_database_logger_name, "--Query DB File----" . $debug_backtrace[0]['file'] . " Line--" . $debug_backtrace[0]['line']);
                $this->common_file_logger($this->file_log_active, $this->file_database_logger_name, "--Query DB Error----" . $this->Error);
            }
            //echo("\074!-\055 \123\x54AR\124 D\x42 \x45\x72r\x6fr\n\n");
            //echo($this->Error . "\n");
            //echo("\x71\165\145\162\x79\072\040" . $query . "\n");
            //echo("\n\105\x4e\104\040\104\x42 \105rro\162\040\055->\n");
            return false;
        }
    }

    function getPageLimited($table, $elements, $condition, $order, $groupby, $page, $elementsPerPage) {
        $countQuery = "S\105\x4cE\103T\040\103\x4f\x55N\x54\x28\x2a)\040A\123\040\x6e\165m\040F\x52OM\040$table";
        if ($condition != "") {
            $countQuery .= "\040\127\x48\105\x52\105\040\050" . $condition . "\x29";
        }

        if (isset($_SESSION['debugdb'])) {
            echo("\n\n<\041--\040$countQuery \055->\n\n");
        }
        $countQuery = $this->makeQuery($countQuery);
        if (($count = mysql_query($countQuery, $this->Connection))) {
            $query = "\x53E\114\x45\x43\x54\040";
            $isFirst = true;
            if ($elements == "") {
                $query .= "*";
            } else {
                foreach ($elements as $value) {
                    if ($isFirst) {
                        $query .= $value;
                        $isFirst = false;
                    } else {
                        $query .= "\x2c" . $value;
                    }
                }
            }

            $query .= "\x20\x46R\x4f\x4d\040$table";

            if ($condition != "") {
                $query .= "\x20\x57\x48\x45R\105\x20\050" . $condition . "\051";
            }

            if ($order != "") {
                $query .= "\040\x4f\122\104ER \102\131 " . $order;
            }

            if ($groupby != "") {
                $query .= " G\122OUP\x20\102Y\x20" . $groupby;
            }

            if (isset($_SESSION['debugdb'])) {
                echo("\n\n\x3c!\x2d-\x20$query\x20-->\n\n");
            }

            $rowCount = mysql_fetch_array($count);
            $selection = array();
            $selection["\143\x6f\165\156t"] = $rowCount["\x6eu\155"];
            $selection["\163\161\154"] = $query;
            $selection["i\x74\145\155s"] = array();
            $query .= " LI\x4dIT " . ($page * $elementsPerPage) . ",$elementsPerPage";

            if (isset($_SESSION['debugdb'])) {
                echo("\n\n\074!\055\055\040$query -\055\076\n\n");
            }

            if (($result = mysql_query($query, $this->Connection))) {
                $i = 0;
                $elements = array();

                while ($i < mysql_num_fields($result)) {
                    $meta = mysql_fetch_field($result);

                    if ($meta) {
                        array_push($elements, $meta->name);
                    }

                    $i++;
                }

                $numRows = $selection["\x63\157u\x6e\164"];
                $pages = 1;
                $remainder = 0;
                if ($numRows > 0) {
                    if ($numRows > $elementsPerPage) {
                        $pages = floor($numRows / $elementsPerPage);
                        $remainder = $numRows - (($pages) * $elementsPerPage);
                    }

                    if ($remainder > 0) {
                        $pages++;
                    }
                }

                $firstItem = $page * $elementsPerPage;

                if ($numRows < $elementsPerPage) {
                    $items = $numRows;
                } else {
                    if (($page + 1 == $pages) && ($remainder > 0 )) {
                        $items = $remainder;
                    } else {
                        $items = $elementsPerPage;
                    }
                }

                if ($numRows == 0) {
                    $items = 0;
                    $pages = 0;
                }

                $selection["\160ag\x65\x73"] = $pages;

                for ($i = 0; $i < $items; $i++) {
                    if (($row = mysql_fetch_array($result))) {
                        $temp = array();

                        foreach ($elements as $value) {
                            $temp[$value] = $row[$value];
                        }

                        array_push($selection["\x69\x74\145\155s"], $temp);
                    } else {
                        break;
                    }
                }

                mysql_free_result($result);

                return $selection;
            } else {
                global $ErrorLog;
                $this->Error = mysql_error($this->Connection);
                $error_string = $this->Error . " on query '" . $query . "'";
                $pretty_error = "<p><b>E_DATABASE [DB]: </b>" . $error_string . "</p><p><i>ON =&gt; LINE: , FILE: , AT: " . date("Y-M-d H:m:s U") . "</i></p>";
                if (isset($ErrorLog)) {
                    fwrite($ErrorLog, strip_tags(str_replace("&gt;", ">", preg_replace("/<p>(.*)<\/p>/Uis", "$1\n", $pretty_error))) . "\n");
                }
                //echo("\074!-\055 \123\x54AR\124 D\x42 \x45\x72r\x6fr\n\n");
                //echo($this->Error . "\n");
                //echo("\x71\165\145\162\x79\072\040" . $query . "\n");
                //echo("\n\105\x4e\104\040\104\x42 \105rro\162\040\055->\n");
                return false;
            }
        } else {
            $this->Error = mysql_error($this->Connection);
            global $ErrorLog;
            $this->Error = mysql_error($this->Connection);
            $error_string = $this->Error . " on query '" . $query . "'";
            $pretty_error = "<p><b>E_DATABASE [DB]: </b>" . $error_string . "</p><p><i>ON =&gt; LINE: , FILE: , AT: " . date("Y-M-d H:m:s U") . "</i></p>";
            if (isset($ErrorLog)) {
                fwrite($ErrorLog, strip_tags(str_replace("&gt;", ">", preg_replace("/<p>(.*)<\/p>/Uis", "$1\n", $pretty_error))) . "\n");
            }
            //echo("\074!-\055 \123\x54AR\124 D\x42 \x45\x72r\x6fr\n\n");
            //echo($this->Error . "\n");
            //echo("\x71\165\145\162\x79\072\040" . $query . "\n");
            //echo("\n\105\x4e\104\040\104\x42 \105rro\162\040\055->\n");
            return false;
        }
    }

    function queryQueryLimited($query, $page, $elementsPerPage, $field_distinct = "") {
        if (strpos($query, "G\x52O\x55P\040\x42\x59") > 0) {
            $start = strpos($query, "\x20\106\122\x4f\x4d ");
            $end = strpos($query, "\107RO\x55P\x20BY");

            if ($field_distinct != "") {
                if ($end > 0) {
                    $countQuery = "\123\x45\114E\103T\040CO\x55NT(\x44\x49\x53TI\x4e\103\x54 $field_distinct\x29\x20\101\x53\040\x6eum" . substr($query, $start, $end - $start);
                } else {
                    $countQuery = "\123E\114\x45\103\124 \103O\x55\116\124\050DI\123\x54\111\116C\x54\040$field_distinct) \x41\123 n\165\155" . substr($query, $start);
                }
            } else {
                if ($end > 0) {
                    $countQuery = "S\x45\114\105C\x54\x20\x43\117\x55\116\x54\x28\x2a)\040\x41S\x20num" . substr($query, $start, $end - $start);
                } else {
                    $countQuery = "\123\x45\114\105\103\124 \x43\117U\116T(\052\051 A\123\x20\x6eu\155" . substr($query, $start);
                }
            }
        } else {
            $start = strpos($query, "\040\x46R\117\x4d\x20");
            $end = strpos($query, "OR\104\x45\122\040BY");

            if ($end > 0) {
                $countQuery = "\123EL\x45\103\124\040\103\x4fU\x4e\x54(\x2a)\x20\x41\x53 \156u\x6d" . substr($query, $start, $end - $start);
            } else {
                $countQuery = "S\x45\114EC\x54\x20\x43\x4f\x55\x4e\x54\x28*\051\x20\x41\123\040\x6eum" . substr($query, $start);
            }
        }

        if (isset($_SESSION['debugdb'])) {
            echo("\n\n\074\041\055\055\040$countQuery\040--\076\n\n");
        }

        $countQuery = $this->makeQuery($countQuery);
        if (($count = mysql_query($countQuery, $this->Connection))) {
            $rowCount = mysql_fetch_array($count);
            $selection = array();
            $selection["\143\157\165n\x74"] = $rowCount["\156\165m"];
            $selection["sq\x6c"] = $query;
            $selection["\x69\x74e\x6ds"] = array();
            $query .= "\040\x4cI\x4dI\124\x20" . ($page * $elementsPerPage) . "\054$elementsPerPage";

            if (isset($_SESSION['debugdb'])) {
                echo("\n\n<!\055-\040$query -\055\076\n\n");
            }

            $query = $this->makeQuery($query);
            if (($result = mysql_query($query, $this->Connection))) {
                $i = 0;
                $elements = array();

                while ($i < mysql_num_fields($result)) {
                    $meta = mysql_fetch_field($result);

                    if ($meta) {
                        array_push($elements, $meta->name);
                    }

                    $i++;
                }

                $numRows = $selection["\x63o\165n\164"];
                $pages = 1;
                $remainder = 0;

                if ($numRows > 0) {
                    if ($numRows > $elementsPerPage) {
                        $pages = floor($numRows / $elementsPerPage);
                        $remainder = $numRows - (($pages) * $elementsPerPage);
                    }

                    if ($remainder > 0) {
                        $pages++;
                    }
                }

                $firstItem = $page * $elementsPerPage;

                if ($numRows < $elementsPerPage) {
                    $items = $numRows;
                } else {
                    if (($page + 1 == $pages) && ($remainder > 0 )) {
                        $items = $remainder;
                    } else {
                        $items = $elementsPerPage;
                    }
                }

                if ($numRows == 0) {
                    $items = 0;
                    $pages = 0;
                }

                $selection["p\x61\x67\145\163"] = $pages;

                for ($i = 0; $i < $items; $i++) {
                    if (($row = mysql_fetch_array($result))) {
                        $temp = array();

                        foreach ($elements as $value) {
                            $temp[$value] = $row[$value];
                        }

                        array_push($selection["\151\164\145\x6d\163"], $temp);
                    } else {
                        break;
                    }
                }

                mysql_free_result($result);

                return $selection;
            } else {
                global $ErrorLog;
                $this->Error = mysql_error($this->Connection);
                $error_string = $this->Error . " on query '" . $query . "'";
                $pretty_error = "<p><b>E_DATABASE [DB]: </b>" . $error_string . "</p><p><i>ON =&gt; LINE: , FILE: , AT: " . date("Y-M-d H:m:s U") . "</i></p>";
                if (isset($ErrorLog)) {
                    fwrite($ErrorLog, strip_tags(str_replace("&gt;", ">", preg_replace("/<p>(.*)<\/p>/Uis", "$1\n", $pretty_error))) . "\n");
                }
                //echo("\074!-\055 \123\x54AR\124 D\x42 \x45\x72r\x6fr\n\n");
                //echo($this->Error . "\n");
                //echo("\x71\165\145\162\x79\072\040" . $query . "\n");
                //echo("\n\105\x4e\104\040\104\x42 \105rro\162\040\055->\n");
                return false;
            }
        } else {
            global $ErrorLog;
            $this->Error = mysql_error($this->Connection);
            $error_string = $this->Error . " on query '" . $query . "'";
            $pretty_error = "<p><b>E_DATABASE [DB]: </b>" . $error_string . "</p><p><i>ON =&gt; LINE: , FILE: , AT: " . date("Y-M-d H:m:s U") . "</i></p>";
            if (isset($ErrorLog)) {
                fwrite($ErrorLog, strip_tags(str_replace("&gt;", ">", preg_replace("/<p>(.*)<\/p>/Uis", "$1\n", $pretty_error))) . "\n");
            }
            //echo("\074!-\055 \123\x54AR\124 D\x42 \x45\x72r\x6fr\n\n");
            //echo($this->Error . "\n");
            //echo("\x71\165\145\162\x79\072\040" . $query . "\n");
            //echo("\n\105\x4e\104\040\104\x42 \105rro\162\040\055->\n");
            return false;
        }
    }

    function xmlquery($query) {
        if (isset($_SESSION['debugdb'])) {
            echo("\n\n\x3c\x21-- $query\040\055\055>\n\n");
        }

        $xml = "\x3c\151\164\145\x6d\x73\x3e\n";
        $query = $this->makeQuery($query);
        if (($result = mysql_query($query))) {
            $i = 0;
            $elements = array();

            if (($result != 1) && ($result != 2)) {
                while ($i < mysql_num_fields($result)) {
                    $meta = mysql_fetch_field($result);

                    if ($meta) {
                        array_push($elements, $meta->name);
                    }

                    $i++;
                }

                while ($row = mysql_fetch_array($result)) {
                    $xml .= "\t\074\151\x74\x65m\076\n";
                    $temp = array();

                    foreach ($elements as $value) {
                        $xml .= "\t\t<" . $value . ">\x3c\x21[\103\104\101T\101\133" . urldecode($row[$value]) . "\135\135\076</" . $value . ">\n";
                    }

                    $xml .= "\t\x3c\057i\164\145\155\076\n";
                }

                mysql_free_result($result);
            }
        } else {
            $this->Error = mysql_error($this->Connection);
            $xml .= "\t\074err\157\x72><\x21\133\103\x44AT\101[\n";
            $xml .= $this->Error;
            $xml .= " ,q\x75\145ry\x3a " . $query . "\x3cb\x72 \x2f>\n";
            $xml .= "\135\135>\074/\145\162\x72\x6f\162>\n";
        }

        $xml .= "<\057i\x74e\155s>\n";

        return $xml;
    }

    function common_file_logger($active = false, $file_name = "", $data = "", $mode = "a+") {
        if ($active == true && !empty($file_name) && !empty($data)) {

            $error_log_file = fopen(COMP_PATH . "/logs/$file_name", $mode);

            fwrite($error_log_file, PHP_EOL . "---" . date("Y-m-d H:i:s") . "---" . $data . PHP_EOL);
            fclose($error_log_file);
        }
    }

}

?>
