-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 05-Mar-2026 às 20:53
-- Versão do servidor: 10.4.18-MariaDB
-- versão do PHP: 8.0.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `escola_gestao`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `alunos`
--

CREATE TABLE `alunos` (
  `id` int(11) NOT NULL,
  `nome_completo` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_aluno` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `senha_acesso` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_nascimento` date DEFAULT NULL,
  `genero` enum('M','F','Outro') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `turma_id` int(11) DEFAULT NULL,
  `responsavel_nome` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `responsavel_telefone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `responsavel_email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `morada` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` enum('ativo','inativo','transferido') COLLATE utf8mb4_unicode_ci DEFAULT 'ativo',
  `data_matricula` date DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `alunos`
--

INSERT INTO `alunos` (`id`, `nome_completo`, `email_aluno`, `senha_acesso`, `data_nascimento`, `genero`, `foto`, `turma_id`, `responsavel_nome`, `responsavel_telefone`, `responsavel_email`, `morada`, `estado`, `data_matricula`, `criado_em`) VALUES
(2, 'Bernardo Silva', 'joao@escola.com', '$2y$10$sjAI6AQgHbDbeQguoHttgeX2.g60gJmISxG4jVRDT.4Qc5Q2rcxZ6', '2008-05-15', 'M', NULL, 1, 'João Silva', '923000111', 'joao@escola.ao', 'Brasil', 'ativo', '2026-03-03', '2026-03-03 20:43:27'),
(3, 'Ana Paula Santos', 'ana@escola.ao', '$2y$10$MNPp7KjKnE.NdmN1faxtK.QIJqmAQFjKMefqALJur7LZL0jyml1F.', '2007-11-20', 'F', NULL, 1, 'Clara Santos', '923000222', 'ana@escola.com', 'Angola', 'ativo', '2026-03-03', '2026-03-03 20:43:27'),
(4, 'Ricardo Jorge', 'rricardo@escola.ao', '$2y$10$S3.qKeL/ClZDprYM27OJFuCvzHAJpXmYbJtZrDga4eRlvFJqw1UbS', '2006-02-10', 'M', NULL, 2, 'Manuel Jorge', '923000333', 'manuel@escola.com', 'Angola', 'ativo', '2026-03-03', '2026-03-03 20:43:27'),
(5, 'Filomena André', 'filomena@escola.ao', '$2y$10$AnVph2QLfF8RxY9g3rWeV.XNtRpvZaP/Shj6UQp30HCWWGbZrGJCK', '2005-09-30', 'F', NULL, 3, 'Teresa André', '923000444', 'teresa@gmail.com', 'Brasil', 'ativo', '2026-03-03', '2026-03-03 20:43:27'),
(6, 'Gaspar Manuel', 'manuel@escola.ao', '$2y$10$PKXkMPlJomwT4dLzfURPQeQnMo49CCjjFEqjWNt8qlB1O22w95zWG', '2007-01-12', 'M', NULL, 2, 'Isabel Manuel', '923000555', 'isa@gamil.com', 'Angola', 'ativo', '2026-03-03', '2026-03-03 20:43:27'),
(7, 'Rodrigues Pongolola', 'rodriguespongolola47@gmail.ao', '$2y$10$/F6OmRS2bv0DxKlPPmsJI.HkoHu.y345bN4IMd657YUQzc7PPcnvm', '1994-10-12', 'M', NULL, 5, 'Rodrigues Pongolola', '930 581 053', 'rodriguespongolola47@gmail.com', 'Angola', 'ativo', '2026-03-03', '2026-03-03 20:48:32');

-- --------------------------------------------------------

--
-- Estrutura da tabela `atividades`
--

CREATE TABLE `atividades` (
  `id` int(11) NOT NULL,
  `titulo` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo` enum('evento','reuniao','feriado','exame','outro') COLLATE utf8mb4_unicode_ci DEFAULT 'evento',
  `cor` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT '#0d6efd',
  `data_inicio` datetime NOT NULL,
  `data_fim` datetime DEFAULT NULL,
  `local_atividade` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `participantes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notificar` tinyint(1) DEFAULT 0,
  `criado_por` int(11) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `atividades`
--

INSERT INTO `atividades` (`id`, `titulo`, `descricao`, `tipo`, `cor`, `data_inicio`, `data_fim`, `local_atividade`, `participantes`, `notificar`, `criado_por`, `criado_em`) VALUES
(1, 'Nota Informativa!', 'O que mudou com a tua tabela real:\r\nutilizador_id: Agora o filtro busca mensagens para o aluno logado ou mensagens gerais (IS NULL).\r\ncriado_em: Ajustado o nome da coluna de data.\r\ntipo: O painel agora reage visualmente. Por exemplo, se for uma notificação de erro (ex: falta de pagamento), ela aparece com borda vermelha. Se for sucesso (ex: nota lançada), aparece verde.\r\nlida: Adicionei uma pequena \"bolinha\" colorida que aparece se a notificação ainda não tiver sido lida pelo aluno.\r\nDeseja que eu crie a funcionalidade para o aluno clicar e marcar como lida (mudar o lida para 1 no banco de dados)?', 'outro', '#f5490f', '2026-03-19 10:30:00', '2026-03-19 15:30:00', 'Escola Visão do Futuro', NULL, 1, 1, '2026-03-04 23:27:23');

-- --------------------------------------------------------

--
-- Estrutura da tabela `disciplinas`
--

CREATE TABLE `disciplinas` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `disciplinas`
--

INSERT INTO `disciplinas` (`id`, `nome`) VALUES
(1, 'Língua Portuguesa'),
(2, 'Matemática'),
(3, 'Ciências da Natureza'),
(4, 'História'),
(5, 'Geografia'),
(6, 'Educação Moral e Cívica'),
(7, 'Educação Física'),
(8, 'Educação Visual e Plástica'),
(9, 'Educação Musical'),
(10, 'Língua Inglesa'),
(11, 'Língua Francesa'),
(12, 'Física'),
(13, 'Química'),
(14, 'Biologia'),
(15, 'Empreendedorismo'),
(16, 'Informática'),
(17, 'Educação Laboral'),
(18, 'Estudo do Meio');

-- --------------------------------------------------------

--
-- Estrutura da tabela `escola_config`
--

CREATE TABLE `escola_config` (
  `id` int(11) NOT NULL,
  `chave` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo` enum('texto','numero','cor','imagem','boolean','json') COLLATE utf8mb4_unicode_ci DEFAULT 'texto',
  `grupo` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'geral',
  `descricao` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `escola_config`
--

INSERT INTO `escola_config` (`id`, `chave`, `valor`, `tipo`, `grupo`, `descricao`, `atualizado_em`) VALUES
(1, 'escola_nome', 'Colégio Visão do Futuro', 'texto', 'identidade', 'Nome da escola', '2026-03-04 09:28:29'),
(2, 'escola_slogan', 'Investindo no seu amanhã', 'texto', 'identidade', 'Slogan da escola', '2026-03-04 09:28:29'),
(3, 'escola_logo', 'uploads/logos/logo_1772731960.jpg', 'imagem', 'identidade', 'Logo da escola', '2026-03-05 17:32:40'),
(4, 'escola_favicon', '', 'imagem', 'identidade', 'Favicon do site', '2026-03-03 19:33:53'),
(5, 'cor_primaria', '#1a3a5c', 'cor', 'visual', 'Cor primária do sistema', '2026-03-03 19:33:53'),
(6, 'cor_secundaria', '#e8a020', 'cor', 'visual', 'Cor secundária/accent', '2026-03-03 19:33:53'),
(7, 'cor_sidebar', '#0f2540', 'cor', 'visual', 'Cor da barra lateral', '2026-03-03 19:33:53'),
(8, 'cor_texto_sidebar', '#ffffff', 'cor', 'visual', 'Cor do texto na sidebar', '2026-03-03 19:33:53'),
(9, 'escola_telefone', '+244 924 100 068', 'texto', 'contacto', 'Telefone principal', '2026-03-04 09:29:46'),
(10, 'escola_email', 'info@escolanova.ao', 'texto', 'contacto', 'Email da escola', '2026-03-03 19:33:53'),
(11, 'escola_website', 'www.escolanova.ao', 'texto', 'contacto', 'Website', '2026-03-03 19:33:53'),
(12, 'escola_morada', 'Luanda, Angola', 'texto', 'localizacao', 'Morada completa', '2026-03-03 19:33:53'),
(13, 'escola_provincia', 'Luanda', 'texto', 'localizacao', 'Província', '2026-03-03 19:33:53'),
(14, 'escola_pais', 'Angola', 'texto', 'localizacao', 'País', '2026-03-03 19:33:53'),
(15, 'escola_latitude', '-8.8147', 'numero', 'localizacao', 'Latitude GPS', '2026-03-03 19:33:53'),
(16, 'escola_longitude', '13.2302', 'numero', 'localizacao', 'Longitude GPS', '2026-03-03 19:33:53'),
(17, 'propina_valor', '15000', 'numero', 'financas', 'Valor padrão da propina mensal (Kz)', '2026-03-03 19:33:53'),
(18, 'ano_letivo_atual', '2024/2025', 'texto', 'academico', 'Ano letivo atual', '2026-03-03 19:33:53'),
(19, 'notificacoes_email', '1', 'boolean', 'sistema', 'Enviar notificações por email', '2026-03-03 19:33:53'),
(20, 'manutencao_modo', '0', 'boolean', 'sistema', 'Modo de manutenção ativo', '2026-03-03 19:33:53'),
(23, 'ano_letivo', '2026/2027', 'texto', 'geral', NULL, '2026-03-04 09:28:29');

-- --------------------------------------------------------

--
-- Estrutura da tabela `financas_categorias`
--

CREATE TABLE `financas_categorias` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('receita','despesa') COLLATE utf8mb4_unicode_ci NOT NULL,
  `cor` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT '#6c757d',
  `ativo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `financas_categorias`
--

INSERT INTO `financas_categorias` (`id`, `nome`, `tipo`, `cor`, `ativo`) VALUES
(1, 'Propinas', 'receita', '#198754', 1),
(2, 'Matrículas', 'receita', '#0d6efd', 1),
(3, 'Donativos', 'receita', '#6f42c1', 1),
(4, 'Salários', 'despesa', '#dc3545', 1),
(5, 'Material Escolar', 'despesa', '#fd7e14', 1),
(6, 'Manutenção', 'despesa', '#6c757d', 1),
(7, 'Água e Energia', 'despesa', '#0dcaf0', 1),
(8, 'Outros', 'despesa', '#adb5bd', 1);

-- --------------------------------------------------------

--
-- Estrutura da tabela `financas_transacoes`
--

CREATE TABLE `financas_transacoes` (
  `id` int(11) NOT NULL,
  `descricao` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `tipo` enum('receita','despesa') COLLATE utf8mb4_unicode_ci NOT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `data_transacao` date NOT NULL,
  `aluno_id` int(11) DEFAULT NULL,
  `estado` enum('pendente','pago','cancelado') COLLATE utf8mb4_unicode_ci DEFAULT 'pendente',
  `observacoes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `criado_por` int(11) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `fin_categorias`
--

CREATE TABLE `fin_categorias` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('receita','despesa') COLLATE utf8mb4_unicode_ci NOT NULL,
  `cor` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '#3498db',
  `ativo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `fin_categorias`
--

INSERT INTO `fin_categorias` (`id`, `nome`, `tipo`, `cor`, `ativo`) VALUES
(1, 'Mensalidade', 'receita', '#10b981', 1),
(2, 'Inscrição', 'receita', '#3b82f6', 1),
(3, 'Salários', 'despesa', '#ef4444', 1),
(4, 'Material Escolar', 'despesa', '#f59e0b', 1);

-- --------------------------------------------------------

--
-- Estrutura da tabela `fin_transacoes`
--

CREATE TABLE `fin_transacoes` (
  `id` int(11) NOT NULL,
  `descricao` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` decimal(15,2) NOT NULL,
  `tipo` enum('receita','despesa') COLLATE utf8mb4_unicode_ci NOT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `aluno_id` int(11) DEFAULT NULL,
  `data_transacao` date NOT NULL,
  `estado` enum('pago','pendente','cancelado') COLLATE utf8mb4_unicode_ci DEFAULT 'pago',
  `observacoes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `criado_por` int(11) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `fin_transacoes`
--

INSERT INTO `fin_transacoes` (`id`, `descricao`, `valor`, `tipo`, `categoria_id`, `aluno_id`, `data_transacao`, `estado`, `observacoes`, `criado_por`, `criado_em`) VALUES
(1, 'Propina de Abril Ana Paula', '13000.00', 'receita', 1, 3, '2026-03-03', 'pago', 'bom...', 1, '2026-03-03 21:20:59'),
(3, 'Propina de Março Rodrigues', '15000.00', 'receita', 1, 7, '2026-03-04', 'pago', 'melhor aluno...', 1, '2026-03-04 09:49:52');

-- --------------------------------------------------------

--
-- Estrutura da tabela `modulos`
--

CREATE TABLE `modulos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'fas fa-puzzle-piece',
  `ativo` tinyint(1) DEFAULT 1,
  `ordem` int(11) DEFAULT 0,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `modulos`
--

INSERT INTO `modulos` (`id`, `nome`, `slug`, `descricao`, `icone`, `ativo`, `ordem`, `criado_em`) VALUES
(1, 'Gestão de Alunos', 'alunos', 'Cadastro e gestão completa dos alunos', 'fas fa-user-graduate', 1, 1, '2026-03-03 19:33:53'),
(2, 'Gestão de Turmas', 'turmas', 'Criação e organização de turmas', 'fas fa-chalkboard', 1, 2, '2026-03-03 19:33:53'),
(3, 'Finanças', 'financas', 'Controlo de propinas e despesas', 'fas fa-coins', 1, 3, '2026-03-03 19:33:53'),
(4, 'Agenda de Atividades', 'agenda', 'Calendário e eventos escolares', 'fas fa-calendar-alt', 1, 4, '2026-03-03 19:33:53'),
(5, 'Notificações', 'notificacoes', 'Sistema de alertas e avisos', 'fas fa-bell', 1, 5, '2026-03-03 19:33:53'),
(6, 'Relatórios', 'relatorios', 'Geração de relatórios e estatísticas', 'fas fa-chart-bar', 1, 6, '2026-03-03 19:33:53'),
(7, 'Biblioteca', 'biblioteca', 'Gestão de livros e recursos', 'fas fa-book', 0, 7, '2026-03-03 19:33:53'),
(8, 'Transporte Escolar', 'transporte', 'Gestão de rotas e transporte', 'fas fa-bus', 0, 8, '2026-03-03 19:33:53'),
(9, 'Portal de Pais', 'portal_pais', 'Acesso dos encarregados de educação', 'fas fa-users', 1, 9, '2026-03-03 19:33:53');

-- --------------------------------------------------------

--
-- Estrutura da tabela `notas`
--

CREATE TABLE `notas` (
  `id` int(11) NOT NULL,
  `aluno_id` int(11) NOT NULL,
  `turma_id` int(11) NOT NULL,
  `disciplina_id` int(11) DEFAULT NULL,
  `professor_id` int(11) DEFAULT NULL,
  `nota` decimal(10,2) DEFAULT 0.00,
  `trimestre` int(1) DEFAULT 1,
  `tipo_avaliacao` varchar(50) DEFAULT 'Prova',
  `data_lancamento` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `notas`
--

INSERT INTO `notas` (`id`, `aluno_id`, `turma_id`, `disciplina_id`, `professor_id`, `nota`, `trimestre`, `tipo_avaliacao`, `data_lancamento`) VALUES
(1, 3, 1, NULL, 2, '10.00', 1, 'Prova', '2026-03-04 11:34:58'),
(2, 2, 1, NULL, 2, '9.00', 1, 'Prova', '2026-03-04 11:34:58'),
(3, 3, 1, NULL, 2, '10.00', 2, 'Prova', '2026-03-04 18:48:45'),
(4, 2, 1, NULL, 2, '9.00', 2, 'Prova', '2026-03-04 18:48:45'),
(5, 3, 1, NULL, 2, '10.00', 2, 'Prova', '2026-03-04 18:48:54'),
(6, 2, 1, NULL, 2, '9.00', 2, 'Prova', '2026-03-04 18:48:54'),
(7, 7, 5, NULL, 5, '20.00', 3, 'Prova', '2026-03-04 19:03:48'),
(8, 3, 1, 14, 7, '10.00', 1, 'Prova', '2026-03-04 19:35:28'),
(9, 2, 1, 14, 7, '9.00', 1, 'Prova', '2026-03-04 19:35:28'),
(10, 3, 1, 14, 7, '17.00', 1, 'Prova', '2026-03-04 20:04:31'),
(11, 2, 1, 14, 7, '14.00', 1, 'Prova', '2026-03-04 20:04:31'),
(12, 3, 1, 7, 7, '10.00', 1, 'Prova', '2026-03-04 20:04:41'),
(13, 2, 1, 7, 7, '12.00', 1, 'Prova', '2026-03-04 20:04:41'),
(14, 3, 1, 14, 7, '10.00', 2, 'Prova', '2026-03-04 20:04:55'),
(15, 2, 1, 14, 7, '11.00', 2, 'Prova', '2026-03-04 20:04:55'),
(16, 3, 1, 7, 7, '19.00', 2, 'Prova', '2026-03-04 20:05:15'),
(17, 2, 1, 7, 7, '18.00', 2, 'Prova', '2026-03-04 20:05:15'),
(18, 6, 2, 1, 9, '11.00', 1, 'Prova', '2026-03-05 12:34:43'),
(19, 4, 2, 1, 9, '15.00', 1, 'Prova', '2026-03-05 12:34:43'),
(20, 6, 2, 2, 9, '13.00', 1, 'Prova', '2026-03-05 12:35:02'),
(21, 4, 2, 2, 9, '14.00', 1, 'Prova', '2026-03-05 12:35:02'),
(22, 6, 2, 9, 9, '11.00', 1, 'Prova', '2026-03-05 12:35:13'),
(23, 4, 2, 9, 9, '14.00', 1, 'Prova', '2026-03-05 12:35:13'),
(24, 6, 2, 6, 9, '17.00', 1, 'Prova', '2026-03-05 12:35:33'),
(25, 4, 2, 6, 9, '15.00', 1, 'Prova', '2026-03-05 12:35:33'),
(26, 6, 2, 3, 9, '12.00', 1, 'Prova', '2026-03-05 12:35:44'),
(27, 4, 2, 3, 9, '14.00', 1, 'Prova', '2026-03-05 12:35:44'),
(28, 6, 2, 2, 9, '10.00', 2, 'Prova', '2026-03-05 17:26:08'),
(29, 4, 2, 2, 9, '11.00', 2, 'Prova', '2026-03-05 17:26:08'),
(30, 6, 2, 1, 9, '11.00', 2, 'Prova', '2026-03-05 17:26:25'),
(31, 4, 2, 1, 9, '11.00', 2, 'Prova', '2026-03-05 17:26:25'),
(32, 6, 2, 9, 9, '13.00', 2, 'Prova', '2026-03-05 17:26:44'),
(33, 4, 2, 9, 9, '14.00', 2, 'Prova', '2026-03-05 17:26:44'),
(34, 6, 2, 6, 9, '11.00', 2, 'Prova', '2026-03-05 17:26:57'),
(35, 4, 2, 6, 9, '10.00', 2, 'Prova', '2026-03-05 17:26:57'),
(36, 6, 2, 3, 9, '11.00', 2, 'Prova', '2026-03-05 17:27:11'),
(37, 4, 2, 3, 9, '19.00', 2, 'Prova', '2026-03-05 17:27:11'),
(38, 6, 2, 3, 9, '11.00', 3, 'Prova', '2026-03-05 17:27:30'),
(39, 4, 2, 3, 9, '12.00', 3, 'Prova', '2026-03-05 17:27:30'),
(40, 6, 2, 6, 9, '11.00', 3, 'Prova', '2026-03-05 17:27:42'),
(41, 4, 2, 6, 9, '14.00', 3, 'Prova', '2026-03-05 17:27:42'),
(42, 6, 2, 9, 9, '13.00', 3, 'Prova', '2026-03-05 17:27:54'),
(43, 4, 2, 9, 9, '15.00', 3, 'Prova', '2026-03-05 17:27:54'),
(44, 6, 2, 9, 9, '11.00', 3, 'Prova', '2026-03-05 17:28:06'),
(45, 4, 2, 9, 9, '18.00', 3, 'Prova', '2026-03-05 17:28:06'),
(46, 6, 2, 1, 9, '11.00', 3, 'Prova', '2026-03-05 17:28:20'),
(47, 4, 2, 1, 9, '12.00', 3, 'Prova', '2026-03-05 17:28:20'),
(48, 6, 2, 2, 9, '16.00', 3, 'Prova', '2026-03-05 17:28:34'),
(49, 4, 2, 2, 9, '17.00', 3, 'Prova', '2026-03-05 17:28:34');

-- --------------------------------------------------------

--
-- Estrutura da tabela `notificacoes`
--

CREATE TABLE `notificacoes` (
  `id` int(11) NOT NULL,
  `titulo` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mensagem` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('info','sucesso','aviso','erro') COLLATE utf8mb4_unicode_ci DEFAULT 'info',
  `lida` tinyint(1) DEFAULT 0,
  `utilizador_id` int(11) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `recuperacao_senhas`
--

CREATE TABLE `recuperacao_senhas` (
  `id` int(11) NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expira` datetime NOT NULL,
  `usado` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `recuperacao_senhas`
--

INSERT INTO `recuperacao_senhas` (`id`, `email`, `token`, `expira`, `usado`) VALUES
(1, 'admin@escola.ao', '0cf9b9f916b0d6bf665a78e947373137d43966dc63c21e7ed0a756eb4f2310a4', '2026-03-03 21:52:21', 0),
(2, 'admin@escola.ao', '7eb74587bb02a7a748944876a9eca6a124054eba5b4a6d5794b01e16d0fc4e6f', '2026-03-03 21:53:34', 1),
(3, 'rodriguespongolola47@gmail.com', '5eb3cb9791c7550d5f7e80adedb6729bf0032d6536d45e4b1c13feef5ef6e2ee', '2026-03-04 23:31:40', 1);

-- --------------------------------------------------------

--
-- Estrutura da tabela `turmas`
--

CREATE TABLE `turmas` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ano_letivo` varchar(9) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `professor_responsavel` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `capacidade` int(11) DEFAULT 30,
  `sala` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `turmas`
--

INSERT INTO `turmas` (`id`, `nome`, `ano_letivo`, `professor_responsavel`, `capacidade`, `sala`, `ativo`, `criado_em`) VALUES
(1, '7.ª Classe A', '2026/2027', 'Arão Rodrigues', 35, 'Sala 06', 1, '2026-03-03 20:34:41'),
(2, '1ª Classe A', '2026/2027', 'Rufina Pngolola', 20, 'Sala 01', 1, '2026-03-03 20:43:27'),
(3, '6ª Classe A', '2026/2027', 'Avelino Pongolola', 30, 'Sala 05', 1, '2026-03-03 20:43:27'),
(4, '5ª Classe A', '2026/2027', 'Domingos Pongolola', 25, 'Sala 02', 1, '2026-03-03 20:43:27'),
(5, '8.ª Classe A', '2026/2027', 'Mario Pongolola', 35, 'Sala 07', 1, '2026-03-03 22:25:57');

-- --------------------------------------------------------

--
-- Estrutura da tabela `turmas_disciplinas`
--

CREATE TABLE `turmas_disciplinas` (
  `id` int(11) NOT NULL,
  `turma_id` int(11) NOT NULL,
  `disciplina_id` int(11) DEFAULT NULL,
  `professor_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `turmas_disciplinas`
--

INSERT INTO `turmas_disciplinas` (`id`, `turma_id`, `disciplina_id`, `professor_id`) VALUES
(2, 1, NULL, 1),
(3, 1, NULL, 1),
(11, 1, 1, 1),
(12, 1, 2, 1),
(13, 1, 3, 1),
(14, 1, 4, 1),
(15, 1, 5, 1),
(16, 1, 6, 1),
(17, 1, 7, 1),
(18, 1, 8, 1),
(19, 1, 9, 1),
(20, 1, 10, 1),
(21, 1, 11, 1),
(22, 1, 12, 1),
(23, 1, 13, 1),
(24, 1, 14, 1),
(25, 1, 15, 1),
(26, 1, 16, 1),
(67, 4, 3, 6),
(68, 4, 6, 6),
(69, 4, 9, 6),
(70, 4, 8, 6),
(71, 4, 5, 6),
(72, 4, 4, 6),
(73, 4, 1, 6),
(74, 4, 2, 6),
(75, 1, 14, 7),
(76, 1, 7, 7),
(77, 1, 12, 7),
(78, 1, 5, 7),
(79, 1, 4, 7),
(80, 1, 11, 7),
(81, 1, 1, 7),
(82, 1, 2, 7),
(83, 1, 13, 7),
(84, 5, 12, 5),
(85, 5, 2, 5),
(86, 5, 13, 5),
(87, 3, 3, 8),
(88, 3, 6, 8),
(89, 3, 9, 8),
(90, 3, 8, 8),
(91, 3, 5, 8),
(92, 3, 4, 8),
(93, 3, 1, 8),
(94, 3, 2, 8),
(95, 2, 3, 9),
(96, 2, 6, 9),
(97, 2, 9, 9),
(98, 2, 1, 9),
(99, 2, 2, 9);

-- --------------------------------------------------------

--
-- Estrutura da tabela `utilizadores`
--

CREATE TABLE `utilizadores` (
  `id` int(11) NOT NULL,
  `nome` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `senha` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('admin','gestor','professor','aluno') COLLATE utf8mb4_unicode_ci DEFAULT 'admin',
  `foto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `ultimo_login` timestamp NULL DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `utilizadores`
--

INSERT INTO `utilizadores` (`id`, `nome`, `email`, `senha`, `tipo`, `foto`, `ativo`, `ultimo_login`, `criado_em`) VALUES
(1, 'Administrador', 'admin@escola.ao', '$2y$10$3lLEV8/KDQDMNTk4lI0s2uFvGtclMBmNuM3YNu2KXtXg6YNJMc2yS', 'admin', NULL, 1, '2026-03-05 18:19:37', '2026-03-03 19:33:53'),
(5, 'Mario Pongolola', 'mariopongolola@gmail.com', '$2y$10$SSiikE.HDQImfdAgnG8T6ebfgcb/Ou/OSh631HymX10NYimSKn.Qu', 'professor', NULL, 1, '2026-03-04 19:42:18', '2026-03-03 22:20:45'),
(6, 'Domingos Pongolola', 'domingospongolola@outlook.com', '$2y$10$FpQ6ptNNi60deYcv/XU5Yeh.duiCEw6AuiaDpeRHVVeUf9LrNNDqG', 'professor', NULL, 1, '2026-03-04 19:42:52', '2026-03-04 19:32:00'),
(7, 'Arão Rodrigues', 'rodriguespongolola47@gmail.com', '$2y$10$5oCm4IJz1/tKWO4YuDfPxuoG1UFRrcTnMTU9J3kSHyrfO/6sC7WK2', 'professor', NULL, 1, '2026-03-04 23:39:25', '2026-03-04 19:33:31'),
(8, 'Avelino Pongolola', 'avelino@escola.ao', '$2y$10$nSrbJjS8TDNMSzpxM7Ei8.UAjG14HpzaF3HTAa287SGcDFOF.F2He', 'professor', NULL, 1, NULL, '2026-03-05 12:30:13'),
(9, 'Rufina Pngolola', 'rufina@escola.ao', '$2y$10$i.HZ88m1quIOptnqHsv9eu1opcvlb3LGv460Ptsggkfy8TcKRsuVm', 'professor', NULL, 1, '2026-03-05 18:52:12', '2026-03-05 12:32:28');

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `alunos`
--
ALTER TABLE `alunos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email_aluno` (`email_aluno`);

--
-- Índices para tabela `atividades`
--
ALTER TABLE `atividades`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `disciplinas`
--
ALTER TABLE `disciplinas`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `escola_config`
--
ALTER TABLE `escola_config`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `chave` (`chave`);

--
-- Índices para tabela `financas_categorias`
--
ALTER TABLE `financas_categorias`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `financas_transacoes`
--
ALTER TABLE `financas_transacoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Índices para tabela `fin_categorias`
--
ALTER TABLE `fin_categorias`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `fin_transacoes`
--
ALTER TABLE `fin_transacoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categoria_id` (`categoria_id`),
  ADD KEY `aluno_id` (`aluno_id`);

--
-- Índices para tabela `modulos`
--
ALTER TABLE `modulos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Índices para tabela `notas`
--
ALTER TABLE `notas`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `notificacoes`
--
ALTER TABLE `notificacoes`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `recuperacao_senhas`
--
ALTER TABLE `recuperacao_senhas`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `turmas`
--
ALTER TABLE `turmas`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `turmas_disciplinas`
--
ALTER TABLE `turmas_disciplinas`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `utilizadores`
--
ALTER TABLE `utilizadores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `alunos`
--
ALTER TABLE `alunos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `atividades`
--
ALTER TABLE `atividades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `disciplinas`
--
ALTER TABLE `disciplinas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de tabela `escola_config`
--
ALTER TABLE `escola_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT de tabela `financas_categorias`
--
ALTER TABLE `financas_categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `financas_transacoes`
--
ALTER TABLE `financas_transacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `fin_categorias`
--
ALTER TABLE `fin_categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `fin_transacoes`
--
ALTER TABLE `fin_transacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `modulos`
--
ALTER TABLE `modulos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `notas`
--
ALTER TABLE `notas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT de tabela `notificacoes`
--
ALTER TABLE `notificacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `recuperacao_senhas`
--
ALTER TABLE `recuperacao_senhas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `turmas`
--
ALTER TABLE `turmas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `turmas_disciplinas`
--
ALTER TABLE `turmas_disciplinas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;

--
-- AUTO_INCREMENT de tabela `utilizadores`
--
ALTER TABLE `utilizadores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `financas_transacoes`
--
ALTER TABLE `financas_transacoes`
  ADD CONSTRAINT `financas_transacoes_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `financas_categorias` (`id`) ON DELETE SET NULL;

--
-- Limitadores para a tabela `fin_transacoes`
--
ALTER TABLE `fin_transacoes`
  ADD CONSTRAINT `fin_transacoes_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `fin_categorias` (`id`),
  ADD CONSTRAINT `fin_transacoes_ibfk_2` FOREIGN KEY (`aluno_id`) REFERENCES `alunos` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
