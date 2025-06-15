<?php
namespace Latecka\Utils;

define('c_CookieTime', time() + (60*60*2));

class utils {
    private static $startTime;
    
    const 
        OUTPUT_COMMENT = 0,
        OUTPUT_DIE = 1,
        OUTPUT_PRINT = 2,
        OUTPUT_RETURN = 3;

    function __construct() {
        /** @var float zacatek mereni casu scriptu. Sekundy + microsekundy */
        self::$startTime = time() + substr(microtime(),0,8);
    }
    
    
    /**
     * Mereni doby behu script
     * @param int $output Jak se vysledek zobrazi. 0 = (OUTPUT_COMMENT) vypis jako komentar, 1 = (OUTPUT_DIE) vypise do stranky a zastavi, jinak return hodnoty
     * @return mixed
     */
    public static function scriptTime($output = '') {
         /** @var float aktualni cas. Sekundy + microsekundy */
	$now = time() + substr(microtime(),0,8);
	
        /** @var float Rozdil mezi zacatkem behu scriptu a aktualnim case.
         * Cili aktualni doba behu scriptu. Sekundy + microsekundy */
        $casscriptu = (float) (round(utils::fixFloat($now - self::$startTime), 4));

        if ($output===self::OUTPUT_COMMENT){
            echo "\n<!-- Cas behu scriptu: ".$casscriptu." sekund -->\n";
	}elseif ($output===self::OUTPUT_DIE){
            die('Cas behu scriptu: '.$casscriptu.' sekund');
        }elseif ($output===self::OUTPUT_PRINT){
            echo ('<p>Cas behu scriptu: '.$casscriptu.' sekund</p>'.PHP_EOL);
        }elseif ($output===self::OUTPUT_RETURN){
            return $casscriptu;
	}else{
            return $casscriptu;
	}
    }
    
    /**
     * Funkce se pokusí vrátit datum a cas ve formatu pro db
     * @param mixed $datum <p>Může to být unixtime nebo string (pro zpracovani ve funkci strtotime)</b>
     */
    public static function formatDbDateTime(string $datum) : string {
        //$unixTime = (self::fixInt($datum) > strtotime('2020-01-01') && self::fixInt($datum) == $datum) ? self::fixInt($datum) : self::fixFloat(strtotime($datum));
        $unixTime = (strlen(self::fixInt($datum))<=8) ? self::fixFloat(strtotime($datum)) : self::fixFloat($datum) ;
        if ($unixTime <= 0) { return ''; }
        return date('Y-m-d H:i:s', $unixTime);
    }
    
    /**
     * Funkce se pokusí vrátit datum ve formatu pro db
     * @param mixed $datum <p>Může to být unixtime nebo string (pro zpracovani ve funkci strtotime)</b>
     */
    public static function formatDbDate(string $datum) : string {
        self::convertDate_dmY2Zmd($datum);
        
        //$unixTime = (self::fixInt($datum) > strtotime('2020-01-01') && self::fixInt($datum) == $datum) ? self::fixInt($datum) : self::fixFloat(strtotime($datum));
        $unixTime = (strlen(self::fixInt($datum))<=8) ? self::fixFloat(strtotime($datum)) : self::fixFloat($datum) ;
        if ($unixTime <= 0) { return ''; }
        return date('Y-m-d', $unixTime);
    }
    
    /**
     * Pokusi se prelozit datum z formatu d.m.Y, do formatu Y-m-d. Kdyz se to nepodari, vrati puvodni datum
     * @param string $datum
     */
    public static function convertDate_dmY2Zmd(string &$datum) {
        list($d, $m, $y) = explode('.', $datum);
        if ($y < 1970 || $m < 1 || $m> 12 || $d<1 || $d > 31) { return; }
        return date('Y-m-d', strtotime($y.'-'.$m.'-'.$d));
    }
    
    public static function debug($val = '') {
        echo '<p>This is debug:</p><pre>';
        print_r($val);
        die('</pre>');
    }
    
    /**
    * Funkce vraci vstupni parametr jako cislo. V pripade,
    * <br>ze se vstupni hodnota nepodari prevest na cislo<br>
    * vrati nulu, pripadne prazdny retezec
    * @param mixed $num vstupni hodnota, kterou se budu snazit prevest na cislo
    * @param bool $printZero
    * @return int
    */
    public static function fixIntArray($array, $printZero = true, $debug = false){
       if (!is_array($array)) { return false; }
       foreach ($array AS $key => $val) {
            if (is_array($val) || is_object($val)) {
                $array[$key] = 0;
            }else{
               $array[$key] = self::fixInt($val);
            }
       }
       return $array;
   }

   /**
    * Funkce sanitizeHTML vstupni parametr (odkazem) jako cislo. V pripade,
    * <br>ze se vstupni hodnota nepodari prevest na cislo<br>
    * vrati nulu, pripadne prazdny retezec
    * @param mixed $num
    * @param bool $printZero
    * @return int
    */
    public static function fixIntRef(&$num, $printZero = true){
       $num = self::fixInt($num, $printZero);
    }

    /**
    * Funkce vraci vstupni parametr jako cislo. V pripade,
    * <br>ze se vstupni hodnota nepodari prevest na cislo<br>
    * vrati nulu, pripadne prazdny retezec
    * @param mixed $num vstupni hodnota, kterou se budu snazit prevest na cislo
    * @param bool $printZero
    * @return int
    */
    public static function fixInt($num, $printZero = true){
       if (is_array($num)) { return 0; }
       $num = intval(str_replace(array(",", " "),array(".", ""),trim($num)));
       if ($num == '' || $num == false) { $num = 0; }
       if ($num != 0) { return $num; }
       if ($printZero === true) { return 0; }

       return '';
   }
    
