<?php
Application::Uses("sys.web.TWidget");
class TDatagrid extends TWidget {
	
	protected function init() {
		$buttons=0;
		if (($this->parameters["id_field"]&&($this->parameters["form"] || $this->parameters["custom_action"])) || ($this->parameters["indexable"])) {
			if (($this->parameters["id_field"]&&($this->parameters["form"] || $this->parameters["custom_action"])) && Application::$page->meetRules(Application::$page->rules,UserRules::UPDATE)) $buttons++;
			if (($this->parameters["id_field"]&&($this->parameters["form"] || $this->parameters["custom_action"])) && Application::$page->meetRules(Application::$page->rules,UserRules::DELETE)) $buttons++;
			if ($this->parameters["indexable"]) $buttons+=2;
			$visibleColumns++;
		}
		
		$this->view->assign("buttonsColumnWidth",(40*$buttons));
		$this->view->assign("tableId",$this->name);
		$this->view->assign("columns",$this->parameters["columns"]);
		$this->view->assign("noSort",$this->parameters["noSort"]);
		$this->view->assign("noSearch",$this->parameters["noSearch"]);
		$this->view->assign("title",$this->parameters["title"]);
		$this->view->assign("subtitle",$this->parameters["subtitle"]);
		$this->view->assign("indexable",$this->parameters["indexable"]);
		$this->view->assign("ajaxUrl",$this->parameters["url"]);
		$this->view->assign("formUrl",$this->parameters["form"]);
		$this->view->assign("breadcrumb",$this->parameters["breadcrumb"]);
		//dump_var($this->parameters);
		$this->view->assign("extraButtons",$this->parameters["extra_buttons"]);
		$this->view->assign("actionToCall",($this->parameters["custom_action"]?$this->parameters["custom_action"]:'doAction'));
		
	}
	
}