let relatorio, pessoa;
let anteriores = new Array();
let validacao_bloqueada = false;

jQuery.fn.sortElements = (function() {
    var sort = [].sort;

    return function(comparator, getSortable) {    
        getSortable = getSortable || function() {
            return this;
        };

        var placements = this.map(function() {    
            var sortElement = getSortable.call(this),
                parentNode = sortElement.parentNode,
                nextSibling = parentNode.insertBefore(
                    document.createTextNode(""),
                    sortElement.nextSibling
                );
            
            return function() {
                if (parentNode === this) {
                    throw new Error(
                        "You can't sort elements if any one is a descendant of another."
                    );
                }
                parentNode.insertBefore(this, nextSibling);
                parentNode.removeChild(nextSibling);                
            }
        });
       
        return sort.call(this, comparator).each(function(i) {
            placements[i].call(getSortable.call(this));
        });
    };
})();

window.onload = function() {
    Array.from(document.querySelectorAll(".btn-toolbar a")).forEach((el) => {
        el.addEventListener("mousemove", function() {
            Array.from(el.children).forEach((elB) => {
                if (elB.classList.value.indexOf("box") > -1) {
                    elB.classList.remove("fa-box");
                    elB.classList.add("fa-box-open");
                } else if (elB.tagName == "I") {
                    elB.classList.remove("fa-light");
                    elB.classList.add("fad");    
                }
            });
        });
        el.addEventListener("mouseout", function() {
            Array.from(el.children).forEach((elB) => {
                if (elB.classList.value.indexOf("box") > -1) {
                    elB.classList.remove("fa-box-open");
                    elB.classList.add("fa-box");
                } else if (elB.tagName == "I") {
                    elB.classList.remove("fad");
                    elB.classList.add("fa-light");
                }
            });
        });
    });

    Array.from(document.querySelectorAll(".modal-body .row")).forEach((el) => {
        if ($(el).prev().hasClass("row")) $(el).css("margin-top", $(el).prev().find(".tam-max").length ? "-14px" : "11px");
    });

    Array.from(document.querySelectorAll(".modal-body button")).forEach((el) => {
        el.parentElement.style.paddingTop = "1px";
    });

    document.getElementById("busca").onkeyup = function(e) {
        if (e.keyCode == 13) listar();
    }

    $(document).on("keydown", "form", function(event) { 
        const enter = event.key == "Enter";
        if (enter && !validacao_bloqueada) {
            try {
                validar();
            } catch(err) {
                try {
                    validar_estoque();
                } catch(err) {
                    try {
                        validar_comodato();
                    } catch(err) {
                        try {
                            relatorio.validar();
                        } catch(err) {
                            pessoa.validar();
                        }
                    }
                }
            }
        }
        return !enter;
    });

    $(".sortable-columns > th:not(.nao-ordena)").each(function() {
        var th = $(this),
            thIndex = th.index(),
            table = $($(this).parent().attr("for"));
        
        th.click(function() {
            var inverse = $(this).hasClass("text-dark") && $(this).html().indexOf("fa-sort-down") > -1;
            $(this).parent().find(".text-dark").removeClass("text-dark");
            $(this).parent().find(".my-icon").remove();
            $(this).addClass("text-dark");
            $(this).append(inverse ? "<i class = 'my-icon ml-2 fad fa-sort-up'></i>" : "<i class = 'my-icon ml-2 fad fa-sort-down'></i>");
            $(".sortable-columns > th:not(.nao-ordena)").each(function() {
                if (!$(this).hasClass("text-dark")) $(this).append("<i class = 'my-icon ml-2 fa-light fa-sort'></i>");
            });
            table.find("td").filter(function() {
                return $(this).index() === thIndex;
            }).sortElements(function(a, b) {
                return $.text([a]) > $.text([b]) ? inverse ? -1 : 1 : inverse ? 1 : -1;
            }, function() {
                return this.parentNode;
            });
        });
    });

    carrega_autocomplete();

    $(".dinheiro-editavel").each(function() {
        $($(this)[0]).focus(function() {
            if ($(this).val() == "") $(this).val("R$ 0,00");
        });
        $($(this)[0]).keyup(function() {
            let texto_final = $(this).val();
            if (texto_final == "") $(this).val("R$ 0,00");
            $(this).val(dinheiro(texto_final));
        });
        $(this).addClass("text-right");
    });

    $("input.data").each(function() {
        $(this).datepicker({
            dateFormat: "dd/mm/yy",
            closeText: "Fechar",
            prevText: "Anterior",
            nextText: "Próximo",
            currentText: "Hoje",
            monthNames: ["Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro"],
            monthNamesShort: ["Jan", "Fev", "Mar", "Abr", "Mai", "Jun", "Jul", "Ago", "Set", "Out", "Nov", "Dez"],
            dayNames: ["Domingo", "Segunda-feira", "Terça-feira", "Quarta-feira", "Quinta-feira", "Sexta-feira", "Sábado"],
            dayNamesShort: ["Dom", "Seg", "Ter", "Qua", "Qui", "Sex", "Sáb"],
            dayNamesMin: ["Dom", "Seg", "Ter", "Qua", "Qui", "Sex", "Sáb"],
            weekHeader: "Sm",
            firstDay: 1,
            beforeShow: function(elem, dp) {
                setTimeout(function() {
                    let tamanho = elem.offsetWidth > 244 ? elem.offsetWidth : 244;
                    dp.dpDiv[0].style.width = tamanho + "px";
                }, 0);
            }
        });
        $(this).keyup(function() {
            let resultado = $(this).val().replace(/\D/g, "");
            if (resultado.length >= 8) {
                resultado = resultado.substring(0, 8);
                resultado = resultado.substring(0, 2) + "/" + resultado.substring(2, 4) + "/" + resultado.substring(4, 8);
                $(this).val(resultado);    
            }
        });
        $(this).blur(function() {
            let aux = $(this).val().split("/");
            data = new Date(parseInt(aux[2]), parseInt(aux[1]) - 1, parseInt(aux[0]));
            if (data.getFullYear() != aux[2] || data.getMonth() + 1 != aux[1] || data.getDate() != aux[0]) $(this).val("");
        });
    });

    $("#relatorioBilateralModal").on("hide.bs.modal", function() {
        if (document.getElementById("rel-grupo").value == "maquinas-por-empresa") relatorio.inverter();
    });

    $("#estoqueModal").on("hide.bs.modal", function() {
        Array.from(document.getElementsByClassName("remove-produto")).forEach((el) => {
            $(el).trigger("click");
        });
    });

    $("#setoresModal").on("hide.bs.modal", function() {
        $(".linha-usuario").each(function() {
            $(this).remove();
        });
    });

    $(".modal").each(function() {
        let that = this;
        $(this).on("shown.bs.modal", function () {
            let cont = 0;
            do {
                var el = $($("#" + that.id + " input[type=text]")[cont]);
                el.focus();
                cont++;
            } while ($($(el).parent()).hasClass("d-none"))
        })
    });

    $(".form-control").each(function() {
        $(this).keydown(function() {
            $(this).removeClass("invalido");
        });
    });

    listar();
}

