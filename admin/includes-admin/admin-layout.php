<?php
/* =========================================
   ADMIN-LAYOUT.PHP
   Componentes visuais reutilizáveis do Painel Admin
   Futebol Brasileiro
========================================= */

/*
  Este arquivo depende preferencialmente de:
  - admin-funcoes.php

  Uso recomendado nas páginas internas:
  require_once __DIR__ . '/includes-admin/admin-auth.php';
  require_once __DIR__ . '/includes-admin/admin-funcoes.php';
  require_once __DIR__ . '/includes-admin/admin-opcoes.php';
  require_once __DIR__ . '/includes-admin/admin-layout.php';
*/

/* =========================================
   FALLBACK DE ESCAPE
========================================= */

if (!function_exists('eAdmin')) {
    function eAdmin($valor): string
    {
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
    }
}

/* =========================================
   HERO / CABEÇALHO DA PÁGINA
========================================= */

if (!function_exists('renderAdminHero')) {
    function renderAdminHero(
        string $titulo,
        string $descricao = '',
        string $eyebrow = 'Admin',
        array $metas = []
    ): void {
        ?>
        <section class="admin-hero">
            <?php if (!empty($eyebrow)): ?>
                <span class="eyebrow"><?= eAdmin($eyebrow) ?></span>
            <?php endif; ?>

            <h1><?= eAdmin($titulo) ?></h1>

            <?php if (!empty($descricao)): ?>
                <p><?= eAdmin($descricao) ?></p>
            <?php endif; ?>

            <?php if (!empty($metas)): ?>
                <div class="admin-meta">
                    <?php foreach ($metas as $meta): ?>
                        <?php if (!empty($meta)): ?>
                            <span><?= eAdmin($meta) ?></span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
        <?php
    }
}

/* =========================================
   FEEDBACK
========================================= */

if (!function_exists('renderAdminFeedback')) {
    function renderAdminFeedback(?string $feedback): void
    {
        if (empty($feedback)) {
            return;
        }
        ?>
        <div class="feedback">
            <?= eAdmin($feedback) ?>
        </div>
        <?php
    }
}

/* =========================================
   TÍTULO DE BLOCO
========================================= */

if (!function_exists('renderAdminTituloBloco')) {
    function renderAdminTituloBloco(string $titulo, string $tag = ''): void
    {
        ?>
        <div class="admin-titulo-bloco">
            <h2><?= eAdmin($titulo) ?></h2>

            <?php if (!empty($tag)): ?>
                <span><?= eAdmin($tag) ?></span>
            <?php endif; ?>
        </div>
        <?php
    }
}

/* =========================================
   ABERTURA / FECHAMENTO DE BLOCO
========================================= */

if (!function_exists('abrirAdminPainelBloco')) {
    function abrirAdminPainelBloco(string $classeExtra = ''): void
    {
        $classe = trim('painel-bloco ' . $classeExtra);
        ?>
        <section class="<?= eAdmin($classe) ?>">
        <?php
    }
}

if (!function_exists('fecharAdminPainelBloco')) {
    function fecharAdminPainelBloco(): void
    {
        ?>
        </section>
        <?php
    }
}

if (!function_exists('abrirAdminColuna')) {
    function abrirAdminColuna(string $titulo = '', string $classeExtra = ''): void
    {
        $classe = trim('painel-coluna ' . $classeExtra);
        ?>
        <div class="<?= eAdmin($classe) ?>">
            <?php if (!empty($titulo)): ?>
                <h2><?= eAdmin($titulo) ?></h2>
            <?php endif; ?>
        <?php
    }
}

if (!function_exists('fecharAdminColuna')) {
    function fecharAdminColuna(): void
    {
        ?>
        </div>
        <?php
    }
}

/* =========================================
   CARD DO PAINEL PRINCIPAL
========================================= */

