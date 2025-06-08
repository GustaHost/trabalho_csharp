using Microsoft.AspNetCore.Builder;
using Microsoft.Extensions.DependencyInjection;
using Microsoft.Extensions.Hosting;

var builder = WebApplication.CreateBuilder(args);

// Adiciona os serviços de controllers (API)
builder.Services.AddControllers();

// ✅ Configura política de CORS para permitir acesso externo (ex: PHP)
builder.Services.AddCors(options =>
{
    options.AddPolicy("AllowAll", policy =>
    {
        policy.AllowAnyOrigin()
              .AllowAnyMethod()
              .AllowAnyHeader();
    });
});

var app = builder.Build();

// ✅ Aplica o CORS
app.UseCors("AllowAll");

// Ativa o roteamento de controllers (API REST)
app.MapControllers();

app.Run();



// using System;
// using TodoList.Controllers;
// using TodoList.Data;

// namespace TodoList
// {
//     class Program
//     {
//         static void Main(string[] args)
//         {
//             BancoDados.CarregarDados();

//             var userAccess = new UserAccessController();
//             string tipoUsuario = null;
//             while (tipoUsuario == null)
//             {
//                 tipoUsuario = userAccess.SelecionarTipoUsuario();
//                 if (tipoUsuario == null)
//                 {
//                     Console.WriteLine("Opção inválida. Tente novamente.");
//                 }
//             }

//             Console.Write("Digite seu nome de usuário: ");
//             string nomeUsuario = Console.ReadLine();

//             var menu = new MenuController();
//             var getTarefas = new GetTarefasController();
//             var postTarefa = new PostTarefaController();
//             var deleteTarefa = new DeleteTarefaController();
//             var updateTarefa = new UpdateTaskController();

//             bool continuar = true;

//             while (continuar)
//             {
//                 menu.MostrarMenu(tipoUsuario);
//                 string opcao = Console.ReadLine();

//                 switch (opcao)
//                 {
//                     case "1":  // Listar tarefas
//                         var tarefas = getTarefas.ListarTarefas(tipoUsuario, nomeUsuario);
//                         Console.Clear();
//                         Console.WriteLine("== Tarefas ==");
//                         foreach (var t in tarefas)
//                         {
//                             Console.WriteLine($"ID: {t.Id} | Título: {t.Titulo} | Status: {(t.Status ? "Concluído" : "Pendente")} | Criado em: {t.CriadoEm} | Concluído em: {(t.ConcluidoEm.HasValue ? t.ConcluidoEm.ToString() : "não concluído")} | Usuário: {t.Usuario}");
//                         }
//                         Console.WriteLine("\nPressione ENTER para voltar...");
//                         Console.ReadLine();
//                         break;

//                     case "2":  // Criar tarefa
//                         Console.Write("Digite o título da tarefa: ");
//                         string titulo = Console.ReadLine();
//                         postTarefa.CriarTarefa(titulo, nomeUsuario);
//                         Console.WriteLine("Tarefa criada com sucesso!");
//                         Console.WriteLine("Pressione ENTER para voltar...");
//                         Console.ReadLine();
//                         break;

//                     case "3":  // Apagar tarefa
//                         Console.Write("Digite o ID da tarefa que deseja apagar: ");
//                         if (int.TryParse(Console.ReadLine(), out int idApagar))
//                         {
//                             bool apagou = deleteTarefa.ApagarTarefa(idApagar, tipoUsuario, nomeUsuario);
//                             if (apagou)
//                                 Console.WriteLine("Tarefa apagada com sucesso!");
//                             else
//                                 Console.WriteLine("Não foi possível apagar a tarefa (não encontrada ou sem permissão).");
//                         }
//                         else
//                         {
//                             Console.WriteLine("ID inválido.");
//                         }
//                         Console.WriteLine("Pressione ENTER para voltar...");
//                         Console.ReadLine();
//                         break;

//                     case "4":  // Atualizar status da tarefa
//                         Console.Write("Digite o ID da tarefa que deseja atualizar o status: ");
//                         if (int.TryParse(Console.ReadLine(), out int idAtualizar))
//                         {
//                             bool atualizou = updateTarefa.AtualizarStatus(idAtualizar, tipoUsuario, nomeUsuario);
//                             if (atualizou)
//                                 Console.WriteLine("Status da tarefa atualizado com sucesso!");
//                             else
//                                 Console.WriteLine("Não foi possível atualizar o status da tarefa (não encontrada ou sem permissão).");
//                         }
//                         else
//                         {
//                             Console.WriteLine("ID inválido.");
//                         }
//                         Console.WriteLine("Pressione ENTER para voltar...");
//                         Console.ReadLine();
//                         break;

//                     case "0":
//                         continuar = false;
//                         break;

//                     default:
//                         Console.WriteLine("Opção inválida. Tente novamente.");
//                         Console.WriteLine("Pressione ENTER para voltar...");
//                         Console.ReadLine();
//                         break;
//                 }
//             }

//             Console.WriteLine("Obrigado por usar o TodoApp!");
//         }
//     }
// }