function contar_char(el, max) {
    el.classList.remove("invalido");
    el.value = el.value.substring(0, max);
    el.nextElementSibling.innerHTML = el.value.length + "/" + max;
}

function modal(nome, id, callback) {
    limpar_invalido();
    if (callback === undefined) callback = function() {}
    if (id) document.getElementById(nome == "pessoasModal" ? "pessoa-id" : "id").value = id;
    Array.from(document.querySelectorAll("#" + nome + " input")).forEach((el) => {
        if (!id && el.name != "_token") el.value = "";
        if (!$(el).hasClass("autocomplete")) $(el).trigger("keyup");
        anteriores[el.id] = el.value;
    });
    $("#" + nome).modal();
    callback();
}

function excluir(_id, prefixo, e) {
    if (e !== undefined) e.preventDefault();
    $.get(URL + prefixo + "/aviso/" + _id, function(data) {
        if (typeof data == "string") data = $.parseJSON(data);
        if (parseInt(data.permitir)) {
            Swal.fire({
                title: "Aviso",
                html : data.aviso,
                showDenyButton : true,
                confirmButtonText : "NÃO",
                confirmButtonColor : "rgb(31, 41, 55)",
                denyButtonText : "SIM"
            }).then((result) => {
                if (result.isDenied) {
                    $.post(URL + prefixo + "/excluir", {
                        _token : $("meta[name='csrf-token']").attr("content"),
                        id : _id
                    }, function() {
                        location.reload();
                    });
                }
            });
        } else s_alert(data.aviso);
    });
}

function s_alert(texto) {
    Swal.fire({
        icon : "warning",
        title : "Atenção",
        html : texto,
        confirmButtonColor : "rgb(31, 41, 55)"
    });
}

