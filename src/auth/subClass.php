<?php
set_time_limit(300);
include(__DIR__ .'/DataTableScada.php');

class SCADA extends DataTableScada
{
    public $gConn;
    public $MNEMO;
    private $Meter;
    private $thead; 
    private $REF_TAG;
    private $gGroupId;
    private $gUserName;
    private $tableGMDR;
    private $tableName;
    private $dtResultGMDR;
    private $dtResultSCADA;
    private $drResultGMDR;
    private $TempGMDR = "";
    private $ColumnDisplay;
    private $drResultSCADA;
    private $TempSCADA = "";
    private $tagdata = array();
    private $newarr = array();
    private $newarrGMDR = array();
    private $GMDRHeader = array();
    private $dbSource = 'pmisdwh-scan.pttplc.com:1521/PMISHS';
    private $dbUser = 'chonburi';
    private $dbPassword = 'chonburi';
    public static function OpenDebug(){
        error_reporting(-1);
        ini_set('display_errors', 'On');
      }
    public function openDB()
    {
        $this->gConn = oci_connect(
            $this->dbUser,
            $this->dbPassword,
            $this->dbSource //'(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST='.$dbSource.')(PORT=1521))(CONNECT_DATA=(SERVICE_NAME='.$dbName.')))'
        ) or die("Can't connect database");
    }
    private function closeDT($dtResult)
    {
        return oci_free_statement($this->dtResult);
    }
    private function closeDB()
    {
        return oci_close($this->gConn);
    }
    public function execu($sql)
    {
        SCADA::openDB();
        $tempstr = substr($sql, 0, 6);
        $checkDrop = strstr($sql, "drop");
        
        if ($this->gConn == "") {
            $this->dtResult = null;
            echo "Database connection hasn't been initialized";
        } else if ($tempstr == "SELECT" AND $checkDrop) {
            $this->dtResult = null;
            echo "Illegal DROP statement";
        } else {
            $this->dtResult = oci_parse($this->gConn, $sql);
            $oci_execute_result = oci_execute($this->dtResult);
            if (!$oci_execute_result) {
                $log_filepath = dirname($_SERVER['SCRIPT_FILENAME']) . '../../log/' . basename($_SERVER['SCRIPT_FILENAME']) . '.' . date('Y-m-d') . '.txt';
                file_put_contents($log_filepath, '[' . date('Y-m-d H:i:s') . '] ' . $sql . "\n", FILE_APPEND);
                header("Location: ../src/Index.php?missing=error");
            }
        }
        return $this->dtResult;
    }
    public function fetch_row($dtResult)
    {
        return oci_fetch_array($dtResult);
    }
    public function checkRoles($roles, $return = false)
    {
        $this->gUserName = $_SESSION["USER"];
        $this->gGroupId = $_SESSION["GROUP"];
        if ($this->gUserName != "" && $this->gGroupId == "") {
            $this->gGroupId = SCADA::loadUserData($this->gUserName);
            $_SESSION["GROUP"] = $this->gGroupId;
        }
        if ($this->gUserName == "" || $this->gGroupId == "" || ($roles != "" && strstr($roles, $this->gGroupId) == 0)) {
            if ($return)
                return false;
            else
                echo 'error';
        }
        return true;
    }

    private function loadUserData($userName)
    {
        $sql = "select * from SYS_USERS where USER_NAME ='$userName'";
        $dt = SCADA::execu($sql);
        if ($dr = SCADA::fetch_row($dt)) {
            $this->groupId = $dr["GROUP_ID"];
        }
        $this->closeDT($dt);
        return ($this->groupId);
    }
    public function loadComboSCADA($sql, $fieldDesc, $fieldValue, $tempSelectVal)
    {
        $this->TempSCADA = "<option value='' selected>-- Choose a Template --</option>";
        $this->dtResultSCADA = SCADA::execu($sql);
        while ($this->drResultSCADA = SCADA::fetch_row($this->dtResultSCADA)) {
            $value = htmlspecialchars($this->drResultSCADA[$fieldValue]);
            $desc = htmlspecialchars($this->drResultSCADA[$fieldDesc]);
            $selected = ($value === $tempSelectVal) ? ' selected' : ''; 
            $this->TempSCADA .= "<option value='$value'$selected>$desc</option>";
        }
        SCADA::closeDT($this->dtResultSCADA);
        return ($this->TempSCADA);
    }
    public function loadComboNullSCADA($sql, $fieldDesc, $fieldValue, $tempSelectVal)
    {
        return ("<option value=''></option>" . SCADA::loadComboSCADA($sql, $fieldDesc, $fieldValue, $tempSelectVal));
    }

