<?php

namespace Aphisitworachorch\Kacher\Traits;

trait DBMLSyntaxTraits
{
    public function table($name): string
    {
        return "Table $name ";
    }

    public function index(): string
    {
        return "\n\tindexes";
    }

    public function start(): string
    {
        return "{\n";
    }

    public function end(): string
    {
        return "}\n";
    }

    public function foreignKey($from,$from_table,$to,$to_table): string
    {
        return "Ref: $from.$from_table > $to.$to_table\n";
    }

    public function indexesKey($col,$type){
        $format = [];
        $annotate = "";
        $final = "";
        $oneFormat = "";
        if(count($col) <= 1){
            $oneFormat = "\t\t".$col[0];
        }
        if(count($col) > 1){
            foreach($col as $col_info){
                $format[] = $col_info;
            }
            $final = "\t\t(".implode(",",$format).")";
        }
        if($type === "pk"){
            $annotate = "[pk]";
        }
        if($type === "unique"){
            $annotate = "[unique]";
        }
        return ($final ?: $oneFormat) . " " . $annotate."\n";
    }

    public function column($name,$type,$special,$note,$nullable,$default,$length): string
    {
        $annotation = [];
        $len_annotate = null;

        foreach($special as $special_annotate){
            if($special_annotate === "pk"){
                $annotation[] = "pk";
            }
            if($special_annotate === "unique"){
                $annotation[] = "unique";
            }
            if($special_annotate === "increment"){
                $annotation[] = "increment";
            }
        }

        if($note){
            $annotation[] = "note: $note";
        }
        if($nullable === "yes"){
            $annotation[] = "null";
        }else{
            $annotation[] = "not null";
        }

        if($default){
            $annotation[] = "default: '$default'";
        }

        if($length){
            $len_annotate = "($length)";
        }

        $results = implode(",",$annotation);
        return "\t{$name} {$type}{$len_annotate} [{$results}]\n";
    }
}