function autocomplete(_this) {
    var _table = _this.data().table,
        _column = _this.data().column,
        _filter = _this.data().filter,
        _filter_col = _this.data().filter_col,
        _search = _this.val(),
        input_id = _this.data().input,
        element = _this,
        div_result;

    $(document).click(function (e) {
        if (e.target.id != element.prop("id")) {
            div_result.remove();
        }
    });

    if (!element.parent().find(".autocomplete-result").length) {
        div_result = $("<div class = 'autocomplete-result' style = 'width:" + document.getElementById($(element).attr("id")).offsetWidth + "px'>");
        element.after(div_result);
    } else {
        div_result = element.parent().find(".autocomplete-result");
        div_result.empty();
    }

    if (!_search) $(input_id).val($(this).data().id).trigger("change");
    $.get(URL + "/autocomplete", {
        table : _table,
        column : _column,
        filter_col : _filter_col,
        filter : _filter,
        search : _search
    }, function (data) {
        if (typeof data == "string") data = $.parseJSON(data);
        div_result.empty();
        data.forEach((item) => {
            div_result.append("<div class = 'autocomplete-line' data-id = '" + item.id + "'>" + item[_column] + "</div>");
        });
        let retira_chars = function(texto) {
            let entityMap = {
                '&amp;': '&',
                '&lt;': '<',
                '&gt;': '>',
                '&quot': '"',
                '&#39;': "'",
                '&#x2F;': '/'
            };
            function unescapeHtml(str) {
                return String(str).replace(/&amp;|&lt;|&gt;|&quot|&#39;|&#x2F;/g, function (s) {
                    return entityMap[s];
                });
            }
            return unescapeHtml(texto);
        }
        element.parent().find(".autocomplete-line").each(function () {
            $(this).click(function () {
                $(input_id).val($(this).data().id).trigger("change");
                element.val(retira_chars($(this).html().toString().split("|")[0].trim()));
                div_result.remove();
            });

            $(this).mouseover(function () {
                $(input_id).val($(this).data().id).trigger("change");
                element.val(retira_chars($(this).html().toString().split("|")[0].trim()));
                $(this).parent().find(".hovered").removeClass("hovered");
                $(this).addClass("hovered");
            });
        });
    });
}

function carrega_autocomplete() {
    $(".autocomplete").each(function() {
        $(this).keyup(function(e) {
            $(this).removeClass("invalido");
            if (e.keyCode == 13) validacao_bloqueada = true;
            if ([9, 13, 17, 38, 40].indexOf(e.keyCode) == -1) autocomplete($(this));
            setTimeout(function() {
                validacao_bloqueada = false;
            }, 50);
        });

        $(this).keydown(function(e) {
            if ([9, 13, 38, 40].indexOf(e.keyCode) > -1) {
                if (e.keyCode == 13) {
                    e.preventDefault();
                    validacao_bloqueada = true;
                }
                seta_autocomplete(e.keyCode, $(this));
            }
        });
    });
}

function seta_autocomplete(direcao, _this) {
    _this = _this.parent();
    var el = _this.find(".autocomplete-result .autocomplete-line");
    var el_hovered = _this.find(".autocomplete-result .autocomplete-line.hovered");
    var target = el.first();
    if (el_hovered.length) {
        switch(direcao) {
            case 38:
                target = el_hovered.prev();
                break;
            case 40:
                target = el_hovered.next();
                break;
            default:
                target = el_hovered;
                break;
        }
    }
    target.trigger(([38, 40].indexOf(direcao) > -1) ? "mouseover" : "click");
}

function verifica_vazios(arr, _erro) {
    if (_erro === undefined) _erro = "";
    let _alterou = false;
    arr.forEach((id) => {
        let el = document.getElementById(id);
        let erro_ou_vazio = !el.value.length;
        if (!erro_ou_vazio && id.indexOf("qtd-") > -1) erro_ou_vazio = !parseInt(el.value);
        if (erro_ou_vazio) {
            if (!_erro) _erro = "Preencha o campo";
            else _erro = "Preencha os campos";
            el.classList.add("invalido");
        }
        try {
            if (el.value.toString().toUpperCase().trim() != anteriores[id].toString().toUpperCase().trim()) _alterou = true;
        } catch(err) {}
    });
    return {
        alterou : _alterou,
        erro : _erro
    };
}

function limpar_invalido() {
    Array.from(document.getElementsByTagName("INPUT")).forEach((el) => {
        el.classList.remove("invalido");
    });
}

function hoje() {
    return new Date().toJSON().slice(0, 10).split('-').reverse().join('/');
}

function validar_datas(el_inicio, el_fim, comodato) {
    let erro = "";
    let aux = el_inicio.value.split("/");
    const inicio = new Date(aux[2], aux[1] - 1, aux[0]);
    aux = el_fim.value.split("/");
    const fim = new Date(aux[2], aux[1] - 1, aux[0]);
    if (inicio > fim) erro = "A data inicial não pode ser maior que a data final";
    else if (inicio.getTime() == fim.getTime() && comodato) erro = "A locação precisa durar mais de um dia";
    if (!comodato && erro) {
        el_inicio.classList.add("invalido");
        el_fim.classList.add("invalido");
    }
    return erro;
}

