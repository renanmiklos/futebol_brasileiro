<?php
/* =========================================
   ADMIN-FUNCOES.PHP
   Funções auxiliares gerais do Painel Admin
   Futebol Brasileiro
========================================= */

/*
  Este arquivo deve ser usado nas páginas internas do admin
  para evitar repetição de funções comuns.

  Uso recomendado:
  require_once __DIR__ . '/includes-admin/admin-auth.php';
  require_once __DIR__ . '/includes-admin/admin-funcoes.php';
*/

/* =========================================
   ESCAPE HTML
========================================= */

if (!function_exists('eAdmin')) {
    function eAdmin($valor): string
    {
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
    }
}

/* =========================================
   FLASH MESSAGES
========================================= */

if (!function_exists('setFlashAdmin')) {
    function setFlashAdmin(string $chave, string $mensagem): void
    {
        $_SESSION[$chave] = $mensagem;
    }
}

if (!function_exists('getFlashAdmin')) {
    function getFlashAdmin(string $chave = 'sucesso'): ?string
    {
        if (empty($_SESSION[$chave])) {
            return null;
        }

        $mensagem = (string)$_SESSION[$chave];
        unset($_SESSION[$chave]);

        return $mensagem;
    }
}

/* =========================================
   REDIRECIONAMENTO
========================================= */

if (!function_exists('redirectAdmin')) {
    function redirectAdmin(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }
}

/* =========================================
   FORMATAÇÃO DE DATAS
========================================= */

if (!function_exists('formatarDataAdmin')) {
    function formatarDataAdmin($data, string $formato = 'd/m/Y'): string
    {
        if (empty($data)) {
            return 'Não informado';
        }

        $timestamp = strtotime((string)$data);

        if (!$timestamp) {
            return 'Não informado';
        }

        return date($formato, $timestamp);
    }
}

if (!function_exists('formatarDataHoraAdmin')) {
    function formatarDataHoraAdmin($data): string
    {
        return formatarDataAdmin($data, 'd/m/Y H:i');
    }
}

/* =========================================
   TEXTO / RESUMO
========================================= */

if (!function_exists('resumirTextoAdmin')) {
    function resumirTextoAdmin($texto, int $limite = 120): string
    {
        $texto = trim(strip_tags((string)$texto));
        $texto = preg_replace('/\s+/', ' ', $texto);

        if ($texto === '') {
            return '';
        }

        if (mb_strlen($texto, 'UTF-8') <= $limite) {
            return $texto;
        }

        return mb_substr($texto, 0, $limite, 'UTF-8') . '...';
    }
}

/* =========================================
   NÚMEROS
========================================= */

if (!function_exists('formatarNumeroAdmin')) {
    function formatarNumeroAdmin($valor): string
    {
        if (!is_numeric($valor)) {
            return '0';
        }

        return number_format((float)$valor, 0, '', '.');
    }
}

if (!function_exists('valorIntAdmin')) {
    function valorIntAdmin($valor, int $padrao = 0): int
    {
        if ($valor === null || $valor === '') {
            return $padrao;
        }

        return is_numeric($valor) ? (int)$valor : $padrao;
    }
}

/* =========================================
   BOOLEANOS
========================================= */

if (!function_exists('labelBooleanoAdmin')) {
    function labelBooleanoAdmin($valor): string
    {
        return (int)$valor === 1 ? 'Sim' : 'Não';
    }
}

/* =========================================
   CAMINHOS DE IMAGEM
========================================= */

if (!function_exists('caminhoImagemAdmin')) {
    function caminhoImagemAdmin($caminho, string $fallback = '../assets/images/escudo_padrao.png'): string
    {
        if (empty($caminho)) {
            return $fallback;
        }

        $caminho = trim((string)$caminho);

        if (
            str_starts_with($caminho, 'http://') ||
            str_starts_with($caminho, 'https://') ||
            str_starts_with($caminho, 'data:')
        ) {
            return eAdmin($caminho);
        }

        return '../' . eAdmin(ltrim($caminho, '/'));
    }
}

/* =========================================
   JSON PARA AJAX
========================================= */

