/**
 * LogiFrete - JavaScript do frontend
 * 1. Linhas de produtos dinâmicas no formulário de frete (adicionar/remover + totais)
 * 2. Busca de endereço pelo CEP (API ViaCEP)
 * 3. Máscaras de CEP, CPF/CNPJ e telefone
 * 4. Confirmação antes de excluir
 */
document.addEventListener('DOMContentLoaded', () => {
    iniciarProdutosDoFrete();
    iniciarBuscaCep();
    iniciarMascaras();
    iniciarConfirmacaoExclusao();
});

/* ---------- 1. Produtos do frete (campos dinâmicos) ---------- */
function iniciarProdutosDoFrete() {
    const lista = document.getElementById('lista-produtos');
    const modelo = document.getElementById('modelo-produto');
    const botaoAdicionar = document.getElementById('btn-add-produto');
    if (!lista || !modelo || !botaoAdicionar) return;

    // Adiciona uma nova linha copiando o <template>
    botaoAdicionar.addEventListener('click', () => {
        const novaLinha = modelo.content.cloneNode(true);
        lista.appendChild(novaLinha);
        lista.lastElementChild.querySelector('select').focus();
        atualizarTotais();
    });

    // Remove a linha clicada (mantém pelo menos uma)
    lista.addEventListener('click', (evento) => {
        const botao = evento.target.closest('.btn-remover');
        if (!botao) return;

        const linhas = lista.querySelectorAll('.linha-produto');
        const linha = botao.closest('.linha-produto');

        if (linhas.length === 1) {
            linha.querySelector('select').value = '';
            linha.querySelector('input').value = 1;
            atualizarTotais();
            return;
        }
        linha.classList.add('removendo');
        setTimeout(() => { linha.remove(); atualizarTotais(); }, 200);
    });

    // Recalcula os totais sempre que um produto ou quantidade mudar
    lista.addEventListener('change', atualizarTotais);
    lista.addEventListener('input', atualizarTotais);

    // Validação antes de enviar: precisa de pelo menos um produto
    document.getElementById('form-frete').addEventListener('submit', (evento) => {
        const algumProduto = [...lista.querySelectorAll('.sel-produto')].some((s) => s.value !== '');
        if (!algumProduto) {
            evento.preventDefault();
            alert('Adicione pelo menos um produto ao frete.');
        }
        const origem = document.getElementById('origem_id').value;
        const destino = document.getElementById('destino_id').value;
        if (origem && origem === destino) {
            evento.preventDefault();
            alert('A origem e o destino devem ser diferentes.');
        }
    });

    atualizarTotais();
}

function atualizarTotais() {
    let itens = 0, peso = 0, valor = 0;

    document.querySelectorAll('#lista-produtos .linha-produto').forEach((linha) => {
        const opcao = linha.querySelector('.sel-produto').selectedOptions[0];
        const qtd = parseInt(linha.querySelector('.inp-qtd').value, 10) || 0;
        if (!opcao || !opcao.value) return;

        itens += qtd;
        peso += qtd * parseFloat(opcao.dataset.peso);
        valor += qtd * parseFloat(opcao.dataset.valor);
    });

    const numero = (n) => n.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    document.getElementById('total-itens').textContent = itens;
    document.getElementById('total-peso').textContent = numero(peso) + ' kg';
    document.getElementById('total-valor').textContent = 'R$ ' + numero(valor);
}

/* ---------- 2. Busca de CEP (ViaCEP) ---------- */
function iniciarBuscaCep() {
    const campoCep = document.getElementById('cep');
    const status = document.getElementById('cep-status');
    if (!campoCep || !status) return;

    campoCep.addEventListener('blur', async () => {
        const cep = campoCep.value.replace(/\D/g, '');
        if (cep.length !== 8) return;

        status.textContent = 'Buscando endereço...';
        try {
            const resposta = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
            const dados = await resposta.json();

            if (dados.erro) {
                status.textContent = 'CEP não encontrado. Preencha o endereço manualmente.';
                return;
            }
            document.getElementById('logradouro').value = dados.logradouro || '';
            document.getElementById('bairro').value = dados.bairro || '';
            document.getElementById('cidade').value = dados.localidade || '';
            document.getElementById('uf').value = dados.uf || '';
            document.getElementById('numero').focus();
            status.textContent = 'Endereço preenchido automaticamente.';
        } catch (erro) {
            status.textContent = 'Não foi possível consultar o CEP agora. Preencha manualmente.';
        }
    });
}

/* ---------- 3. Máscaras ---------- */
function iniciarMascaras() {
    const mascaras = {
        cep: (v) => v.slice(0, 8).replace(/(\d{5})(\d)/, '$1-$2'),
        telefone: (v) => v.slice(0, 11)
            .replace(/(\d{2})(\d)/, '($1) $2')
            .replace(/(\d{4,5})(\d{4})$/, '$1-$2'),
        cpfcnpj: (v) => {
            v = v.slice(0, 14);
            if (v.length <= 11) {
                return v.replace(/(\d{3})(\d)/, '$1.$2')
                        .replace(/(\d{3})(\d)/, '$1.$2')
                        .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            }
            return v.replace(/(\d{2})(\d)/, '$1.$2')
                    .replace(/(\d{3})(\d)/, '$1.$2')
                    .replace(/(\d{3})(\d)/, '$1/$2')
                    .replace(/(\d{4})(\d{1,2})$/, '$1-$2');
        },
    };

    document.querySelectorAll('[data-mascara]').forEach((campo) => {
        const aplicar = mascaras[campo.dataset.mascara];
        campo.addEventListener('input', () => {
            campo.value = aplicar(campo.value.replace(/\D/g, ''));
        });
    });
}

/* ---------- 4. Confirmação de exclusão ---------- */
function iniciarConfirmacaoExclusao() {
    document.querySelectorAll('.form-excluir').forEach((form) => {
        form.addEventListener('submit', (evento) => {
            if (!confirm('Deseja realmente excluir este registro?')) {
                evento.preventDefault();
            }
        });
    });
}