function RelatorioBilateral(_grupo) {
    let that = this;
    let grupo = _grupo;

    this.validar = function() {
        limpar_invalido();
        let el_empresa = document.getElementById("rel-empresa");
        let el_maquina = document.getElementById("rel-maquina1");
        $.get(URL + "/relatorios/bilateral/consultar", {
            empresa : el_empresa.value,
            maquina : el_maquina.value,
            id_empresa : document.getElementById("rel-id_empresa").value,
            id_maquina : document.getElementById("rel-id_maquina1").value,
            prioridade : grupo == "maquinas-por-empresa" ? "empresas" : "maquinas"
        }, function(erro) {
            if (erro) {
                if (erro == "empresa") {
                    el_empresa.classList.add("invalido");
                    erro = "Empresa não encontrada";
                } else {
                    el_maquina.classList.add("invalido");
                    erro = "Máquina não encontrada";
                }
                s_alert(erro);
            } else document.querySelector("#relatorioBilateralModal form").submit();
        });
    }

    this.inverter = function() {
        const arr = [1, 0];
        let wrapper = document.querySelectorAll("#relatorioBilateralModal .container");
        let items = wrapper[0].children;
        let elements = document.createDocumentFragment();
        arr.forEach(function(idx) {
        	elements.appendChild(items[idx].cloneNode(true));
        });
        wrapper[0].innerHTML = null;
        wrapper[0].appendChild(elements);
        Array.from(document.querySelectorAll(".modal-body .row")).forEach((el) => {
            el.style.removeProperty("margin-top");
            if ($(el).prev().hasClass("row")) $(el).css("margin-top", $(el).prev().find(".tam-max").length ? "-14px" : "11px");
        });
    }

    if (grupo == "maquinas-por-empresa") {
        that.inverter();
        var titulo = "Máquinas por empresa";
    } else var titulo = "Empresas por máquina";
    document.getElementById("relatorioBilateralModalLabel").innerHTML = titulo;
    
    limpar_invalido();
    setTimeout(function() {
        modal("relatorioBilateralModal", 0, function() {
            document.getElementById("rel-grupo").value = grupo;
        });
    }, 0);
}

function RelatorioItens() {
    let el_inicio = document.getElementById("rel-inicio");
    let el_fim = document.getElementById("rel-fim");

    this.validar = function() {
        limpar_invalido();
        let erro = "";
        let el_produto = document.getElementById("rel-produto");
        let el_maquina = document.getElementById("rel-maquina2");
        if (el_inicio.value.length && el_fim.value.length) erro = validar_datas(el_inicio, el_fim, false);
        $.get(URL + "/relatorios/extrato/consultar", {
            produto : el_produto.value,
            maquina : el_maquina.value,
            id_produto : document.getElementById("rel-id_produto").value,
            id_maquina : document.getElementById("rel-id_maquina2").value,
        }, function(data) {
            if (data) {
                if (!erro) {
                    if (data == "maquina") {
                        el_maquina.classList.add("invalido");
                        erro = "Máquina não encontrada";
                    } else {
                        el_produto.classList.add("invalido");
                        erro = "Produto não encontrado";
                    }    
                }
                s_alert(erro);
            } else document.querySelector("#relatorioItensModal form").submit();
        });
    }
    
    limpar_invalido();
    setTimeout(function() {
        modal("relatorioItensModal", 0, function() {
            el_inicio.value = hoje();
            el_fim.value = hoje();
            document.getElementById("rel-lm").value = "N";
        });
    }, 0);
}

function limitar(el) {
    let texto = el.value.toString();
    if (!texto.length) el.value = 1;
    else if (texto.length > 11) el.value = "".padStart(11, "9");
    else if (parseInt(texto) < 1) el.value = 1;
    if (el.id == "quantidade" && location.href.indexOf("colaboradores") > -1) {
        if (parseInt(texto) > limite_maximo) el.value = limite_maximo;
    }
}

function extrato_maquina(id_maquina) {
    $.get(URL + "/valores/maquinas/mostrar/" + id_maquina, function(descr) {
        let req = {};
        ["inicio", "fim", "produto", "id_produto"].forEach((chave) => {
            req[chave] = "";
        });
        req.lm = "S";
        req.maquina = descr;
        req.id_maquina = id_maquina;
        let link = document.createElement("a");
        link.href = URL + "/relatorios/extrato?" + $.param(req);
        link.target = "_blank";
        link.click();
    });
}

function numerico(el) {
    el.value = el.value.replace(/\D/g, "");
}