    /**
    * Funkce vraci vstupni parametr jako float. V pripade,
    * <br>ze se vstupni hodnota nepodari prevest na float<br>
    * vrati nulu, pripadne prazdny retezec
    * @param mixed $num
    * @param bool $printZero
    * @return float
    */
   public static function fixFloat($num, $printZero = true){
       if (is_array($num)) { $num = 0; }
       $num = floatval(str_replace(array(",", " "),array(".", ""),trim($num)));
       $num = str_replace(",",".",$num);
       if ($num != 0) { return $num; }
       if ($printZero === true) { return 0; }
       return '';
   }

   /**
    * Funkce sanitizeHTML vstupni parametr odkazem jako float. V pripade,
    * <br>ze se vstupni hodnota nepodari prevest na float<br>
    * vrati nulu, pripadne prazdny retezec
    * @param mixed $num
    * @param bool $printZero
    * @return float
    */
   public static function fixFloatRef(&$num, $printZero = true){
       $num = self::fixFloat($num, $printZero);
   }

    /**
     * Funkce vraci vstupni parametr jako hodnotu typu decimal
     * @param mixed $num
     * @param int $precision
     * @param boolean $printZero
     * @return int
     */
    public static function fixDecimal($num, $precision = 2, $printZero = true){
        if (is_array($num)) { $num = 0; }
        self::fixFloatRef($num);
        self::fixIntRef($precision);
        $num = sprintf("%01.{$precision}f",round($num, $precision));
        if ($num != 0) { return $num; }
        if ($printZero === true) { return $num; }
        return '';
    }
    
    
    /**
     * Funkce sanitizeHTML vstupni parametr odkazem jako hodnotu typu decimal
     * @param mixed $num
     * @param int $precision
     * @param boolean $printZero
     * @return int
     */
    public static function fixDecimalRef(&$num, $precision = 2, $printZero = true){
        $num = self::fixDecimal($num, $precision, $printZero);
    } 
    
    /**
     * Funkce sanitizeHTML vstupni parametr a vrati ho jako logickou hodnotu, nebo jako string (true/false), pripadne ciselnou hodnotu (1/0)
     * @param mixed $input <p>Vstupni hodnota, ktera bude prevadena na "boolean"</p>
     * @param int $returnType <p>Pozadovany typ navracene hodnoty, default = 0 (0 = boolean, 1 = string, 2 = int (0/1))</p>
     * @return mixed
     */
    public static function fixBoolean($input, $returnType = 0){
        $return = ($input === true || $input == 1);
        if ($returnType == 0) { //return as boolean
            return $return;
        }elseif ($returnType == 1) { //return as string
            return ($return) ? 'true' : 'false';
        }else{ //return as int
            return ($return) ? 1 : 0;
        }
    }
    
    /**
     * Funkce sanitizeHTML vstupni parametr a vrati ho jako logickou hodnotu, nebo jako string (true/false), pripadne ciselnou hodnotu (1/0)
     * @param mixed $input <p>Vstupni hodnota, ktera bude prevadena na "boolean"</p>
     * @param int $returnType <p>Pozadovany typ navracene hodnoty, default = 0 (0 = boolean, 1 = string, 2 = int (0/1))</p>
     * @return mixed
     */
    public static function fixBooleanRef(&$input, $returnType = 0){
        $input = self::fixBoolean($input, $returnType);
    }
        
    
    public static function safeForm($str) {
        if (is_array($str)) { return ''; }
        return str_replace('"','&quot;',$str);
    }

    public static function safeJS($str) {
        if (is_array($str)) { return ''; }
        return str_replace(array("\n", "\r", "'"),array("","","`"),$str);   
    }
   
    public static function safeFormRef(&$str) {
        if (is_array($str)) {
            $str = '';
        }else{
            $str = str_replace('"','&quot;',$str);
        }
    }

    public static function safeJSRef(&$str) {
        if (is_array($str)) {
            $str = '';
        }else{
            $str = str_replace(array("\n", "\r", "'"),array("","","`"),$str); 
        }
    }
    
   
    /**
     * Vrati obsah pole jako HTML tabulku
     * @param array $input pole ktere chceme zobrazit jako tabulku
     * @param string $classTable CSS trida tabulky (volitelne)
     * @param boolean $withHeader Ma ci nema se v tabulce zobrazit hlavicka? (default true)
     * @param int $maxRows Maximalni pocet zobrazenych radku (default 5000)
     * @return boolean|string
     */
    public static function arrayToTable($input, $classTable='', $withHeader = true, $maxRows = 8000){
        utils::fixIntRef($maxRows);
        if (empty($input)) { return false; }
        if (!is_array($input)) { return false; }
        //Jde-li o dvourozmerne pole, prevedu ho na trirozmerne
        if (!is_array($input[array_key_first($input)])) {
            foreach ($input AS $key => $value) { 
                $array[] = array($key, $value);
                if ($maxRows > 0 && count($array) >= $maxRows) { break; }
            }
        }else{
            $array = $input;
        }
        if ($maxRows > 0) { $array = array_slice($array,0,$maxRows,true); }
        $html = '<table class="'.$classTable.'">'; //Start table
        
        if ($withHeader) { // header row
            $html .= '<tr>';
            foreach($array[array_key_first($array)] as $key=>$value) { $html .= '<th>' . htmlspecialchars($key) . '</th>'; } //Header cells
            $html .= '</tr>';
        }
        
        foreach( $array as $aValues){ // data rows
            $html .= '<tr>';
            foreach($aValues as $value){ $html .= '<td>' . htmlspecialchars($value) . '</td>'; }
            $html .= '</tr>';
        }
        $html .= '</table>';
        return $html;
    }
   
   
    public static function sanitizeFilename($filename) {
        if (is_array($filename) || trim($filename)=='') { return ''; }
        
        //Prevedu vsechny non ASCI znaky na ASCII podobu
        $filename = self::toASCII($filename);

        
        //Nahradim vse mimo alpha a digit znaky pomlckami a pak pripadne pomlcky na konci retezce odstranim
        $filename = trim(preg_replace ("/[^\\/.[:alpha:][:digit:]]/", '-', $filename), '-');

        // odstrani z retezce pomlcky, pokud jsou dve a vice vedle sebe
        $filename = preg_replace ("/[-]+/", '-', $filename);

        return $filename;
    }
    