if (!function_exists('renderAdminCard')) {
    function renderAdminCard(
        string $titulo,
        string $descricao,
        string $url,
        string $botao = 'Acessar',
        ?int $total = null,
        string $labelTotal = ''
    ): void {
        ?>
        <article class="bloco-admin">
            <div class="bloco-admin-topo">
                <h2><?= eAdmin($titulo) ?></h2>

                <?php if ($total !== null): ?>
                    <span class="badge-admin">
                        <?= (int)$total ?> <?= eAdmin($labelTotal) ?>
                    </span>
                <?php endif; ?>
            </div>

            <?php if (!empty($descricao)): ?>
                <p><?= eAdmin($descricao) ?></p>
            <?php endif; ?>

            <a href="<?= eAdmin($url) ?>" class="btn-link">
                <?= eAdmin($botao) ?>
            </a>
        </article>
        <?php
    }
}

/* =========================================
   CARD DE RESUMO
========================================= */

if (!function_exists('renderAdminResumoCard')) {
    function renderAdminResumoCard($numero, string $label): void
    {
        ?>
        <div class="resumo-card">
            <strong><?= eAdmin($numero) ?></strong>
            <span><?= eAdmin($label) ?></span>
        </div>
        <?php
    }
}

/* =========================================
   CAMPO DE PESQUISA
========================================= */

if (!function_exists('renderAdminPesquisa')) {
    function renderAdminPesquisa(
        string $inputId,
        string $placeholder,
        string $classeWrapper = 'pesquisa-admin',
        string $label = 'Pesquisar'
    ): void {
        ?>
        <div class="<?= eAdmin($classeWrapper) ?>">
            <label class="label-pesquisa" for="<?= eAdmin($inputId) ?>">
                <?= eAdmin($label) ?>
            </label>

            <input
                type="text"
                id="<?= eAdmin($inputId) ?>"
                placeholder="<?= eAdmin($placeholder) ?>"
                class="input-pesquisa"
                autocomplete="off"
                data-admin-filter
            >
        </div>
        <?php
    }
}

/* =========================================
   BOTÃO / LINK DE AÇÃO
========================================= */

if (!function_exists('renderAdminBotaoLink')) {
    function renderAdminBotaoLink(string $url, string $label, string $classe = 'btn-link'): void
    {
        ?>
        <a href="<?= eAdmin($url) ?>" class="<?= eAdmin($classe) ?>">
            <?= eAdmin($label) ?>
        </a>
        <?php
    }
}

/* =========================================
   LINKS FINAIS
========================================= */

if (!function_exists('renderAdminLinksRodape')) {
    function renderAdminLinksRodape(bool $mostrarVoltarPainel = true, bool $mostrarVerSite = false): void
    {
        ?>
        <nav class="link-sair">
            <?php if ($mostrarVerSite): ?>
                <a href="../index.php">Ver Site</a>
                <span>|</span>
            <?php endif; ?>

            <a href="logout.php">Sair do Painel</a>

            <?php if ($mostrarVoltarPainel): ?>
                <span>|</span>
                <a href="admin.php">Voltar ao Painel Principal</a>
            <?php endif; ?>
        </nav>
        <?php
    }
}

/* =========================================
   LINHA DE TABELA VAZIA
========================================= */

if (!function_exists('renderAdminTabelaVazia')) {
    function renderAdminTabelaVazia(int $colspan, string $mensagem = 'Nenhum registro encontrado.'): void
    {
        ?>
        <tr>
            <td colspan="<?= (int)$colspan ?>" class="tabela-vazia">
                <?= eAdmin($mensagem) ?>
            </td>
        </tr>
        <?php
    }
}

/* =========================================
   IMAGEM PEQUENA / PREVIEW
========================================= */

