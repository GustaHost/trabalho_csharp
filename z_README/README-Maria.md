using Microsoft.AspNetCore.Builder;
using Microsoft.AspNetCore.Http;
using Microsoft.Extensions.Hosting;
using System;
using System.Collections.Generic;

// Modelo de tarefa
public class TodoTask
{
    public int Id { get; set; }
    public string Titulo { get; set; } = string.Empty;
    public bool Status { get; set; } = false;
    public DateTime CriadoEm { get; set; } = DateTime.Now;
    public DateTime? ConcluidoEm { get; set; } = null;
}

// Simulação de banco de dados
public static class BancoDados
{
    public static List<TodoTask> Tarefas { get; set; } = new List<TodoTask>();
}

// Aplicação ASP.NET minimal API
var builder = WebApplication.CreateBuilder(args);
var app = builder.Build();

// Rota POST para adicionar nova tarefa
app.MapPost("/tarefas", (TodoTask novaTarefa) =>
{
    novaTarefa.Id = BancoDados.Tarefas.Count + 1;
    novaTarefa.CriadoEm = DateTime.Now;

    BancoDados.Tarefas.Add(novaTarefa);

    return Results.Created($"/tarefas/{novaTarefa.Id}", novaTarefa);
});

app.Run();