if (!function_exists('responderJsonAdmin')) {
    function responderJsonAdmin(array $dados): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($dados, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

/* =========================================
   VALIDAÇÃO DE REQUEST
========================================= */

if (!function_exists('isPostAdmin')) {
    function isPostAdmin(): bool
    {
        return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
    }
}

if (!function_exists('postAdmin')) {
    function postAdmin(string $campo, $padrao = null)
    {
        return $_POST[$campo] ?? $padrao;
    }
}

if (!function_exists('postTextoAdmin')) {
    function postTextoAdmin(string $campo, $padrao = null)
    {
        $valor = $_POST[$campo] ?? $padrao;

        if ($valor === null) {
            return null;
        }

        $valor = trim((string)$valor);

        return $valor === '' ? null : $valor;
    }
}

if (!function_exists('postIntAdmin')) {
    function postIntAdmin(string $campo, $padrao = null)
    {
        $valor = filter_input(INPUT_POST, $campo, FILTER_VALIDATE_INT);

        return $valor === false || $valor === null ? $padrao : $valor;
    }
}

/* =========================================
   TOKEN CSRF
========================================= */

if (!function_exists('gerarCsrfAdmin')) {
    function gerarCsrfAdmin(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(50));
        }

        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('validarCsrfAdmin')) {
    function validarCsrfAdmin(?string $token): bool
    {
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }

        return hash_equals((string)$_SESSION['csrf_token'], (string)$token);
    }
}

if (!function_exists('campoCsrfAdmin')) {
    function campoCsrfAdmin(): void
    {
        ?>
        <input type="hidden" name="csrf_token" value="<?= eAdmin(gerarCsrfAdmin()) ?>">
        <?php
    }
}

/* =========================================
   FEEDBACK HTML
========================================= */

if (!function_exists('renderFeedbackAdmin')) {
    function renderFeedbackAdmin(?string $feedback): void
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
   LINKS DO RODAPÉ INTERNO
========================================= */

if (!function_exists('renderLinksAdmin')) {
    function renderLinksAdmin(bool $mostrarVoltarPainel = true): void
    {
        ?>
        <p class="link-sair">
            <a href="logout.php">Sair do Painel</a>

            <?php if ($mostrarVoltarPainel): ?>
                <span>|</span>
                <a href="admin.php">Voltar ao Painel Principal</a>
            <?php endif; ?>
        </p>
        <?php
    }
}

/* =========================================
   OPTIONS HTML
========================================= */

if (!function_exists('optionSelecionadaAdmin')) {
    function optionSelecionadaAdmin($valorAtual, $valorOption): string
    {
        return (string)$valorAtual === (string)$valorOption ? 'selected' : '';
    }
}

if (!function_exists('renderOptionsAdmin')) {
    function renderOptionsAdmin(array $itens, $valorAtual = null, string $valueKey = 'id', string $labelKey = 'nome'): void
    {
        foreach ($itens as $item) {
            $value = $item[$valueKey] ?? '';
            $label = $item[$labelKey] ?? '';

            ?>
            <option value="<?= eAdmin($value) ?>" <?= optionSelecionadaAdmin($valorAtual, $value) ?>>
                <?= eAdmin($label) ?>
            </option>
            <?php
        }
    }
}

/* =========================================
   FASES DE CLASSIFICAÇÃO
========================================= */

if (!function_exists('renderOptionsFasesAdmin')) {
    function renderOptionsFasesAdmin(array $fases, $valorAtual = null): void
    {
        foreach ($fases as $grupo => $opcoes) {
            ?>
            <optgroup label="<?= eAdmin($grupo) ?>">
                <?php foreach ($opcoes as $valor => $label): ?>
                    <option value="<?= eAdmin($valor) ?>" <?= optionSelecionadaAdmin($valorAtual, $valor) ?>>
                        <?= eAdmin($label) ?>
                    </option>
                <?php endforeach; ?>
            </optgroup>
            <?php
        }
    }
}

/* =========================================
   MODAL
========================================= */

if (!function_exists('abrirModalAdmin')) {
    function abrirModalAdmin(string $id, string $titulo): void
    {
        ?>
        <div id="<?= eAdmin($id) ?>" class="modal">
            <div class="modal-content">
                <span class="close" data-modal-close="<?= eAdmin($id) ?>">&times;</span>
                <h2><?= eAdmin($titulo) ?></h2>
        <?php
    }
}

if (!function_exists('fecharModalAdmin')) {
    function fecharModalAdmin(): void
    {
        ?>
            </div>
        </div>
        <?php
    }
}

/* =========================================
   TABELA VAZIA
========================================= */

if (!function_exists('renderLinhaTabelaVaziaAdmin')) {
    function renderLinhaTabelaVaziaAdmin(int $colspan, string $mensagem): void
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
   SLUG SIMPLES
========================================= */

if (!function_exists('gerarSlugAdmin')) {
    function gerarSlugAdmin(string $texto): string
    {
        $texto = trim(mb_strtolower($texto, 'UTF-8'));

        $mapa = [
            'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'ä' => 'a',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
            'ó' => 'o', 'ò' => 'o', 'õ' => 'o', 'ô' => 'o', 'ö' => 'o',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'ç' => 'c',
            'ñ' => 'n'
        ];

        $texto = strtr($texto, $mapa);
        $texto = preg_replace('/[^a-z0-9]+/', '-', $texto);
        $texto = trim($texto, '-');

        return $texto ?: 'item';
    }
}