    public static function sanitizeFilenameRef(&$filename) {
        $filename = self::sanitizeFilename($filename);
    }
    
    public static function toSeoUrl ($str){
        
        if (is_array($str) || trim($str)=='') { return ''; }
        
        //Prevedu vsechny non ASCI znaky na ASCII podobu a prevedu na mala pismena
        $str =  strtolower(self::toASCII($str));

        
        //Nahradim vse mimo alpha a digit znaky pomlckami a pak pripadne pomlcky na konci retezce odstranim
        $str = trim(preg_replace ("/[^[:alpha:][:digit:]]/", '-', $str), '-');

        // odstrani z retezce pomlcky, pokud jsou dve a vice vedle sebe
        $str = preg_replace ("/[-]+/", '-', $str);

        return $str;
    }
    
    public static function toSeoUrlRef(&$str, $bToLower = true) {
        $str = self::toSeoUrl($str, $bToLower);
    }
    
    public static function toASCII($str) {
        if (is_array($str) || trim($str)=='') { return ''; }
        $transTable = array('á'=>'a','Á'=>'A','à'=>'a','À'=>'A','ă'=>'a','Ă'=>'A','â'=>'a','Â'=>'A','å'=>'a','Å'=>'A','ã'=>'a','Ã'=>'A','ą'=>'a','Ą'=>'A','ā'=>'a','Ā'=>'A',
            'ä'=>'a','Ä'=>'A','æ'=>'ae','Æ'=>'AE','ḃ'=>'b','Ḃ'=>'B','ć'=>'c','Ć'=>'C','ĉ'=>'c','Ĉ'=>'C','č'=>'c','Č'=>'C','ċ'=>'c','Ċ'=>'C','ç'=>'c','Ç'=>'C','ď'=>'d',
            'Ď'=>'D','ḋ'=>'d','Ḋ'=>'D','đ'=>'d','Đ'=>'D','ð'=>'dh','Ð'=>'Dh','é'=>'e','É'=>'E','è'=>'e','È'=>'E','ĕ'=>'e','Ĕ'=>'E','ê'=>'e','Ê'=>'E','ě'=>'e','Ě'=>'E','ë'=>'e',
            'Ë'=>'E','ė'=>'e','Ė'=>'E','ę'=>'e','Ę'=>'E','ē'=>'e','Ē'=>'E','ḟ'=>'f','Ḟ'=>'F','ƒ'=>'f','Ƒ'=>'F','ğ'=>'g','Ğ'=>'G','ĝ'=>'g','Ĝ'=>'G','ġ'=>'g','Ġ'=>'G','ģ'=>'g',
            'Ģ'=>'G','ĥ'=>'h','Ĥ'=>'H','ħ'=>'h','Ħ'=>'H','í'=>'i','Í'=>'I','ì'=>'i','Ì'=>'I','î'=>'i','Î'=>'I','ï'=>'i','Ï'=>'I','ĩ'=>'i','Ĩ'=>'I','į'=>'i','Į'=>'I','ī'=>'i',
            'Ī'=>'I','ĵ'=>'j','Ĵ'=>'J','ķ'=>'k','Ķ'=>'K','ĺ'=>'l','Ĺ'=>'L','ľ'=>'l','Ľ'=>'L','ļ'=>'l','Ļ'=>'L','ł'=>'l','Ł'=>'L','ṁ'=>'m','Ṁ'=>'M','ń'=>'n','Ń'=>'N','ň'=>'n',
            'Ň'=>'N','ñ'=>'n','Ñ'=>'N','ņ'=>'n','Ņ'=>'N','ó'=>'o','Ó'=>'O','ò'=>'o','Ò'=>'O','ô'=>'o','Ô'=>'O','ő'=>'o','Ő'=>'O','õ'=>'o','Õ'=>'O','ø'=>'oe','Ø'=>'OE','ō'=>'o',
            'Ō'=>'O','ơ'=>'o','Ơ'=>'O','ö'=>'oe','Ö'=>'OE','ṗ'=>'p','Ṗ'=>'P','ŕ'=>'r','Ŕ'=>'R','ř'=>'r','Ř'=>'R','ŗ'=>'r','Ŗ'=>'R','ś'=>'s','Ś'=>'S','ŝ'=>'s','Ŝ'=>'S','š'=>'s',
            'Š'=>'S','ṡ'=>'s','Ṡ'=>'S','ş'=>'s','Ş'=>'S','ș'=>'s','Ș'=>'S','ß'=>'SS','ť'=>'t','Ť'=>'T','ṫ'=>'t','Ṫ'=>'T','ţ'=>'t','Ţ'=>'T','ț'=>'t','Ț'=>'T','ŧ'=>'t','Ŧ'=>'T',
            'ú'=>'u','Ú'=>'U','ù'=>'u','Ù'=>'U','ŭ'=>'u','Ŭ'=>'U','û'=>'u','Û'=>'U','ů'=>'u','Ů'=>'U','ű'=>'u','Ű'=>'U','ũ'=>'u','Ũ'=>'U','ų'=>'u','Ų'=>'U','ū'=>'u','Ū'=>'U',
            'ư'=>'u','Ư'=>'U','ü'=>'ue','Ü'=>'UE','ẃ'=>'w','Ẃ'=>'W','ẁ'=>'w','Ẁ'=>'W','ŵ'=>'w','Ŵ'=>'W','ẅ'=>'w','Ẅ'=>'W','ý'=>'y','Ý'=>'Y','ỳ'=>'y','Ỳ'=>'Y','ŷ'=>'y','Ŷ'=>'Y',
            'ÿ'=>'y','Ÿ'=>'Y','ź'=>'z','Ź'=>'Z','ž'=>'z','Ž'=>'Z','ż'=>'z','Ż'=>'Z','þ'=>'th','Þ'=>'Th','µ'=>'u','а'=>'a','А'=>'a','б'=>'b','Б'=>'b','в'=>'v','В'=>'v','г'=>'g',
            'Г'=>'g','д'=>'d','Д'=>'d','е'=>'e','Е'=>'E','ё'=>'e','Ё'=>'E','ж'=>'zh','Ж'=>'zh','з'=>'z','З'=>'z','и'=>'i','И'=>'i','й'=>'j','Й'=>'j','к'=>'k','К'=>'k','л'=>'l',
            'Л'=>'l','м'=>'m','М'=>'m','н'=>'n','Н'=>'n','о'=>'o','О'=>'o','п'=>'p','П'=>'p','р'=>'r','Р'=>'r','с'=>'s','С'=>'s','т'=>'t','Т'=>'t','у'=>'u','У'=>'u','ф'=>'f',
            'Ф'=>'f','х'=>'h','Х'=>'h','ц'=>'c','Ц'=>'c','ч'=>'ch','Ч'=>'ch','ш'=>'sh','Ш'=>'sh','щ'=>'sch','Щ'=>'sch','ъ'=>'','Ъ'=>'','ы'=>'y','Ы'=>'y','ь'=>'','Ь'=>'','э'=>'e',
            'Э'=>'e','ю'=>'ju','Ю'=>'ju','я'=>'ja','Я'=>'ja');
        return str_replace(array_keys($transTable), array_values($transTable), $str);
    }
    
