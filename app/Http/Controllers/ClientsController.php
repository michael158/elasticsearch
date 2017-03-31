<?php

namespace App\Http\Controllers;

use Elasticsearch\Client;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Illuminate\Foundation\Testing\HttpException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ClientsController extends Controller
{
    protected $elasticParams = [];

    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;

        $this->elasticParams['index'] = env('ES_INDEX');
        $this->elasticParams['type'] = 'clients';
    }

    public function index(Request $request)
    {
        $name = $request->get('name');

        // BUSCA COM ELASTICSEARCH //
        if($name){
            $this->elasticParams['body'] = [
              'query' => [
                  'match' => [
                      'name' => $name
                  ]
              ]
            ];

        }

        $this->elasticParams['size'] = 8000;
        $clients = $this->client->search($this->elasticParams);

        return view('clients.index', compact('clients'));
    }

    public function create()
    {
        return view('clients.create');
    }

    public function store(Request $request)
    {
        $data = $request->all();
        // MATA O CSRF TOKEN PARA QUE NAO SEJA SALVO NO DOCUMENTO DO ELASTICSEARCH //
        unset($data['_token']);

        $this->elasticParams['refresh'] = true;
        $this->elasticParams['body'] = $data;

        $this->client->index($this->elasticParams);

        return redirect()->route('clients.index');
    }

    public function edit($id)
    {
        try {
            $this->elasticParams['id'] = $id;
            $client = $this->client->get($this->elasticParams);
        } catch (Missing404Exception $e) {
            throw new NotFoundHttpException('Client not Found');
        }

        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, $id)
    {
        $this->elasticParams['id'] = $id;

        if (!$this->client->exists($this->elasticParams))
            throw new HttpException('Client not Found');

        $data = $request->all();
        unset($data['_token']);
        unset($data['_method']);

        $this->elasticParams['refresh'] = true;
        $this->elasticParams['body']['doc'] = $data;
        $this->client->update($this->elasticParams);

        return redirect()->route('clients.index');
    }

    public function destroy($id)
    {
        $this->elasticParams['id'] = $id;

        if (!$this->client->exists($this->elasticParams))
            throw new HttpException('Client not Found');

        $this->elasticParams['refresh'] = true;
        $this->client->delete($this->elasticParams);

        return redirect()->route('clients.index');
    }


}
