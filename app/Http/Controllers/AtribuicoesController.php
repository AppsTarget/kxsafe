<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\Models\Atribuicoes;

class AtribuicoesController extends ControllerKX {
    public function verMaximo(Request $request) {
        $resultado = new \stdClass;

        $subquery = "(
            SELECT
                CASE
                    WHEN (es = 'E') THEN qtd
                    ELSE qtd * -1
                END AS qtd,
        ";
        $subquery .= $request->tipo == "produto" ? "id_produto" : "referencia";
        $subquery .= " FROM estoque";
        $subquery .= " JOIN maquinas_produtos AS mp ON mp.id = estoque.id";
        if ($request->tipo == "referencia") $subquery .= " JOIN produtos ON produtos.id = mp.id_produto";
        $subquery .= ") AS estq";

        $where = $request->tipo == "produto" ? "id_produto = ".$request->id : "referencia IN (
            SELECT referencia
            FROM produtos
            WHERE id = ".$request->id."
        )";

        $resultado->maximo = DB::table(DB::raw($subquery))
                                    ->selectRaw("IFNULL(SUM(qtd), 0) AS saldo")
                                    ->whereRaw($where)
                                    ->value("saldo");
        
        $resultado->validade = DB::table("produtos")
                                    ->selectRaw($request->tipo == "produto" ? "validade" : "MAX(validade) AS validade")
                                    ->whereRaw(str_replace("id_produto", "id", $where))
                                    ->value("validade");

        return json_encode($resultado);
    }

    public function salvar(Request $request) {
        if (!sizeof(
            DB::table("produtos")
                ->where($request->produto_ou_referencia_chave == "produto" ? "descr" : "referencia", $request->produto_ou_referencia_valor)
                ->where("lixeira", 0)
                ->get()
        )) return 404;
        $produto_ou_referencia_valor = $request->produto_ou_referencia_chave == "produto" ?
            DB::table("produtos")
                ->where("descr", $request->produto_ou_referencia_valor)
                ->where("lixeira", 0)
                ->value("cod_externo")
        : $request->produto_ou_referencia_valor;
        if (sizeof(
            DB::table("atribuicoes")
                ->where("pessoa_ou_setor_chave", $request->pessoa_ou_setor_chave)
                ->where("pessoa_ou_setor_valor", $request->pessoa_ou_setor_valor)
                ->where("produto_ou_referencia_valor", $produto_ou_referencia_valor)
                ->where("produto_ou_referencia_chave", $request->produto_ou_referencia_chave)
                ->where("lixeira", 0)
                ->get()
        )) return 403;
        $linha = new Atribuicoes;
        $linha->pessoa_ou_setor_chave = $request->pessoa_ou_setor_chave;
        $linha->pessoa_ou_setor_valor = $request->pessoa_ou_setor_valor;
        $linha->produto_ou_referencia_chave = $request->produto_ou_referencia_chave;
        $linha->produto_ou_referencia_valor = $produto_ou_referencia_valor;
        $linha->qtd = $request->qtd;
        $linha->validade = $request->validade;
        $linha->obrigatorio = $request->obrigatorio;
        $linha->save();
        $this->log_inserir("C", "atribuicoes", $linha->id);
        return 201;
    }

    public function excluir(Request $request) {
        $linha = Atribuicoes::find($request->id);
        $linha->lixeira = 1;
        $linha->save();
        $this->log_inserir("D", "atribuicoes", $linha->id);
    }

    private function consulta($select, $where) {
        return DB::table("produtos")
                    ->select(DB::raw($select))
                    ->join("atribuicoes", function($join) {
                        $join->on(function($sql) {
                            $sql->on("atribuicoes.produto_ou_referencia_valor", "produtos.cod_externo")
                                ->where("atribuicoes.produto_ou_referencia_chave", "produto");
                        })->orOn(function($sql) {
                            $sql->on("atribuicoes.produto_ou_referencia_valor", "produtos.referencia")
                                ->where("atribuicoes.produto_ou_referencia_chave", "referencia");
                        });
                    })
                    ->whereRaw($where)
                    ->where("produtos.lixeira", 0)
                    ->where("atribuicoes.lixeira", 0);
    }

    public function mostrar(Request $request) {
        $select = "atribuicoes.id, ";
        if ($request->tipo == "produto") $select .= "produtos.descr AS ";
        $select .= "produto_ou_referencia_valor,
            atribuicoes.qtd,
            atribuicoes.validade, 
            CASE
                WHEN obrigatorio = 1 THEN 'SIM'
                ELSE 'NÃO'
            END AS obrigatorio
        ";
        $where = "pessoa_ou_setor_valor = ".$request->id."
            AND produto_ou_referencia_chave = '".$request->tipo."'
            AND pessoa_ou_setor_chave = '".$request->tipo2."'";
        return $this->consulta($select, $where)
                    ->groupby(
                        "atribuicoes.id",
                        ($request->tipo == "produto" ? "produtos.descr" : "produto_ou_referencia_valor"),
                        "atribuicoes.qtd",
                        "atribuicoes.validade",
                        "atribuicoes.obrigatorio"
                    )
                    ->orderby("atribuicoes.id")
                    ->get();
    }

    public function produtos($id) {
        return json_encode($this->consulta("
            produtos.id,
            CASE
                WHEN produto_ou_referencia_chave = 'referencia' THEN CONCAT(produtos.descr, ' ', tamanho)
                ELSE produtos.descr
            END AS descr,
            CASE
                WHEN produto_ou_referencia_chave = 'referencia' THEN produtos.referencia
                ELSE produtos.descr
            END AS titulo
        ", "atribuicoes.id = ".$id)->orderby("descr")->get());
    }
}