    public static function defineRemoteAddr (){
        if (defined('c_RemoteAddr')) { return true; }
	$a = array(
                'REMOTE_ADDR'=>$_SERVER["REMOTE_ADDR"],
                'HTTP_CLIENT_IP'=>$_SERVER['HTTP_CLIENT_IP'],
                'HTTP_X_FORWARDED_FOR'=>$_SERVER['HTTP_X_FORWARDED_FOR'],
                'HTTP_X_FORWARDED'=>$_SERVER['HTTP_X_FORWARDED'],
                'HTTP_FORWARDED_FOR'=>$_SERVER['HTTP_FORWARDED_FOR'],
                'HTTP_FORWARDED'=>$_SERVER['HTTP_FORWARDED'],
                'HTTP_VIA'=>$_SERVER["HTTP_VIA"]
            );
	define('c_RemoteAddr', serialize(array_filter($a)));
    }
    
    public static function truncateword($str, $maxlength = 15){
        $final = "";
        if (is_array($str)) { return ''; }
        $aStr  = explode("·¨·",preg_replace("(<([^>]+)>)","·¨·\\0·¨·",$str));
        foreach($aStr AS $i => $val){
            if(mb_substr($val,0,1)!="<") {
                $aStr2 = explode(" ",$val);
                foreach ($aStr2 AS $j => $val2){
                    $aStr[$j] = mb_substr($val2,0,$maxlength);
                }
                $aStr[$i] = implode(" ",$aStr);
            }
            $final .= $aStr[$i];
        }
        return $final;
    }
    
    
    /**
     * Zkrati retezen na max. pozadovanou delku po celych slovech
     * prekroci-li delka puvodniho retezce maximalni delku, doplni retezec trojteckou ...
     * @param string $str
     * @param string $maxlength
     * @return string
     */
    public static function vycuc($str,$maxlength = 50){
        $final = self::truncateword(strip_tags(trim($str)));
        if (mb_strlen($final)<=$maxlength) { return $final; }

        $final = mb_substr($final,0,$maxlength);
        $aStr = explode(" ",$final);
        $aStr[count($aStr)-1] = "&hellip;";
        $final = implode(" ",$aStr);
        return $final;
    }
    
    public static function getHashHeslo($what, $salt1 = '') {
        $toHash = (array)$what;
        if (count($toHash) == 0) { return ''; }

        return self::saltHash(implode(';', $toHash), $salt1);
    }
    
    public static function getHash($what, $salt1 = '', $salt2 = '') {
        if (!defined('c_RemoteAddr')) : self::defineRemoteAddr(); endif;
        $toHash = (array)$what;
        if (count($toHash) == 0) { return ''; }

        $toHash[] = request::string('PHPSESSID', 'cookie'); //Do hashe automaticky zahrnuju i PHPSESSID
        $toHash[] = c_RemoteAddr; //Do hashe automaticky zahrnuju i constantu c_RemoteAddr
        return self::saltHash(implode(';', $toHash), $salt1, $salt2);
    }
    
