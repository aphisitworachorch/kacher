<?php

namespace Aphisitworachorch\Kacher\Controller;

use Aphisitworachorch\Kacher\Traits\DBMLSyntaxTraits;
use App\Http\Controllers\Controller;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Illuminate\Support\Facades\DB;

class DBMLController extends Controller
{
    use DBMLSyntaxTraits;

    /**
     * @var AbstractSchemaManager
     */
    private $doctrine_instance;

    /**
     * @throws Exception
     */
    public function __construct ()
    {
        DB::connection()->getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
        $this->doctrine_instance = DB::connection()->getDoctrineSchemaManager();
    }

    /**
     * @throws Exception
     */
    private function getColumns($table_name, $type)
    {
        $columnInfo = [];
        if (!empty($table_name)) {
            $instance = $this->doctrine_instance->listTableColumns ($table_name);
            foreach($instance as $tableColumn){
                if($type === "artisan"){
                    $columnInfo[] = "name : {$tableColumn->getName ()}\n" . "type : {$tableColumn->getType ()->getName ()}\n";
                }
                if($type === "array"){
                    $special = [];
                    if($this->isPrimaryKey ($tableColumn->getName (),$table_name) === "yes"){
                        $special[] = "pk";
                    }
                    if($this->isUniqueKey ($tableColumn->getName (),$table_name) === "yes"){
                        $special[] = "unique";
                    }

                    $columnInfo[] = [
                        "name"=>$tableColumn->getName (),
                        "type"=>$tableColumn->getType ()->getName (),
                        "special"=>$special,
                        "note"=>$tableColumn->getComment (),
                        "default_value"=>$tableColumn->getDefault (),
                        "is_nullable"=>($tableColumn->getNotnull () ? "no" : "yes"),
                        "length"=>$tableColumn->getLength ()
                    ];
                }
            }
        }
        return $columnInfo;
    }

    /**
     * @throws Exception
     */
    private function getForeignKey($table_name, $type)
    {
        $columnInfo = [];
        if (!empty($table_name)) {
            $instance = $this->doctrine_instance->listTableForeignKeys ($table_name);
            foreach($instance as $tableFK){
                $fromColumns = implode(" | ",$tableFK->getColumns ());
                $toColumns = implode(" | ",$tableFK->getForeignColumns ());
                if($type === "artisan"){
                    $columnInfo[] = "[{$table_name}][{$fromColumns}] -> "."[$toColumns] of [{$tableFK->getForeignTableName ()}]";
                }
                if($type === "array"){
                    $columnInfo[] = [
                        "from"=>$table_name,
                        "name"=>$fromColumns,
                        "to"=>$toColumns,
                        "table"=>$tableFK->getForeignTableName ()
                    ];
                }
            }
        }
        return $columnInfo;
    }

    /**
     * @throws Exception
     */
    private function getIndexes($table_name, $type)
    {
        $columnInfo = [];
        if (!empty($table_name)) {
            $instance = $this->doctrine_instance->listTableIndexes ($table_name);
            foreach($instance as $tableIndex){
                $unique = $tableIndex->isUnique () ? "yes" : "no";
                $primary = $tableIndex->isPrimary () ? "yes" : "no";
                if($type === "artisan"){
                    $columns = implode(" | ",$tableIndex->getColumns ());
                    $columnInfo[] = "name : {$tableIndex->getName ()}\n"."columns : {$columns}\n"."unique : {$unique}\n"."primary : {$primary}\n";
                }
                if($type === "array"){
                    $columnInfo[] = ["name"=>$tableIndex->getName (),"columns"=>$tableIndex->getColumns (),"unique"=>$unique,"primary"=>$primary,"table"=>$table_name];
                }
            }
        }
        return $columnInfo;
    }

    /**
     * @throws Exception
     */
    private function isPrimaryKey($column, $table_name){
        $primaryKeyInstance = $this->doctrine_instance->listTableIndexes ($table_name);
        foreach($primaryKeyInstance as $tableIndex){
            if($tableIndex->getColumns ()[0] === $column){
                return $tableIndex->isPrimary () ? "yes" : "no";
            }
        }
        return 0;
    }

    /**
     * @throws Exception
     */
    private function isUniqueKey($column, $table_name){
        $uniqueKeyInstance = $this->doctrine_instance->listTableIndexes ($table_name);
        foreach($uniqueKeyInstance as $tableIndex){
            if($tableIndex->getColumns ()[0] === $column){
                return $tableIndex->isUnique () ? "yes" : "no";
            }
        }
        return 0;
    }
    /**
     * @throws Exception
     */
    public function getDatabaseTable($type){
        $tableName = $this->doctrine_instance->listTableNames();
        $data = [];
        if($tableName){
            if($type === "artisan"){
                foreach($tableName as $tb){
                    $data[] = [
                        "table_name" => $tb,
                        "columns" => implode("\n",$this->getColumns ($tb,$type)),
                        "foreign_key" => implode("\n",$this->getForeignKey ($tb,$type)),
                        "indexes"=> implode("\n",$this->getIndexes ($tb,$type))
                    ];
                }
                return $data;
            }
            if($type === "array"){
                foreach($tableName as $tb){
                    $data[] = [
                        "table_name" => $tb,
                        "columns" => $this->getColumns ($tb,$type),
                        "foreign_key" => $this->getForeignKey ($tb,$type),
                        "indexes"=> $this->getIndexes ($tb,$type)
                    ];
                }
                return $data;
            }
        }
    }

    /**
     * @throws Exception
     */
    public function parseToDBML(): string
    {
        $table = $this->getDatabaseTable ("array");
        $syntax = "";
        $foreign = "";
        foreach($table as $info){
            if($info['table_name']){
                $syntax .= $this->table ($info['table_name']) . $this->start ();
                foreach($info['columns'] as $col){
                    $syntax .= $this->column ($col['name'],$col['type'],$col['special'],$col['note'],$col['is_nullable'],$col['default_value'],$col['length']);
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
    }
}
