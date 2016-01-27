<?php

namespace Mitul\Generator\Commands;

use Exception;
use Illuminate\Console\Command;
use Mitul\Generator\CommandData;
use Mitul\Generator\File\FileHelper;
use Mitul\Generator\Utils\GeneratorUtils;
use Mitul\Generator\Utils\TableFieldsGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class BaseCommand extends Command
{
    /**
     * The command Data.
     *
     * @var CommandData
     */
    public $commandData;

    public function handle()
    {
        $this->commandData->modelName = $this->argument('model');
        $this->commandData->useSoftDelete = $this->option('softDelete');
        $this->commandData->fieldsFile = $this->option('fieldsFile');
        $this->commandData->paginate = $this->option('paginate');
        $this->commandData->tableName = $this->option('tableName');
        $this->commandData->skipMigration = $this->option('skipMigration');
        $this->commandData->fromTable = $this->option('fromTable');
        $this->commandData->rememberToken = $this->option('rememberToken');

        $this->commandData->main_table_id = $this->option('mainTableId');
        $this->commandData->main_module = $this->option('mainModuleName');
        $this->commandData->sub_module = $this->option('subModuleName');
        $this->commandData->module_name = ucwords(str_replace("-", " ", $this->commandData->main_module));
        $this->commandData->model_primary_key = $this->option('primaryKey');
        $this->commandData->layout_name = $this->option('layoutName');

        if($this->commandData->tableName==""){
            $this->commandData->tableName = $this->ask("Please specify the table name");
        }
        if($this->commandData->model_primary_key==""){
            $this->commandData->model_primary_key = $this->ask("Please specify the primary key field name for the table");
        }
        if($this->commandData->main_table_id==""){
            $this->commandData->main_table_id = $this->commandData->tableName;
        }

        if($this->commandData->main_module==""){
            $this->commandData->main_module = $this->ask("Please specify the main module name in lower case separated by hypen - do not use whitespaces, e.g. bulk-metrics");
        }

        if($this->commandData->sub_module==""){
            $this->commandData->sub_module = $this->ask("Please specify the sub module name - do not use whitespaces, use hyphen as a separator, e.g. bulk-metrics-comparison");
        }

        if($this->commandData->layout_name==""){
            $this->commandData->layout_name = $this->ask("Please specify whether you want to use the app layout or the  baf layout or any other. Example, type app for app layout");
        }








        if ($this->commandData->fromTable) {
            if (!$this->commandData->tableName) {
                $this->error('tableName required with fromTable option.');
                exit;
            }
        }

        if ($this->commandData->paginate <= 0) {
            $this->commandData->paginate = 10;
        }

        $this->commandData->initVariables();
        $this->commandData->addDynamicVariable('$NAMESPACE_APP$', $this->getLaravel()->getNamespace());
        $this->commandData->addDynamicVariable('$MAIN_TABLE_ID$', $this->commandData->main_table_id);
        $this->commandData->addDynamicVariable('$MAIN_MODULE$', $this->commandData->main_module);
        $this->commandData->addDynamicVariable('$SUB_MODULE', $this->commandData->sub_module);
        $this->commandData->addDynamicVariable('$MODULE_NAME', $this->commandData->module_name);
        $this->commandData->addDynamicVariable('$MODEL_PRIMARY_KEY$', $this->commandData->model_primary_key);
        $this->commandData->addDynamicVariable('$LAYOUT_NAME$', $this->commandData->layout_name);
        $this->commandData->addDynamicVariable('$NAMESPACE_CONTRACT$', "App\Contracts\Services\\".ucwords($this->commandData->layout_name)."\\".$this->commandData->modelName);


        if ($this->commandData->fieldsFile) {
            $fileHelper = new FileHelper();
            try {
                if (file_exists($this->commandData->fieldsFile)) {
                    $filePath = $this->commandData->fieldsFile;
                } else {
                    $filePath = base_path($this->commandData->fieldsFile);
                }

                if (!file_exists($filePath)) {
                    $this->commandData->commandObj->error('Fields file not found');
                    exit;
                }

                $fileContents = $fileHelper->getFileContents($filePath);
                $fields = json_decode($fileContents, true);

                $this->commandData->inputFields = GeneratorUtils::validateFieldsFile($fields);
            } catch (Exception $e) {
                $this->commandData->commandObj->error($e->getMessage());
                exit;
            }
        } elseif ($this->commandData->fromTable) {
            $tableFieldsGenerator = new TableFieldsGenerator($this->commandData->tableName);
            $this->commandData->inputFields = $tableFieldsGenerator->generateFieldsFromTable();
        } else {
            $this->commandData->inputFields = $this->commandData->getInputFields();
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['model', InputArgument::REQUIRED, 'Singular Model name'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    public function getOptions()
    {
        return [
            ['softDelete', null, InputOption::VALUE_NONE, 'Use Soft Delete trait'],
            ['fieldsFile', null, InputOption::VALUE_REQUIRED, 'Fields input as json file'],
            ['paginate', null, InputOption::VALUE_OPTIONAL, 'Pagination for index.blade.php', 10],
            ['tableName', null, InputOption::VALUE_REQUIRED, 'Table Name'],
            ['skipMigration', null, InputOption::VALUE_NONE, 'Skip Migration generation'],
            ['fromTable', null, InputOption::VALUE_NONE, 'Generate from table'],
            ['rememberToken', null, InputOption::VALUE_NONE, 'Generate rememberToken field in migration'],
            ['mainTableId', null, InputOption::VALUE_REQUIRED, 'Define an ID for the HTML table'],
            ['mainModuleName', null, InputOption::VALUE_REQUIRED, 'Module Name'],
            ['subModuleName', null, InputOption::VALUE_REQUIRED, 'Sub-module Name'],
            ['primaryKey', null, InputOption::VALUE_REQUIRED, 'Primary Key Field Name'],
            ['layoutName', null, InputOption::VALUE_REQUIRED, 'Layout name: app or baf or anything else']

        ];
    }
}
