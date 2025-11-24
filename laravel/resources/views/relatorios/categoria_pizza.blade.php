@php use Carbon\Carbon; @endphp
<div>
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    Total Gasto de {{ Carbon::parse($dataInicio)->format('d/m/Y') }}
                    a {{ Carbon::parse($dataFim)->format('d/m/Y') }}
                </div>
                <div class="card-body">
                    <h1>
                        R$ {{ number_format($relatorio_categoria_pizza['totalGastoMes'], 2, ',', '.') }}
                    </h1>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    Distribuição de Gastos por Categoria
                </div>
                <div class="card-body">
                    <canvas id="graficoPizzaGastos"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>

    <script
        src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0/dist/chartjs-plugin-datalabels.min.js"></script>

    <script>
        Chart.register(ChartDataLabels);

        // 2. Transfere os dados do PHP para o JavaScript
        const dadosGrafico = @json($relatorio_categoria_pizza['dadosGrafico']);

        // Gera cores aleatórias para o gráfico (opcional, mas recomendado para pizza)
        function generateColors(count) {
            let colors = [];
            for (let i = 0; i < count; i++) {
                colors.push('hsl(' + Math.random() * 360 + ', 70%, 50%)');
            }
            return colors;
        }

        const ctx = document.getElementById('graficoPizzaGastos').getContext('2d');

        // 3. Configuração e Renderização do Gráfico de Pizza
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: dadosGrafico.labels, // Nomes das categorias (ALIMENTOS, LIMPEZA, etc.)
                datasets: [{
                    label: 'Valor Gasto (R$)',
                    data: dadosGrafico.data,
                    backgroundColor: generateColors(dadosGrafico.labels.length),
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    datalabels: {
                        color: '#fff', // Cor do texto (branco para contraste)
                        formatter: (value, ctx) => {
                            let sum = 0;
                            let dataArr = ctx.chart.data.datasets[0].data;
                            dataArr.map(data => {
                                sum += Number(data);
                            });
                            // Calcula a porcentagem
                            let percentage = (value * 100 / sum).toFixed(1) + "%";
                            return percentage;
                        },
                        // Posiciona o label dentro da fatia
                        anchor: 'center',
                        align: 'center',
                    },
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Distribuição Percentual'
                    }
                }
            }
        });
    </script>
@endpush
