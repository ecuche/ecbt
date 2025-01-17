<?php
// declare(strict_types= 1);

namespace Framework\Helpers;
use Framework\Helpers\Data;
use Framework\Helpers\MyCSV;


// require_once("MyCSV.class.php");

class CSV{
	private $_csv,
			$_table = null,
			$_table_name = null,
			$_table_dir = null,
			$_search = [],
			$_last_id,
			$_count_null_field = 0,
			$_count_not_null_field = 0,
			$_all_row_count = 0;

	public function __construct($table = null, $folder = null){
		$this->_table_dir = $folder ? "{$_ENV['CSV_PATH']}/{$folder}" : "{$_ENV['CSV_PATH']}";
		$this->_table = $table ? "{$this->_table_dir}/{$table}" : null;
		$this->_table_name = $table ?? null ;
		$this->_csv = $table ? new MyCSV($this->_table): new MyCSV() ;
	}

	public function csv(): MyCSV
	{
		return $this->_csv;
	}
	
	public function getRow($id = null){
		if(isset($id) && is_int((int) $id)){
			return (object) $this->_csv->data($id);
		}else{
			$result = [];
			if($this->_csv->count()){
				$ids = $this->_csv->ids();
				foreach($ids as $id){
					$rand_questions = $this->_csv->data($id);
					array_push($result, $rand_questions);
				}
				return (object) $result;
			}else{
				return false;
			}
		}
	}

	public function insertRows(array $data, $multi = false){
		if(Data::is_multi_dim($data) && $multi){
			foreach($data as $value){
				$this->_csv->insert($value);
			}
				$this->_csv->write();
				$this->_last_id = $this->_csv->insert_id();
				return $this->_last_id;
		}elseif(is_array($data) && !$multi){
			$this->_csv->insert($data);
			$this->_csv->write();
			$this->_last_id = $this->_csv->insert_id();
			return $this->_last_id;
		}else{
			return false;
		}
	}

	public function addFields($field){
		if(is_array($field)){
			foreach($field as $key=>$value){
				$this->_csv->add_field($value);
			}
			$this->_csv->write();
			return true;
		}else{
			$result = $this->_csv->add_field($field);
			$this->_csv->write();
			return $result;
		}
	}

	public function getFieldNames(){
		return $this->_csv->fields;
	}

	public function randRows($num = null){
		$result = [];
		$all_id = $this->_csv->ids();
		shuffle($all_id);
		$ids = $num ? array_rand(array_flip($all_id), $num) : $all_id;
		$max = $this->_csv->count();
		$min = 1;
		if(empty($num) || (isset($num) && $num <= $max && $num >= $min)){
			foreach($ids as $id){
				$rand_questions = $this->_csv->data($id);
				array_push($result, $rand_questions);
			}
			return $result;
		}
		return false;
	}

	public function randRow(): array | object | bool
	{
		$id = self::randId();
		return $this->_csv->data($id);
	}
	
	public function deleteRow($ids){
		if(is_array($ids)){
			foreach($ids as $id){
				$this->_csv->delete($id);
			}
		}
		if(is_int((int)$ids)){
			$this->_csv->delete($ids);
		}
		if(!isset($ids)){
			$this->_csv->delete();
		}
		$this->_csv->write();
		return true;
	}

	public function updateRows(array $data, $id = null){
		if(Data::is_multi_dim($data) && !isset($id)){
			foreach($data as $key => $value){
				$value_id = $value['id'];
				unset($value['id']);
				$this->_csv->update($value, $value_id);
			}
		}elseif(isset($id) && is_int((int)$id)){
			$this->_csv->update($data, $id);
		}
		$this->_csv->write();
		$this->_last_id = $this->_csv->insert_id();
		return $this->_last_id;
	}

	public function lastId(){
		return $this->_csv->last();
	}

	public function tableExists(){
		if($this->_csv->exists()){
			return true;
		}else{
			return false;
		}
	}

	public function deleteCSV(){
		if($this->_csv->exists()){
			unlink($this->_table);
			return true;
		}else{
			return false;
		}
	}

	public function clearCSV(){
		if($this->_csv->exists()){
			$this->_csv->drop_table();
			$this->_csv->write();
            return true;
		}else{
            return false;
        }
	}

	public function randId(){
		return $this->_csv->rand();
	}

	public function tableName(){
		return $this->_csv->tablename();
	}

	public function andSearch(array $where1, $where2 = []){

        if(count($where1) === 2){
		    while ($row = $this->_csv->each()) {
				if($row[$where1[0]] == $where1[1]){
					array_push($this->_search, $row);
				}
			}
		}

        if(count($where2) === 2){
            $result         = $this->_search;
            $this->_search  = [];
            foreach($result as $row) {
				if($row[$where2[0]] == $where2[1]){
					array_push($this->_search, $row);
				}
			}
        }
		return $this->_search;
	}

    public function orSearch(array $where1, $where2 = []){
        if(count($where1) === 2){
            while ($row = $this->_csv->each()) {
				if($row[$where1[0]] == $where1[1]){
					array_push($this->_search, $row);
				}
			}
		}
		if(count($where2) === 2){
			$this->_csv->reset();
			while ($row = $this->_csv->each()) {
				if(($row[$where2[0]] == $where2[1]) && !in_array($row, $this->_search)){
					array_push($this->_search, $row);
				}
			}
		}
		return $this->_search;
	}

	public function countNullFields($field): int
	{
		$this->_count_null_field = 0;
        foreach(self::getRow() as $key => $value){
            if(empty($value[$field])){
                $this->_count_null_field++;
            }
        }
        return $this->_count_null_field;
	}

	public function countNotNullFields($field): int
	{
		$this->_count_not_null_field = 0;
        foreach(self::getRow() as $key => $value){
            if(!empty($value[$field])){
                $this->_count_not_null_field++;
            }
        }
        return $this->_count_not_null_field;
	}

	public function countAllRow(): int
	{
		$this->_all_row_count = 0;
		if(empty(self::getRow())){
			return false;
		}
        foreach(self::getRow() as $key => $value){
            $this->_all_row_count++;
        }
        return $this->_all_row_count;
	}

	public function end()
	{
		return $this->_csv->close();
	}

	
}