    public static function saltHash($what, $salt1 = '4č˘^ggůĽú$;_', $salt2 = 'ýáh°°%b99¨¨') {
       return md5($salt1 . strrev((sha1($salt2 . $what)))); 
    }
    
    public static function checkHash($what, $hash) {
        if (empty($what) || empty($hash)) { return false; }
        return ($hash === self::getHash($what));
    }
    
    public static function unsetCookie($ckyName) {
        self::setCookie($ckyName, '', -86400);
    }
    
    public static function clearCookiesArray(array $aCookies = array()) {
        if (is_array($aCookies)){
            foreach ($aCookies AS $ckyName) {
                self::unsetCookie($ckyName);
            }
        } 
    }
    
    public static function refreshCookies($exp=0) {
        self::fixFloatRef($exp);
        //Kdyz neni expirace $exp zadana, zadam hodnotu v c_CookieTime
        if (is_array($_COOKIE)){
            foreach ($_COOKIE AS $ckyName => $value) {
                self::setCookie($ckyName, $value, (($exp === 0) ? c_CookieTime : $exp), '/', '.'.$_SERVER['SERVER_NAME']);
            }
        }
    }
    
    /**
     * Naplni a ulozi cookie
     * @param string $ckyName
     * @param mixed $ckyVal
     * @param int $exp Expirace (sekundy od 1.1.1970)<br>
     * Kdyz neni expirace $exp zadana, dafalt hodnota je 31536000 = 365 dnu
     */
    public static function setCookie($ckyName, $ckyVal, $exp=0) {
        self::fixFloatRef($exp);
        //Kdyz neni expirace $exp zadana, zadam hodnotu v c_CookieTime
        setCookie($ckyName, $ckyVal, (($exp === 0) ? c_CookieTime : $exp), '/', $_SERVER['SERVER_NAME']);
        setCookie($ckyName, $ckyVal, (($exp === 0) ? c_CookieTime : $exp), '/', '.'.$_SERVER['SERVER_NAME']);
    }
    
    /**
     * Delete non-empty folders
     * @param string $dir
     */
    public static function delDir($dir){
        if (mb_substr($dir,-1)=="/") { $dir = mb_substr($dir,0,mb_strlen($dir)-1); }
        if ($current_dir = opendir($dir)){
            while($entryname = readdir($current_dir)){
                 if(is_dir("$dir/$entryname") and ($entryname != "." and $entryname!="..")){
                    self::delDir("${dir}/${entryname}");
                 }elseif($entryname !== "." && $entryname!==".."){
                    unlink("${dir}/${entryname}");
                 }
            }
            closedir($current_dir);
            rmdir("${dir}");
        }
    }

    
    /**
     * Delete (only) files in folders
     * @param string $dir
     */
    public static function clearDir($dir){
        $dir = trim(rtrim($dir, '/\\'));
        if ($current_dir = opendir($dir)){
            while($entryname = readdir($current_dir)){
                if((!is_dir("$dir/$entryname")) and ($entryname != "." and $entryname!="..")){
                    unlink("${dir}/${entryname}");
                }
            }
            closedir($current_dir);
        }
    }
    
    
    public static function checkFileExtention($filename, $exts, $ext_save = 1) {
        if ($ext_save == 1) {
            if (preg_match("/^\./", $filename)) { return true; }
        }
        if ($exts == "all") { return true; }
        if (is_string($exts)) {
            return (preg_match("/\.". $exts ."$/i", $filename));
        } else if (is_array($exts)) {
            foreach ($exts as $theExt) {
                if (preg_match("/\.". $theExt ."$/i", $filename)) {
                    return true;
                }
            }
        }
        return false;
     }
    
    public static function getDirectoryListing($dirname, $sortorder = "a", $show_subdirs = 0, $show_subdirfiles = 0, $exts = "", $ext_save = 1) {
        // This function will return an array with filenames based on the criteria you can set in the variables
        // @sortorder : a for ascending (the standard) or d for descending (you can use the "r" for reverse as well, works the same)
        // @show_subdirs : 0 for NO, 1 for YES - meaning it will show the names of subdirectories if there are any
        // Logically subdirnames will not be checked for the required extentions
        // @show_subdirfiles : 0 for NO, 1 for YES - meaning it will show files from the subdirs
        // Files from subdirs will be prefixed with the subdir name and checked for the required extentions.
        // @exts can be either a string or an array, if not passed to the function, then the default will be a check for common image files
        // If exts is set to "all" then all extentions are allowed
        // @ext_save : 1 for YES, 0 for NO - meaning it will filter out system files or not (such as .htaccess)

        if (!$exts || empty($exts) || $exts == "") {
            $exts = array("jpg", "gif", "jpeg", "png", "xls", "xlsx", "doc", "docx", "odt", "ods", "pdf", "zip", "ppt", "txt", "csv", "tsv");
        }
        
        $dirname = trim(rtrim($dirname, '/\\'));
        $filelist = array();
        
        try {
            $handle = opendir($dirname);
        } catch (\Exception $e) {
            return $filelist;
        }

         
         while (false !== ($file = readdir($handle))) {
            // Filter out higher directory references
            if ($file == "." || $file == "..") { continue; }
            
            // Only look at directories or files, filter out symbolic links
            if ( filetype ($dirname."/".$file) == "link") { continue; }
            
            
                 
                // If it's a file, check against valid extentions and add to the list
                if ( filetype ($dirname."/".$file) == "file" ) {
                   if (self::checkFileExtention($file, $exts, $ext_save)) {
                       $filelist[] = $file;
                    }
                }
                // If it's a directory and either subdirs should be listed or files from subdirs add relevant names to the list
                else if ( filetype ($dirname."/".$file) == "dir" && ($show_subdirs == 1 || $show_subdirfiles == 1)) {
                    if ($show_subdirs == 1) {
                        $filelist[] = $file;
                    }
                    if ($show_subdirfiles == 1) {
                        $subdirname = $file;
                        $subdirfilelist = self::getDirectoryListing($dirname."/".$subdirname."/", $sortorder, $show_subdirs, $show_subdirfiles, $exts, $ext_save);
                        for ($i = 0 ; $i < count($subdirfilelist) ; $i++) {
                            $subdirfilelist[$i] = $subdirname."/".$subdirfilelist[$i];
                        }
                        $filelist = array_merge($filelist, $subdirfilelist);
                    }

                }
        }
        closedir($handle);

        // Sort the results
        if (count($filelist) > 1) {
             natcasesort($filelist);
             if ($sortorder == "d" || $sortorder == "r" ) {
                 $filelist = array_reverse($filelist, TRUE);
             }
         }
         return $filelist;

     }
     
     
    public static function after ($needle, $inthat){
        if (!is_bool(strpos($inthat, $needle))) {
            return substr($inthat, strpos($inthat,$needle)+strlen($needle));
        }
    }