    public function loadComboGMDR($sql, $fieldDesc, $fieldValue)
    {
        $this->TempGMDR = "<option value='' selected>-- Please choose a Meter Name --</option>";
        $this->TempGMDR .= "<option value='ALLTAG'>ALL TAG</option>";
        $this->dtResultGMDR = SCADA::execu($sql);
        while ($this->drResultGMDR = SCADA::fetch_row($this->dtResultGMDR)) {
            $value = htmlspecialchars($this->drResultGMDR[$fieldValue]);
            $desc = htmlspecialchars($this->drResultGMDR[$fieldDesc]);
            $this->TempGMDR .= "<option value='$value'>$desc</option>";
        }
        SCADA::closeDT($this->dtResultGMDR);
        return ($this->TempGMDR);
    }
    public function loadComboNullGMDR($sql, $fieldDesc, $fieldValue)
    {
        return ("<option value=''></option>" . SCADA::loadComboGMDR($sql, $fieldDesc, $fieldValue));
    }
    public function loadComboRTU($sql, $fieldDesc, $fieldValue)
    {
        
        $TempRTU = "<option value='' selected>-- Please choose RTU --</option>";
        $dtResultRTU = SCADA::execu($sql);
        $n = 0;
        while ($drResultRTU = SCADA::fetch_row($dtResultRTU)) {
          $TempRTU .= '<option value="' . htmlspecialchars($drResultRTU[$fieldValue]) . '">' . htmlspecialchars($drResultRTU[$fieldDesc]) . '</option>';
        }
        SCADA::closeDT($dtResultRTU);
        return ($TempRTU);
    }
    public function loadComboNullRTU($sql, $fieldDesc, $fieldValue)
    {
        return ("<option value=''></option>" . SCADA::loadComboRTU($sql, $fieldDesc, $fieldValue));
    }
    public function loadComboTagconfig($sql, $fieldDesc, $fieldValue)
    {
        $TempTagconfig = "<option value='' selected>-- Please choose an Meter Name --</option>";
        $dtResultTagconfig = SCADA::execu($sql);
        $n = 0;
        while ($drResultGMDR = SCADA::fetch_row($dtResultTagconfig)) {
          $TempTagconfig .= '<option value=' . htmlspecialchars($drResultGMDR["$fieldValue"]) . '>' . htmlspecialchars($drResultGMDR["$fieldDesc"]) . '</option>';
        }
        SCADA::closeDT($dtResultTagconfig);
        return ($TempTagconfig);
    }
    public function loadComboNullTagconfig($sql, $fieldDesc, $fieldValue)
    {
        return ("<option value=''></option>" . SCADA::loadComboTagconfig($sql, $fieldDesc, $fieldValue));
    }
    public static function CheckDurationMoreThan($start,$end){
        $start_date = new DateTime($start);
        $end_date = new DateTime($end);
        $interval = $start_date->diff($end_date);
        $days = $interval->days;
        if ($days <= 35) {  return true; } else { header("Location: ./Index.php?Morethan=35days"); }
    }
    public static function CheckDurationMoreThan1min($start,$end){
        $start_date = new DateTime($start);
        $end_date = new DateTime($end);
        $interval = $start_date->diff($end_date);
        $days = $interval->days;
        if ($days >= 3) {  return true; } else { return false; }
    }
    public function genTagList($tmplID)
    {
        SCADA::openDB();
        $valtmp = intval($tmplID);
        $resMeter = SCADA::execu("SELECT TEMPLATE_DETAILS.*,TYPE_VAL.*
                                  FROM TYPE_VAL
                                  INNER JOIN TEMPLATE_DETAILS
                                  ON TYPE_VAL.CODE_TYPE = TEMPLATE_DETAILS.CODE_TYPE
                                  WHERE TMPL_ID = $valtmp ORDER BY COL_ID");
        while ($rowMeter = SCADA::fetch_row($resMeter)) {
            $e = array();
            $e['TAGNAME'] = trim($rowMeter['TAGNAME']);
            $e['CODE_TYPE'] = trim($rowMeter['CODE_TYPE']);
            $e['TYPE_VALUE'] = trim($rowMeter['TYPE_VALUE']);
            $this->tagdata[] = $e;
        }
        return $this->tagdata;
    }


    public function login($username, $password)
    {
        $client = new SoapClient("http://plwebapp2.ptt.corp/ADHelper/ws.asmx?WSDL");
        $params = array(
            'username' => $username,
            'password' => $password
        );
        $result = $client->__soapCall("login", array('parameters' => $params));
        return $result->loginResult;
    }

    public function isPersonelUnderUnit($username, $unitcode)
    {
        $params = array(
            'fn' => 'isPersonelUnderUnit',
            'code' => $username,
            'unitcode' => $unitcode
        );
        $json = file_get_contents('http://plwebapp2.ptt.corp/ADHelper/php/json.php?' . http_build_query($params));
        return json_decode($json);
    }
    public function CheckLogin($username, $password)
    {
        SCADA::openDB();
        $_SESSION['USER'] = '';
        $_SESSION['GROUP'] = '';
        $sql = "SELECT * FROM SYS_USERS WHERE USER_NAME = :bv_username";
        $stid = oci_parse($this->gConn, $sql);
        oci_bind_by_name($stid, ":bv_username", $username);
        oci_execute($stid);
        if ($row = oci_fetch_assoc($stid)) {
            if (md5($password) == $row['PASSWORD'] || SCADA::login($username, $password)) {
                $_SESSION['USER'] = $row['USER_NAME'];
                $_SESSION['GROUP'] = $row['GROUP_ID'];
            }
        }
         if ($_SESSION['USER'] === '') {
            if (SCADA::login($username, $password)) {
                if (SCADA::isPersonelUnderUnit($username, '80000512')) {
                    $_SESSION['USER'] = $username;
                    $_SESSION['GROUP'] = '2';
                } else if (SCADA::isPersonelUnderUnit($username, '80000510')) {
                    $_SESSION['USER'] = $username;
                    $_SESSION['GROUP'] = '1';
                } else if (substr($username, 0, 2) === "cg" || substr($username, 0, 2) === "ch") {
                    echo $_SESSION['USER'] = $username;
                    $_SESSION['GROUP'] = '1';
                } else {
                    header("Location: ../../Login.php?Insuff=Insuff");
                    exit();
                }
            } else {
                header("Location: ../../Login.php?Incor=Incor");
                exit();
            }
        }
        $sql = "INSERT INTO LOGIN_USER_LOG (USER_NAME) VALUES (:bv_username)";
        $stid = oci_parse($this->gConn, $sql);
        oci_bind_by_name($stid, ":bv_username", $username);
        oci_execute($stid);
        header("Location: ../Index.php");
    }
    public function getMNEMO($cboTemplate, $TagRTU, $chtag = null) {
    {
        if($chtag === 'Add' && $cboTemplate === '') {
            $e = 1;
            foreach($TagRTU as $key => $value) {
                if($key === $key){
                    $splitkey = explode("/", $key);               
                    $this->MNEMO .= "($this->tableName.MNEMO = '" . $splitkey[0] . "' AND TYPE_VAL.TYPE_VALUE = '" . trim($value) . "')";
                    if ($e < count($TagRTU))
                        $this->MNEMO .= " OR ";
                    $e++;
                }
            }
            return $this->MNEMO; 
        }else if($chtag === '' && $cboTemplate !== ''){   
            $n = 1;
            $GetTag = SCADA::genTagList($cboTemplate);
            foreach ($GetTag as $key => $value) {
                $this->MNEMO .= "($this->tableName.MNEMO = '" . $value['TAGNAME'] . "' AND $this->tableName.CODE_TYPE = " . $value['CODE_TYPE'] . ")";
                if ($n < count($GetTag))
                    $this->MNEMO .= " OR ";
                $n++;
            }
            return $this->MNEMO;  
        }
    }
    }
    public function TABLE_SCADA(
        $period = null,$FromdateXFromtime = null,$TodateXToTime = null,$cboTemplate,
        $iDisplay = null,$iCodition = null,$TagRTU = null, $chtag = null
    ) {
        switch (trim($period)) {
            case "1 Minute":
                $this->tableName = "ARCH_1MN";
                break;
            case "10 Minute":
                $this->tableName = "ARCH_10MN";
                break;
            case "Hour":
                $this->tableName = "ARCH_HOUR";
                break;
            case "Day":
                $this->tableName = "ARCH_DAY";
                break;
        }
        
        $sqlORA = 'SELECT ' . $this->tableName . '.*,TYPE_VAL.*,DUMP_UNIT_HIST.* 
        FROM ' . $this->tableName . ' 
        INNER JOIN TYPE_VAL
        ON ' . $this->tableName . '.CODE_TYPE = TYPE_VAL.CODE_TYPE
        INNER JOIN DUMP_UNIT_HIST 
        ON ' . $this->tableName . '.MNEMO = DUMP_UNIT_HIST.TAGNAME
        WHERE (' . SCADA::getMNEMO($cboTemplate,$this->ConvertData($TagRTU),$chtag) . ') AND 
        ("DATE" BETWEEN TO_TIMESTAMP(' . "'$FromdateXFromtime'" . ', ' . "'YYYY-MM-DD HH24:MI'" . ') AND 
        TO_TIMESTAMP(' . "'$TodateXToTime'" . ', ' . "'YYYY-MM-DD HH24:MI'" . '))';
        if(SCADA::GetHeaderLook(SCADA::ConvertData($TagRTU)) == true ){
            return SCADA::getLookTag($TagRTU,SCADA::execu($sqlORA),$iDisplay);
        }
    }
    private function GetHeaderLook($arr){
        $e = 1;
        $str = "";
        foreach($arr as $key => $value) {
            if($key === $key){
                $splitkey = explode("/", $key);
                $tag[] = $splitkey[0];               
                    $str .= "(TAGNAME = '" . $splitkey[0] . "')";
                if ($e < count($arr)) $str .= " OR ";
                $e++;
            }
        }
        $sql = SCADA::execu("SELECT * FROM DUMP_UNIT_HIST
        WHERE $str");
        while ($row = SCADA::fetch_row($sql)) { 
            $this->thead[$row['TAGNAME']] = $row['DESCRIPTION'] ." => ". $row['UNIT']; 
        }
        foreach($tag as $keyTag => $valueTag) {
            if(!isset($this->thead[$valueTag])) 
                $this->thead[$valueTag] = '<a style="color:#FF8C00;">No Description</a>'." => ";
        }
        return true;
    }
    private static function ConvertData($string){
        $str = trim(str_replace(PHP_EOL, ' ', $string));
        $array = explode(" ", $str);
        if(is_array($array) && $array !== null){
            $new_array = array();
            for ($i = 0; $i < count($array); $i += 2) {
                $key = $array[$i];
                $value = str_replace("(", "", $array[$i+1]);
                $value = str_replace(")", "", $value);
                if($key === $key && $key !== null)
                    $new_array[$key."/".$value] = $value;
                else
                    $new_array[$key] = $value;
            }
            return $new_array;
        }

    }

    private function getLookTag($string,$data,$iDisplay){  
        $null = array();    $raw = array();
        $arr = SCADA::ConvertData($string);       
        while ($row = SCADA::fetch_row($data)) {
            $key = $row['MNEMO']." => ".trim($row['TYPE_VALUE']);
            if (!isset($raw[$key]))  $raw[$key] = []; 
            $raw[$key][self::DateFormat($row['DATE'])] = $row['VALUE'];
        }
        foreach($raw as $key1 => $value1){ 
            foreach($value1 as $k => $v){ 
                $null[$k] = null; 
            } 
        }
        foreach($arr as $tag => $val){
            $splt = explode("/",$tag);
            $keystr = trim($splt[0])." => ".trim($splt[1]);
            if(isset($raw[$keystr]))
                $this->newarr[$keystr] = $raw[$keystr];
            else
                $this->newarr[$keystr] = $null;
        }
        $table = "<thead><tr><th id='first'>DATE TIME</th>";
        foreach($this->newarr as $tag => $array) {
            $spl = explode(" => ", $tag);
            foreach($this->thead as $key => $value) {
                $val = explode(" => ", $value);
                if($spl[0] == $key){
                    switch ($iDisplay) {
                        case "Tagname":
                            $table .= "<th>" . $spl[0] . "</br>". $val[1] . "<a style='color:#1E90FF;'> (" . $spl[1] . ")</a>"."</th>";
                        break;
                        case "Description":
                            $table .= "<th>" . $val[0] . "</br>". $val[1] . "<a style='color:#1E90FF;'> (" . $spl[1] . ")</a>"."</th>";
                        break;
                        case "Tagnameanddescription":
                            $table .= "<th>" . $spl[0] ."</br>" . $val[1] ."<a style='color:#1E90FF;'> (" . $spl[1] . ")</a>"."</br>".$val[0]."</th>";
                        break;
                    }
                }

            }
        }
        $table .= "</tr></thead><tbody>".SCADA::CreateTbody($this->newarr)."</tbody>";
        return $table;
    }
    private  function CreateTbody($arr)
    {
        $e = 0;
        $tr = "";
        foreach ($arr as $keyCol => $valueCol) {
            if ($e++ == 1) {
                foreach ($valueCol as $datetime => $valueRow) {
                    $tr .= "<tr role='row'><td>" . $datetime . "</td>";
                    foreach ($arr as $keyCol1 => $valueCol1) {
                        foreach ($valueCol1 as $datetime1 => $value) {
                            if ($datetime1 === $datetime) {
                                $tr .= "<td>" . $value . "</td>";
                            }
                        }
                    }
                    $tr .= "</tr>";
                }
            }
        }
        //$this->getFooter();
        return $tr;
    }
    private function SETDUPLICATE($e)
    {
        return array_unique($e);
    }
    private function getTdDuplicate($operat)
    {
        $tr = '';
        if ($operat == true) {
            foreach ($this->GMDRHeader as $keyCol => $valueCol) {
                $tr .= "<td style='background-color:#E74C3C;'></td>";
            }
        } else {
            foreach ($this->GMDRHeader as $keyCol => $valueCol) {
                $tr .= "<td></td>";
            }
        }
        return $tr;
    }
    
    private  function CreateTbodyGMDR($arr, $Meter)
    {
        $tr = "";
        $n = 0;
        $duplicates = array();
        if ($Meter == 'ALLTAG') {
            foreach ($arr as $key => $value) {
                foreach ($value as $keyCol => $valueCol) {
                    if ($n++ == 1) {
                        foreach ($valueCol as $datetime => $valuex) {
                            foreach ($arr as $key1 => $value1) {
                                foreach ($value1 as $tag => $valueGMDR) {
                                    foreach ($valueGMDR as $date => $valueGMDR) {
                                        if ($date === $datetime) {
                                            if ($valueGMDR === $valueGMDR && $valueGMDR !== null && $valueGMDR != 0) {
                                                $duplicates[$datetime][] = $valueGMDR;
                                                continue;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        $e = array();
                        foreach ($duplicates as $keyRow => $valueRow) {
                            for ($i = 0; $i < count($valueRow); $i++) {
                                if ($valueRow[1] == $valueRow[$i]) {
                                    $e[$keyRow] = $valueRow[$i];
                                    continue;
                                }
                            }
                        }
                        $dup = $this->SETDUPLICATE($e);
                        foreach ($e as $date => $val) {
                            if (array_key_exists($date, $dup)) {
                                $tr .= "<tr><td>" . $date . "</td>";
                                $tr .= $this->getTdDuplicate(false);
                            } else {
                                $tr .= "<tr><td style='background-color:#E74C3C;color:#fff;'>" . $date . "</td>";
                                $tr .= $this->getTdDuplicate(true);
                            }
                        }
                        $tr .= "</tr>";
                    }
                }
            }
        } else {
            foreach ($arr as $key => $value) {
                foreach ($value as $keyCol => $valueCol) {
                    if ($n++ == 1) {
                        foreach ($valueCol as $datetime => $valuex) {
                        
                            $tr .= "<tr><td>" . $datetime . "</td>";
                            foreach ($arr as $key1 => $value1) {
                                foreach ($value1 as $tag => $value_GMDR) {
                                  if(isset($value_GMDR[$datetime])){
                                     foreach ($value_GMDR as $date => $valueGMDR) {
                                        if ($date === $datetime) {
                                            $tr .= "<td>" . $valueGMDR . "</td>";
                                        }
                                    }
                                  }else{
                                    $tr .= "<td style='background-color:#E74C3C;color:#fff;'>N/A</td>";
                                  }
                                }
                            }
                            $tr .= "</tr>";
                        }
                    }
                }
            }
            $this->getFooter();
        }

        return $tr;
    }


    private function ConvertFloatingPoint($val = null, $tag = null)
    {
        $col = $this->GMDRHeader;
        for ($b = 0; $b < count($col); $b++) {
            if ($tag === $col[$b]['MNEMO']) {
                $value = number_format(floatval($val), $col[$b]['PRECISION'], '.', '');
            }
        }
        return $value;
    }
    private function CreateTableGMDR($res, $Meter)
    {
        $raw_data = array();
        $arr = array();
        $n = 0;
        $col = $this->GMDRHeader;
        function SortGMDRCallBack($a, $b)
        {
            if (isset($a) && isset($b) &&
                $a !== null && $b !== null
            ) return intval($a["SORT_ORDER"]) - intval($b["SORT_ORDER"]);
        }
        if (isset($col) && $col !== null) usort($col, "SortGMDRCallBack");
        while ($row = SCADA::fetch_row($res)) {
            if (!isset($raw_data[$row['MNEMO']])) $raw_data[$row['MNEMO']] = [];
            $raw_data[$row['MNEMO']][self::DateFormat($row['DATE'])] = $this->ConvertFloatingPoint($row['VALUE'], $row['MNEMO']);
        }
        foreach ($raw_data as $k => $v) {
            if ($n++ == 1) {
                foreach ($v as $k2 => $v2) {
                    $arr[$k2] = null;
                }
            }
        }
        for ($u = 0; $u < count($col); $u++) {
            if (isset($raw_data[$col[$u]['MNEMO']])) {
                $this->newarrGMDR[$col[$u]['SORT_ORDER']][$col[$u]['MNEMO']] = $raw_data[$col[$u]['MNEMO']];
            } else {
                $this->newarrGMDR[$col[$u]['SORT_ORDER']][$col[$u]['MNEMO']] = $arr;
            }
        }
        return $this->CreateTbodyGMDR($this->newarrGMDR, $Meter);
    }

    private function GetHeaderGMDR($Meter, $REF)
    {
        if ($Meter == 'ALLTAG') {
            $reqheader = SCADA::execu("SELECT * FROM $this->REF_TAG WHERE MONITOR_FLAG = 1");
        } else {
            $reqheader = SCADA::execu('SELECT * FROM ' . $REF . ' WHERE FC_NAME = ' . "'$Meter'" . '');
        }
        while ($row = SCADA::fetch_row($reqheader)) {
            $e = array();
            if ($Meter == 'ALLTAG') {
                $e['TAG_HEADER'] = trim($row['FC_NAME']) . "<BR><p class=thder >" . trim($row['TAG_HEADER']) . "</p>";
            } else {
                $e['TAG_HEADER'] = trim($row['TAG_HEADER']);
            }
            $e['MNEMO'] = trim($row['TAGNAME']);
            $e['DESCRIPTION'] = trim($row['DESCRIPTION']);
            $e['PRECISION'] = trim($row['PRECISION']);
            $e['SORT_ORDER'] = trim($row['SORT_ORDER']);
            $this->GMDRHeader[] = $e;
        }
        function SortHeaderGMDRCallBack($a, $b)
        {
            if (isset($a) && isset($b) &&
                $a !== null && $b !== null
            ) return intval($a["SORT_ORDER"]) - intval($b["SORT_ORDER"]);
        }
        if (isset($this->GMDRHeader) && $this->GMDRHeader !== null) usort($this->GMDRHeader, "SortHeaderGMDRCallBack");
        return ($this->GMDRHeader);
    }
    private static function DateFormat($date)
    {
        $date = DateTime::createFromFormat('d-M-y h.i.s.u A', $date);
        return $date->format('d-m-y') . ' ' . date("H:i", strtotime($date->format('h.i A')));
    }
    public function TABLE_GMDR(
        $table = null,
        $Meter = null,
        $FromtimeGMDR = null,
        $ToTimeGMDR = null
    ) {
        $sqlORA = '';
        switch ($table) {
            case "Hour":
                $this->tableGMDR = 'HOURLY_GMDR';
                $this->REF_TAG = 'REF_HOURLY_GMDR_TAG';
                break;
            case "Day";
                $this->tableGMDR = 'DAILY_GMDR';
                $this->REF_TAG = 'REF_DAILY_GMDR_TAG';
                break;
        }
        $this->Meter = $Meter;
        if ($Meter == 'ALLTAG') {
            $sqlORA = '
                SELECT ' . $this->tableGMDR . '.*
                FROM ' . $this->tableGMDR . '
                WHERE ("DATE" BETWEEN TO_DATE(' . "'$FromtimeGMDR'" . ', ' . "'YYYY-MM-DD HH24:MI'" . ') 
                AND TO_DATE(' . "'$ToTimeGMDR'" . ', ' . "'YYYY-MM-DD HH24:MI'" . ')) 
                AND (MNEMO in (SELECT TAGNAME FROM ' . $this->REF_TAG . ' WHERE MONITOR_FLAG = 1)) ORDER BY "DATE"';
        } else {
            $sqlORA = '
                SELECT ' . $this->tableGMDR . '.*
                FROM ' . $this->tableGMDR . '
                WHERE ' . $this->tableGMDR . '.FC_NAME = ' . "'$Meter'" . '
                AND ("DATE" BETWEEN TO_DATE(' . "'$FromtimeGMDR'" . ', ' . "'YYYY-MM-DD HH24:MI'" . ') 
                AND TO_DATE(' . "'$ToTimeGMDR'" . ', ' . "'YYYY-MM-DD HH24:MI'" . '))';
        }
        $getHeaderdata = SCADA::GetHeaderGMDR($Meter, $this->REF_TAG);
        $getBodydata = SCADA::CreateTableGMDR(SCADA::execu($sqlORA), $Meter);
        $Table = "
                    <thead>
                        <tr>
                            <th id='first'>DATE TIME</th>";
        for ($i = 0; $i < count($getHeaderdata); ++$i) {
            $Table .= "<th>" . $getHeaderdata[$i]['TAG_HEADER'] . "</th>";
        }
        $Table .= "</tr></thead><tbody>$getBodydata</tbody>";
        return $Table;
    }
    private static function CheckdataNull($arr){
        foreach ($arr as $key => $value) {
           if(empty($value)){
                return false;   
           }else{
                return true;
           }
        }
    }
    public function getFooter()
    {
        $tr = "";
        if (self::CheckdataNull($this->newarr) === true) {
            $tr .= "<div id='foot'>
                <tr id='mma'>
                        <td>MAX</td>";
            foreach ($this->newarr as $Maxkey => $Maxvalue) {
                $tr .= "<td>" . max($Maxvalue) . "</td>";
            }
            $tr .= "</tr>";
            $tr .= "<tr id='mma'>
                        <td>MIN</td>";
            foreach ($this->newarr as $Minkey => $Minvalue) {
                $tr .= "<td>" . min($Minvalue) . "</td>";
            }
            $tr .= "</tr>";
            $tr .= "<tr id='mma'>
                        <td>AVG</td>";
            foreach ($this->newarr as $Avgkey => $Avgvalue) {
                $tr .= "<td>" . array_sum($Avgvalue) / count($Avgvalue) . "</td>";
            }
            $tr .= "</tr>
            </div>";
            return $tr;
        }
        if ($this->newarrGMDR !== null && $this->Meter != 'ALLTAG') {
            $tr .= "<div id='foot'>
            <tr id='mma'>
                    <td>MAX</td>";
            foreach ($this->newarrGMDR as $Maxkey => $Maxvalue) {
                foreach ($Maxvalue as $Maxtag => $MaxtagValue) {
                    if (!empty($MaxtagValue)) $tr .= "<td>" . max($MaxtagValue) . "</td>";
                }
            }
            $tr .= "</tr>";
            $tr .= "<tr id='mma'>
                        <td>MIN</td>";
            foreach ($this->newarrGMDR as $Minkey => $Minvalue) {
                foreach ($Minvalue as $Mintag => $MintagValue) {
                    if (!empty($MintagValue)) $tr .= "<td>" . min($MintagValue) . "</td>";
                }
            }
            $tr .= "</tr>";
            $tr .= "<tr id='mma'>
                        <td>AVG</td>";
            foreach ($this->newarrGMDR as $Avgkey => $Avgvalue) {
                foreach ($Avgvalue as $Avgtag => $AvgtagValue) {
                    if (!empty($AvgtagValue)) $tr .= "<td>" . array_sum($AvgtagValue) / count($AvgtagValue) . "</td>";
                }
            }
            $tr .= "</tr></div>";
            if (
                !empty($MaxtagValue) &&
                !empty($MaxtagValue) &&
                !empty($AvgtagValue)
            )
                return $tr;
        }
    }

    public function SetTextField($array){
        $decode_array = json_decode($array,true);
        $tag = '';
        foreach($decode_array as $key => $value){	
            foreach($value as $tagname => $checked){
                if($checked === 'Checked'){
                    if($key === 'CurrentTag'){
                        $tag .= $tagname." (Current)".'<br />';
                    }
                    if($key === 'AverageTag'){
                        $tag .= $tagname." (Average)".'<br />';
                    }
                    if($key === 'MinTag'){
                        $tag .= $tagname." (Min)".'<br />';
                    }
                    if($key === 'MaxTag'){
                        $tag .= $tagname." (Max)".'<br />';
                    }
                    if($key === 'DiffIndexTag'){
                        $tag .= $tagname." (Diff&nbspIndex)".'<br />';
                    }
                    if($key === 'NotusedTag'){
                        $tag .= $tagname." (Not&nbspUsed)".'<br />';
                    }
                    if($key === 'IntegratedTag'){
                        $tag .= $tagname." (Integrated)".'<br />';
                    }
                }
            }
        }
        return nl2br($tag);
    }
    public function SaveNewTamplate($name, $tag){
        $name = trim($name);
        $code_type = '';
        $n = 1;
        $owner = $_SESSION['USER'];
        $arrtag = SCADA::ConvertData($tag);
        $checktag = SCADA::fetch_row(SCADA::execu("SELECT TMPL_DESC FROM TEMPLATES WHERE TMPL_DESC = '$name'"));
        if($name === $checktag['TMPL_DESC']) {
            header("Location: ../Index.php?tmpduplicate=error");          
        }else{
            $date = date("d-M-y");
            $GEN_ID = SCADA::fetch_row(SCADA::execu("select max(TMPL_ID)+1 as MAX_ID from TEMPLATES"));
            SCADA::execu("INSERT INTO TEMPLATES (TMPL_ID,TMPL_DESC,TMPL_OWNER,PUBLIC_FLAG) VALUES ('$GEN_ID[0]','$name','$owner','')");
            foreach($arrtag as $key => $value){
                $TAG = explode("/", $key);
                ($value == 'Current') ? $code_type = 1 : $code_type;
                ($value == 'Average') ? $code_type = 2 : $code_type;
                ($value == 'Min') ? $code_type = 3 : $code_type;
                ($value == 'Max') ? $code_type = 4 : $code_type;
                ($value == 'Diff Index') ? $code_type = 5 : $code_type;
                ($value == 'Not Used') ? $code_type = 6 : $code_type;
                ($value == 'Integrated') ? $code_type = 7 : $code_type;
                SCADA::execu("INSERT INTO TEMPLATE_DETAILS (TMPL_ID,COL_ID,TAGNAME,CODE_TYPE,PRECISION,ADD_DATE) VALUES ($GEN_ID[0],".$n++.",'$TAG[0]',$code_type,' ','$date')");
            }   
            header("Location: ../Index.php?save=success&nametmp=$name");
        }
       
    }
    public function DeleteTemplate($id) {
        SCADA::execu("DELETE FROM TEMPLATES WHERE TMPL_ID = $id");
        SCADA::execu("DELETE FROM TEMPLATE_DETAILS WHERE TMPL_ID = $id");
    }
    public function DeleteMeter($name){
        SCADA::execu("DELETE FROM REF_FC_NAME WHERE FC_NAME = '$name'");
    }
    public function DeleteTag($fc_name,$tagname,$table){
        SCADA::execu("DELETE FROM $table WHERE TAGNAME = '$tagname' AND FC_NAME = '$fc_name'");
    }
    public function DeleteUser($username,$group){
        SCADA::execu("DELETE FROM SYS_USERS WHERE USER_NAME = '$username'");
    }
    public function EditMeter($meter_name,$desription,$maintenance,$check_db,$check_flag,$check_rtu){
        $check_db === 'true' ? $check_db = 1 : $check_db = 0;
        $check_flag === 'true' ? $check_flag = 1: $check_flag = 0;
        $check_rtu === 'true' ? $check_rtu = 1: $check_rtu = 0;
        SCADA::execu("UPDATE REF_FC_NAME SET 
            FC_NAME = '$meter_name',FC_DESC = '$desription',REMARK = '$maintenance',CHECKTIME_DB = $check_db,
            CHECKTIME_FLAG = $check_flag ,CHECKTIME_RTU = $check_rtu WHERE FC_NAME = '$meter_name'
        ");
    }
    public function UpdateTamplate($id,$val,$flag){
        $pubflag = "";
        $flag === 'true' ? $pubflag = "Y" : $pubflag = null;
        SCADA::execu("UPDATE TEMPLATES SET TMPL_DESC = '$val', PUBLIC_FLAG = '$pubflag' WHERE TMPL_ID = $id");
    }
    public function updateTag(
        $table,$meter_name,$tag_name,$tag_header,$description,
        $Precision,$Sort_order,$check_meter,$keeptag){
        $check_meter === 'true' ? $check_meter = 1: $check_meter = 0;
        SCADA::execu("UPDATE $table SET TAGNAME = '$tag_name', DESCRIPTION = '$description', 
        PRECISION = $Precision, SORT_ORDER = $Sort_order,TAG_HEADER = '$tag_header', 
        MONITOR_FLAG = $check_meter WHERE FC_NAME = '$meter_name' AND TAGNAME = '$keeptag'");
    }
    public function UpdateUserManage($username,$groupid,$keepuser){
        self::CheckUserGroupPTT($groupid);
        SCADA::execu("UPDATE SYS_USERS SET USER_NAME = '$username',GROUP_ID = $groupid WHERE USER_NAME = '$keepuser'");
    }
    public static function CheckUserGroupPTT($GroupID){
        $Group = '';
        $GroupID == 1 ? $Group = 'PTT Users' : $Group;
        $GroupID == 2 ? $Group = 'PTT Admins' : $Group;
        $GroupID == 3 ? $Group = 'SCADA History Users' : $Group;
        $GroupID == 4 ? $Group = 'SCADA History Admins' : $Group;
        $GroupID == 5 ? $Group = 'GMDR Users' : $Group;
        $GroupID == 6 ? $Group = 'GMDR Admins' : $Group;
        return $Group;
    }
    public function InsertNewUser($username,$groupid){
        SCADA::execu("INSERT INTO SYS_USERS(USER_NAME,GROUP_ID)VALUES('$username',$groupid)");
    }
    public function InsertNewMeter(
        $meter_name,$description,$maintenance,$check_db,$check_flag,$check_rtu
    ){
        $check_db == 'true' ? $check_db = 1: $check_db = 0;
        $check_flag == 'true' ? $check_flag = 1: $check_flag = 0;
        $check_rtu == 'true' ? $check_rtu = 1: $check_rtu = 0;
        SCADA::execu("INSERT INTO REF_FC_NAME(FC_NAME,FC_DESC,REMARK,CHECKTIME_FLAG,CHECKTIME_RTU,CHECKTIME_DB)
        VALUES('$meter_name','$description','$maintenance',$check_flag,$check_rtu,$check_db)");
    }
    public function InsertNewTagGMDR(
        $meter_name,$tag_name,$tag_header,$desription,
        $Precision,$Sort_order, $check_meter,$table){
        $check_meter == 'true' ? $check_meter = 1 : $check_meter = 0;
        SCADA::execu("INSERT INTO $table(FC_NAME,TAGNAME,DESCRIPTION,PRECISION,SORT_ORDER,TAG_HEADER,MONITOR_FLAG)
        VALUES('$meter_name','$tag_name','$desription','$Precision',$Sort_order,'$tag_header',$check_meter)");
    }
};
?>