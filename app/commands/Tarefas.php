<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class Tarefas extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'correios:update';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Procura atualizacoes nos Correios.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$records = Tracking::where('atualiza_status', '=', '1')
                ->where('ativo', '=', '1')
                ->orderBy('usuario_id')
                ->get();

                if(count($records) > 0){
                    foreach($records as $row){
                        CorreiosController::update($row->numero);
                    }
                }
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	/*protected function getArguments()
	{
		return array(
			array('example', InputArgument::REQUIRED, 'An example argument.'),
		);
	}*/

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	/*protected function getOptions()
	{
		return array(
			array('example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null),
		);
	}*/

}
