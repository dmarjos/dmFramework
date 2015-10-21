<?php
Application::Uses("com.admin.PanelAdmin");

class roles_y_reglas extends PanelAdmin {

  public $rules   = 'roles';
  public $package = "app.admin.administracion";

  public $rol = array();
  public $rul = array();
  public $rule = array();

  public $rulesTree=array();
  public $rulesCombo=array();
  
  public function create() {
	Application::addScript("/resources/js/admin/administracion/roles_y_reglas.js");
  	parent::create();
  }

  public function init() {
  	$retVal=parent::init();
  	if (!$retVal) return false;
    $db = Application::GetDatabase();
    $this->rul = $db->getRow("SELECT * FROM tbl_usuario_rule WHERE rul_codigo = ".intval($_GET['id2']));
    $this->rol = $db->getRow("SELECT * FROM tbl_usuario_role WHERE rol_codigo = ".intval($_GET['id']));
    $this->rule = empty($this->rol) ? array() : unserialize($this->rol['rol_rules']);
    $this->rule = is_array($this->rule) ? $this->rule : array();
    if (isset($_POST['submitted'])) {
      $this->rol['rol_nombre'] = $_POST['nombre'];
      $this->rol['rol_front'] = intval($_POST['roltipo']) == 0 ? 0 : 1;
      $this->rule = array();
      $values = explode(';',$_POST['values']);
      foreach ($values as $val) {
        $val = explode('=',$val);
        if (trim($val[0]) != '' && trim($val[1]) != '' && intval($val[1]) != 0) {
          $this->rule[$val[0]] = intval($val[1]);
        }
      }
      if ($_POST['submitted'] != 6) {
        $this->rul['rul_nombre'] = strtolower($_POST['clave']);
        $this->rul['rul_descripcion'] = $_POST['nombre2'];
        $this->rul['rul_codrul'] = intval($_POST['parent']);
        $this->rul['rul_rules'] = UserRules::VIEW;
      }
      $_POST['accion']['insert'] && ($this->rul['rul_rules'] = $this->rul['rul_rules'] | UserRules::INSERT);
      $_POST['accion']['update'] && ($this->rul['rul_rules'] = $this->rul['rul_rules'] | UserRules::UPDATE);
      $_POST['accion']['delete'] && ($this->rul['rul_rules'] = $this->rul['rul_rules'] | UserRules::DELETE);
      switch ($_POST['submitted']) {
        case 1: $this->addRol($db); break;
        case 2: $this->updRol($db); break;
        case 3: $this->delRol($db); break;
        case 4: $this->addRul($db); break;
        case 5: $this->updRul($db); break;
        case 6: $this->delRul($db); break;
      }
    }
  	$this->getRolValues();
  	$this->getRoles();
  	$this->getRules($this->rul['rul_codigo'],-1);
  	$rules=$this->rulesCombo;
  	$this->getRules($this->rul['rul_codrul'],$this->rul['rul_codigo']);
  	$parentRules=$this->rulesCombo;
  	$this->view->assign("rules",$rules);
  	$this->view->assign("parentRules",$parentRules);
  	$this->view->assign("rol",$this->rol);
  	$this->view->assign("rul",$this->rul);
  	$this->view->assign("rule",$this->rule);
  	return true;
  	
  }
  private function delRol($db) {
    if ($this->meetRules('roles',UserRules::DELETE)) {
      try {
        $rec = $db->getRow("SELECT * FROM tbl_usuario LEFT JOIN tbl_usuario_role ON rol_codigo = usr_codrol WHERE usr_codrol = ".intval($this->rol['rol_codigo']));
        if (empty($rec)) {
          $db->execute("DELETE FROM tbl_usuario_role WHERE rol_codigo = ".intval($this->rol['rol_codigo']));
          Application::Redirect(Application::Get('SELF').'.php?id2='.intval($this->rul['rul_codigo']));
        } else {
          $this->errors[] = "No se puede eliminar el rol <b>".Application::GetString($rec['rol_nombre'])."</b> porque se encuentra en uso por uno o mas usuarios.";
        }
      } catch (Exception $e) {
        $this->errors[] = TException::getErrorMessage($e);
      }
    } else {
      $this->errors[] = "Su cuenta no posee los permisos necesarios para realizar esta accion";
    }
  }

