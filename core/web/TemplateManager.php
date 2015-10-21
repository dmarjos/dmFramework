<?php
class TemplateManager {
    
    private $callbacks=array();
    
    public function __construct() { 
    } 
     
    private function prParseParameters($theParamStr) { 
        $params=array(); 
        if (preg_match_all('/([A-Za-z_0-9\-]*)="([A-Za-z0-9_\s\+\-\*\/\.\(\)\[\]=><!\'&|]*)"/s',$theParamStr,$matches,PREG_SET_ORDER)) { 
            foreach($matches as $match) { 
                $theParamValue=$match[2]; 
                if (substr($theParamValue,-1)=="/") $theParamValue=substr($theParamValue,0,-1); 
                $params[strtoupper($match[1])]=str_replace('"','',$theParamValue); 
            } 
        } elseif (preg_match_all('/([A-Za-z_0-9\-]*)=([A-Za-z0-9_\+\-\*\/\.\(\)\[\]=><!]*)/s',$theParamStr,$matches,PREG_SET_ORDER)) { 
            foreach($matches as $match) { 
                $theParamValue=$match[2]; 
                if (substr($theParamValue,-1)=="/") $theParamValue=substr($theParamValue,0,-1); 
                $params[strtoupper($match[1])]=str_replace('"','',$theParamValue); 
            } 
        } 
         
        return $params; 
    } 

    private function prProcessBlockTags($source,$vars="") { 

        if (empty($vars)) $vars=$this->TemplateVars; 
        $auxPlaceHolder=$source; 
        if(preg_match_all("/(<TPL:([a-z]*)(\s+)([^>]*)(\s*)>)(.*?)(<\/TPL:\\2>)/Usi",$auxPlaceHolder,$matches,PREG_SET_ORDER)) { 
            dump_var($matches);
        } 
        $auxPlaceHolder=$this->prProcessSimpleTags($auxPlaceHolder,$vars); 
        return $auxPlaceHolder; 
         
    } 

    function prProcessSimpleTags($theBlock) {
        $auxPlaceHolder=$theBlock; 
        foreach($this->callbacks as $tagName=>$params) {
            if(preg_match_all("/(<TPL:{$tagName}([^\/]*)\/>)/si",$auxPlaceHolder,$matches,PREG_SET_ORDER)) {
                foreach($matches as $tag) {
                    $params=(
                    !empty($tag[2])?$this->prParseParameters(trim($tag[2])):array()
                    );
                    $params["full_tag"]=$tag[0];
                    if ($this->callbacks[$tagName]) {
                        $auxPlaceHolder=call_user_func($this->callbacks[$tagName],$auxPlaceHolder,$params);
                    }
                }
                
            } 
        }
        return $auxPlaceHolder; 
    }     

    public function prProcessTemplate($placeHolder) {     
        $auxPlaceHolder=$placeHolder; 
        $auxPlaceHolder=$this->prProcessBlockTags($auxPlaceHolder); 
        $auxPlaceHolder=$this->prProcessSimpleTags($auxPlaceHolder); 
        return $auxPlaceHolder; 
    } 

    /**
     * Set callbacks for tags
     */

    public function setTagCallback($tag,$callback) {
        $tag=strtolower($tag);
        $this->callbacks[$tag]=$callback;
    }

    /* 
    Below are the block tag processing functions 
    */      
    private function bt_foreach($Params,$tplBlock,$vars) { 
     
        if (isset($Params["CONTROL"]))      
        $tplVars=$vars[$Params["CONTROL"]];  
        else  
        return $tplBlock; 
         
        if (isset($Params["ITEM"]))  
        $loopIdentifier=$Params["ITEM"]."."; 
        else  
        return $tplBlock; 
         
        if (!is_array($tplVars) || (count($tplVars)==0)) return $tplBlock; 
            // 
            $_tplVars=array(); 
        foreach($tplVars as $idx => $rec) { 
            $_rec=array(); 
            if (is_array($rec)) { 
                foreach($rec as $f => $v) $_rec[$loopIdentifier.$f]=$v; 
                $_tplVars[]=$_rec; 
            } 
        } 
         
        $tplVars=$_tplVars; 
        $source=array(); 
        foreach($tplVars as $idx => $record) { 
            $src=$tplBlock; 
        foreach($record as $key => $val)  
            if (!is_array($val)) $src=str_replace("{".$key."}",$val,$src); 
                 
        $src=$this->prProcessTemplate($src,array($key=>$val)); 

        $source[]=$src; 
        } 
        $auxPlaceHolder=implode("\n",$source); 
            return $auxPlaceHolder; 
    } 

    /* 
    Below are the single tag processing functions 
    */ 

