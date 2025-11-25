<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                Operações
            </div>
            <div class="card-body" style="overflow-y: scroll">
                <table class="table table-striped table-hover">
                    <thead>
                    <tr>
                        <th style="width: 15%">Data da Compra</th>
                        <th>Descrição do Item</th>
                        <th class="text-center">Categoria</th>
                        <th class="text-right">Preço Unitário</th>
                        <th class="text-right">Total (R$)</th>
                        <th class="text-center">Local</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($itensRecentes as $item)
                        <tr>
                            <td>
                                {{ date('d/m/Y', strtotime($item->notaFiscal->data_emissao)) }}
                                <small class="text-muted d-block">{{ date('H:i', strtotime($item->notaFiscal->hora_emissao)) }}</small>
                            </td>
                            <td>{{ $item->descricao }}</td>
                            <td class="text-center">
                            <span class="badge bg-success">
                                {{ $item->categoria->nome ?? 'N/D' }}
                            </span>
                            </td>
                            <td class="text-right">R$ {{ number_format($item->preco_unitario, 2, ',', '.') }}</td>
                            <td class="text-right"><strong>R$ {{ number_format($item->total_item, 2, ',', '.') }}</strong></td>
                            <td class="text-center">
                                {{-- Link para a listagem detalhada que criamos anteriormente --}}
                                <a href="{{route('view.receipt.read', $item->notaFiscal->id)}}" class="btn btn-sm btn-success text-nowrap">
                                    <i class="bi bi-card-checklist"></i> Ver Nota
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Nenhum item recente encontrado na base de dados.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center">
                {{ $itensRecentes->links() }}
            </div>
        </div>
    </div>
</div>