  private function addRol($db) {
  	if ($this->meetRules('roles',UserRules::INSERT)) {
    	
      try {
        if ($this->validate()) {
          $db->execute("INSERT INTO tbl_usuario_role (rol_nombre,rol_rules,rol_front) VALUES ('".$db->escape($this->rol['rol_nombre'])."','".$db->escape(serialize($this->rule))."',".intval($this->rol['rol_front']).")");
          Application::Redirect(Application::get('SELF').'.php?id='.$db->lastInsertId().'&id2='.intval($this->rul['rul_codigo']));
          //echo Application::get('SELF').'.php?id='.$db->lastInsertId().'&id2='.intval($this->rul['rul_codigo']);
          die();
        }
      } catch (Exception $e) {
        $this->errors[] = TException::getErrorMessage($e);
      }
    } else {
      $this->errors[] = "Su cuenta no posee los permisos necesarios para realizar esta acci�n";
    }
  }

  private function updRol($db) {
    if ($this->meetRules('roles',UserRules::UPDATE)) {
      try {
        if ($this->validate()) {
          $validos = array();
          foreach ($this->rule as $name => $val) {
            $rec = $db->getRow("SELECT * FROM tbl_usuario_rule WHERE rul_nombre = '".$db->escape($name)."'");
            if (!empty($rec)) { $validos[$name] = $val; }
          }
          $db->execute("UPDATE tbl_usuario_role SET rol_nombre = '".$db->escape($this->rol['rol_nombre'])."', rol_rules = '".$db->escape(serialize($validos))."', rol_front = ".intval($this->rol['rol_front'])." WHERE rol_codigo = ".intval($this->rol['rol_codigo']));
          Application::Redirect(Application::get('SELF').'.php?id='.intval($this->rol['rol_codigo']).'&id2='.intval($this->rul['rul_codigo']));
          //echo Application::get('SELF').'.php?id='.intval($this->rol['rol_codigo']).'&id2='.intval($this->rul['rul_codigo']);
          die();
        }
      } catch (Exception $e) {
        $this->errors[] = TException::getErrorMessage($e);
      }
    } else {
      $this->errors[] = "Su cuenta no posee los permisos necesarios para realizar esta acci�n";
    }
  }

  private function addRul($db) {
    if ($this->meetRules('reglas',UserRules::VIEW)) {
      try {
        if ($this->validate2($db)) {
          $db->execute("INSERT INTO tbl_usuario_rule (rul_codrul,rul_rules,rul_nombre,rul_descripcion) VALUES ('".intval($this->rul['rul_codrul'])."','".intval($this->rul['rul_rules'])."','".$db->escape($this->rul['rul_nombre'])."','".$db->escape($this->rul['rul_descripcion'])."')");
          $id = $db->lastInsertId();
          $default = 0;
          $_POST['default']['view'] && ($default = $default | UserRules::VIEW);
          $_POST['default']['insert'] && ($default = $default | UserRules::INSERT);
          $_POST['default']['update'] && ($default = $default | UserRules::UPDATE);
          $_POST['default']['delete'] && ($default = $default | UserRules::DELETE);
          if ($default > 0) {
            try {
              $recs = $db->execute("SELECT * FROM tbl_usuario_role");
              while ($rec=$db->getNextRecord($recs)) {
                $rules = unserialize($rec['rol_rules']);
                $rules[$this->rul['rul_nombre']] = $default;
                $db->execute("UPDATE tbl_usuario_role SET rol_rules = '".$db->escape(serialize($rules))."' WHERE rol_codigo = ".intval($rec['rol_codigo']));
              }
            } catch (Exception $e) { }
          }
          Application::Redirect(Application::get('SELF').'.php?id2='.$id.'&id='.intval($this->rol['rol_codigo']));
        }
      } catch (Exception $e) {
        $this->errors[] = TException::getErrorMessage($e);
      }
    } else {
      $this->errors[] = "Su cuenta no posee los permisos necesarios para realizar esta acci�n";
    }
  }

  private function updRul($db) {
    if ($this->meetRules('reglas',UserRules::VIEW)) {
      try {
        if ($this->validate2($db)) {
          $db->execute("UPDATE tbl_usuario_rule SET rul_codrul='".intval($this->rul['rul_codrul'])."', rul_rules='".intval($this->rul['rul_rules'])."',rul_descripcion='".$db->escape($this->rul['rul_descripcion'])."' WHERE rul_codigo = ".intval($this->rul['rul_codigo']));
          if (($this->rul['rul_rules'] & UserRules::INSERT) != UserRules::INSERT) {
            $this->unsupport($db,$this->rul['rul_nombre'],UserRules::INSERT);
          }
          if (($this->rul['rul_rules'] & UserRules::UPDATE) != UserRules::UPDATE) {
            $this->unsupport($db,$this->rul['rul_nombre'],UserRules::UPDATE);
          }
          if (($this->rul['rul_rules'] & UserRules::DELETE) != UserRules::DELETE) {
            $this->unsupport($db,$this->rul['rul_nombre'],UserRules::DELETE);
          }
          Application::Redirect(Application::get('SELF').'.php?id2='.intval($this->rul['rul_codigo']).'&id='.intval($this->rol['rol_codigo']));
        }
      } catch (Exception $e) {
        $this->errors[] = TException::getErrorMessage($e);
      }
    } else {
      $this->errors[] = "Su cuenta no posee los permisos necesarios para realizar esta acci�n";
    }
  }

