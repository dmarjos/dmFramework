<?php
function utf8_encode_mix($input, $encode_keys=false)
{
    if(is_array($input))
    {
        $result = array();
        foreach($input as $k => $v)
        {                
            $key = ($encode_keys)? utf8_encode($k) : $k;
            $result[$key] = utf8_encode_mix( $v, $encode_keys);
        }
    }
    else
    {
        $result = utf8_encode($input);
    }

    return $result;
}
function smarty_modifier_to_utf8($string){	
	return utf8_encode_mix($string);
}