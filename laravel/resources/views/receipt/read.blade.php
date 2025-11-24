@extends('template')

@section('title', 'Dados da Nota Fiscal')

@section('content')
    <div>
        <br>
        <a href="{{ route('view.receipt.list') }}" class="btn btn-secondary mt-3">
            <i class="bi bi-arrow-left"></i> Voltar para a Lista de Notas
        </a>
        <hr>
        <h2 style="font-size: 150%">Detalhes da Nota Fiscal</h2>

        @if ($notaFiscal)
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-primary text-white">
                            <i class="bi bi-shop"></i> Informações da Compra
                        </div>
                        <div class="card-body">
                            <p><strong>Estabelecimento:</strong> {{ $notaFiscal->estabelecimento->nome ?? 'N/D' }}</p>
                            <p><strong>CNPJ:</strong> {{ $notaFiscal->estabelecimento->cnpj ?? 'N/D' }}</p>
                            <p><strong>Endereço:</strong> {{ $notaFiscal->estabelecimento->endereco ?? 'Não Informado' }}</p>
                            <hr>
                            <p><strong>Data da Compra:</strong> {{ date('d/m/Y', strtotime($notaFiscal->data_emissao)) }} às {{ date('H:i:s', strtotime($notaFiscal->hora_emissao)) }}</p>
                            <p><strong>Número do Doc/Cupom:</strong> {{ $notaFiscal->numero_doc }}</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-success text-white">
                            <i class="bi bi-cash-coin"></i> Valores e Chaves Fiscais
                        </div>
                        <div class="card-body">
                            <h4>Total Pago: R$ {{ number_format($notaFiscal->valor_pago, 2, ',', '.') }}</h4>
                            <p>Total Bruto: R$ {{ number_format($notaFiscal->total_bruto, 2, ',', '.') }}</p>
                            <p>Total de Descontos: R$ {{ number_format($notaFiscal->descontos, 2, ',', '.') }}</p>
                            <hr>
                            <p><strong>Chave de Acesso (44 dig.):</strong> <code class="small">{{ $notaFiscal->chave_acesso ?? 'N/D' }}</code></p>
                            <p><strong>Protocolo Autorização:</strong> <code class="small">{{ $notaFiscal->protocolo_autorizacao ?? 'N/D' }}</code></p>
                        </div>
                    </div>
                </div>
            </div>

            <h2 style="font-size: 150%" class="mt-4">Itens da Compra ({{ $notaFiscal->itens->count() }} itens)</h2>

            <div class="card" style="overflow-y: scroll">

                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                    <tr>
                        <th>Descrição do Item</th>
                        <th class="text-center">Categoria</th>
                        <th class="text-center">Qtd</th>
                        <th class="text-right">Preço Unitário</th>
                        <th class="text-right">Total Item</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($notaFiscal->itens as $item)
                        <tr>
                            <td>
                                {{ $item->descricao }}
                                <small class="text-muted d-block">Cód: {{ $item->codigo_produto }}</small>
                            </td>
                            <td class="text-center">
                                    <span class="badge bg-primary rounded-pill">
                                        {{ $item->categoria->nome ?? 'N/D' }}
                                    </span>
                            </td>
                            <td class="text-center">{{ number_format($item->quantidade, 3, ',', '.') }}</td>
                            <td class="text-right">R$ {{ number_format($item->preco_unitario, 2, ',', '.') }}</td>
                            <td class="text-right"><strong>R$ {{ number_format($item->total_item, 2, ',', '.') }}</strong></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-warning">
                Nota fiscal não encontrada.
            </div>
        @endif
    </div>
@endsection