    public static function after_last ($needle, $inthat){
        if (!is_bool(self::strrevpos($inthat, $needle))) {
            return substr($inthat, self::strrevpos($inthat, $needle)+strlen($needle));
        }
    }

    public static function before ($needle, $inthat){
        return substr($inthat, 0, strpos($inthat, $needle));
    }

    public static function before_last ($needle, $inthat){
        return substr($inthat, 0, self::strrevpos($inthat, $needle));
    }
    
    
    /**
     * Nalezne pocizi posledniho vyskytu nejakeho znaku ve stringu
     * @param string $haystack 
     * @param string $needle
     * @return int
     */
    public static function strrevpos($haystack, $needle) {
        $rev_pos = strpos (strrev($haystack), strrev($needle));
        return ($rev_pos===false) ? false : strlen($haystack) - $rev_pos - strlen($needle);
    }
    
    
    /**
     * Generator nahodneho retezce bez cisel
     * @param int $lenght Delka nahodneho retezce
     * @return string
     */
    public static function rndStrS($lenght = 6, $bToUpper = false) {
        $strRdm = "";
        $i = 1;
        while ($i <= $lenght){
            do {
                $intRdm = mt_rand(65,122);
                if (!($intRdm>89 && $intRdm<98)){
                    $i++;
                    $strRdm .= chr($intRdm);
                    break;
                }
            } while(0);
        }
        
        return ($bToUpper) ?  mb_strtoupper($strRdm) : $strRdm;
    }
    
    /**
     * Generator nahodnych cisel
     */
    public static function rndInt($lenght) {
        $start = sprintf("%'1".$lenght."s", 1);
        $end = sprintf("%'9".$lenght."s", 9);
        return mt_rand($start ,$end);
    }
    
    
    /**
     * Validace obsahu promenne. 
     * @param string $val Obsahuje nazev promenne (key pole globalni promenne)
     * @param string $type Ocekavany typ ctene promenne 
     * @return mixed Obsah promenne po validaci, nebo false pri neuspechu
     */
    public static function validate($val, $type) {
        /** @var string Regexpy pro pouziti ve funkci validate (jako parametr pro FILTER_VALIDATE_REGEXP) */
        $regexes = Array(
            'phone' => "^[\+]?[0-9]{9,14}\$",
            'psc' => "^[1-9][0-9]{4}\$");
        
        $flags = [];
        
        if (empty($type)) { return $val; }
        if (empty($val) && ($type == 'float' || $type == 'int')) { $val = 0; return $val; }
        if($type=='string') {
            return $val;
        }elseif ($type == 'phone') {
            $val = trim(str_replace(array(' ', '-', '/'), '', $val));
            return filter_var($val, FILTER_VALIDATE_REGEXP, array("options"=> array("regexp"=>'!'.$regexes["phone"].'!i')));
        }elseif($type=='psc') {
            $val = trim(str_replace(array(' ', '-'), '', $val));
            return filter_var($val, FILTER_VALIDATE_REGEXP, array("options"=> array("regexp"=>'!'.$regexes["psc"].'!i')));
        }elseif ($type == 'email') {
            $filter = FILTER_VALIDATE_EMAIL;
        }elseif ($type == 'float') { 
            $filter = FILTER_VALIDATE_FLOAT;
        }elseif ($type == 'int') { 
            $filter = FILTER_VALIDATE_INT;
        }elseif ($type == 'boolean') { 
            $filter = FILTER_VALIDATE_BOOLEAN;
        }elseif ($type == 'ipv4') { 
            $filter = FILTER_VALIDATE_IP;
            $flags = FILTER_FLAG_IPV4;
        }elseif ($type == 'ipv6') { 
            $filter = FILTER_VALIDATE_IP;
            $flags = FILTER_FLAG_IPV6;
        }elseif ($type == 'url') { 
            $filter = FILTER_VALIDATE_URL;
        }
        if(!empty($filter)) { return filter_var($val, $filter, $flags); }
        return $val;
    }
    
