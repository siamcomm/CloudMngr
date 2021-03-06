<?php
/* Copyright Mark Walker (AWcode) 2014
 *
 * CloudMngrWebsiteModule Class
 */

class CloudMngrWebsiteModule extends CloudMngrBaseModule{
	private $data_arr;
	protected $module_display_name = "";
	protected $module_type = "Website";
	
	function __construct($group_id="", $region_id=""){
		parent::__construct($group_id, $region_id);
	}

	protected function _getTotalCount(){
		$cnt = 0;
		$groups = $this->group()->getAllGroups();
		if(! $this->arrFull($groups)) return 0;
		foreach($groups as $id => $group){
			$data = $this->loadByGroup($id);
			if($this->arrFull($this->data_arr['websites'])) $cnt += count($this->data_arr['websites']);
		}
		return $cnt;
	}
	
	protected function _getCountByRegion($region_id=""){
		$cnt = 0;
		$region_id = (($region_id)?$region_id:$this->region_id);
		$groups = $this->group()->getAllGroups();
		if(! $this->arrFull($groups)) return 0;
		foreach($groups as $id => $group){
			$this->loadByGroup($id);
			if(in_array($region_id, $group['regions'])){
				if($this->arrFull($this->data_arr['websites'])) $cnt += count($this->data_arr['websites']);
			}
		}
		return $cnt;
	}
	
	protected function _getCountByGroup($group_id=""){
		$group_id = ($group_id!="") ? $group_id : $this->group_id;	
		$data = $this->loadByGroup($group_id);
		if(! $this->arrFull($this->data_arr['websites'])) return 0;
		$cnt = 0;
		if($this->arrFull($this->data_arr['websites'])) $cnt += count($this->data_arr['websites']);
		return $cnt;
	}
	
	protected function _getHealthByRegion(){
		return "Coming soon";
	}
	
	protected function _getHealthByGroup(){
		return "Coming soon";
	}

	function loadByGroup($group_id=""){
		$group_id = ($group_id!="") ? $group_id : $this->group_id;	
		if(!$group_id) return $this->_error("No Group ID");

		$data_str = @file_get_contents($this->base_path."/data/".$this->module_name."/group-".$group_id);
		$this->data_arr = json_decode($data_str, true);

		return $this->data_arr;
	}

	function writeArray(){
		$this->runHooks("beforeWriteArray", $this->module_name);
		if(!is_dir($this->base_path. "/data/".$this->module_name)){mkdir($this->base_path. "/data/".$this->module_name);}
		file_put_contents($this->base_path. "/data/".$this->module_name."/group-".$this->group_id, json_encode($this->data_arr, 128));
		$this->runHooks("afterWriteArray", $this->module_name);
	}

	function getData(){
		$this->loadByGroup();
		if(! is_array($this->data_arr)) return array();
		return $this->data_arr;

	}

	function getRegion($region_id=""){
		$region_id = ($region_id!="") ? $region_id : $this->region_id;	
		if(!$region_id) return -1;

		$this->loadByGroup();
		if(! is_array($this->data_arr)) return array();

		return $this->data_arr['regions'][$region_id];
	}


	function saveConfig(){
		$this->runHooks("beforeSaveConfig", $this->module_name);
		$this->loadByGroup();

		if(! is_array($this->data_arr)) $this->data_arr = array();

		$this->data_arr['website-default']['init'] = $_POST['init'];
		$this->data_arr['website-default']['config'] = $_POST['config'];

		$this->writeArray();
		return $this->data_arr;
		$this->runHooks("afterSaveConfig", $this->module_name);
	}


	function addNew($config){
		$this->runHooks("beforeAddNewWebsite", $this->module_name);
		
		$web_key = preg_replace("/[^a-zA-Z0-9]+/", "", $config['hostname']);
		$append = "";
		while(isset($this->data_arr['websites'][$web_key.$append])){$append +=1;}
		$web_key .= $append;
		$this->data_arr['websites'][$web_key]['hostname'] = $config['hostname'];
		$this->data_arr['websites'][$web_key]['user'] = $config['user'];
		$this->data_arr['websites'][$web_key]['directory'] = $config['directory'];
		
		$this->writeArray();
		
		$this->runHooks("afterAddNewWebsite", $this->module_name);
		$this->redirect("/?page=group&id=".$config['group']);
	}

	function modifyConfig($config){
		$this->runHooks("beforeModifyWebsite", $this->module_name);
		
		
		$this->runHooks("afterModifyWebsite", $this->module_name);
	}

	function remove($web_key){
		$this->runHooks("beforeRemoveWebsite", $this->module_name);

		unset($this->data_arr['websites'][$web_key]);
		$this->writeArray();
		
		echo($this->module_display_name." Deleted");
		
		$this->runHooks("afterRemoveWebsite", $this->module_name);
	}
}
