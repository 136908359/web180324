<?php

# 配置表解析

class Config_Model extends CI_Model {
	private $last_error;
	function __construct() {
		parent::__construct();
		$this->load->database();
	}
	function get($name,$rowValue,$colValue) {
		$this->last_error = "";

		$row = $this->db->query("select content from gm_table where name=?",array($name))->row_array();
		if ( !$row ) {
			return null;
		}
		$content = $row['content'];
		$info = $this->parse($content);
		$table = $info["Table"];
		$rowHeaders = $info["RowHeaders"];
		$colHeaders = $info["ColHeaders"];

		if ( !isset($rowHeaders[$rowValue]) || !isset($colHeaders[$colValue]) ) {
			return null;
		}
		$rowID = $rowHeaders[$rowValue];
		$colID = $colHeaders[$colValue];
		if ( !isset($table[$rowID][$colID]) ) {
			return null;
		}
		return $table[$rowID][$colID];
	}
	function parse_items($s) {
		$this->last_error = "";
		$items = array();

		$a = explode(";",$s);
		foreach( $a as $k=>$v ) {
			$item = explode("*",$v);
			$items []= array("Id"=>intval($item[0]),"Num"=>intval($item[1]));
		}
		return $items;
	}
	function parse($content) {
		$this->last_error = "";

		$content = str_replace("\r\n","\n",$content);	
		$lines = explode("\n",$content);

		$table = array();
		$rowHeaders = array();
		$colHeaders = array();
		foreach ($lines as $rowID=>$line) {
			$line = trim($line);
			if ( !$line ) {
				continue;
			}
			$cols = explode("\t",$line);
			if ( $rowID == 1 ) {
				foreach ( $cols as $colID => $col ) {
					$colHeaders[$col] = $colID;	
				}
			}
			$colNum = count($colHeaders);
			if ( $rowID > 1 ) {
				$rowHeaders[$cols[0]] = $rowID;
				if ( $colNum != count($cols) ) {
					$this->last_error = "[line:$rowID]$line is invalid";
				}
			}
			$table []= $cols;
		}
		return array(
			"Table"=>$table,
			"RowHeaders"=>$rowHeaders,
			"ColHeaders"=>$colHeaders,
		);
	}
	function get_last_error() {
		$err = $this->last_error;
		$err = str_replace("\t","  ",$err);
		return $err;
	}
}