  private function delRul($db) {
    if ($this->meetRules('reglas',UserRules::VIEW)) {
      try {
        $rec = $db->getRow("SELECT * FROM tbl_usuario_rule WHERE rul_codrul = ".intval($this->rul['rul_codigo']));
        if (!empty($rec)) {
          $this->errors[] = "No se puede eliminar una regla que contenga a otras reglas. Primero elimine las reglas contenidas y vuelva a intentarlo";
        }
        $used = array();
        $recs = $db->execute("SELECT * FROM tbl_usuario_role");
        while ($rec=$db->getNextRecord($recs)) {
          $rules = unserialize($rec['rol_rules']);
          if ($rules[$this->rul['rul_nombre']] && ($rules[$this->rul['rul_nombre']] & UserRules::VIEW) == UserRules::VIEW) {
            $used[] = $rec['rol_nombre'];
          }
        }
        if (!empty($used)) {
          $this->errors[] = "No se puede eliminar la regla <b>".$this->rul['rul_nombre']."</b> porque la aplicaci&oacute;n ha detectado que los siguientes roles de usuario la est&aacute;n utilizando: <b><i>".Application::GetString(implode(', ',$used),200)."</i></b>";
        }
        if (empty($this->errors)) {
          $db->execute("DELETE FROM tbl_usuario_rule WHERE rul_codigo = ".intval($this->rul['rul_codigo']));
          Application::Redirect(Application::get('SELF').'.php?id='.intval($this->rol['rol_codigo']));
        }
      } catch (Exception $e) {
        $this->errors[] = "Se produjo un error al eliminar la regla:<br />".TException::getErrorMessage($e);
      }
    } else {
      $this->errors[] = "Su cuenta no posee los permisos necesarios para realizar esta acci�n";
    }
  }

  private function validate() {
    if (trim($this->rol['rol_nombre']) == '') {
      $this->errors[] = "El nombre del rol no puede quedar vac�o";
    }
    $found = false;
    // Normalizar rules
    foreach ($this->rule as $rule => $value) {
      $active = (intval($value) & UserRules::VIEW) == UserRules::VIEW;
      // Si no esta activa, desactivar el resto de las acciones
      $this->rule[$rule] = $active ? intval($value) : 0;
      // Chequear que al menos una est� activa
      $found = $found || $active;
    }
    if (!$found) {
      $this->errors[] = "Debe activar al menos una regla";
    }
    return empty($this->errors);
  }

  private function validate2($db) {
    if (trim($this->rul['rul_descripcion']) == '') {
      $this->errors[] = "El nombre de la regla no puede quedar vac�o";
    }
    if ($_POST['submitted'] != 5) {
      if (!preg_match('/^[a-z][a-z0-9]+$/',$this->rul['rul_nombre'])) {
        $this->errors[] = "La clave de la regla debe ser alfanum�rica, de al menos 2 caracteres y comenzar con una letra";
      } else {
        try {
          $rec = $db->getRow("SELECT * FROM tbl_usuario_rule WHERE rul_nombre = '".$db->escape($this->rul['rul_nombre'])."'");
          if (!empty($rec)) {
            $this->errors[] = "La clave <b>".Application::GetString($this->rul['rul_nombre'])."</b> ya se encuentra en uso por otra regla, escoja una clave diferente";
          }
        } catch (Exception $e) {
          $this->errors[] = "Se produjo un error al realizar la validaci�n:<br />".TException::getErrorMessage($e);
        }
      }
    }
    return empty($this->errors);
  }

