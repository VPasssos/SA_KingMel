// Aplica a máscara chamando: oninput="mascara(this, cpf)" por exemplo.
function mascara(o, f) {
    setTimeout(function() {
        var v = f(o.value);
        if (v != o.value) {
            o.value = v;
        }
    }, 1);
}

// Máscara de CPF: 000.000.000-00
function cpfM(v) {
    v = v.replace(/\D/g, ""); // Remove tudo que não for número
    v = v.replace(/(\d{3})(\d)/, "$1.$2");
    v = v.replace(/(\d{3})(\d)/, "$1.$2");
    v = v.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
    return v;
}

// Máscara de CNPJ: 00.000.000/0000-00
function cnpjM(v) {
    v = v.replace(/\D/g, "");
    v = v.replace(/^(\d{2})(\d)/, "$1.$2");
    v = v.replace(/^(\d{2})\.(\d{3})(\d)/, "$1.$2.$3");
    v = v.replace(/\.(\d{3})(\d)/, ".$1/$2");
    v = v.replace(/(\d{4})(\d)/, "$1-$2");
    return v;
}

// Máscara de Telefone: (00) 00000-0000 ou (00) 0000-0000
function telefoneM(v) {
    v = v.replace(/\D/g, "");
    v = v.replace(/^(\d{2})(\d)/g, "($1) $2");
    if (v.length <= 13) {
        v = v.replace(/(\d{4})(\d)/, "$1-$2");
    } else {
        v = v.replace(/(\d{5})(\d)/, "$1-$2");
    }
    return v;
}

// Máscara de CEP: 00000-000
function cepM(v) {
    v = v.replace(/\D/g, "");
    v = v.replace(/(\d{5})(\d)/, "$1-$2");
    return v;
}

function nomeM(v) {
    return v.replace(/\d/g, "");
}

function qtdeM(v) {
    return v.replace(/\D/g, "");
}