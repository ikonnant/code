<? 
/**
    Функция удаления кэшированных значений с ~ из массива
**/
function __mprClearKey($arData) {
    $arResult = $arData;
 
    if (is_array($arData)) {
        $arResult = array();
        foreach ($arData as $key => $val) {
            if (is_integer($key) || is_string($key) && $key[0] != '~') {
                $arResult[$key] = __mprClearKey($val);
            }
        }
    }
    return $arResult;
}
 
/**
    Функция распечатывает данные в древовидном виде
    $arData = данные, которые необходимо распечатать. Может быть любого типа
**/
function __mprPrint($arData) {
    if (is_object($arData) || is_array($arData)) {
        echo '<div>';
            echo '<details open>';
                echo '<summary style="outline:none!important;cursor:pointer">';
                    echo (is_object($arData)) ? '<span style="color:#c678dd;">' . get_class($arData) . ' Object {' . count((array)$arData) . '}</span>' : '<span style="color:#e06c75">Array [' . count($arData) . ']</span>';
                echo '</summary>';
                __mprPrintRec($arData);
            echo '</details>';
        echo '</div>';
    } else {
        $sType = gettype($arData);
        if($arData === "NO DATA!!!") {
            $sType = "ERROR";
        }
        $sChars = "";
        $sColor = "";
        switch($sType) {
            case 'string':
                $sColor = '#61afef';
                $arData = str_replace(chr(13), '', $arData);
                $arData = str_replace(chr(10), '', $arData);
                $sChars = ' <small>' . iconv_strlen($arData) . '</small>';
            break;
            case 'integer':
                $sColor = '#98c379';
            break;
            case 'double':
                $sColor = '#98c379';
            break;
            case 'boolean':
                $sColor = '#d19a66';
                $arData = $arData ? 'TRUE' : 'FALSE';
            break;
            case 'NULL':
                $sColor = '#d19a66';
                $arData = 'NULL';
            break;
            case 'ERROR':
                $sColor = '#e06c75';
            break;
        }
        echo '<span style="color:' . $sColor . '">' . $arData . '</span> <span style="opacity:0.5">(' . $sType . $sChars . ')</span></div>';
    }
}
 
/**
    Рекурсивная часть функции __mprPrint()
    $arData = данные, которые необходимо распечатать. Может быть любого типа
    $margin = отступ в пикселях от левого края
**/
function __mprPrintRec($arData, $margin = 20){
    if (!is_object($arData) && !is_array($arData)) {
        return;
    }
    $arData = __mprClearKey($arData);
 
    echo '(';
    foreach ($arData as $key => $value) {
        if (is_object($value) || is_array($value)) {
            echo '<details open style="margin-left:' . $margin . 'px">';
                echo '<summary style="outline:none!important;cursor:pointer">';
                    echo (is_object($value)) ? '[' . $key . '] => <span style="color: #c678dd;">' . get_class($value) . ' Object {' . count((array)$value) . '}' : '[' . $key . ']</span> => <span style="color: #e06c75">Array [' . count($value) . ']</span>';
                echo '</summary>';
                __mprPrintRec($value, $margin + 10);
            echo '</details>';
        } else {
            $sType = gettype($value);
            $sChars = "";
            $sColor = "";
            switch ($sType) {
                case 'string':
                    $sColor = '#61afef';
                    $value = str_replace(chr(13), '', $value);
                    $value = str_replace(chr(10), '', $value);
                    $sChars = ' <small>' . iconv_strlen($value) . '</small>';
                break;
                case 'integer':
                    $sColor = '#98c379';
                break;
                case 'double':
                    $sColor = '#98c379';
                break;
                case 'boolean':
                    $sColor = '#d19a66';
                    $value = $value ? 'TRUE' : 'FALSE';
                break;
                case 'NULL':
                    $sColor = '#d19a66';
                    $value = 'NULL';
                break;
            }
            echo '<div style="margin-left:' . $margin . 'px"><span>[' . $key . ']</span> => <span style="display:inline-table;color:' . $sColor . '">' . $value . '</span> <span style="opacity: 0.5">(' . $sType . $sChars . ')</span></div>';
        }
    }
    if (count($arData) == 0) {
        echo '<br>';
    }
    echo ')';
}
 
