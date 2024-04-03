<!DOCTYPE html>
<html lang = "{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset = "utf-8" />
        <meta name = "viewport" content = "width=device-width, initial-scale=1" />
        <meta name = "csrf-token" content = "{{ csrf_token() }}" />
        <title>Kx-safe</title>
        <link rel = "icon shortcut" href = "{{ asset('img/favicon.ico') }}" type = "image/x-icon" />
        <link rel = "icon"          href = "{{ asset('img/favicon.ico') }}" type = "image/x-icon" />
        <link href = "{{ asset('css/bootstrap.min.css') }}" rel = "stylesheet" />
        <link href = "{{ asset('css/jquery-ui.min.css') }}" rel = "stylesheet" />
        <link href = "{{ asset('css/my-style.css')      }}" rel = "stylesheet" />
        <link href = "{{ asset('css/fa.css')            }}" rel = "stylesheet" />
        <style type = "text/css">
            .form-search::after, .form-search-2::after {
                background: url("{{ config('app.root_url') }}/img/keyboard.png") no-repeat;
                background-size: contain;
                bottom: 4.5px;
                content: " ";
                position: absolute;
                right: 20px;
                height: 30px;
                width: 30px;
            }

            .form-search-2::after {
                right: 7px
            }

            .form-search-3::after {
                right: -15px
            }
        </style>
    </head>
    <body>
        <div id = "app">
            <main class = "py-4">
                <div class = "main-toolbar">
                    <a id = "link-home" href = "{{ config('app.root_url') }}">
                        <img src = "{{ asset('img/logo.png') }}" style = "height:100px">
                    </a>
                    <div class = "btn-toolbar px-3 mr-auto">
                        <a href = "#">
                            <i class = "my-icon fa-light fa-city"></i>
                            <span>Corporativo</span>
                            <img class = "dropdown-icon" src = "{{ asset('img/sort-down.png') }}">
                            <ul class = "dropdown-toolbar">
                                <li onclick = "redirect('/kxsafe/empresas')">
                                    <span>Empresas</span>
                                </li>
                                <li onclick = "redirect('/kxsafe/colaboradores')">
                                    <span>Colaboradores</span>
                                </li>
                            </ul>
                        </a>
                        @if (!intval(App\Models\Pessoas::find(Auth::user()->id_pessoa)->id_empresa))
                            <a href = "#">
                                <i class = "my-icon fa-light fa-box"></i>
                                <span>Itens</span>
                                <img class = "dropdown-icon" src = "{{ asset('img/sort-down.png') }}">
                                <ul class = "dropdown-toolbar">
                                    <li onclick = "redirect('/kxsafe/valores/categorias')">
                                        <span>Categorias</span>
                                    </li>
                                    <li onclick = "redirect('/kxsafe/produtos')">
                                        <span>Produtos</span>
                                    </li>
                                </ul>
                            </a>
                        @endif
                        <a href = "{{ config('app.root_url') }}/valores/maquinas">
                            <i class = "my-icon fa-light fa-shelves-empty"></i>
                            <span>Máquinas</span>
                        </a>
                        @if (!intval(App\Models\Pessoas::find(Auth::user()->id_pessoa)->id_empresa))
                            <a href = "{{ config('app.root_url') }}/setores">
                                <i class = "my-icon fa-light fa-layer-group"></i>
                                <span>Setores</span>
                            </a>
                            <a href = "#">
                                <i class = "my-icon fa-light fa-file-chart-line"></i>
                                <span>Relatórios</span>
                                <img class = "dropdown-icon" src = "{{ asset('img/sort-down.png') }}">
                                <ul class = "dropdown-toolbar">
                                    <li onclick = "window.open('/kxsafe/relatorios/comodatos', '_blank')">
                                        <span>Locação</span>
                                    </li>
                                    <li onclick = "relatorio = new RelatorioBilateral('empresas-por-maquina')">
                                        <span>Empresas por máquina</span>
                                    </li>
                                    <li onclick = "relatorio = new RelatorioItens()">
                                        <span>Extrato de itens</span>
                                    </li>
                                    <li onclick = "relatorio = new RelatorioBilateral('maquinas-por-empresa')">
                                        <span>Máquinas por empresa</span>
                                    </li>
                                </ul>
                            </a>
                        @endif
                    </div>
                    <div class = "d-flex mx-3">
                        <div class = "user-card d-flex my-auto">
                            <div class = "user-pic mr-3">
                                <span class = "m-auto">
                                    @foreach(explode(" ", Auth::user()->name, 2) as $nome)
                                        {{ substr($nome, 0, 1) }}
                                    @endforeach
                                </span>
                            </div>
                            <div class = "user-name d-grid ml-1">
                                <div class = "m-auto">
                                    @php
                                        $full_name = explode(" ", trim(Auth::user()->name));
                                    @endphp
                                    <span class = "mt-2">{{ $full_name[0] }}</span>
                                    <span></span>
                                </div>
                            </div>
                            <img class = "dropdown-icon" src = "{{ asset('img/sort-down.png') }}">
                            <ul class = "dropdown-toolbar-user">
                                <li onclick = "pessoa = new Pessoa({{ Auth::user()->id_pessoa }})">
                                    <span class = "pb-2">Editar</span>
                                </li>
                                <li onclick = "document.getElementById('logout-form').submit()">
                                    <span class = "pb-2">Sair</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <form id = "logout-form" class = "d-none" action = "{{ route('logout') }}" method = "POST">
                        @csrf
                    </form>
                </div>
                @yield("content")
                
                @include("modals.pessoas_modal")
                @include("modals.reports.bilateral_modal")
                @include("modals.reports.itens_modal")
            </main>
        </div>
        <script type = "text/javascript" language = "JavaScript">
            const URL = "{{ config('app.root_url') }}";

            function redirect(url, bNew_Tab) {
                if (bNew_Tab) window.open(url, '_blank');
                else document.location.href = url;
            }
        </script>
        <script type = "text/javascript" language = "JavaScript" src = "{{ asset('js/sweetalert2.js')   }}"></script>
        <script type = "text/javascript" language = "JavaScript" src = "{{ asset('js/jquery.min.js')    }}"></script>
        <script type = "text/javascript" language = "JavaScript" src = "{{ asset('js/jquery-ui.min.js') }}"></script>
        <script type = "text/javascript" language = "JavaScript" src = "{{ asset('js/bootstrap.min.js') }}"></script>
        @if (!intval(App\Models\Pessoas::find(Auth::user()->id_pessoa)->id_empresa) || ((isset($alias) ? $alias : "maquinas") == "maquinas"))
            <script type = "text/javascript" language = "JavaScript" src = "{{ asset('js/my-functions.js')  }}"></script>
        @else
            <script type = "text/javascript" language = "JavaScript">
                window.onload = function() {
                    location.href = URL;
                }
            </script>
        @endif
        <script type = "text/javascript" language = "JavaScript">
            function Pessoa(id) {
                let that = this;

                this.formatar_cpf = function(el) {
                    el.classList.remove("invalido");
                    let cpf = el.value;
                    let num = cpf.replace(/[^\d]/g, '');
                    let len = num.length;
                    if (len <= 6) cpf = num.replace(/(\d{3})(\d{1,3})/g, '$1.$2');
                    else if (len <= 9) cpf = num.replace(/(\d{3})(\d{3})(\d{1,3})/g, '$1.$2.$3');
                    else {
                        cpf = num.replace(/(\d{3})(\d{3})(\d{3})(\d{1,2})/g, "$1.$2.$3-$4");
                        cpf = cpf.substring(0, 14);
                    }
                    el.value = cpf;
                }

                this.toggle_user = function(setor) {
                    $.get(URL + "/setores/mostrar/" + setor, function(data) {
                        if (typeof data == "string") data = $.parseJSON(data);
                        Array.from(document.getElementsByClassName("usr-info")).forEach((el) => {
                            let pes_info = document.getElementById("pes-info").classList;
                            let palavras = document.getElementById("pessoasModalLabel").innerHTML.split(" ");
                            if (parseInt(data.cria_usuario)) {
                                el.classList.remove("d-none");
                                pes_info.add("d-none");
                                palavras[1] = "administrador";
                            } else {
                                el.classList.add("d-none");
                                pes_info.remove("d-none");
                                palavras[1] = "colaborador";
                            }
                            document.getElementById("pessoasModalLabel").innerHTML = palavras.join(" ");
                        });
                    })
                }

                this.validar = function() {
                    let validar_email = function(__email) {
                        if ((__email == null) || (__email.length < 4)) return false;
                        let partes = __email.split("@");
                        if (partes.length != 2) return false;
                        let pre = partes[0];
                        if (!pre.length) return false;
                        if (!/^[a-zA-Z0-9_.-/+]+$/.test(pre)) return false;
                        let partesDoDominio = partes[1].split(".");
                        if (partesDoDominio.length < 2) return false;
                        let valido = true;
                        partesDoDominio.forEach((parteDoDominio) => {
                            if (!parteDoDominio.length) valido = false;
                            if (!/^[a-zA-Z0-9-]+$/.test(parteDoDominio)) valido = false;
                        })
                        return valido;
                    }

                    let validar_cpf = function(__cpf) {
                        __cpf = __cpf.replace(/\D/g, "");
                        if (__cpf == "00000000000") return false;
                        if (__cpf.length != 11) return false;
                        let soma = 0;
                        for (let i = 1; i <= 9; i++) soma = soma + (parseInt(__cpf.substring(i - 1, i)) * (11 - i));
                        let resto = (soma * 10) % 11;
                        if ((resto == 10) || (resto == 11)) resto = 0;
                        if (resto != parseInt(__cpf.substring(9, 10))) return false;
                        soma = 0;
                        for (i = 1; i <= 10; i++) soma = soma + (parseInt(__cpf.substring(i - 1, i)) * (12 - i));
                        resto = (soma * 10) % 11;
                        if ((resto == 10) || (resto == 11)) resto = 0;
                        if (resto != parseInt(__cpf.substring(10, 11))) return false;
                        return true;
                    }

                    limpar_invalido();
                    let erro = "";

                    const id_setor = document.getElementById("id_setor").value;

                    let _email = document.getElementById("email");
                    let _cpf = document.getElementById("cpf");
                    let nome = document.getElementById("nome");

                    if (id_setor == 1) {
                        if (!_email.value.length) {
                            erro = "Preencha o campo";
                            _email.classList.add("invalido");
                        }
                    }
                    if (!_cpf.value.length) {
                        if (!erro) erro = "Preencha o campo";
                        else erro = "Preencha os campos";
                        _cpf.classList.add("invalido");
                    }
                    let aux = verifica_vazios(["nome", "setor", "pessoa-empresa"], erro);
                    erro = aux.erro;
                    let alterou = aux.alterou;
                    if (id_setor == 1) {
                        if (!erro && !validar_email(_email.value)) {
                            erro = "E-mail inválido";
                            _email.classList.add("invalido");
                        }
                        if (
                            document.getElementById("password").value.length ||
                            _email.value.toLowerCase() != anteriores.email.toLowerCase()
                        ) alterou = true;
                    }
                    if (!erro && !validar_cpf(_cpf.value)/* && _cpf.value.trim()*/) {
                        erro = "CPF inválido";
                        _cpf.classList.add("invalido");
                    }
                    if (_cpf.value != anteriores.cpf) alterou = true;
                    
                    aux = document.getElementById("admissao").value;
                    if (aux) {
                        aux = aux.split("/");
                        const hj = new Date();
                        const admissao = new Date(aux[2], aux[1] - 1, aux[0]);
                        if (!erro && admissao > hj) erro = "A admissão não pode ser no futuro";
                    }

                    $.get(URL + "/colaboradores/consultar/", {
                        cpf : _cpf.value.replace(/\D/g, ""),
                        email : _email.value,
                        empresa : document.getElementById("pessoa-empresa").value,
                        id_empresa : document.getElementById("pessoa-id_empresa").value,
                        setor : document.getElementById("setor").value,
                        id_setor : document.getElementById("id_setor").value
                    }, function(data) {
                        if (typeof data == "string") data = $.parseJSON(data);
                        if (!erro && data.tipo == "invalido") {
                            erro = data.dado + " não encontrad" + (data.dado == "Empresa" ? "a" : "o");
                            document.getElementById(data.dado == "Empresa" ? "pessoa-empresa" : "setor").classList.add("invalido");
                        }
                        if (!erro && data.tipo == "duplicado" && !parseInt(document.getElementById("pessoa-id").value)) {
                            erro = "Já existe um registro com esse " + data.dado;
                            document.getElementById(data.dado == "CPF" ? "cpf" : "email").classList.add("invalido");
                        }
                        if (!erro && !alterou) erro = "Altere pelo menos um campo para salvar";
                        if (!erro) {
                            _cpf.value = _cpf.value.replace(/\D/g, "");
                            document.querySelector("#pessoasModal form").submit();
                        } else s_alert(erro);
                    });
                }

                let titulo = id ? "Editando" : "Cadastrando";
                titulo += " colaborador";
                document.getElementById("pessoasModalLabel").innerHTML = titulo;
                let estilo_bloco_senha = document.getElementById("password").parentElement.parentElement.style;
                if (id) {
                    $.get(URL + "/colaboradores/mostrar/" + id, function(data) {
                        if (typeof data == "string") data = $.parseJSON(data);
                        ["nome", "cpf", "setor", "pessoa-empresa", "id_setor", "pessoa-id_empresa", "email", "funcao", "admissao"].forEach((_id) => {
                            document.getElementById(_id).value = data[_id.replace("pessoa-", "")];
                        });
                        setTimeout(function() {
                            modal("pessoasModal", id, function() {
                                that.toggle_user(parseInt(data.id_setor));
                                estilo_bloco_senha.display = id != {{ Auth::user()->id_pessoa }} ? "none" : "";
                                document.getElementById("setor").disabled = id == {{ Auth::user()->id_pessoa }};
                                Array.from(document.getElementsByClassName("pessoa-senha")).forEach((el) => {
                                    el.innerHTML = "Senha:";
                                });
                            });
                        }, 0);
                    });
                } else {
                    setTimeout(function() {
                        modal("pessoasModal", id, function() {
                            that.toggle_user(id);
                            estilo_bloco_senha.removeProperty("display");
                            document.getElementById("setor").disabled = false;
                            Array.from(document.getElementsByClassName("pessoa-senha")).forEach((el) => {
                                el.innerHTML = "Senha: *";
                            });
                        });
                    }, 0);
                }
            }
        </script>
    </body>
</html>