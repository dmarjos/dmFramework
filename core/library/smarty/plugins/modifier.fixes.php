<?php

function smarty_modifier_fixes($str){
            $open = '<blockquote>';  
            $close = '</blockquote>';
    
            preg_match_all ('/\<cite\>/i', $str, $matches);
            $opentags = count($matches['0']);
    
            preg_match_all ('/\<\/cite\>/i', $str, $matches);
            $closetags = count($matches['0']);
    
            $unclosed = $opentags - $closetags;
            for ($i = 0; $i < $unclosed; $i++) {
                    $str .= '</blockquote>';
            }
    
            $str = str_replace ('<cite>', $open, $str);
            $str = preg_replace('/\<cite\]/is','<blockquote>', $str);
            $str = str_replace ('</cite>', $close, $str); 
            
            /* */
            $open = '<strong>';  
            $close = '</strong>';
    
            preg_match_all ('/\<b\>/i', $str, $matches);
            $opentags = count($matches['0']);
    
            preg_match_all ('/\<\/b\>/i', $str, $matches);
            $closetags = count($matches['0']);
    
            $unclosed = $opentags - $closetags;
            for ($i = 0; $i < $unclosed; $i++) {
                    $str .= '</strong>';
            }
    
            $str = str_replace ('<b>', $open, $str);
            $str = preg_replace('/\<b\]/is','<strong>', $str);
            $str = str_replace ('</b>', $close, $str);
                 
            /* */
            $open = '<em>';  
            $close = '</em>';
    
            preg_match_all ('/\<i\>/i', $str, $matches);
            $opentags = count($matches['0']);
    
            preg_match_all ('/\<\/i\>/i', $str, $matches);
            $closetags = count($matches['0']);
    
            $unclosed = $opentags - $closetags;
            for ($i = 0; $i < $unclosed; $i++) {
                    $str .= '</em>';
            }
    
            $str = str_replace ('<i>', $open, $str);
            $str = preg_replace('/\<i\]/is','<em>', $str);
            $str = str_replace ('</i>', $close, $str);                                                                  
         return $str;
}