/**
    Функция распечатки кода
    $arArgs[0] = всегда те данные, которые нужно распечатать
    $arArgs[1+] = если false - то отключает обертку $bTest, если "die" - то включает $bDie, если "js" - то использует вывод в консоль, иначе - $sTitle
**/
function mpr() {
    $nNumargs = func_num_args();
    $arArgs = func_get_args();
 
    $sTitle = "";
    if(@defined('IS_TEST')) {
        $bTest = !IS_TEST;
    } else {
        $bTest = true;
    }
    $bDie = false;
    $bJS = false;
 
    if ($nNumargs < 1) {
        $arData = "NO DATA!!!";
    } elseif ($nNumargs == 1) {
        $arData = __mprClearKey($arArgs[0]);
    } else {
        $arData = __mprClearKey($arArgs[0]);
        unset($arArgs[0]);
 
        $nTest = array_search(false, $arArgs, true);
        if ((boolean)$nTest > 0) {
            $bTest = (boolean)$arArgs[$nTest];
            unset($arArgs[$nTest]);
        }
 
        $nDie = array_search("die", $arArgs, true);
        if ((boolean)$nDie > 0) {
            $bDie = (boolean)$arArgs[$nDie];
            unset($arArgs[$nDie]);
        }
 
        $nJS = array_search("js", $arArgs, true);
        if ((boolean)$nJS > 0) {
            $bJS = (boolean)$arArgs[$nJS];
            unset($arArgs[$nJS]);
        }
 
        $nTitle = array_search(true, $arArgs);
        $sTitle = (string)$arArgs[$nTitle];
    }
 
    $arDebug = debug_backtrace();
    $arDebug = $arDebug[0];
 
    if($bJS) {
        ?>
            <script>console.log("<?=($sTitle ? $sTitle . " - " : '') . str_replace($_SERVER["DOCUMENT_ROOT"], '', $arDebug['file']) . " [" . $arDebug['line'] . "]";?>", <?=json_encode($arData);?>);</script>
        <?
        return;
    }
 
    if ($bTest) {
        if(!isset($_GET['test'])) {
            return;
        }
    }
 
    echo "<div class='mpr' style='border:5px solid #DDD;background-color:#DDD;margin:15px 0;min-height:34px;'>";
    if (strlen($sTitle) > 0) {
        echo "<span style='padding:5px 10px 10px;float:right;opacity:0.5;font-family:monospace;word-wrap:break-word;max-width:100%;'>" . $sTitle . "</span>";
    }
 
    echo "<span style='padding:5px 10px 10px;float:left;opacity:0.5;font-family:monospace;word-wrap:break-word;max-width:100%;'>" . str_replace($_SERVER["DOCUMENT_ROOT"], '', $arDebug['file']) . " [" . $arDebug['line'] . "]</span>";
 
    echo "<pre style='background:#282c34;color:#abb2bf;border:0;border-radius:0;margin:29px 0 0;font-family:monospace;font-size:13px;font-weight:400;max-height:500px;overflow:auto;clear:both;padding:5px;'>";
        __mprPrint($arData);
    echo "</pre>";
 
    echo "</div>";
 
    if ($bDie) {
        die();
    }
}
 
/**
    $nNum - количество
    $arForms = array('товар','товара','товаров');
**/
function variation($nNum, $arForms) {
    return $nNum % 10 == 1 && $nNum % 100 != 11 ? $arForms[0] : ($nNum % 10 >= 2 && $nNum % 10 <= 4 && ($nNum % 100 < 10 || $nNum % 100 >= 20) ? $arForms[1] : $arForms[2]);
}
 
/**
    $sCode    - символьный код элемента или раздела Битрикс
    $iblockID - ID инфоблока, в котором ищем элемент или раздел
    $sType    - Элемент ищем или раздел (IBLOCK_ELEMENT или IBLOCK_SECTION соответственно)
**/
function getIDByCode($sCode, $iblockID, $sType) {
    if (CModule::IncludeModule("iblock")) {
        if ($sType == 'IBLOCK_ELEMENT') {
            $arFilter = array("IBLOCK_ID" => $iblockID, "CODE" => $sCode);
            $res = CIBlockElement::GetList(array(), $arFilter, false, array("nPageSize" => 1), array('ID'));
            $element = $res->Fetch();
            if ($res->SelectedRowsCount() !== 1) {
                return '<p style="font-weight:bold;color:#ff0000">Элемент не найден</p>';
            }
            else {
                return $element['ID'];
            }
        }
        elseif ($sType == 'IBLOCK_SECTION') {
            $res = CIBlockSection::GetList(array(), array('IBLOCK_ID' => $iblockID, 'CODE' => $sCode));
            $section = $res->Fetch();
            if ($res->SelectedRowsCount() !== 1) {
                return '<p style="font-weight:bold;color:#ff0000">Раздел не найден</p>';
            }
            else {
                return $section['ID'];
            }
        }
        else {
            echo '<p style="font-weight:bold;color:#ff0000">Укажите тип</p>';
            return;
        }
    }
}
 