    private function tg_htmlselect($Params) { 
     
        if (!$this->prCheckParameters($Params,array("NAME"=>PARAM_STRING))) return ""; 

        $cboName=$Params["NAME"]; 
        $cboOptions=$this->prGetValue($Params["OPTIONS"],$this->TemplateVars); 
         
        $htmlCode="<select name=\"$cboName\""; 
         
        if (isset($Params["MULTIPLE"])) $htmlCode.=" multiple"; 
        if (isset($Params["CLASS"])) $htmlCode.=" class=\"".$Params["CLASS"]."\""; 
        if (isset($Params["ID"])) $htmlCode.=" id=\"".$Params["ID"]."\""; 
        if (isset($Params["SIZE"])) $htmlCode.=" size=\"".$Params["SIZE"]."\""; 
         
        foreach(array("ONCLICK","ONCHANGE","ONBLUR") as $event) 
            if (isset($Params[$event])) $htmlCode.=" $event=\"".$Params[$event]."\""; 

        $htmlCode.=">\n"; 
        $options=array(); 
        foreach($cboOptions as $idx => $option) { 
        if (is_array($option)) { 
            $_option=array(); 
            $keys=array_keys($option); 
            foreach($keys as $key) $_option[strtoupper($key)]=$option[$key]; 
            if (isset($_option["VALUE"]) && isset($_option["TEXT"])) { 
            $theOption="<option value=\"".$_option["VALUE"]."\""; 
            if (isset($_option["SELECTED"])) 
                $theOption.=" selected"; 
            $theOption.=">".$_option["TEXT"]."</option>"; 
            $options[]=$theOption; 
            } 
        } 
        } 
        $htmlCode.=implode("\n",$options)."\n</select>\n"; 
                 
        return $htmlCode; 
    }     
     
    private function tg_htmlradio($Params) { 
     
        if (!$this->prCheckParameters($Params,array("NAME"=>PARAM_STRING))) return ""; 

        $cboName=$Params["NAME"]; 
        $cboOptions=$this->prGetValue($Params["OPTIONS"],$this->TemplateVars); 
         
        $options=array(); 
        foreach($cboOptions as $idx => $option) { 
        if (is_array($option)) { 
            $_option=array(); 
            $keys=array_keys($option); 
            foreach($keys as $key) $_option[strtoupper($key)]=$option[$key]; 
            if (isset($_option["VALUE"]) && isset($_option["TEXT"])) { 
                $htmlCode="<input type=\"radio\" name=\"$cboName\""; 
         
            if (isset($Params["CLASS"])) $htmlCode.=" class=\"".$Params["CLASS"]."\""; 
            if (isset($Params["ID"])) $htmlCode.=" id=\"".$Params["ID"]."\""; 
            $htmlCode.=" value=\"".$_option["VALUE"]."\""; 
            if (isset($_option["CHECKED"])) 
                $htmlCode.=" checked"; 
            $htmlCode.=" />".$_option["TEXT"]; 
            $options[]=$htmlCode; 
            } 
        } 
        } 
        $htmlCode=implode("\n",$options)."\n"; 
                 
        return $htmlCode; 
    }     
     
    private function tg_htmlcheck($Params) { 
     
        if (!$this->prCheckParameters($Params,array("NAME"=>PARAM_STRING))) return ""; 

        $cboName=$Params["NAME"]; 
        $cboOptions=$this->prGetValue($Params["OPTIONS"],$this->TemplateVars); 
         
        $options=array(); 
        foreach($cboOptions as $idx => $option) { 
        if (is_array($option)) { 
            $_option=array(); 
            $keys=array_keys($option); 
            foreach($keys as $key) $_option[strtoupper($key)]=$option[$key]; 
            if (isset($_option["VALUE"]) && isset($_option["TEXT"])) { 
                $htmlCode="<input type=\"checkbox\" name=\"{$cboName}[]\""; 
         
            if (isset($Params["CLASS"])) $htmlCode.=" class=\"".$Params["CLASS"]."\""; 
            if (isset($Params["ID"])) $htmlCode.=" id=\"".$Params["ID"]."\""; 
            $htmlCode.=" value=\"".$_option["VALUE"]."\""; 
            if (isset($_option["CHECKED"])) 
                $htmlCode.=" checked"; 
            $htmlCode.=" />".$_option["TEXT"]; 
            $options[]=$htmlCode; 
            } 
        } 
        } 
        $htmlCode=implode("\n",$options)."\n"; 
                 
        return $htmlCode; 
    }     
     
    private function tg_Include($Params) { 
        if (!$this->prCheckParameters($Params,array("FILE"=>PARAM_STRING))) return ""; 
        $fileName=""; 
        $baseDir=""; 
        if(isset($Params["FILE"])) $fileName=$Params["FILE"]; 
        if(isset($Params["DIR"])) $baseDir=$Params["DIR"]; 
        if (!empty($baseDir)) $fileName=$baseDir."/".$fileName; 
        if (file_exists($this->TemplateDir."/".$fileName)) { 
            $retVal=$this->ProcessFile($fileName); 
        } else 
            $retVal=""; 

        return $retVal; 
    } 
}