    /**
     * Zvaliduje e-mail
     * @param string $str
     * @return mixed Bud vrati zvalidovany e-mail nebo false
     */
    public static function validateEmail($str) {
        return self::validate($str, 'email');
    }
    
    /**
     * Alternativa k funkci validateEmail
     * @param string $str
     * @return mixed Bud vrati zvalidovany e-mail nebo false
     */
    public static function validateMail($str) {
        return self::validate($str, 'email');
    }
    
    /**
     * Zvaliduje url adresu
     * @param string $str
     * @return mixed Bud vrati zvalidovanou url nebo false
     */
    public static function validateUrl($str) {
        return self::validate($str, 'url');
    }
    
    /**
     * Zvaliduje telefon
     * @param string $str
     * @return mixed Bud vrati zvalidovane telefonni cislo nebo false
     */
    public static function validatePhone($str) {
        return self::validate($str, 'phone');
    }
    
    /**
     * Zvaliduje PSC
     * @param string $str
     * @return mixed Bud vrati zvalidovane PSC nebo false
     */
    public static function validatePSC($str) {
        return self::validate($str, 'psc');
    }
    
    /**
     * Zvaliduje IP adresu - IPV4
     * @param string $str
     * @return mixed Bud vrati zvalidovanou IP adresu nebo false
     */
    public static function validateIPV4($str) {
        return self::validate($str, 'ipv4');
    }
    
    
    /**
    * Funkce prevede odradkovany text na jednu radku. Konce radku mohou byt nahrazeny oddelovacem
    * @param string $str text, ktery chci sanitizeHTMLt
    * @param string znak, kterym nahradim konce radku - default ,
    * @return str
    */
    public static function nl2line($str, $delimiter = ', '){
        $finalStr = '';
        if (is_array($str)) { return ''; }
        if (empty($str)) { return ''; }
        $aStr = explode(PHP_EOL, $str);
        //debug($aStr);
        foreach ($aStr AS $val) {
            if (empty(trim($val, $delimiter))) { continue; }
            if (empty(trim($val, trim($delimiter)))) { continue; }
            if (empty(trim($val))) { continue; }
            $finalStr .= $val.$delimiter;
        }
        return $finalStr;
   }
    
