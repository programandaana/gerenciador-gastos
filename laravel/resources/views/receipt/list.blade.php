@extends('template')

@section('title', 'Notas Fiscais')

@section('content')
    <div class="container-fluid">
        <br>
        <a href="{{ route('view.receipt.upload') }}" class="btn-success btn">
            <i class="bi bi-plus-lg"></i> Cadastrar Nota Fiscal
        </a>
        <hr>
        <h2 style="font-size: 150%">Listagem de Notas Fiscais Processadas</h2>

        <div style="overflow-y: scroll;">

            <table class="table table-striped table-hover">
                <thead>
                <tr>
                    <th>Chave de Acesso</th>
                    <th>Estabelecimento</th>
                    <th class="text-center">Emissão</th>
                    <th class="text-right">Valor Pago</th>
                    <th class="text-center">Ações</th>
                </tr>
                </thead>
                <tbody>
                {{-- Verifica se há notas para listar --}}
                @forelse ($notasFiscais as $nota)
                    <tr>
                        <td>{{ $nota->chave_acesso }}</td>
                        <td>{{ $nota->estabelecimento->nome ?? 'Estabelecimento Não Encontrado' }}</td>
                        <td class="text-center">{{ date('d/m/Y', strtotime($nota->data_emissao)) }}</td>
                        <td class="text-right">R$ {{ number_format($nota->valor_pago, 2, ',', '.') }}</td>
                        <td class="text-center">
                            {{-- Link para a listagem detalhada que criamos anteriormente --}}
                            <a href="{{route('view.receipt.read', $nota->id)}}" class="btn btn-sm btn-success">
                                <i class="bi bi-card-checklist"></i> Ver Detalhes
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Nenhuma nota fiscal foi processada ainda.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>

            {{-- Links de Paginação --}}
            <div class="d-flex justify-content-center">
                {{ $notasFiscais->links() }}
            </div>
        </div>
    </div>
@endsection
