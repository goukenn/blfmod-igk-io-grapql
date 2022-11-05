<?php

// @author: C.A.D. BONDJE DOUE
// @filename: GraphQl
// @date: 20221104 19:24:33
// @desc: 
namespace igk\io\GraphQl;


use IGK\System\IO\Configuration\ConfigurationReader;

/**
 * parse custom graphQL 
 * @package igk\io\GraphQl
 */
class GraphQlParser{
    private $m_text;
    private $m_token;
    private $m_offset = 0;
    private $m_readMode = self::READ_NAME;
    private $m_listener;
    private $m_declared_types = [];
    const READ_NAME = 0;
    const READ_READ_TYPE = 1;
    const READ_READ_DEFAULT = 2;
    const READ_ARGUMENT = 3;
    const READ_END_ARGUMENT = 4;
    const READ_DEFINITION = 5;

    const T_GRAPH_START = 1;
    const T_GRAPH_END = 2;
    const T_GRAPH_NAME = 3;
    const T_GRAPH_TYPE = 4;
    const T_GRAPH_DEFAULT = 5;
    const T_GRAPH_ARGUMENT = 6;
    const T_GRAPH_COMMENT = 7;
    const T_GRAPH_INTROPECTION = 8; 
    const T_GRAPH_DECLARE_TYPE = 9; 
    const T_GRAPH_DECLARE_INPUT = 10;
    const T_GRAPH_DEFINITION = 11;
    const LITTERAL_TOKEN = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_';

