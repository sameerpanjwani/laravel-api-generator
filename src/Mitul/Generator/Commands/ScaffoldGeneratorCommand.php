<?php

namespace Mitul\Generator\Commands;

use Mitul\Generator\CommandData;
use Mitul\Generator\Generators\Common\MigrationGenerator;
use Mitul\Generator\Generators\Common\ModelGenerator;
use Mitul\Generator\Generators\Common\RepositoryGenerator;
use Mitul\Generator\Generators\Common\RequestGenerator;
use Mitul\Generator\Generators\Common\RoutesGenerator;
use Mitul\Generator\Generators\Scaffold\ViewControllerGenerator;
use Mitul\Generator\Generators\Scaffold\ViewGenerator;

class ScaffoldGeneratorCommand extends BaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'mitul.generator:scaffold';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a full CRUD for given model with initial views';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->commandData = new CommandData($this, CommandData::$COMMAND_TYPE_SCAFFOLD);
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        parent::handle();

        if (!$this->commandData->skipMigration and !$this->commandData->fromTable) {
            $migrationGenerator = new MigrationGenerator($this->commandData);
            $migrationGenerator->generate();
        }

        $modelGenerator = new ModelGenerator($this->commandData);
        $modelGenerator->generate();

        $requestGenerator = new RequestGenerator($this->commandData);
        $requestGenerator->generate();

        $repositoryGenerator = new RepositoryGenerator($this->commandData);
        $repositoryGenerator->generate();

        $repoControllerGenerator = new ViewControllerGenerator($this->commandData);
        $repoControllerGenerator->generate();

        $viewsGenerator = new ViewGenerator($this->commandData);
        $viewsGenerator->generate();

        $routeGenerator = new RoutesGenerator($this->commandData);
        $routeGenerator->generate();

        if ($this->confirm("\nDo you want to migrate database? [y|N]", false)) {
            $this->call('migrate');
        }

        $display_service_creation = $this->ask("Would you like to create a display service as well for ".$this->commandData->modelName."DisplayService? (recommended) [yes/no]");
        if(strtolower($display_service_creation=="yes")){
            $this->call("make:display-service");
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array_merge(parent::getArguments());
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    public function getOptions()
    {
        return array_merge(parent::getOptions(), []);
    }
}