if (!function_exists('renderAdminImagemPreview')) {
    function renderAdminImagemPreview(
        ?string $src,
        string $alt = 'Imagem',
        string $classe = 'admin-img-preview',
        string $fallback = '../assets/images/escudo_padrao.png'
    ): void {
        $imagem = $src;

        if (function_exists('caminhoImagemAdmin')) {
            $imagem = caminhoImagemAdmin($src, $fallback);
        } elseif (empty($src)) {
            $imagem = $fallback;
        } else {
            $imagem = $src;
        }

        ?>
        <img
            src="<?= eAdmin($imagem) ?>"
            alt="<?= eAdmin($alt) ?>"
            class="<?= eAdmin($classe) ?>"
            loading="lazy"
            onerror="this.onerror=null; this.src='<?= eAdmin($fallback) ?>';"
        >
        <?php
    }
}

/* =========================================
   BADGE
========================================= */

if (!function_exists('renderAdminBadge')) {
    function renderAdminBadge(string $texto, string $classeExtra = ''): void
    {
        $classe = trim('badge-admin ' . $classeExtra);
        ?>
        <span class="<?= eAdmin($classe) ?>">
            <?= eAdmin($texto) ?>
        </span>
        <?php
    }
}

/* =========================================
   MODAL
========================================= */

if (!function_exists('abrirAdminModal')) {
    function abrirAdminModal(string $id, string $titulo): void
    {
        ?>
        <div id="<?= eAdmin($id) ?>" class="modal">
            <div class="modal-content">
                <span class="close" data-modal-close="<?= eAdmin($id) ?>">&times;</span>
                <h2><?= eAdmin($titulo) ?></h2>
        <?php
    }
}

if (!function_exists('fecharAdminModal')) {
    function fecharAdminModal(): void
    {
        ?>
            </div>
        </div>
        <?php
    }
}

/* =========================================
   OPTION SIMPLES
========================================= */

if (!function_exists('renderAdminOption')) {
    function renderAdminOption($valor, string $label, $valorAtual = null): void
    {
        $selected = (string)$valor === (string)$valorAtual ? 'selected' : '';
        ?>
        <option value="<?= eAdmin($valor) ?>" <?= $selected ?>>
            <?= eAdmin($label) ?>
        </option>
        <?php
    }
}

/* =========================================
   SELECT COM ARRAY CHAVE => LABEL
========================================= */

if (!function_exists('renderAdminOptionsAssoc')) {
    function renderAdminOptionsAssoc(array $opcoes, $valorAtual = null): void
    {
        foreach ($opcoes as $valor => $label) {
            renderAdminOption($valor, (string)$label, $valorAtual);
        }
    }
}

/* =========================================
   SELECT COM LISTA DE REGISTROS
========================================= */

if (!function_exists('renderAdminOptionsRegistros')) {
    function renderAdminOptionsRegistros(
        array $registros,
        $valorAtual = null,
        string $valueKey = 'id',
        string $labelKey = 'nome'
    ): void {
        foreach ($registros as $registro) {
            $valor = $registro[$valueKey] ?? '';
            $label = $registro[$labelKey] ?? '';
            renderAdminOption($valor, (string)$label, $valorAtual);
        }
    }
}

/* =========================================
   OPTIONS DE FASES AGRUPADAS
========================================= */

if (!function_exists('renderAdminOptionsFases')) {
    function renderAdminOptionsFases(array $fasesAgrupadas, $valorAtual = null): void
    {
        foreach ($fasesAgrupadas as $grupo => $opcoes) {
            ?>
            <optgroup label="<?= eAdmin($grupo) ?>">
                <?php foreach ($opcoes as $valor => $label): ?>
                    <?php renderAdminOption($valor, (string)$label, $valorAtual); ?>
                <?php endforeach; ?>
            </optgroup>
            <?php
        }
    }
}

/* =========================================
   CAMPO CSRF
========================================= */

if (!function_exists('renderAdminCsrf')) {
    function renderAdminCsrf(): void
    {
        if (function_exists('campoCsrfAdmin')) {
            campoCsrfAdmin();
            return;
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(50));
        }

        ?>
        <input type="hidden" name="csrf_token" value="<?= eAdmin($_SESSION['csrf_token']) ?>">
        <?php
    }
}