/**
    функция проверки валидности email
**/
function __isEmail($sEmail) {
    $user   = '[a-zA-Z0-9_\-\.\+\^!#\$%&*+\/\=\?\`\|\{\}~\']+';
    $domain = '(?:(?:[a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9])\.?)+';
    $ipv4   = '[0-9]{1,3}(\.[0-9]{1,3}){3}';
    $ipv6   = '[0-9a-fA-F]{1,4}(\:[0-9a-fA-F]{1,4}){7}';
    return preg_match("/^$user@($domain|(\[($ipv4|$ipv6)\]))$/", $sEmail);
}
 
/**
    $sTo      - кому
    $sSubject - тема письма
    $sMessage - текст письма
**/
function testMail($sTo, $sSubject = false, $sMessage = false) {
    if (!$sSubject) {
        $sSubject = 'Тест';
    }
    if (!$sMessage) {
        $sMessage = '
        Тестовое сообщение
        ';
    }
    // Для отправки HTML-письма должен быть установлен заголовок Content-type
    $sHeaders  = 'MIME-Version: 1.0' . "\r\n";
    $sHeaders .= 'Content-type: text/html; charset=utf-8' . "\r\n";
    // Отправляем
    if (__isEmail($sTo)) {
        $bComplete = mail($sTo, $sSubject, $sMessage, $sHeaders);
    }
    else {
        $bComplete = false;
    }
    if ($bComplete) {
        die("Email successfully sent to ".$sTo);
    }
    else {
        die("An error occurred while sending the message");
    }
}
if (isset($_GET['mailto']) && $_GET['mailto'] !== '') {
    testMail($_GET['mailto']);
}
 
/**
    $sGeoip - IP для которого требуется найти геоданные. Поумолчанию $_SERVER['REMOTE_ADDR'] - IP посетителя
    $bCity  - возвращать только название города
**/
function getCityByIP($sGeoip = false, $bCity = false) {
    if ($curl = curl_init()) {
        if (!$sGeoip) {
            $sGeoip = $_SERVER['REMOTE_ADDR'];
        }
        curl_setopt($curl, CURLOPT_URL, "http://ipgeobase.ru:7020/geo?ip=$sGeoip");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $out = curl_exec($curl);
        curl_close($curl);
 
        if ($bCity) {
            preg_match('/<city>(.*)<\/city>/', $out, $vals);
            $vals = iconv('windows-1251', 'UTF-8', $vals[1]);
        }
        else {
            $xml = xml_parser_create();
            xml_parse_into_struct($xml, $out, $vals, $index);
            xml_parser_free($xml);
        }
 
        return $vals;
    }
    else {
        return "An error occurred while geoIP";
    }
}
 
/**
    $arData - массив, который нужно записать в лог
    $sFile  - имя файла, в который нужно записать лог (лог будет храниться в корне сайта в папке /local/logs и иметь расширение .log)
**/
function logFile($arData, $sFile) {
 
    $logDir = $_SERVER["DOCUMENT_ROOT"] . "/local/logs";
    $logDirData = $logDir ."/". $sFile;
    $logFile = $logDirData . "/" . $sFile . "_" . date("d.m.Y") . ".log";
 
    if (is_dir($logDir) === false) {
        mkdir($logDir);
    }
 
    if (is_dir($logDirData) === false) {
        mkdir($logDirData);
    }
 
    $msg  = "\r\n" . "............" . "\r\n";
    $msg .= "| " . date("H:i:s") . " |" . "\r\n";
    $msg .= "''''''''''''" . "\r\n";
    $msg .= print_r($arData, true);
 
    $f = fopen($logFile, 'a');
        fwrite($f, $msg);
    fclose($f);
 
    $arFiles = array_diff(scandir($logDirData), array('..', '.')); //Получаем список логов и выкидываем из массива переходы на 1 и 2 уровня вверх (. и ..)
    $count = count($arFiles);
 
    if ($count > 10) { //Если логов более 10
        foreach ($arFiles as $key => $file) {
            $date = str_replace($sFile . '_', '', $file);
            $date = str_replace('.log', '', $date);
            if ((strtotime(date("d.m.Y")) - strtotime($date)) >= (10 * 24 * 60 * 60)) { //Удалять лог файлы, которым более 10 дней (в секундах)
                unlink($logDirData . "/" .$file);
            }
        }
    }
}
 
