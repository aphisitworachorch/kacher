<?php

namespace Aphisitworachorch\Kacher\Controller;

use Aphisitworachorch\Kacher\Traits\DBMLSyntaxTraits;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Schema;

class DBMLController extends Controller
{
    use DBMLSyntaxTraits;

    /**
     *
     */
    public function __construct ($custom_type=null)
    {
        /*if ($custom_type != null){
            foreach($custom_type as $ct => $key) {
                DB::connection()->getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping($ct, $key);
            }
        }*/
    }

    /**
     *
     */
    private function getColumns($table_name, $type)
    {
        $columnInfo = [];
        if (!empty($table_name)) {
            $instance = Schema::getColumns ($table_name);
            foreach($instance as $tableColumn){
                if($type === "artisan"){
                    $columnInfo[] = "name : {$tableColumn['name']}\n" . "type : {$tableColumn['type']}\n";
                }
                if($type === "array"){
                    $special = [];
                    if($this->isPrimaryKey ($tableColumn['name'],$table_name) === "yes"){
                        $special[] = "pk";
                    }
                    if($this->isUniqueKey ($tableColumn['name'],$table_name) === "yes"){
                        $special[] = "unique";
                    }
                    $length = '';
                    if (preg_match('/.+\(([0-9]+)\)/', $tableColumn['type'], $matches)) {
                        $length = $matches[1];
                    }

                    $columnInfo[] = [
                        "name"=>$tableColumn['name'],
                        "type"=>$tableColumn['type'],
                        "special"=>$special,
                        "note"=>$tableColumn['comment'],
                        "default_value"=>$tableColumn['default'],
                        "is_nullable"=>($tableColumn['nullable'] ? "yes" : "no"),
                        "length"=>$length
                    ];
                }
            }
        }
        return $columnInfo;
    }

    /**
     *
     */
    private function getForeignKey($table_name, $type)
    {
        $columnInfo = [];
        if (!empty($table_name)) {
            $instance = Schema::getForeignKeys ($table_name);
            foreach($instance as $tableFK){
                $fromColumns = implode(" | ",$tableFK['columns']);
                $toColumns = implode(" | ",$tableFK['foreign_columns']);
                if($type === "artisan"){
                    $columnInfo[] = "[{$table_name}][{$fromColumns}] -> "."[$toColumns] of [{$tableFK['foreign_table']}]";
                }
                if($type === "array"){
                    $columnInfo[] = [
                        "from"=>$table_name,
                        "name"=>$fromColumns,
                        "to"=>$toColumns,
                        "table"=>$tableFK['foreign_table']
                    ];
                }
            }
        }
        return $columnInfo;
    }

    /**
     *
     */
    private function getIndexes($table_name, $type)
    {
        $columnInfo = [];
        if (!empty($table_name)) {
            $instance = Schema::getIndexes ($table_name);
            foreach($instance as $tableIndex){
                $unique = $tableIndex['unique'] ? "yes" : "no";
                $primary = $tableIndex['primary'] ? "yes" : "no";
                if($type === "artisan"){
                    $columns = implode(" | ",$tableIndex['columns']);
                    $columnInfo[] = "name : {$tableIndex['name']}\n"."columns : {$columns}\n"."unique : {$unique}\n"."primary : {$primary}\n";
                }
                if($type === "array"){
                    $columnInfo[] = ["name"=>$tableIndex['name'],"columns"=>$tableIndex['columns'],"unique"=>$unique,"primary"=>$primary,"table"=>$table_name];
                }
            }
        }
        return $columnInfo;
    }

    /**
     *
     */
    private function isPrimaryKey($column, $table_name){
        $primaryKeyInstance = Schema::getIndexes ($table_name);
        foreach($primaryKeyInstance as $tableIndex){
            if($tableIndex['name'] === $column){
                return $tableIndex['primary'] ? "yes" : "no";
            }
        }
        return 0;
    }

    /**
     *
     */
    private function isUniqueKey($column, $table_name){
        $uniqueKeyInstance = Schema::getIndexes ($table_name);
        foreach($uniqueKeyInstance as $tableIndex){
            if($tableIndex['name'] === $column){
                return $tableIndex['unique'] ? "yes" : "no";
            }
        }
        return 0;
    }
    /**
     *
     */
    public function getDatabaseTable($type){
        $tableName = Schema::getTables();
        $data = [];
        if($tableName){
            if($type === "artisan"){
                foreach($tableName as $tb){
                    $data[] = [
                        "table_name" => $tb['name'],
                        "columns" => implode("\n",$this->getColumns ($tb['name'],$type)),
                        "foreign_key" => implode("\n",$this->getForeignKey ($tb['name'],$type)),
                        "indexes"=> implode("\n",$this->getIndexes ($tb['name'],$type)),
                        "comment"=> $tb['comment']
                    ];
                }
                return $data;
            }
            if($type === "array"){
                foreach($tableName as $tb){
                    $data[] = [
                        "table_name" => $tb['name'],
                        "columns" => $this->getColumns ($tb['name'],$type),
                        "foreign_key" => $this->getForeignKey ($tb['name'],$type),
                        "indexes"=> $this->getIndexes ($tb['name'],$type),
                        "comment"=> $tb['comment']
                    ];
                }
                return $data;
            }
        }
    }

    public function getDatabasePlatform()
    {
        $db = env('DB_CONNECTION');
        $dbname = env('DB_DATABASE');
        return $this->projectName($dbname,$db);
    }

    /**
     */
    public function parseToDBML()
    {
        try{
            $table = $this->getDatabaseTable ("array");
            $syntax = $this->getDatabasePlatform();
            foreach($table as $info){
                if($info['table_name']){
                    $syntax .= $this->table ($info['table_name']) . $this->start ();
                    foreach($info['columns'] as $col){
                        $syntax .= $this->column ($col['name'],$col['type'],$col['special'],$col['note'],$col['is_nullable'],$col['default_value'] ?? null,'');
                    }
                    if($info['comment']){
                      $syntax .= "\n\tNote: '".$info['comment']."'\n";
                    }
                    if($info['indexes']){
                        $syntax .= $this->index () . $this->start ();
                        foreach($info['indexes'] as $index){
                            $type = "";
                            if($index['primary'] === "yes"){
                                $type = "pk";
                            }else if($index['unique'] === "yes"){
                                $type = "unique";
                            }
                            $syntax .= $this->indexesKey ($index['columns'],$type);
                        }
                        $syntax .= "\t".$this->end();
                    }
                    $syntax .= $this->end ();
                    if($info['foreign_key']){
                        foreach ($info['foreign_key'] as $fk){
                            $syntax .= $this->foreignKey ($fk['from'],$fk['name'],$fk['table'],$fk['to'])."\n";
                        }
                    }
                }
            }
            return $syntax."\n";
        }catch(Exception $e){
            print_r($e->getMessage ());
        }
    }
}
