<?php

use Illuminate\Database\Seeder;
use Elasticsearch\Client;
use Faker\Factory as Faker;

class ClientTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();
        $client = app(Client::class);

        $start = microtime(true);
        foreach (range(1,1000) as $value){
            $client->index([
                'index' => env('ES_INDEX'),
                'type' => 'clients',
                'body' => [
                    'name' => $faker->firstName . ' ' . $faker->lastName,
                    'cpf' => rand(100,999) . '.' . rand(100,999) . '.' .rand(100,999) . '-' .rand(10,99)
                ]
            ]);
        }
        $end = microtime(true);

        $time = $end - $start;

        $this->command->info("Execucao em: $time segundos");
    }
}