    private function _create_info(){
        return (object)[
            "name"=>null,
            "type"=>null,
            "default"=>null,
        ];
    }
    /**
     * parse graph string
     * @param string $graph 
     * @return void 
     */
    public static function Parse(string $graph, $data =null, $listener = null){
        $o = null;
        $parser = new static;
        $parser->m_listener = $listener;
        $parser->_load($graph, $data, $o);        
        return (object)$o;
    }
    protected function _load(string $graph, $data = null , & $o=null){
        $parser = $this;
        $parser->m_text = $graph;        
        $q = [];
        $p = [];
        $f_info = $parser->_create_info();
        while($parser->read()){
            $v = $e = $parser->token();
            $id = null;
            if (is_array($e)){
                $id = $e[0];
                $v = $e[1];
            }
            switch($id){
                case self::T_GRAPH_START:
                    if (is_null($o)){
                        // first object
                        $o = [];
                    } else {
                        igk_debug_wln("add - parent ");
                        if (!empty($f_info->name)) {
                            $n = trim($f_info->name);
                            $o[$n] = [];
                            $q[] = & $o;
                            $o = & $o[$n];
                            $f_info = $parser->_create_info();
                        }
                        else{
                            igk_die("no name defined");
                        }
                        // array_push($q, $o);
                        //     $n = $f_info->name; // $this->_update_mark($o, $f_info, $data);                          
                        //     $tab = [];
                        //     $o[] = [$n => & $tab];                            
                        //     array_push($q, $o);
                        //     $o = & $tab;
                        //     $f_info = $parser->_create_info();
                        // } else{                            
                        //     array_push($q, $o);
                        //     $o = [];  
                        // }
                    }                  
                    break;
                case self::T_GRAPH_END:
                    if (!empty($f_info->name)) {
                        $this->_update_mark($o, $f_info, $data);  
                    }
                    if (($c = count($q))>0){
                        $o = & $q[$c-1];
                        array_pop($q);
                    }
                    if (count($p)>0){
                        $data = array_pop($p);
                    }
                    break;
                case self::T_GRAPH_TYPE:
                    $f_info->type = $v;                    
                    break;
                case self::T_GRAPH_DEFAULT:
                    $f_info->default = $v;
                    break;
                case self::T_GRAPH_NAME:   
                    if (!empty($f_info->name)) {
                        $this->_update_mark($o, $f_info, $data);
                    }
                    $f_info->name = $v;                    
                    break;
                case self::T_GRAPH_DEFINITION:
                    $f_info->type = $v["type"];
                    $f_info->default = $v["default"];
                    $this->_update_mark($o, $f_info, $data);
                    break;
                case self::T_GRAPH_ARGUMENT:
                    if (!empty($n = $f_info->name)) {
                        $tab = $this->_get_field_info($n);
                        $n = $tab["name"];
                        // must call argument
                        $cvalue = $this->m_listener->$n((array)$v);
                        array_push($p, $data);
                        $data = $cvalue;
                        $f_info->name = $tab["alias"];
                    }
                    break;
            }
        }
        if (!empty($f_info->name)) {
            $this->_update_mark($o, $f_info, $data);
        }
    }
    protected function _get_field_info($n){
        $tab = explode(' ', $n);
        $alias = $name = array_pop($tab);
        if (count($tab)>0){
            $alias = array_shift($tab);
        }
        return compact("name", "alias" );
    }
    protected function _update_mark(& $o, & $f_info, $data){
        $n = $f_info->name;
        if (empty($n)){
            igk_die("name is empty");
        }
        $v = $f_info->default;
        if (!is_null($data)){
            $v = igk_getv($data, $n, $f_info->default);
        }
        $o[$n] = $v;
        $f_info = $this->_create_info();
        return $n;
    }
    protected function __construct(){        
    }
    public function read(): bool{
        $pos = & $this->m_offset;
        $ln = strlen($this->m_text);
        $l = "";
        $skip = false;
        
        while($pos < $ln){
            $ch = $this->m_text[$pos];
            $pos++;
            switch($this->m_readMode){
                case self::READ_ARGUMENT:                    
                    $l = $ch.$this->_read_argument($pos, $ln);
                    $r = new ConfigurationReader;
                    $r->delimiter = ',';
                    $r->separator = ':';
                    $o = $r->read($l);
                        // read argument
                    $this->m_token = [self::T_GRAPH_ARGUMENT, $o]; 
                    $this->m_readMode = self::READ_NAME; 
                    return true;
                case self::READ_DEFINITION:                    
                    $l = $ch.$this->_read_definition($pos, $ln);
                    $r = new ConfigurationReader;
                    $o = $r->read($l);
                    $type = $o ? array_keys((array)$o)[0] : null;
                    $value = null;
                    if ($type && $this->_is_know_type($type)){
                        if (is_null($value = $o->$type)){
                            $value = $this->_get_known_default_value($type);
                        }else {
                            $value = $this->_get_known_value($type, $value);
                        }
                    }else{
                        if (!$type || is_null($value = $o->$type)){
                            // constant value
                            $value = $type;
                            $type = 'mixed';
                        }
                    }
                    $this->m_token = [self::T_GRAPH_DEFINITION, ['type'=>$type, 'default'=>$value]]; 
                    $this->m_readMode = self::READ_NAME;                   
                    return true;
            }

            switch($ch){
                case "#":
                    $e = strpos($this->m_text, "\n", $this->m_offset);
                    if ($e !== false){
                        $v = substr($this->m_text, $pos, $e - $pos);
                        $pos = $e +1;
                    }else{
                        $v = substr($this->m_text, $pos);
                        $pos = $ln+1;
                    }
                    $this->m_token = [self::T_GRAPH_COMMENT, $ch.$v];
                    return true;
                case '{':
                    $this->m_token = [self::T_GRAPH_START, $ch];
                    return true;
                case '}':
                    if (!empty($l)){
                        $pos--;
                        $this->m_token = [self::T_GRAPH_NAME, trim($l)];
                        return true;
                    }
                    $this->m_token = [self::T_GRAPH_END, $ch];
                    return true;
                case ':':
                    if ($this->_handle_name($l, $pos))
                        return true;                          
                    $this->m_token = $ch;
                    $this->m_readMode = self::READ_DEFINITION;  
                    return true; 
                case ' ':
                    if (!$skip)
                        $l .= $ch;
                    $skip = true;
                    break;
                case '(':
                    if ($this->_handle_name($l, $pos))
                        return true; 
                    $this->m_token = $ch;
                    $this->m_readMode = self::READ_ARGUMENT;
                    return true;
                case ')':
                    $this->m_token = $ch;
                    $this->m_readMode = self::READ_END_ARGUMENT;
                    return true;
                default:
                    $ip = strpos(self::LITTERAL_TOKEN, $ch);
                    if ( $ip !==false){
                        $l .= $ch;
                    } else {
                        if ($this->m_readMode == self::READ_NAME){
                            $n = trim($l);
                            if (strpos($n, "__")===0){
                                $this->m_token = [self::T_GRAPH_INTROPECTION, $n];
                                return true;
                            }
                            $token = $this->_get_token($n) ?? self::T_GRAPH_NAME;
                            $this->m_token = [$token, $n];
                            return true;
                        }
                    }
                    break;
            }
           
        }
        return false;
    }
    protected function _igk_get_know_parser($type){
  

        $cl = __NAMESPACE__.'\\GraphQl'.$type.'Parser';
        if (class_exists($cl)){
            $cl = new $cl;
            return $cl;
        } 
    }
    protected function _get_known_value($type, $value){
        if ($parser = $this->_igk_get_know_parser($type)){
            return $parser->parse($value);
        }
        return $value;
    }
    protected function _get_known_default_value($type){
        return igk_getv([
            "String"=>"",
            "Int"=>0,
            "Float"=>0.0,
            "Double"=>0.0,
            "Date"=>date("Y-m-d"),
            "DateTime"=>date("Y-m-d H:i:s"),
        ], $type);
    }
    protected function _is_know_type($t){
        return in_array($t, array_merge(explode('|', 'String|Int|Float|Double|Date|DataTime'), $this->m_declared_types));
        
    }
    public function token(){
        return $this->m_token;
    }
    protected function _handle_name($l, & $pos):bool{
        if (!empty($l)){
            $pos--;
            $this->m_token = [self::T_GRAPH_NAME, trim($l)];
            return true;
        }
        return false;
    }
    protected function _read_definition(& $pos, $ln){
        $v = "";
        while($pos < $ln){
            $ch = $this->m_text[$pos];
            $pos++;
            switch($ch){
                case '{':
                case '}':
                case "\n":
                case ',':
                    $pos--;
                    return $v;
            }
            $v .= $ch;
        }
        return $v;
    }
    protected function _read_argument(& $pos, $ln){
        $v = "";
        $depth = 1;
        while($pos < $ln){
            $ch = $this->m_text[$pos];
            $pos++;
            switch($ch){
                case '(':
                    $depth++;
                case ')':
                    $depth--;
                    if ($depth== 0){
                        $pos--;
                        return $v;
                    }
                    break;
            }
            $v .= $ch;
        }
        return $v;
    }
    protected function _get_token(string $n){
        return igk_getv([
            "type"=>self::T_GRAPH_DECLARE_TYPE,
            "input"=>self::T_GRAPH_DECLARE_INPUT,
            "query"=>self::T_GRAPH_DECLARE_INPUT,
            "mutation"=>self::T_GRAPH_DECLARE_INPUT,
            "on"=>self::T_GRAPH_DECLARE_INPUT,
            "extends"=>self::T_GRAPH_DECLARE_INPUT,
            "implements"=>self::T_GRAPH_DECLARE_INPUT,
            
        ], $n);
    }
}
