<?php

namespace shell;

class ModelCli extends Model
{
    protected $tablePrefix = '';

    protected function _initialize()
    {
        $tableName       = get_called_class();// __CLASS__;
        $this->tableName = str_replace('ModelCli_', '', $tableName);
        //echo $this->tableName . "\n";
    }
}
