# FinAI - Gerenciador de Gastos em compras no Mercado - Para Clientes Finais

"Ajudando a economizar no dia-a-dia para realizar seus sonhos"

# Resumo do aplicativo

Com o auxílio da Inteligência Artificial, é feita a leitura da chave de acesso através da foto tirada pelo celular, então através dela se discrimina, categoriza todos os itens comprados e através do painel é possível ver onde se concentram os gastos

# Arquitetura Mínima: 

| Componente                            | Tecnologia sugerida          | Função                                                 |
|:-------------------------------------:|:----------------------------:|:------------------------------------------------------:|
| Ferramenta de Inteligência Artificial | Google Gemini API Javascript | Auxílio na geração de código                           |
| OCR (leitura de texto da imagem)      | Google Gemini                | Extrair texto bruto da nota                            |
| Parser simples                        | Google Gemini                | Separar itens, quantidades e valores do texto extraído |
| Classificação básica                  | Google Gemini                | Associar produtos da nota a categorias                 |
| Banco de dados                        | PostgreSQL                   | Armazenar dados das notas                              |
| Dashboard web simples                 | Laravel (PHP)                | Mostrar categorias e valores totais                    |

# Desesenvolvedores: 
William Trindade https://github.com/williamtrindade

Jordan Durante https://github.com/JordanDurante

# Conceito e Pesquisa de Mercado
William Trindade

Analissa do Prado https://github.com/programandaana


Trabalho desenvolvido em virtude da proposta elaborada pelo Professor Rafael Milbradt - Curso de Sistemas Para Internet - Politécnico UFSM