/**
    $sStr   - Строка, которую требуется обрезать
    $nSize  - Длина строки или ширина строки, под обрезание
    $bWidth - Обрезать по ширине строки или по длине
**/
function cropStr($sStr, $nSize, $bWidth = true) {
    if ($bWidth) {
        return mb_strimwidth($sStr, 0, $nSize, '...');
    }
    else {
        $sStr = $sStr." ";
        $sStr = substr($sStr, 0, $size);
        $sStr = substr($sStr, 0, strrpos($sStr, ' '));
        if (iconv_strlen($sStr) > $nSize) {
            $sStr = $sStr."...";
        }
        return $sStr;
    }
}
 
/**
    $bShow   - Выводить ли ошибки на сайте
    $bAll    - Выводить все ошибки
**/
function showDebug($bAll = false) {
    if ($bAll) {
        ini_set('error_reporting', E_ALL & ~E_NOTICE);
    } else {
        ini_set('error_reporting', E_ERROR | E_WARNING | E_PARSE | E_DEPRECATED);
    }
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
}
if (isset($_GET['dbg'])) {
    showDebug();
}
 
/**
    Генераруем уникальный GUID
**/
function createGuid() {
    if (function_exists('com_create_guid')) {
        return com_create_guid();
    } else {
        mt_srand((double)microtime() * 10000);
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45); // "-"
        $uuid = chr(123) // "{"
                        .substr($charid, 0, 8) . $hyphen
                        .substr($charid, 8, 4) . $hyphen
                        .substr($charid,12, 4) . $hyphen
                        .substr($charid,16, 4) . $hyphen
                        .substr($charid,20,12)
                        .chr(125); // "}"
        return $uuid;
    }
}
 
/**
    $nNum - число для преобразования формата
    $bEnd - добавлять в конце пробел
    $bSoft - использовать неразрывный пробел
**/
function numFormat($nNum, $bEnd = true, $bSoft = true) {
    if ($bSoft) {
        $space = "&#160;";
    } else {
        $space = " ";
    }
    if ($bEnd) {
        return number_format($nNum, 0, "", $space) . $space;
    } else {
        return number_format($nNum, 0, "", $space);
    }
}
 
/**
  $sUrl - Ссылка на видео с YouTube
**/
function getYoutubeVideoID($sUrl = false) {
    if(!$sUrl) {
        return false;
    }
 
    if(preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $sUrl, $arMatch)) {
        return $arMatch[1];
    } else {
        return false;
    }
}
 
/**
    $file - имя файла pdf в который нужно сохранить получивший pdf
    $HTML - html который нужно превратить в pdf. Принимает в себя код html, файл html или другой, генерирующий или содержащий html, а так же URL
    $arParams - Массив параметров. Документация: https://wkhtmltopdf.org/usage/wkhtmltopdf.txt
**/
function curlPDF($file, $HTML = "<html><body>test</body></html>", $arParams = array()) {
    $postfields["KEY"] = "Z2FaUiSCymHcSCgOXR2FAhwFicejR6";
    $postfields["DATA"] = base64_encode(serialize(array("HTML" => $HTML, "arParams" => $arParams)));
 
    $ch = curl_init();
 
    curl_setopt($ch, CURLOPT_URL, 'http://services.vprioritete.com/htmltopdf/index.php');
    curl_setopt($ch, CURLOPT_USERPWD, "1:1");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_TIMEOUT, -1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields, '', '&'));
 
    $output = curl_exec($ch);
 
    if(isset($output["ERROR"])) {
        return  $output["ERROR"];
    } else {
        $file = str_replace(".pdf", "", $file) . ".pdf";
 
        $fh = fopen($file, 'w');
            fwrite($fh, $output);
        fclose($fh);
 
        if($fh) {
            return $file;
        } else {
            return "Error " . $file . ": " . $php_errormsg;
        }
    }
}
 
/**
 $nBytes - количество байт
 $nPrecision - количество знаков при округлении
**/
function floorBytes($nBytes, $nPrecision = 2) {
    $arUnits = array('Б', 'КБ', 'МБ', 'ГБ', 'ТБ');
    $nBytes = max($nBytes, 0);
    $iPow = floor(($nBytes ? log($nBytes) : 0) / log(1024));
    $iPow = min($iPow, count($arUnits) - 1);
    $nBytes /= pow(1024, $iPow);
    return round($nBytes, $nPrecision) . ' ' . $arUnits[$iPow];
}