  private function unsupport($db,$rule,$action) {
    try {
      $recs = $db->execute("SELECT * FROM tbl_usuario_role");
      while ($rec=$db->getNextRecord($recs)) {
        $rules = unserialize($rec['rol_rules']);
        if ($rules[$rule] && ($rules[$rule] & $action) == $action) {
          $rules[$rule] = $rules[$rule] & ~$action;
          $db->execute("UPDATE tbl_usuario_role SET rol_rules = '".$db->escape(serialize($rules))."' WHERE rol_codigo = ".intval($rec['rol_codigo']));
        }
      }
    } catch (Exception $e) { }
  }

  protected function getRoles() {
    $grupo = '';
    $db = Application::GetDatabase();
    $recs = $db->execute("SELECT * FROM tbl_usuario_role ORDER BY rol_front DESC, rol_nombre");
    $rec = $db->getNextRecord($recs);
    $roles=array();
    while (!empty($rec)) {
    	$option=array();
    	if ($rec['rol_codigo'] == intval($this->rol['rol_codigo']))
    		$option["selected"]=1;
    	$option["text"]=Application::GetString($rec['rol_nombre']);
    	$option["value"]=intval($rec['rol_codigo']);
    	$roles[]=$option;
    	$rec = $db->getNextRecord($recs);
    }
    $this->view->assign("roles",$roles);
  }

  protected function getRules($selected,$skip=-1) {
    $db = Application::GetDatabase();
    $recs = $db->execute("SELECT * FROM tbl_usuario_rule ORDER BY rul_descripcion");
    $rules = array();
    while ($rec=$db->getNextRecord($recs)) {
      $rules[$rec['rul_codrul']][] = $rec;
    }
    $this->rulesCombo=array();
    $level = 0;
    $this->getRulesTree($rules, 0, $level, intval($selected), $skip);
  }

  private function getRulesTree($tree, $index, &$level, $selected, $skip) {
  	if (is_array($tree[$index])) {
      foreach ($tree[$index] as $op) {
      	if ($op['rul_codigo'] != $skip) {
          $padding = '';
        	for ($i=0; $i < $level; $i++) { $padding .= '&nbsp;&nbsp;&nbsp;&nbsp;'; };
          $select = $op['rul_codigo'] == intval($selected) ? '1' : '0';
        	$this->rulesCombo[]=array("value"=>intval($op['rul_codigo']),"selected"=>$select,"text"=>$padding.Application::GetString($op['rul_descripcion']));
          $level++;
          $this->getRulesTree($tree,$op['rul_codigo'],$level,$selected,$skip);
          $level--;
        }
      }
    }
  }

  protected function getRolValues() {
    $tree = array();
    $indent = array();
    $collapsed = array();
    $db = Application::GetDatabase();
    $recs = $db->execute("SELECT * FROM tbl_usuario_rule ORDER BY rul_descripcion");
    while ($rec=$db->getNextRecord($recs)) {
      $tree[$rec['rul_codrul']][] = $rec;
    }
    $this->drawRules($tree, 0, $indent, $collapsed);
    $this->view->assign("rolvalues",$this->rulesTree);
  }

  private function drawRules($tree, $index, &$indent, &$collapsed) {
    if (is_array($tree[$index])) {
      foreach ($tree[$index] as $rule) {
        $id = count($indent);
        while ($id > 0) {
          $id--;
          $indent[$id] = $indent[$id]-1;
          if ($indent[$id] > 0) {
            $indent[$id] = $indent[$id]+count($tree[$rule['rul_codigo']]);
          }
        }
        $count = is_array($tree[$index]) ? count($tree[$index]) : 0;
        $trid = count($indent).':'.Application::GetString($rule['rul_nombre']);
        $this->drawRule($tree, $rule, $indent, $collapsed);     // dibuja + indent
        if ($count > 0) { array_push($collapsed, intval($_COOKIE['rule-node-collapse'][$trid])); }
        array_push($indent,count($tree[$rule['rul_codigo']]));
        $this->drawRules($tree, $rule['rul_codigo'], $indent, $collapsed);
        array_pop($indent);
        if ($count > 0) { array_pop($collapsed); }
      }
    }
  }