   public static function curl_get_contents ($Url, $bLog = false) {
    
        if (!function_exists('curl_init')){ 
            die('CURL is not installed!');
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $Url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($bLog) { // open file for READ and write 
            $curl_log = fopen("upload/curl.txt", 'w');
            curl_setopt($ch, CURLOPT_VERBOSE, true);
            curl_setopt($ch, CURLOPT_STDERR, $curl_log);
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $output = curl_exec($ch);
        curl_close($ch);
        if ($bLog) { fclose($curl_log); }

        return $output;
    }
    
    
    public static function celeCisloSlovy($cislo) {
        self::fixIntRef($cislo);
        $aCislo = array();
        $aSlova = array(
            0 => array (0=> "", 1=>"jedna", 2=>"dvě", 3=>"tři", 4=>"čtyři", 5=>"pět", 6=>"šest", 7=>"sedm", 8=>"osm", 9=>"devět", 10=>"deset", 11=>"jedenáct", 12=>"dvanáct", 13=>"třináct", 14=>"čtrnáct", 15=>"patnáct", 16=>"šestnáct", 17=>"sedmnáct", 18=>"osmnáct", 19=>"devatenáct"),
            1 => array (1=>"deset", 2=>"dvacet", 3=>"třicet", 4=>"čtyřicet", 5=>"padesát", 6=>"šedesát", 7=>"sedmdesát", 8=>"osmdesát", "devadesát"),
            2 => array (1=>"jednosto",2=> "dvěstě", 3=>"třista", 4=>"čtyřista", 5=>"pětset", 6=>"šestset", 7=>"sedmset", 8=>"osmset", "devětset"),
            3 => array (1=>"jedentisíc", 2=>"dvatisíc", 3=>"třitisíce", 4=>"čtyřitisíce", 5=>"pěttisíc", 6=>"šesttisíc", 7=>"sedmtisíc", 8=>"osmtisíc", "devěttisíc"),
            6 => array (1=>"jedenmilión", 2=>"dvamilióny", 3=>"třimilióny", 4=>"čtyřimilióny", 5=>"pětmiliónů", 6=>"šestmiliónů", 7=>"sedmmiliónů", 8=>"osmmiliónů", "devětmiliónů")
        );

        if ($cislo == 0) { return 'nula'; }
        $aCislo[] = ($cislo < 0) ? 'mínus' : '' ;
        $cislo = abs($cislo);
        if (strlen($cislo) > 7) { return 'Chyba. Max. hodnta čísla je 9 999 999.'; }
        
        for ($i = 1; $i <= strlen($cislo); $i++) {
            $rad = strlen($cislo) - $i;
            $znak = substr($cislo, ($i - 1), 1);
            //Resim miliony
            if ($rad == 6 ) { $aCislo[] = $aSlova[$rad][$znak]; continue; }
            
            //Resim statisice
            if ($rad == 5) { 
                if ($znak == 0) { continue; }
                $aCislo[] = $aSlova[2][$znak];
                if (utils::fixInt(substr($cislo, $i, 2)) == 0) { //celych sto, dveste, trista, ... tisic
                    $i = $i + 2;
                    $aCislo[] = "tisíc";
                }
                continue;
            }
            
            //Resim desetitisice
            if ($rad == 4 ) { 
                if ($znak == 0) { continue; }
                if ($znak == 1 ) { //deset, jedenact, dvanact, ... tisic
                    $aCislo[] = $aSlova[0][substr($cislo, ($i - 1), 2)];
                    $aCislo[] = "tisíc";
                    $i++;
                }elseif (utils::fixInt(substr($cislo, $i, 1)) == 0) { //celych dvacet, tricet, ctyricet, ... tisic
                    $aCislo[] = $aSlova[1][$znak];
                    $aCislo[] = "tisíc";
                    $i++;
                }else{
                    $aCislo[] = $aSlova[1][$znak];

                }
                continue;
            }
            if ($znak == 0 && in_array($rad, array(1,0))) { continue; }
            
            if ($znak == 1 && $rad == 1) { //deset, jedenact, dvanact, ...
                $aCislo[] = $aSlova[0][substr($cislo, ($i - 1), 2)];
                $i++;
                continue;
            }

            $aCislo[] = $aSlova[$rad][$znak];
        }

        return implode('', $aCislo);
        
    }
    

    public static function getLocaleDateTime(?string $date = '', string $locale = '') : string {
        if (utils::fixInt($date)===0) { return ''; }
        if (trim($locale == '')) { $locale = \Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']); }
        
        $origLocale = setlocale(LC_ALL, 0);
        setlocale (LC_ALL, $locale);
        
        $unixTime = ($date=='') ? time() : ((utils::fixFloat($date) == $date) ? $date : strtotime($date));
        
        $formatter = new \IntlDateFormatter($locale, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::SHORT);
        if ($formatter === null) {
            //throw new \InvalidConfigException(intl_get_error_message());
        }
        
        $formatedDate = $formatter->format($unixTime);
        setlocale (LC_ALL, $origLocale);
        return $formatedDate;
    }    
    
    public static function getLocaleDate(?string $date = '', string $locale = '') : string {
        if (utils::fixInt($date)===0) { return ''; }
        if (trim($locale == '')) { $locale = \Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']); }
        
        $origLocale = setlocale(LC_ALL, 0);
        setlocale (LC_ALL, $locale);
        
        $unixTime = ($date=='') ? time() : ((utils::fixFloat($date) == $date) ? $date : strtotime($date));
        
        $formatter = new \IntlDateFormatter($locale, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::NONE);
        if ($formatter === null) {
            //throw new \InvalidConfigException(intl_get_error_message());
        }
        
        $formatedDate = $formatter->format($unixTime);
        setlocale (LC_ALL, $origLocale);
        return $formatedDate;
    }
            
    public static function toCamel(string $text) : string {
        $veta = ucwords(str_replace('-', ' ', $text));
        return str_replace(' ', '', $veta);
    }
    
    public static function toCamelRef(string &$text) {
        $text = self::toCamel($text);
    }
    
    public static function mb_strtoupperRef(&$str) {
        $str = mb_strtoupper($str);
    }
    
    public static function mb_strtolowerRef(&$str) {
        $str = mb_strtolower($str);
    }
    
    /*
    public static function tidyHtml($str) : ?string {
        if (empty($str)) { return null; }
        
        //Tidy obsahuje chybu, ze meni nastaveni LOCALE. Prto si povodni locale ulozim 
        //do $origLovćale a po pouziti tidy jej nastavim zpet
        
        $origLocale = setlocale(LC_ALL, 0);
        
        $config = array(
            'drop-proprietary-attributes' => false,
            'drop-empty-paras' => false,
            'enclose-text' => false,
            'fix-backslash' => false,
            'force-output' => false,
            'hide-comments' => false,
            'indent' => true,
            'indent-spaces' => 4,
            'join-classes' => true,
            'join-styles' => true,
            'logical-emphasis' => true,
            'show-body-only' => true,
            'merge-divs' => true,
            'word-2000' => true,
            'wrap' => 200);

        // Tidy
        $tidy = new \tidy;
        $tidy->parseString($str, $config, 'utf8');
        $tidy->cleanRepair();
        setlocale (LC_ALL, $origLocale);
        // Output
        return $tidy;
    }
    */
    
    public static function getQueryParams() {
        parse_str( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_QUERY), $aQueryParams );
        return $aQueryParams;
    }
    
    /**
     * @param int $jenAktivni
     * @return boolean
     */
    public static function jenAktivni(?int $jenAktivni = null) {
        utils::fixIntRef($jenAktivni);
        if (count(self::getQueryParams())>0) { $jenAktivni = request::int("jenAktivni"); }
        return ($jenAktivni==1);
    }
    
    public static function strToArray(&$input) {
        if (is_array($input)) : return; endif;
        if ($input=='') : $input = []; return; endif;
        $tmp = $input;
        unset ($input);
        $input = [$tmp];
    }
    
    public static function objToArray(&$input) {
        if (gettype($input)=='object') : $input = $input->toArray(); endif;
        $input = json_decode(json_encode($input,true), true);
    }
    
    public static function setNull($input, $bInt = false) {
        if ($input == '' || $input == "0" || $input == 'NULL') : return NULL; endif;
	if ($bInt) : return self::fixFloat($input); endif;
	return "'".$input."'";
    }
}



/**
 * V PHP < 7 neexistuje funkce array_key_first(), ktery vraci první klic pole
*/
if (!function_exists('array_key_first')) {
    
    /**
     * V PHP < 7 neexistuje funkce array_key_first(), ktery vraci první klic pole
     * @param array $arr
     * @return string
     */
    function array_key_first($arr = array()) {
        if (!is_array($arr)) { return NULL; }
        $arrKeys = array_keys($arr);
        return $arrKeys[0];
    }
}

