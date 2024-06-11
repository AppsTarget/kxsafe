<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\LogController;
use App\Http\Controllers\MaquinasController;
use App\Models\Valores;
use App\Models\Produtos;
use App\Models\Estoque;
use App\Models\Retiradas;
use App\Models\Atribuicoes;

class ApiController extends Controller {
    public function empresas() {
        return json_encode(
            DB::table("empresas")
                ->select(
                    "empresas.id",
                    DB::raw("
                        CONCAT(
                            empresas.nome_fantasia,
                            IFNULL(CONCAT(' - ', matriz.nome_fantasia), '')
                        ) AS descr
                    ")
                )
                ->leftjoin("empresas AS matriz", "matriz.id", "empresas.id_matriz")
                ->where("empresas.lixeira", 0)
                ->where("matriz.lixeira", 0)
                ->get()
        );
    }

    public function maquinas(Request $request) {
        $query = "
            SELECT
                tab.id,
                tab.descr
            
            FROM (
                SELECT
                    id,
                    descr
                FROM valores
                WHERE alias = 'maquinas'
                  AND lixeira = 0
            ) AS tab
        ";
        if (isset($request->idEmp)) {
            $query .= "
                JOIN (
                    SELECT id_maquina
                    FROM comodatos
                    WHERE id_empresa = ".$request->idEmp."
                      AND CURDATE() >= inicio
                      AND CURDATE() < fim
                ) AS aux ON aux.id_maquina = tab.id
            ";
        }
        return DB::select(DB::raw($query));
    }

    public function produtos_por_maquina(Request $request) {
        $consulta = DB::table("maquinas_produtos AS mp")
                        ->select(
                            "produtos.id",
                            "produtos.descr",
                            DB::raw("IFNULL(mp.preco, 0) AS preco"),
                            DB::raw("IFNULL(tab.saldo, 0) AS saldo"),
                            DB::raw("IFNULL(mp.minimo, 0) AS minimo"),
                            DB::raw("IFNULL(mp.maximo, 0) AS maximo")
                        )
                        ->leftjoin(DB::raw("(
                            SELECT
                                IFNULL(SUM(qtd), 0) AS saldo,
                                id_mp
                                
                            FROM (
                                SELECT
                                    CASE
                                        WHEN (es = 'E') THEN qtd
                                        ELSE qtd * -1
                                    END AS qtd,
                                    id_mp
                        
                                FROM estoque
                            ) AS estq
                        
                            GROUP BY id_mp
                        ) AS tab"), "tab.id_mp", "mp.id")
                        ->join("produtos", "produtos.id", "mp.id_produto")
                        ->where("mp.id_maquina", $request->idMaquina)
                        ->where("produtos.lixeira", 0)
                        ->get();
        foreach ($consulta as $linha) {
            $linha->preco = floatval($linha->preco);
            $linha->saldo = floatval($linha->saldo);
            $linha->minimo = floatval($linha->minimo);
            $linha->maximo = floatval($linha->maximo);
        }
        return json_encode($consulta);
    }

    public function categorias(Request $request) {
        $linha = Valores::firstOrNew(["id" => $request->id]);
        $linha->descr = mb_strtoupper($request->descr);
        $linha->alias = "categorias";
        if (!$request->id) {
            $linha->seq = intval(
                DB::table("valores")
                    ->selectRaw("IFNULL(MAX(seq), 0) AS ultimo")
                    ->where("alias", "categorias")
                    ->value("ultimo")
            ) + 1;
        }
        $linha->save();
        $log = new LogController;
        $modelo = $log->inserir($request->id ? "E" : "C", "valores", $linha->id, true);
        if (isset($request->usu)) $modelo->nome = $request->usu;
        $modelo->save();
        $resultado = new \stdClass;
        $resultado->id = $linha->id;
        $resultado->descr = $linha->descr;
        return json_encode($resultado);
    }

    public function produtos(Request $request) {
        $linha = Produtos::firstOrNew(["id" => $request->id]);
        $linha->descr = mb_strtoupper($request->descr);
        $linha->preco = $request->preco;
        $linha->validade = $request->validade;
        $linha->ca = $request->ca;
        $linha->cod_externo = $request->codExterno;
        $linha->id_categoria = $request->idCategoria;
        $linha->foto = $request->foto;
        $linha->lixeira = $request->lixeira;
        if (isset($request->refer)) $linha->referencia = $request->refer;
        if (isset($request->tamanho)) $linha->tamanho = $request->tamanho;
        $linha->save();
        $log = new LogController;
        $letra_log = $request->id ? "E" : "C";
        if (intval($request->lixeira)) $letra_log = "D";
        $modelo = $log->inserir($letra_log, "produtos", $linha->id, true);
        if (isset($request->usu)) $modelo->nome = $request->usu;
        $modelo->save();
        $maquinas = new MaquinasController;
        $maquinas->mov_estoque($linha->id, true);
        $consulta = DB::table("produtos")
                        ->select(
                            "id",
                            "descr",
                            "preco",
                            "validade",
                            DB::raw("IFNULL(ca, '') AS ca"),
                            DB::raw("IFNULL(foto, '') AS foto"),
                            "lixeira",
                            "referencia AS refer",
                            DB::raw("IFNULL(tamanho, '') AS tamanho"),
                            "id_categoria AS idCategoria",
                            "cod_externo AS codExterno",
                            "'123' AS usu"
                        )
                        ->where("id", $linha->id)
                        ->first();
        $consulta->preco = floatval($consulta->preco);
        $consulta->lixeira = intval($consulta->lixeira);
        return json_encode($consulta);
    }

    public function movimentar_estoque(Request $request) {
        for ($i = 0; $i < sizeof($request->idProduto); $i++) {
            $linha = new Estoque;
            $linha->es = $request->es[$i];
            $linha->descr = $request->descr[$i];
            $linha->qtd = $request->qtd[$i];
            $linha->id_mp = DB::table("maquinas_produtos")
                                ->where("id_produto", $request->idProduto[$i])
                                ->where("id_maquina", $request->idMaquina)
                                ->value("id");
            $linha->save();
            $log = new LogController;
            $modelo = $log->inserir("C", "estoque", $linha->id, true);
            if (isset($request->usu)) $modelo->nome = $request->usu;
            $modelo->save();
        }
        return 200;
    }

    public function gerenciar_estoque(Request $request) {
        $precoProd = floatval(DB::select("produtos")->where("id", $request->idProduto)->value("preco"));
        if (isset($request->preco)) $precoProd = floatval($request->preco) > 0 ? floatval($request->preco) : floatval(DB::select("produtos")->where("id", $request->idProduto)->value("preco"));
        $log = new LogController;
        DB::statement("
            UPDATE maquinas_produtos SET
                minimo = ".$request->minimo.",
                maximo = ".$request->maximo.",
                preco = ".$precoProd."
            WHERE id_produto = ".$request->idProduto."
              AND id_maquina = ".$request->idMaquina
        );
        $consulta = DB::table("maquinas_produtos")
                    ->select("id")
                    ->where("id_produto", $request->idProduto)
                    ->where("id_maquina", $request->idMaquina)
                    ->get();
        foreach ($consulta as $linha) {
            $modelo = $log->inserir("E", "maquinas_produtos", $linha->id, true);
            if (isset($request->usu)) $modelo->nome = $request->usu;
            $modelo->save();
        }
    }

    public function validarApp(Request $request) {
        return sizeof(
            DB::table("pessoas")
                ->where("cpf", $request->cpf)
                ->where("senha", $request->senha)
                ->where("lixeira", 0)
                ->get()
        ) ? 1 : 0;
    }

    public function verPessoa(Request $request) {
        return json_encode(
            DB::table("pessoas")
                ->where("cpf", $request->cpf)
                ->first()
        );
    }

    public function produtosPorPessoa(Request $request) {
        $consulta = DB::table("produtos")
                        ->select(
                            "produtos.id",
                            "produtos.referencia",
                            "produtos.descr AS nome",
                            "produtos.detalhes",
                            "produtos.cod_externo AS codbar",
                            DB::raw("IFNULL(produtos.tamanho, '') AS tamanho"),
                            DB::raw("IFNULL(produtos.foto, '') AS foto"),
                            "atribuicoes.id AS id_atribuicao",
                            DB::raw("(atribuicoes.qtd - IFNULL(ret.qtd, 0)) AS qtd"),
                            DB::raw("IFNULL(ret.ultima_retirada, '') AS ultima_retirada"),
                            DB::raw("DATE_FORMAT(IFNULL(ret.proxima_retirada, CURDATE()), '%d/%m/%Y') AS proxima_retirada")
                        )->join("atribuicoes", "atribuicoes.id", DB::raw("(
                            SELECT atribuicoes.id
                            
                            FROM atribuicoes

                            JOIN pessoas
                                ON (atribuicoes.pessoa_ou_setor_chave = 'pessoa' AND atribuicoes.pessoa_ou_setor_valor = pessoas.id)
                                    OR (atribuicoes.pessoa_ou_setor_chave = 'setor' AND atribuicoes.pessoa_ou_setor_valor = pessoas.id_setor)
                            
                            WHERE (
                                (produto_ou_referencia_chave = 'produto' AND produto_ou_referencia_valor = produtos.cod_externo)
                            OR (produto_ou_referencia_chave = 'referencia' AND produto_ou_referencia_valor = produtos.referencia)
                            ) AND pessoas.cpf = '".$request->cpf."'
                              AND pessoas.lixeira = 0
                              AND atribuicoes.lixeira = 0

                            ORDER BY pessoa_ou_setor_chave

                            LIMIT 1
                        )"))->leftjoin(DB::raw("(
                            SELECT
                                SUM(retiradas.qtd) AS qtd,
                                id_atribuicao,
                                DATE_FORMAT(MAX(retiradas.created_at), '%d/%m/%Y') AS ultima_retirada,
                                DATE_ADD(MAX(retiradas.created_at), INTERVAL atribuicoes.validade DAY) AS proxima_retirada
                            FROM retiradas
                            JOIN atribuicoes
                                ON atribuicoes.id = retiradas.id_atribuicao
                            WHERE DATE_ADD(DATE(retiradas.created_at), INTERVAL atribuicoes.validade DAY) >= CURDATE()
                            GROUP BY
                                id_atribuicao,
                                atribuicoes.validade
                        ) AS ret"), "ret.id_atribuicao", "atribuicoes.id")
                        ->get();
        $resultado = array();
        foreach ($consulta as $linha) {
            if ($linha->foto) {
                $foto = explode("/", $linha->foto);
                $linha->foto = $foto[sizeof($foto) - 1];
            }
            array_push($resultado, $linha);
        }
        return json_encode(collect($resultado)->groupBy("referencia")->map(function($itens) use($request) {
            return [
                "id_pessoa" => DB::table("pessoas")->where("cpf", $request->cpf)->value("id"),
                "nome" => $itens[0]->nome,
                "foto" => $itens[0]->foto,
                "referencia" => $itens[0]->referencia,
                "qtd" => $itens[0]->qtd,
                "detalhes" => $itens[0]->detalhes,
                "ultima_retirada" => $itens[0]->ultima_retirada,
                "proxima_retirada" => $itens[0]->proxima_retirada,
                "tamanhos" => $itens->map(function($tamanho) use($request) {
                    return [
                        "id" => $tamanho->id,
                        "id_pessoa" => DB::table("pessoas")->where("cpf", $request->cpf)->value("id"),
                        "id_atribuicao" => $tamanho->id_atribuicao,
                        "selecionado" => false,
                        "codbar" => $tamanho->codbar,
                        "numero" => $tamanho->tamanho ? $tamanho->tamanho : "UN"
                    ];
                })->values()->all()
            ];
        })->sortBy("nome")->values()->all());
    }

    public function retirar(Request $request) {
        $log = new LogController;
        $resultado = new \stdClass;
        $cont = 0;
        while (isset($request[$cont]["id_atribuicao"])) {
            $retirada = $request[$cont];
            $atribuicao = Atribuicoes::find($retirada["id_atribuicao"]);
            if ($atribuicao == null) {
                $resultado->code = 404;
                $resultado->msg = "Atribuição não encontrada";
                return json_encode($resultado);
            }
            $maquinas = DB::table("valores")
                            ->where("seq", $retirada["id_maquina"])
                            ->where("alias", "maquinas")
                            ->get();
            if (!sizeof($maquinas)) {
                $resultado->code = 404;
                $resultado->msg = "Máquina não encontrada";
                return json_encode($resultado);
            }
            $comodato = DB::table("comodatos")
                            ->select("id")
                            ->where("id_maquina", $maquinas[0]->id)
                            ->whereRaw("inicio <= CURDATE()")
                            ->whereRaw("fim >= CURDATE()")
                            ->get();
            if (!sizeof($comodato)) {
                $resultado->code = 404;
                $resultado->msg = "Máquina não comodatada para nenhuma empresa";
                return json_encode($resultado);
            }
            $ja_retirados = DB::table("retiradas")
                                ->selectRaw("IFNULL(SUM(retiradas.qtd), 0) AS qtd")
                                ->join("atribuicoes", "atribuicoes.id", "retiradas.id_atribuicao")
                                ->whereRaw("DATE_ADD(DATE(retiradas.created_at), INTERVAL atribuicoes.validade DAY) >= CURDATE()")
                                ->where("atribuicoes.id", $retirada["id_atribuicao"])
                                ->get();
            $ja_retirados = sizeof($ja_retirados) ? floatval($ja_retirados[0]->qtd) : 0;
            if (floatval($atribuicao->qtd) < (floatval($retirada["qtd"]) + $ja_retirados)) {
                $resultado->code = 401;
                $resultado->msg = "Essa quantidade de produtos não é permitida para essa pessoa";
                return json_encode($resultado);
            }
            $linha = new Retiradas;
            $linha->id_pessoa = $retirada["id_pessoa"];
            $linha->id_atribuicao = $retirada["id_atribuicao"];
            $linha->id_produto = $retirada["id_produto"];
            $linha->id_comodato = $comodato[0]->id;
            $linha->qtd = $retirada["qtd"];
            $linha->save();
            $modelo = $log->inserir("C", "retiradas", $linha->id, true);
            $modelo->nome = "APP";
            $modelo->save();
            $cont++;
        }
        $resultado->code = 201;
        $resultado->msg = "Sucesso";
        return json_encode($resultado);
    }

    public function validarSpv(Request $request) {
        $consulta = DB::table("pessoas")
                        ->where("cpf", $request->cpf)
                        ->where("senha", $request->senha)
                        ->where("supervisor", 1)
                        ->where("lixeira", 0)
                        ->get();
        return sizeof($consulta) ? $consulta[0]->id : 0;
    }

    public function retirarComSupervisao(Request $request) {
        $log = new LogController;
        $resultado = new \stdClass;
        $cont = 0;
        while (isset($request[$cont]["id_atribuicao"])) {
            $retirada = $request[$cont];
            $atribuicao = Atribuicoes::find($retirada["id_atribuicao"]);
            if ($atribuicao == null) {
                $resultado->code = 404;
                $resultado->msg = "Atribuição não encontrada";
                return json_encode($resultado);
            }
            $maquinas = DB::table("valores")
                            ->where("seq", $retirada["id_maquina"])
                            ->where("alias", "maquinas")
                            ->get();
            if (!sizeof($maquinas)) {
                $resultado->code = 404;
                $resultado->msg = "Máquina não encontrada";
                return json_encode($resultado);
            }
            $comodato = DB::table("comodatos")
                            ->select("id")
                            ->where("id_maquina", $maquinas[0]->id)
                            ->whereRaw("inicio <= CURDATE()")
                            ->whereRaw("fim >= CURDATE()")
                            ->get();
            if (!sizeof($comodato)) {
                $resultado->code = 404;
                $resultado->msg = "Máquina não comodatada para nenhuma empresa";
                return json_encode($resultado);
            }
            $linha = new Retiradas;
            $linha->id_pessoa = $retirada["id_pessoa"];
            $linha->id_supervisor = $retirada["id_supervisor"];
            $linha->observacao = $retirada["obs"];
            $linha->id_atribuicao = $retirada["id_atribuicao"];
            $linha->id_produto = $retirada["id_produto"];
            $linha->id_comodato = $comodato[0]->id;
            $linha->qtd = $retirada["qtd"];
            $linha->save();
            $modelo = $log->inserir("C", "retiradas", $linha->id, true);
            $modelo->nome = "APP";
            $modelo->save();
            $cont++;
        }
        $resultado->code = 201;
        $resultado->msg = "Sucesso";
        return json_encode($resultado);
    }
}