  private function drawRule($tree, $rule, $indent, $collapsed) {
    $visible = 1;
    $collapse = 0;
    $index = $rule['rul_codigo'];
    $db = Application::GetDatabase();
    $count = is_array($tree[$index]) ? count($tree[$index]) : 0;
    $trid = count($indent).':'.Application::GetString($rule['rul_nombre']);
    for ($i = count($collapsed)-1; $visible == 1 && $i >= 0; $i--) {
      $visible = $visible && !$collapsed[$i] ? 1 : 0;
    }
    if ($count > 0) {
      $collapse = intval($_COOKIE['rule-node-collapse'][$trid]);
    }
    // Can delete
    $rol = $db->getRow("SELECT * FROM tbl_usuario_rule WHERE rul_codrul = '".intval($rule['rul_codigo'])."'");
    $used = array();
    $recs = $db->execute("SELECT * FROM tbl_usuario_role");
    while ($rec=$db->getNextRecord($recs)) {
      $rules = unserialize($rec['rol_rules']);
      if ($rules[$rule['rul_nombre']] && ($rules[$rule['rul_nombre']] & UserRules::VIEW) == UserRules::VIEW) {
        $used[] = $rec['rol_nombre'];
      }
    }
    $can_delete = empty($rol) && empty($used);
    // Dibujar la regla
    $ruleRow=array(
    	"trid"=>$trid,
    	"classes"=>array(),
    	"children"=>array()
    );
    if ($count>0)
    	$ruleRow["classes"][]="haschild";
    if ($collapse)
    	$ruleRow[$trid]["classes"][]="collapsed";
    if (!$visible)
    	$ruleRow[$trid]["classes"][]="display-none";
    
    $theCell='<td class="tree-node" style="border-left:1px solid #ddd">';
    foreach ($indent as $id => $childsleft) { $theCell.='<div class="indent'.($childsleft >= 0 ? ($childsleft > 0 ? ' dotted' : ' shortdot') : '').'">'.($id == count($indent)-1 ? '<div class="node">&nbsp;</div>' : '&nbsp;').'</div>'; }
    switch (count($indent)) {
      case 0: $style = 'color:#05f;font-weight:bold'; break;
      case 1: $style = 'font-weight:bold'; break;
      default: $style = '';
    }
    $theCell.='<div class="button'.($count > 0 ? ' expand' : '').'" onclick="toogleNode(this)">&nbsp;</div>';
    if ($this->meetRules('reglas',UserRules::VIEW)) {
      $theCell.='<div class="button '.($can_delete ? 'delete' : 'deldis').'"'.($can_delete ? ' onclick="doDelRul2('.intval($rule['rul_codigo']).')"' : '').'>&nbsp;</div>';
    }
    $theCell.='<div class="caption" style="'.$style.'">'.Application::GetString($rule['rul_descripcion'],25).'</div></td>';
    $ruleRow["children"][]=$theCell;
    $ruleRow["children"][]='<td style="text-align:center;padding:0px;border-bottom:0px;border-top:1px solid #ddd"><div style="margin:2px;width:13px;height:13px;overflow:hidden">'.$this->drawRuleCheckbox($rule, UserRules::VIEW).'</div></td>';
    $ruleRow["children"][]='<td style="text-align:center;padding:0px;border-bottom:0px;border-top:1px solid #ddd"><div style="margin:2px;width:13px;height:13px;overflow:hidden">'.$this->drawRuleCheckbox($rule, UserRules::INSERT).'</div></td>';
    $ruleRow["children"][]='<td style="text-align:center;padding:0px;border-bottom:0px;border-top:1px solid #ddd"><div style="margin:2px;width:13px;height:13px;overflow:hidden">'.$this->drawRuleCheckbox($rule, UserRules::UPDATE).'</div></td>';
    $ruleRow["children"][]='<td style="text-align:center;padding:0px;border-bottom:0px;border-top:1px solid #ddd"><div style="margin:2px;width:13px;height:13px;overflow:hidden">'.$this->drawRuleCheckbox($rule, UserRules::DELETE).'</div></td>';
    if ($this->meetRules('reglas',UserRules::VIEW)) {
      $ruleRow["children"][]='<td style="background:#f5f5f5;text-align:left;padding:3px 2px 0px;color:#1c59ca;border-bottom:0px;border-top:1px solid #ddd">'.Application::GetString($rule['rul_nombre']).'</td>';
    }
    $this->rulesTree[]=$ruleRow;
  }

  private function drawRuleCheckbox($rule, $type) {
    if ($rule['rul_rules'] & $type) {
      return '<input class="checkbox" type="checkbox" value="'.$rule['rul_nombre'].':'.$type.'"'.(($this->rule[$rule['rul_nombre']] & $type) == $type ? ' checked="checked"' : '').' style="float:none" onclick="typeof doRolClick == \'function\' && doRolClick(this)"'.($type != 1 && ($this->rule[$rule['rul_nombre']] & UserRules::VIEW) != UserRules::VIEW ? ' disabled="disabled"' : '').' />';
    } else {
      return '&nbsp;';
    }
  }

}

?>