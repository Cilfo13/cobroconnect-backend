<?php

namespace App\Console\Commands;

use App\Models\Cliente;
use Illuminate\Console\Command;

class ReiniciarLimitesClientes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cliente:reiniciar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reinicia los limitesActuales de los clientes para que queden como el limiteTotal';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $clientes = Cliente::all();

        foreach ($clientes as $cliente) {
            $cliente->limiteActual = $cliente->limiteTotal;
            $cliente->save();
        }

        $this->info('Valores actualizados correctamente en todos los clientes.');
        return Command::SUCCESS;
    }
}