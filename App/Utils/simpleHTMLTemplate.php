<?php
namespace Latecka\HomeApp\Utils;

/**
 * Jednoduchý sablonovaci system pro HTML
 * Povinnym argumentem pri volani je $templateFile (cesta a nazev sablony)
 * Pro oddeleni nahrazovaneho vyrizu jsou urcene oddelovace startDelimiter a endDelimiter. Default {{ a }}
 * V pripade volani bloku ve smycce, je oddelovacem bloku tag {%LOOP XYZ%}{%ENDLOOP%}, kde XYZ je unikatni nazev bloku
 * 
 */
class simpleHTMLTemplate {
    
    /**
     * Cesta k souboru sablony
     * @var string
     */
    public $templateFile;
    
    /**
     * Finalni HTML
     * @var string
     */
    public $finalHTML;
    
    /**
     * Retezec oddelujici zacatek retezce k vymene. Default {{ 
     * @var string
     */
    public $startDelimiter;
    
    /**
     * Retezec oddelujici konec retezce k vymene. Default }} 
     * @var string
     */
    public $endDelimiter;
    
    /**
     * Pole obsahujici retezce k vymene. Nejedna-li se o retezce v cyklu LOOP, staci
     * je priradit $foo->LibovolnyNazevPromenne = HodnotaPromenne. Nazev promenne odpovida retezci v HTML sablone
     * V pripade, vymeny retezcu va smycce, prirazuje se hodnota do pole $foo->aVars["NazevBloku"]["NazevPromenneVBloku"]
     * @var array
     */
    public $aVars;
    
    /**
     * Pomocna promenna
     * @var string
     */
    private $srcHTML;
    
    public function __construct($templateFile) {
        $this->startDelimiter = '{{ ';
        $this->endDelimiter = ' }}';
        
        if(!file_exists($templateFile) && $templateFile <> '') {
            trigger_error('Template File not found!',E_USER_ERROR);
            return;
        }
        $this->templateFile = $templateFile;
    }
    
    
    public function __set($name, $value) {
        $this->aVars[$name] = $value;
    }
    
    public function render($aVars = '', $srcHtml = '') {
        if (!is_array($aVars) ) { $aVars = $this->aVars; }
        if ($srcHtml == '') {
            if(file_exists($this->templateFile) == false || $this->templateFile == '') {
                trigger_error('Template File \''.$this->templateFile.'\' not found!',E_USER_ERROR);
                return;
            }
            $srcHtml = $this->srcHTML = str_replace (array('<!-- {%', '%} -->'), array('{%', '%}'), file_get_contents($this->templateFile));
            $srcHtml = $this->renderLoops();
        }
        foreach($aVars as $key => $value) {
            if (is_array($value)) { continue; }
            $srcHtml = str_replace($this->startDelimiter .$key. $this->endDelimiter, $value, $srcHtml);
            //debug([$this->startDelimiter .$key. $this->endDelimiter, $value, $srcHtml]);
        }
        $this->finalHTML = $srcHtml;
        return $this->finalHTML;
    }
    
    private function renderLoops() {
        $finalLoopHTML = "";
        $matches = array();
        preg_match_all ("%\{\%LOOP[^\%}]((?s).*?){\%ENDLOOP\%}%", $this->srcHTML, $matches);
        foreach ($matches[1] AS $loop) {
            $finalLoopHTML = "";
            list ($key, $val) = explode('%}', $loop);
            foreach ($this->aVars[trim($key)] AS $valLoopArray) {
                $this->bTest = true;
                $finalLoopHTML .= $this->render($valLoopArray, $val);
            }
            unset($this->aVars[trim($key)]);
            $this->srcHTML = str_replace('{%LOOP ' .$loop . '{%ENDLOOP%}' , $finalLoopHTML, $this->srcHTML);
            $this->render($this->aVars[trim($key)], trim($val));
        }
        return $this->srcHTML;
    }
}
